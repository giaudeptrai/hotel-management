<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Customer;
use App\Models\RoomDefinition;
use Illuminate\Http\Request;
use App\Services\BookingServices;
use App\Services\Client\ClientBookingService;
use Carbon\Carbon;
use Inertia\Inertia;
use Illuminate\Support\Facades\URL;
use Throwable;

class BookingController extends Controller
{
    public function __construct(
        private readonly ClientBookingService $bookingService,
        private readonly BookingServices $adminBookingService,
    )
    {
    }

    public function create(Request $request)
    {
        return Inertia::render('Client/Booking/Create', $this->bookingService->getCreatePayload($request->all(), $request->user()));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'room_id' => 'required|integer|exists:room_definitions,id',
            'check_in' => 'required|date',
            'check_out' => 'required|date|after:check_in',
            'guests' => 'nullable|integer|min:1|max:20',
            'full_name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'email' => 'required|email|max:255',
            'special_requests' => 'nullable|string|max:1000',
        ], [
            'room_id.required' => 'Bạn chưa chọn hạng phòng để gửi yêu cầu.',
            'check_out.after' => 'Ngày trả phải sau ngày nhận phòng.',
            'full_name.required' => 'Vui lòng nhập họ tên người đặt phòng.',
            'phone.required' => 'Vui lòng nhập số điện thoại liên hệ.',
            'email.required' => 'Vui lòng nhập email để nhận thông báo.',
        ]);

        try {
            $booking = $this->bookingService->createPendingRequest($validated, $request->user());

            return redirect()->route('client.booking.create', [
                'room' => $validated['room_id'],
                'check_in' => $validated['check_in'],
                'check_out' => $validated['check_out'],
                'guests' => $validated['guests'] ?? 1,
            ])->with('success', "Đặt phòng thành công! Mã đơn {$booking->booking_code} đã được gửi. Vui lòng đợi ít phút để nhân viên gọi xác nhận đặt phòng.");
        } catch (Throwable $e) {
            return back()->withErrors([
                'booking' => $e->getMessage(),
            ])->withInput();
        }
    }

    public function index(Request $request)
    {
        $customer = $this->resolveCustomerFromUser($request);

        if (!$customer) {
            return Inertia::render('Client/Bookings/Index', [
                'bookings' => [
                    'data' => [],
                    'links' => [],
                    'meta' => [],
                ],
                'stats' => [
                    'total' => 0,
                    'completed' => 0,
                    'cancelled' => 0,
                ],
            ]);
        }

        $bookings = Booking::query()
            ->where('customer_id', $customer->id)
            ->with(['bookingRooms.definition', 'invoice'])
            ->latest('created_at')
            ->paginate(9)
            ->through(function (Booking $booking) {
                $firstBookingRoom = $booking->bookingRooms->first();
                $roomDefinition = $firstBookingRoom?->definition;
                $hasStarted = $booking->check_in_expected && now()->isAfter($booking->check_in_expected);

                return [
                    'id' => $booking->id,
                    'booking_code' => $booking->booking_code,
                    'status' => $booking->status,
                    'thumbnail' => (($roomDefinition?->image_urls) ?? [])[0] ?? null,
                    'room_type_name' => $roomDefinition?->name ?? 'Phòng tiêu chuẩn',
                    'created_at' => optional($booking->created_at)->toISOString(),
                    'check_in_expected' => optional($booking->check_in_expected)->toISOString(),
                    'check_out_expected' => optional($booking->check_out_expected)->toISOString(),
                    'total_amount' => (float) ($booking->invoice?->total_amount ?? $booking->total_amount ?? 0),
                    'can_cancel' => in_array($booking->status, ['pending', 'confirmed'], true) && !$hasStarted,
                ];
            });

        return Inertia::render('Client/Bookings/Index', [
            'bookings' => $bookings,
            'stats' => [
                'total' => Booking::where('customer_id', $customer->id)->count(),
                'completed' => Booking::where('customer_id', $customer->id)->where('status', 'checked_out')->count(),
                'cancelled' => Booking::where('customer_id', $customer->id)->where('status', 'cancelled')->count(),
            ],
        ]);
    }

    public function show(Request $request, Booking $booking)
    {
        $customer = $this->resolveCustomerFromUser($request);

        if (!$customer || $booking->customer_id !== $customer->id) {
            abort(403);
        }

        $booking->load([
            'customer',
            'bookingRooms.definition',
            'bookingRooms.room',
            'bookingServices.service',
            'invoice.cashier',
            'review',
        ]);

        return Inertia::render('Client/Bookings/Show', $this->buildBookingDetailPayload($booking));
    }

    public function invoice(Request $request, Booking $booking)
    {
        if (!$request->hasValidSignature()) {
            abort(403);
        }

        $booking->load([
            'bookingRooms.definition',
            'bookingRooms.room',
            'bookingServices.service',
            'invoice.cashier',
            'review',
        ]);

        $invoiceTotal = (float) ($booking->invoice?->total_amount ?? 0);
        $paidAmount = (float) ($booking->invoice?->amount_paid ?? 0);

        return Inertia::render('Client/Bookings/Invoice', $this->buildBookingDetailPayload($booking));
    }

    private function buildBookingDetailPayload(Booking $booking): array
    {
        $stayNights = max(1, (int) ($booking->stay_duration ?? 1));

        $roomItems = $booking->bookingRooms->map(function ($bookingRoom) use ($stayNights) {
            $nightlyRate = (float) ($bookingRoom->price ?? 0);

            return [
                'room_type' => $bookingRoom->definition?->name ?? 'Phòng',
                'room_number' => $bookingRoom->room?->room_number,
                'thumbnail' => (($bookingRoom->definition?->image_urls) ?? [])[0] ?? null,
                'nightly_rate' => $nightlyRate,
                'nights' => $stayNights,
                'subtotal' => $nightlyRate * $stayNights,
            ];
        })->values();

        $serviceItems = $booking->bookingServices->map(function ($bookingService) {
            return [
                'name' => $bookingService->service?->name ?? 'Dịch vụ POS',
                'quantity' => (int) ($bookingService->quantity ?? 0),
                'unit_price' => (float) ($bookingService->price ?? 0),
                'total_price' => (float) ($bookingService->total_price ?? 0),
            ];
        })->values();

        $roomSubtotal = (float) $roomItems->sum('subtotal');
        $serviceSubtotal = (float) $serviceItems->sum('total_price');
        $invoiceTotal = (float) ($booking->invoice?->total_amount ?? ($roomSubtotal + $serviceSubtotal));
        $paidAmount = (float) ($booking->invoice?->amount_paid ?? $booking->deposit_amount ?? 0);
        $outstandingAmount = max(0, $invoiceTotal - $paidAmount);
        $hasStarted = Carbon::parse($booking->check_in_expected)->lessThanOrEqualTo(now());
        $roomDefinition = $booking->bookingRooms->first()?->definition;
        $reviewSummary = null;

        if ($roomDefinition) {
            $roomReviewStats = RoomDefinition::query()
                ->whereKey($roomDefinition->id)
                ->withAvg('reviews', 'rating')
                ->withCount('reviews')
                ->first();

            $reviewSummary = [
                'average' => round((float) ($roomReviewStats?->reviews_avg_rating ?? 0), 1),
                'count' => (int) ($roomReviewStats?->reviews_count ?? 0),
            ];
        }

        return [
            'booking' => [
                'id' => $booking->id,
                'booking_code' => $booking->booking_code,
                'status' => $booking->status,
                'customer_name' => $booking->customer?->full_name ?? $booking->customer_name ?? 'Khách đặt phòng',
                'invoice_url' => URL::signedRoute('client.bookings.invoice', ['booking' => $booking->id]),
                'booked_at' => optional($booking->created_at)->toISOString(),
                'check_in_expected' => optional($booking->check_in_expected)->toISOString(),
                'check_out_expected' => optional($booking->check_out_expected)->toISOString(),
            ],
            'room_items' => $roomItems,
            'service_items' => $serviceItems,
            'invoice_summary' => [
                'room_subtotal' => $roomSubtotal,
                'service_subtotal' => $serviceSubtotal,
                'total_amount' => $invoiceTotal,
                'deposit_amount' => (float) ($booking->deposit_amount ?? 0),
                'paid_amount' => $paidAmount,
                'outstanding_amount' => $outstandingAmount,
                'payment_status' => $booking->invoice?->payment_status ?? 'unpaid',
                'cashier_name' => $booking->invoice?->cashier?->name ?? ($booking->invoice?->payment_status === 'paid' ? 'Hệ thống Auto' : null),
                'paid_at' => optional($booking->invoice?->paid_at)->toISOString(),
            ],
            'hotel_info' => [
                'check_in_notice' => 'Check-in sau 14:00',
                'check_out_notice' => 'Check-out trước 12:00',
                'address' => 'Dasher Hotel, Số 68 Đường Trung Tâm, Quận 1, TP.HCM',
                'hotline' => '1900 6868',
            ],
            'review_summary' => $reviewSummary,
            'actions' => [
                'can_cancel' => in_array($booking->status, ['pending', 'confirmed'], true) && !$hasStarted,
                'can_review' => $booking->status === 'checked_out' && !$booking->review,
                'reviewed' => (bool) $booking->review,
            ],
        ];
    }

    public function cancel(Request $request, Booking $booking)
    {
        $customer = $this->resolveCustomerFromUser($request);

        if (!$customer || $booking->customer_id !== $customer->id) {
            abort(403);
        }

        if (!in_array($booking->status, ['pending', 'confirmed'], true)) {
            return back()->withErrors(['error' => 'Đơn đặt phòng này không thể hủy.']);
        }

        if (Carbon::parse($booking->check_in_expected)->lessThanOrEqualTo(now())) {
            return back()->withErrors(['error' => 'Đơn đã bắt đầu, không thể hủy online.']);
        }

        $this->adminBookingService->cancelBooking($booking, 'Khách hủy trước ngày nhận phòng (online).');

        return redirect()->route('client.bookings.show', $booking->id)
            ->with('success', 'Đã hủy đơn đặt phòng thành công.');
    }

    private function resolveCustomerFromUser(Request $request): ?Customer
    {
        $user = $request->user();

        if (!$user) {
            return null;
        }

        $directCustomer = $user->customer;
        if ($directCustomer) {
            return $directCustomer;
        }

        if (!$user->email) {
            return null;
        }

        $matchedCustomer = Customer::query()
            ->whereNull('user_id')
            ->where('email', $user->email)
            ->latest()
            ->first();

        if ($matchedCustomer) {
            $matchedCustomer->update(['user_id' => $user->id]);
        }

        return $matchedCustomer;
    }
}
