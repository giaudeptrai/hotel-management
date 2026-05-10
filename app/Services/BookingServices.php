<?php

namespace App\Services;

use App\Mail\ClientBookingConfirmedMail;
use App\Models\Booking;
use App\Models\BookingRoom;
use App\Models\BookingService;
use App\Models\Room;
use App\Models\RoomDefinition;
use App\Models\Invoice;
use App\Services\InvoiceService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Exception;

class BookingServices
{
    // ==========================================
    // 1. NHÓM TRUY VẤN & LẤY DỮ LIỆU
    // ==========================================

    public function getAll($filters = [])
    {
        $listStartDate = $filters['list_start_date'] ?? null;
        $listEndDate = $filters['list_end_date'] ?? null;

        $startDate = $listStartDate ? Carbon::parse($listStartDate)->startOfDay() : null;
        $endDate = $listEndDate ? Carbon::parse($listEndDate)->startOfDay() : null;

        if ($startDate && $endDate && $endDate->lessThanOrEqualTo($startDate)) {
            $endDate = $startDate->copy()->addDay();
        }

        return Booking::with(['customer', 'bookingRooms.definition', 'bookingRooms.room', 'invoice'])
            ->when($startDate && $endDate, function ($q) use ($startDate, $endDate) {
                $q->where('check_in_expected', '<', $endDate)
                    ->where('check_out_expected', '>', $startDate);
            })
            ->when($startDate && !$endDate, function ($q) use ($startDate) {
                $q->whereDate('check_in_expected', '>=', $startDate);
            })
            ->when(!$startDate && $endDate, function ($q) use ($endDate) {
                $q->whereDate('check_in_expected', '<=', $endDate);
            })
            ->when($filters['search'] ?? null, function ($q, $search) {
                $q->where(function ($sub) use ($search) {
                    $sub->where('booking_code', 'like', "%{$search}%")
                        ->orWhereHas('customer', function ($customerQuery) use ($search) {
                            $customerQuery->where('full_name', 'like', "%{$search}%")
                                ->orWhere('phone', 'like', "%{$search}%");
                        });
                });
            })
            ->when($filters['status'] ?? null, function ($q, $status) {
                $q->where('status', $status);
            }, function ($q) {
                $q->whereIn('status', ['pending', 'confirmed', 'checked_in']);
            })
            ->when($filters['source'] ?? null, function ($q, $source) {
                $q->where('source', $source);
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();
    }

    public function getHistory($filters = [])
    {
        return Booking::with(['customer', 'bookingRooms.definition', 'bookingRooms.room'])
            ->whereIn('status', ['checked_out', 'cancelled'])
            ->when($filters['search'] ?? null, function ($q, $search) {
                $q->where(function ($sub) use ($search) {
                    $sub->where('booking_code', 'like', "%{$search}%")
                        ->orWhereHas('customer', function ($customerQuery) use ($search) {
                            $customerQuery->where('full_name', 'like', "%{$search}%")
                                ->orWhere('phone', 'like', "%{$search}%");
                        });
                });
            })
            ->when($filters['date'] ?? null, function ($q, $date) {
                $q->whereDate('created_at', $date);
            })
            ->latest()
            ->paginate(15)
            ->through(function ($booking) {
                $firstRoom = $booking->bookingRooms->first();

                return [
                    'id' => $booking->id,
                    'booking_code' => $booking->booking_code,
                    'status' => $booking->status,
                    'customer_name' => $booking->customer?->full_name,
                    'customer_phone' => $booking->customer?->phone,
                    'room_number' => $firstRoom?->room?->room_number ? 'P.' . $firstRoom->room->room_number : '-',
                    'room_type_name' => $firstRoom?->definition?->name ?? '-',
                    'created_at' => $booking->created_at,
                    'check_in' => optional($booking->check_in_expected)->format('d/m/Y'),
                    'check_out' => optional($booking->check_out_expected)->format('d/m/Y'),
                    'nights' => (int) ($booking->stay_duration ?? 1),
                    'total_price' => (float) ($booking->total_amount ?? 0),
                ];
            })
            ->withQueryString();
    }

    public function getRoomsMatrix($startDate, $endDate)
    {
        $rooms = Room::with(['definition', 'bookingRooms' => function($q) use ($startDate, $endDate) {
            $q->whereHas('booking', function($b) use ($startDate, $endDate) {
                $b->whereIn('status', ['pending', 'confirmed', 'checked_in'])
                  ->where(function($query) use ($startDate, $endDate) {
                      $query->where(function ($sub) use ($startDate, $endDate) {
                          $sub->where('check_in_expected', '<', $endDate)
                              ->where('check_out_expected', '>', $startDate);
                      })
                      ->orWhere(function ($sub2) use ($startDate) {
                          $sub2->where('status', 'checked_in')
                               ->whereNull('check_out_actual')
                               ->where('check_in_expected', '<=', Carbon::now())
                               ->whereRaw('DATE(?) <= DATE(NOW())', [$startDate]);
                      });
                  });
            })->with(['booking.customer', 'booking.invoice']);
        }])->orderBy('floor')->orderBy('room_number')->get();

        foreach ($rooms as $room) {
            foreach ($room->bookingRooms as $br) {
                if ($br->booking && $br->booking->status === 'checked_in') {
                    $overstayData = $this->calculateOverstayData($br->booking, Carbon::now());
                    $br->booking->setAttribute('is_overstay', $overstayData['is_overstay']);
                    $br->booking->setAttribute('overstay_minutes', $overstayData['overstay_minutes']);
                    $br->booking->setAttribute('overstay_hours', $overstayData['overstay_hours']);
                } elseif ($br->booking) {
                    $br->booking->setAttribute('is_overstay', false);
                    $br->booking->setAttribute('overstay_minutes', 0);
                    $br->booking->setAttribute('overstay_hours', 0);
                }
            }
        }

        return $rooms;
    }

    public function getAvailableRooms($checkIn, $checkOut)
    {
        $checkInDate = Carbon::parse($checkIn)->startOfDay();
        $checkOutDate = Carbon::parse($checkOut)->startOfDay();
        $today = Carbon::now()->startOfDay();

        if ($checkInDate->greaterThanOrEqualTo($checkOutDate)) {
            throw new Exception('Ngày trả phòng phải sau ngày nhận phòng ít nhất 1 ngày!');
        }

        $rooms = Room::where('is_active', true)
            ->where('status', 'available')
            ->whereNotIn('id', function ($query) use ($checkInDate, $checkOutDate, $today) {
                $query->select('room_id')
                    ->from('booking_rooms')
                    ->join('bookings', 'booking_rooms.booking_id', '=', 'bookings.id')
                    ->whereNotNull('room_id')
                    ->whereIn('bookings.status', ['pending', 'confirmed', 'checked_in'])
                    ->where(function ($q) use ($checkInDate, $checkOutDate, $today) {
                        $q->where(function ($sub1) use ($checkInDate, $checkOutDate) {
                            $sub1->where('bookings.check_in_expected', '<', $checkOutDate)
                                 ->where('bookings.check_out_expected', '>', $checkInDate);
                        })
                        ->orWhere(function ($sub2) use ($checkInDate, $today) {
                            $sub2->where('bookings.status', 'checked_in')
                                 ->where('bookings.check_in_expected', '<=', $today)
                                 ->whereDate('bookings.check_out_expected', '<=', $today)
                                 ->whereRaw('DATE(?) <= DATE(?)', [$checkInDate, $today]);
                        });
                    });
            })
            ->with('definition')
            ->get()
            ->filter(function ($room) {
                return $room->status === 'available';
            })
            ->values();

        $heldByDefinition = $this->getOnlinePendingHoldCounts($checkInDate, $checkOutDate);

        return $rooms
            ->groupBy('room_definition_id')
            ->flatMap(function ($group, $definitionId) use ($heldByDefinition) {
                $held = (int) ($heldByDefinition[(int) $definitionId] ?? 0);
                $availableSlots = max(0, $group->count() - $held);
                return $group->take($availableSlots);
            })
            ->values();
    }

    public function getTransferCandidates(Booking $booking)
    {
        $booking->loadMissing('bookingRooms');

        $checkInDate = Carbon::parse($booking->check_in_expected)->startOfDay();
        $checkOutDate = Carbon::parse($booking->check_out_expected)->startOfDay();
        $today = Carbon::now()->startOfDay();

        $currentRoomIds = $booking->bookingRooms->pluck('room_id')->filter()->values();

        return Room::query()
            ->where('is_active', true)
            ->where('status', 'available')
            ->whereNotIn('id', $currentRoomIds)
            ->whereNotIn('id', function ($query) use ($checkInDate, $checkOutDate, $today, $booking) {
                $query->select('booking_rooms.room_id')
                    ->from('booking_rooms')
                    ->join('bookings', 'booking_rooms.booking_id', '=', 'bookings.id')
                    ->whereNotNull('booking_rooms.room_id')
                    ->where('bookings.id', '!=', $booking->id)
                    ->whereIn('bookings.status', ['pending', 'confirmed', 'checked_in'])
                    ->where(function ($q) use ($checkInDate, $checkOutDate, $today) {
                        $q->where(function ($sub1) use ($checkInDate, $checkOutDate) {
                            $sub1->where('bookings.check_in_expected', '<', $checkOutDate)
                                ->where('bookings.check_out_expected', '>', $checkInDate);
                        })->orWhere(function ($sub2) use ($checkInDate, $today) {
                            $sub2->where('bookings.status', 'checked_in')
                                ->where('bookings.check_in_expected', '<=', $today)
                                ->whereDate('bookings.check_out_expected', '<=', $today)
                                ->whereRaw('DATE(?) <= DATE(?)', [$checkInDate, $today]);
                        });
                    });
            })
            ->with('definition:id,name,base_price')
            ->orderBy('floor')
            ->orderBy('room_number')
            ->get(['id', 'room_definition_id', 'room_number', 'floor', 'status']);
    }

    // ==========================================
    // 2. NHÓM NGHIỆP VỤ TẠO ĐƠN & TÀI CHÍNH
    // ==========================================

    public function create(array $data)
    {
        return DB::transaction(function () use ($data) {
            $checkIn = Carbon::parse($data['check_in_expected']);
            $checkOut = Carbon::parse($data['check_out_expected'])->setTime(12, 0, 0);
            $stayDuration = $checkIn->diffInDays($checkOut) ?: 1;

            $requestedByDefinition = collect($data['rooms'])
                ->groupBy('room_definition_id')
                ->map(fn ($rooms) => count($rooms));

            foreach ($requestedByDefinition as $roomDefinitionId => $requestedCount) {
                $availableSlots = $this->getWalkInAvailableSlots((int) $roomDefinitionId, $checkIn, $checkOut);

                if ($requestedCount > $availableSlots) {
                    $definitionName = RoomDefinition::find($roomDefinitionId)?->name ?? 'khong xac dinh';
                    throw new Exception("Hạng phòng {$definitionName} chỉ còn {$availableSlots} suất trống do có đơn online chờ xử lý.");
                }
            }

            $booking = Booking::create([
                'customer_id'        => $data['customer_id'] ?? null,
                'check_in_expected'  => $checkIn,
                'check_out_expected' => $checkOut,
                'status'             => $data['status'] ?? 'pending',
                'source'             => $data['source'] ?? 'walk_in',
                'deposit_amount'     => $data['deposit_amount'] ?? 0,
                'special_requests'   => $data['special_requests'] ?? null,
                'admin_note'         => $data['admin_note'] ?? null,
            ]);

            $totalRoomAmount = 0;

            foreach ($data['rooms'] as $roomItem) {
                $roomId = $roomItem['room_id'] ?? null;
                $definitionId = $roomItem['room_definition_id'];

                $price = RoomDefinition::find($definitionId)->base_price;

                if ($roomId && !$this->checkSpecificRoomAvailability($roomId, $checkIn, $checkOut)) {
                    throw new Exception("Phòng ID {$roomId} vừa mới có người chốt mất rồi!");
                }

                BookingRoom::create([
                    'booking_id'         => $booking->id,
                    'room_definition_id' => $definitionId,
                    'room_id'            => $roomId,
                    'price'              => $price,
                ]);

                $totalRoomAmount += ($price * $stayDuration);
            }

            $booking->update(['total_amount' => $totalRoomAmount]);
            app(\App\Services\InvoiceService::class)->generateInvoiceForBooking($booking);

            return $booking;
        });
    }

    public function addDeposit(Booking $booking, $amount)
    {
        return DB::transaction(function () use ($booking, $amount) {
            $previousStatus = $booking->status;
            $booking->increment('deposit_amount', $amount);

            if ($booking->status === 'pending') {
                $booking->update(['status' => 'confirmed']);
            }

            if ($previousStatus !== 'confirmed' && $booking->fresh()->status === 'confirmed') {
                $this->sendBookingConfirmedEmail($booking->fresh());
            }

            app(\App\Services\InvoiceService::class)->generateInvoiceForBooking($booking);
            return $booking;
        });
    }

    // ==========================================
    // 3. NHÓM NGHIỆP VỤ ĐIỀU PHỐI (CHUYỂN PHÒNG, CHECK-IN/OUT)
    // ==========================================

    public function transferRoom(Booking $booking, int $bookingRoomId, int $newRoomId)
    {
        return DB::transaction(function () use ($booking, $bookingRoomId, $newRoomId) {
            if (!in_array($booking->status, ['confirmed', 'checked_in'])) {
                throw new Exception('Chỉ được chuyển phòng cho đơn đã xác nhận hoặc đang lưu trú.');
            }

            $bookingRoom = $booking->bookingRooms()->whereKey($bookingRoomId)->first();

            if (!$bookingRoom) {
                throw new Exception('Không tìm thấy phòng cần chuyển trong đơn đặt này.');
            }

            if (empty($bookingRoom->room_id)) {
                throw new Exception('Phòng hiện tại chưa được xếp nên không thể chuyển.');
            }

            if ((int) $bookingRoom->room_id === $newRoomId) {
                throw new Exception('Phòng mới trùng với phòng hiện tại.');
            }

            $newRoom = Room::query()->with('definition:id,name,base_price')->lockForUpdate()->findOrFail($newRoomId);
            $oldRoom = Room::query()->lockForUpdate()->findOrFail($bookingRoom->room_id);

            if (!$newRoom->is_active) {
                throw new Exception('Phòng mới đang tắt kinh doanh, không thể chuyển.');
            }

            $checkInDate = Carbon::parse($booking->check_in_expected)->startOfDay();
            $checkOutDate = Carbon::parse($booking->check_out_expected)->startOfDay();
            $today = Carbon::now()->startOfDay();

            $isConflicted = BookingRoom::query()
                ->where('room_id', $newRoom->id)
                ->whereHas('booking', function ($q) use ($checkInDate, $checkOutDate, $today, $booking) {
                    $q->where('id', '!=', $booking->id)
                        ->whereIn('status', ['pending', 'confirmed', 'checked_in'])
                        ->where(function ($query) use ($checkInDate, $checkOutDate, $today) {
                            $query->where(function ($sub1) use ($checkInDate, $checkOutDate) {
                                $sub1->where('check_in_expected', '<', $checkOutDate)
                                    ->where('check_out_expected', '>', $checkInDate);
                            })->orWhere(function ($sub2) use ($checkInDate, $today) {
                                $sub2->where('status', 'checked_in')
                                    ->where('check_in_expected', '<=', $today)
                                    ->whereDate('check_out_expected', '<=', $today)
                                    ->whereRaw('DATE(?) <= DATE(?)', [$checkInDate, $today]);
                            });
                        });
                })->exists();

            if ($isConflicted) {
                throw new Exception('Phòng muốn chuyển đã có booking khác trong khung thời gian này.');
            }

            $bookingRoom->update([
                'room_id' => $newRoom->id,
                'room_definition_id' => $newRoom->room_definition_id,
                'price' => (float) $newRoom->definition->base_price,
            ]);

            if ($booking->status === 'checked_in') {
                $newRoom->update(['status' => 'occupied']);
                $oldRoom->update(['status' => 'available']);
            } else {
                $newRoom->update(['status' => 'available']);
                $oldRoom->update(['status' => 'available']);
            }

            app(\App\Services\InvoiceService::class)->generateInvoiceForBooking($booking->fresh());

            return $bookingRoom->fresh(['room', 'definition']);
        });
    }

    public function updateStatus(Booking $booking, $status)
    {
        return DB::transaction(function () use ($booking, $status) {
            $previousStatus = $booking->status;

            if ($status === 'checked_in') {
                $booking->status = 'checked_in';
                $booking->check_in_actual = Carbon::now();
                $booking->save();

                $booking->bookingRooms()->whereNotNull('room_id')->each(function($br) {
                    $br->room->update(['status' => 'occupied']);
                });
                app(\App\Services\InvoiceService::class)->generateInvoiceForBooking($booking);
                return $booking;
            }

            if ($status === 'checked_out') {
                $invoice = $booking->invoice;
                if (!$invoice) {
                    app(\App\Services\InvoiceService::class)->generateInvoiceForBooking($booking);
                    $booking->refresh();
                    $invoice = $booking->invoice;
                }

                $debt = (float) ($invoice->total_amount ?? 0) - (float) ($invoice->amount_paid ?? 0);
                if ($debt > 0) {
                    throw new Exception('Khách hàng còn nợ ' . number_format($debt) . ' VNĐ. Vui lòng thanh toán hết trước khi check-out.');
                }
                $booking->check_out_actual = Carbon::now();
                $booking->status = 'checked_out';
                $booking->save();

                app(\App\Services\InvoiceService::class)->generateInvoiceForBooking($booking);

                $booking->bookingRooms()->whereNotNull('room_id')->each(function ($br) {
                    $br->room->update(['status' => 'cleaning']);
                });

                return $booking;
            }

            $booking->update(['status' => $status]);

            if ($status === 'confirmed' && $previousStatus !== 'confirmed') {
                $this->sendBookingConfirmedEmail($booking);
            }

            return $booking;
        });
    }

    public function cancelBooking(Booking $booking, $reason = 'Khách hủy / Không đến (No-show)')
    {
        return DB::transaction(function () use ($booking, $reason) {
            $booking->update(['status' => 'cancelled']);

            $booking->bookingRooms()->whereNotNull('room_id')->each(function($br) {
                if ($br->room && $br->room->status === 'occupied') {
                     $br->room->update(['status' => 'cleaning']);
                }
            });

            $invoice = Invoice::firstOrNew(['booking_id' => $booking->id]);
            $penaltyFee = $booking->deposit_amount;

            // Đã fix: Trả về đúng mảng thuộc tính theo Invoice.php của bạn
            $invoice->fill([
                'room_charge'      => 0,
                'service_charge'   => 0,
                'tax_amount'       => 0,
                'total_amount'     => $penaltyFee,
                'amount_paid'      => $penaltyFee,
                'payment_status'   => 'paid',
                'payment_method'   => 'cash'
            ]);

            if (empty($invoice->invoice_code)) {
                $invoice->invoice_code = 'INV-' . date('ymd') . '-CANC';
            }
            $invoice->save();

            return $booking;
        });
    }

    // ==========================================
    // 4. NHÓM NGHIỆP VỤ POS & DỊCH VỤ
    // ==========================================

    public function addServiceToBooking(Booking $booking, $serviceId, $quantity, $price)
    {
        return DB::transaction(function () use ($booking, $serviceId, $quantity, $price) {
            $totalPrice = $price * $quantity;

            $bookingService = BookingService::create([
                'booking_id'  => $booking->id,
                'service_id'  => $serviceId,
                'quantity'    => $quantity,
                'price'       => $price,
                'total_price' => $totalPrice,
            ]);

            $booking->increment('total_amount', $totalPrice);
            app(\App\Services\InvoiceService::class)->generateInvoiceForBooking($booking);

            return $bookingService;
        });
    }

    public function addMultipleServicesToBooking(Booking $booking, array $services)
    {
        return DB::transaction(function () use ($booking, $services) {
            foreach ($services as $service) {
                $quantity = (int) ($service['quantity'] ?? 1);
                $price = (float) ($service['price'] ?? 0);

                BookingService::create([
                    'booking_id'  => $booking->id,
                    'service_id'  => $service['service_id'],
                    'quantity'    => $quantity,
                    'price'       => $price,
                    'total_price' => $price * $quantity,
                ]);

                $booking->increment('total_amount', $price * $quantity);
            }

            app(\App\Services\InvoiceService::class)->generateInvoiceForBooking($booking);
            return true;
        });
    }

    public function calculateOverstayData(Booking $booking, ?Carbon $referenceTime = null): array
    {
        if ($booking->status !== 'checked_in' && is_null($booking->check_out_actual)) {
            return [
                'is_overstay' => false,
                'overstay_minutes' => 0,
                'overstay_hours' => 0,
            ];
        }

        $expectedCheckout = Carbon::parse($booking->check_out_expected)->setTime(12, 0, 0);
        $actualTime = $booking->check_out_actual
            ? Carbon::parse($booking->check_out_actual)
            : ($referenceTime ? $referenceTime->copy() : Carbon::now());

        if ($actualTime->lte($expectedCheckout)) {
            return [
                'is_overstay' => false,
                'overstay_minutes' => 0,
                'overstay_hours' => 0,
            ];
        }

        $overstayMinutes = $expectedCheckout->diffInMinutes($actualTime);
        $overstayHours = $overstayMinutes > 60 ? (int) ceil(($overstayMinutes - 60) / 60) : 0;

        return [
            'is_overstay' => $overstayHours > 0,
            'overstay_minutes' => $overstayMinutes,
            'overstay_hours' => $overstayHours,
        ];
    }

    // ==========================================
    // 5. CÁC HÀM TIỆN ÍCH (PRIVATE HELPERS)
    // ==========================================

    private function checkSpecificRoomAvailability($roomId, $checkIn, $checkOut)
    {
        $checkInDate = Carbon::parse($checkIn)->startOfDay();
        $checkOutDate = Carbon::parse($checkOut)->startOfDay();
        $today = Carbon::now()->startOfDay();

        return !BookingRoom::where('room_id', $roomId)
            ->whereHas('booking', function ($q) use ($checkInDate, $checkOutDate, $today) {
                $q->whereIn('status', ['confirmed', 'checked_in'])
                  ->where(function($query) use ($checkInDate, $checkOutDate, $today) {
                      $query->where(function ($sub1) use ($checkInDate, $checkOutDate) {
                          $sub1->where('check_in_expected', '<', $checkOutDate)
                               ->where('check_out_expected', '>', $checkInDate);
                      })
                      ->orWhere(function ($sub2) use ($checkInDate, $today) {
                          $sub2->where('status', 'checked_in')
                               ->where('check_in_expected', '<=', $today)
                               ->whereDate('check_out_expected', '<=', $today)
                               ->whereRaw('DATE(?) <= DATE(?)', [$checkInDate, $today]);
                      });
                  });
            })->exists();
    }

    private function sendBookingConfirmedEmail(Booking $booking): void
    {
        $booking->loadMissing('customer');
        $email = $booking->customer?->email;

        if (!$email) {
            return;
        }

        Mail::to($email)->send(new ClientBookingConfirmedMail($booking));
    }

    private function getOnlinePendingHoldCounts(Carbon $checkInDate, Carbon $checkOutDate): array
    {
        return DB::table('booking_rooms')
            ->join('bookings', 'booking_rooms.booking_id', '=', 'bookings.id')
            ->selectRaw('booking_rooms.room_definition_id, COUNT(*) as hold_count')
            ->whereNull('booking_rooms.room_id')
            ->where('bookings.source', 'online')
            ->where('bookings.status', 'pending')
            ->where('bookings.check_in_expected', '<', $checkOutDate)
            ->where('bookings.check_out_expected', '>', $checkInDate)
            ->groupBy('booking_rooms.room_definition_id')
            ->pluck('hold_count', 'room_definition_id')
            ->map(fn ($count) => (int) $count)
            ->toArray();
    }

    private function getWalkInAvailableSlots(int $roomDefinitionId, Carbon $checkInDate, Carbon $checkOutDate): int
    {
        $physicalFreeCount = Room::query()
            ->where('is_active', true)
            ->where('room_definition_id', $roomDefinitionId)
            ->whereNotIn('id', function ($query) use ($checkInDate, $checkOutDate) {
                $query->select('booking_rooms.room_id')
                    ->from('booking_rooms')
                    ->join('bookings', 'booking_rooms.booking_id', '=', 'bookings.id')
                    ->whereNotNull('booking_rooms.room_id')
                    ->whereIn('bookings.status', ['pending', 'confirmed', 'checked_in'])
                    ->where('bookings.check_in_expected', '<', $checkOutDate)
                    ->where('bookings.check_out_expected', '>', $checkInDate);
            })
            ->count();

        $heldByOnlinePending = (int) ($this->getOnlinePendingHoldCounts($checkInDate, $checkOutDate)[$roomDefinitionId] ?? 0);

        return max(0, $physicalFreeCount - $heldByOnlinePending);
    }
}
