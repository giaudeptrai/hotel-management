<?php

namespace App\Services\Client;

use App\Models\Booking;
use App\Models\BookingRoom;
use App\Models\Customer;
use App\Models\Room;
use App\Models\RoomDefinition;
use App\Models\User;
use App\Services\InvoiceService;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\DB;

class ClientBookingService
{
    public function __construct(
        private readonly ClientRoomAvailabilityService $availabilityService,
        private readonly InvoiceService $invoiceService,
    )
    {
    }

    public function getCreatePayload(array $inputs, ?User $user = null): array
    {
        $roomId = isset($inputs['room']) ? (int) $inputs['room'] : 0;
        $checkIn = $inputs['check_in'] ?? now()->addDay()->toDateString();
        $checkOut = $inputs['check_out'] ?? now()->addDays(2)->toDateString();
        $guests = max(1, (int) ($inputs['guests'] ?? 2));
        $nights = max(1, Carbon::parse($checkIn)->diffInDays(Carbon::parse($checkOut)));
        [$checkInDate, $checkOutDate] = $this->availabilityService->resolveStayRange($checkIn, $checkOut);

        $room = $roomId
            ? RoomDefinition::query()->with(['type', 'category', 'amenities'])->find($roomId)
            : null;

        $roomAvailability = null;

        if ($room) {
            $this->availabilityService->applyAvailabilityCountsToRoom($room, $checkInDate, $checkOutDate);
            $this->availabilityService->attachBookableFlag($room);
            $roomAvailability = $this->buildRoomAvailabilitySnapshot($room, $checkInDate, $checkOutDate);
        }

        $customer = $user?->customer;
        $contact = [
            'full_name' => $customer?->full_name ?? $user?->name ?? '',
            'phone' => $customer?->phone ?? '',
            'email' => $customer?->email ?? $user?->email ?? '',
        ];

        return [
            'room' => $room,
            'roomAvailability' => $roomAvailability,
            'contact' => $contact,
            'selection' => [
                'check_in' => $checkIn,
                'check_out' => $checkOut,
                'guests' => $guests,
                'nights' => $nights,
            ],
        ];
    }

    public function createPendingRequest(array $data, ?User $user = null): Booking
    {
        return DB::transaction(function () use ($data, $user) {
            $checkIn = Carbon::parse($data['check_in'])->startOfDay();
            $checkOut = Carbon::parse($data['check_out'])->startOfDay();
            $stayNights = max(1, $checkIn->diffInDays($checkOut));

            $room = RoomDefinition::query()->with(['type', 'category'])->find($data['room_id']);

            if (!$room) {
                throw new Exception('Không tìm thấy hạng phòng bạn vừa chọn.');
            }

            $this->availabilityService->applyAvailabilityCountsToRoom($room, $checkIn, $checkOut);
            $this->availabilityService->attachBookableFlag($room);

            if (!(bool) $room->is_bookable) {
                throw new Exception('Hạng phòng này đã hết chỗ trong khoảng ngày đã chọn.');
            }

            $assignedRoom = Room::query()
                ->where('is_active', true)
                ->where('room_definition_id', $room->id)
                ->whereDoesntHave('bookingRooms.booking', function ($bookingQuery) use ($checkIn, $checkOut) {
                    $this->availabilityService->applyConflictScope($bookingQuery, $checkIn, $checkOut);
                })
                ->orderBy('room_number')
                ->lockForUpdate()
                ->first();

            if (!$assignedRoom) {
                throw new Exception('Hiện không còn phòng cụ thể trống cho hạng phòng này trong ngày đã chọn.');
            }

            if ($user && $user->customer) {
                $customer = $user->customer;
            } elseif ($user) {
                $customer = Customer::query()->firstOrNew(['user_id' => $user->id]);
            } else {
                $customer = Customer::query()->firstOrNew(['phone' => $data['phone']]);
            }

            $customer->full_name = $data['full_name'];
            $customer->phone = $data['phone'];
            $customer->email = $data['email'];
            $customer->save();

            $totalAmount = ((float) $room->base_price) * $stayNights;

            $booking = Booking::create([
                'customer_id' => $customer->id,
                'check_in_expected' => $checkIn,
                'check_out_expected' => $checkOut,
                'total_amount' => $totalAmount,
                'deposit_amount' => 0,
                'status' => 'pending',
                'source' => 'online',
                'special_requests' => $data['special_requests'] ?? null,
                'admin_note' => 'Yêu cầu từ khách online, đã giữ phòng tạm P.' . $assignedRoom->room_number . ', chờ admin xác nhận cọc.',
            ]);

            BookingRoom::create([
                'booking_id' => $booking->id,
                'room_definition_id' => $room->id,
                'room_id' => $assignedRoom->id,
                'price' => $room->base_price,
            ]);

            $this->invoiceService->generateInvoiceForBooking($booking);

            return $booking;
        });
    }

    private function buildRoomAvailabilitySnapshot(RoomDefinition $room, ?Carbon $checkInDate, ?Carbon $checkOutDate): ?array
    {
        if (!$checkInDate || !$checkOutDate) {
            return null;
        }

        // Lấy tất cả phòng vật lý của hạng này
        $allRooms = Room::query()
            ->where('is_active', true)
            ->where('room_definition_id', $room->id)
            ->orderBy('room_number')
            ->get(['id', 'room_number', 'floor', 'status']);

        // Lấy booking_rooms có conflict với khoảng ngày này
        $conflictingBookingRooms = BookingRoom::query()
            ->whereIn('room_id', $allRooms->pluck('id'))
            ->with([
                'booking' => fn($q) => $q
                    ->select(['id', 'booking_code', 'customer_id', 'status', 'check_in_expected', 'check_out_expected'])
                    ->with('customer:id,full_name'),
            ])
            ->whereHas('booking', function ($bookingQuery) use ($checkInDate, $checkOutDate) {
                $this->availabilityService->applyConflictScope($bookingQuery, $checkInDate, $checkOutDate);
            })
            ->get()
            ->groupBy('room_id');

        $occupiedRooms = [];
        $availableRoomNumbers = [];

        foreach ($allRooms as $physicalRoom) {
            $roomBookings = $conflictingBookingRooms->get($physicalRoom->id, collect([]));

            if ($roomBookings->isEmpty()) {
                $availableRoomNumbers[] = (string) $physicalRoom->room_number;
                continue;
            }

            // Lấy booking đầu tiên (sớm nhất)
            $currentBookingRoom = $roomBookings
                ->sortBy(fn ($br) => $br->booking?->check_in_expected)
                ->first();

            if (!$currentBookingRoom || !$currentBookingRoom->booking) {
                $availableRoomNumbers[] = (string) $physicalRoom->room_number;
                continue;
            }

            $booking = $currentBookingRoom->booking;
            $occupiedRooms[] = [
                'room_number' => (string) $physicalRoom->room_number,
                'booking_code' => (string) $booking->booking_code,
                'status' => (string) $booking->status,
                'customer_name' => (string) ($booking->customer?->full_name ?? ''),
                'check_in' => optional($booking->check_in_expected)->format('Y-m-d'),
                'check_out' => optional($booking->check_out_expected)->format('Y-m-d'),
            ];
        }

        return [
            'total_rooms' => $allRooms->count(),
            'available_rooms_count' => count($availableRoomNumbers),
            'occupied_rooms_count' => count($occupiedRooms),
            'available_room_numbers' => $availableRoomNumbers,
            'occupied_rooms' => $occupiedRooms,
        ];
    }
}
