<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Customer;
use App\Models\RoomDefinition;
use App\Models\Room;
use App\Services\BookingServices;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;
use Exception;

class BookingController extends Controller
{
    protected $bookingService;

    public function __construct(BookingServices $bookingService)
    {
        $this->bookingService = $bookingService;
    }

    public function index(Request $request)
    {
        $startDate = $request->input('start_date', Carbon::today()->toDateString());
        $endDate = $request->input('end_date', Carbon::parse($startDate)->addDay()->toDateString());

        $bookings = $this->bookingService->getAll($request->all());

        $roomsMatrix = $this->bookingService->getRoomsMatrix($startDate, $endDate);

        return Inertia::render('Admin/Bookings/RoomMatrix', [
            'bookings'    => $bookings,
            'roomsMatrix' => $roomsMatrix,

            'services'    => \App\Models\Service::where('is_active', true)->get(),

            'filters'     => [
                'tab'        => $request->input('tab', 'matrix'),
                'search'     => $request->search,
                'status'     => $request->status,
                'source'     => $request->source,
                'start_date' => $startDate,
                'end_date'   => $endDate,
                'list_start_date' => $request->input('list_start_date', ''),
                'list_end_date'   => $request->input('list_end_date', ''),
            ]
        ]);
    }

    public function create(Request $request)
    {
        return Inertia::render('Admin/Bookings/Create', [
            'customers'       => Customer::select('id', 'full_name', 'phone')->latest()->get(),
            'roomDefinitions' => RoomDefinition::select('id', 'name', 'base_price')->get(),
            'preFill'         => [
                'check_in'  => $request->start_date,
                'check_out' => $request->end_date,
                'room_id'   => $request->room_id,
            ]
        ]);
    }

    public function history(Request $request)
    {
        $bookings = $this->bookingService->getHistory($request->all());

        return Inertia::render('Admin/Bookings/History', [
            'bookings' => $bookings,
            'filters' => [
                'search' => $request->input('search', ''),
                'date' => $request->input('date', ''),
            ],
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_id'        => 'required|exists:customers,id',
            'check_in_expected'  => 'required|date',
            'check_out_expected' => 'required|date|after:check_in_expected',
            'status'             => 'nullable|in:pending,confirmed,checked_in',
            'source'             => 'nullable|in:online,walk_in',
            'deposit_amount'     => 'nullable|numeric|min:0',
            'special_requests'   => 'nullable|string',
            'admin_note'         => 'nullable|string',
            'rooms'                      => 'required|array|min:1',
            'rooms.*.room_definition_id' => 'required|exists:room_definitions,id',
            'rooms.*.room_id'            => 'nullable|exists:rooms,id',
        ], [
            'check_out_expected.after' => 'Ngày trả phòng phải sau ngày nhận phòng ní ơi!',
            'rooms.required'           => 'Ní phải chọn ít nhất 1 phòng mới tạo đơn được.'
        ]);

        try {
            $this->bookingService->create($validated);
            return redirect()->route('admin.bookings.index')->with('success', 'Chốt đơn thành công!');
        } catch (Exception $e) {
            return back()->with('error', $e->getMessage())->withInput();
        }
    }

    public function show(Booking $booking)
    {
        $booking->load([
            'customer',
            'bookingRooms.definition',
            'bookingRooms.room',
            'bookingServices.service',
            'invoice'
        ]);

        $overstayData = $this->bookingService->calculateOverstayData($booking);

        $booking->setAttribute('is_overstay', $overstayData['is_overstay']);
        $booking->setAttribute('overstay_minutes', $overstayData['overstay_minutes']);
        $booking->setAttribute('overstay_hours', $overstayData['overstay_hours']);

        return Inertia::render('Admin/Bookings/Show', [
            'booking' => $booking,
            'availableServices' => \App\Models\Service::where('is_active', true)->get(),
            'transferCandidates' => $this->bookingService->getTransferCandidates($booking),
        ]);
    }

    public function quickStoreCustomer(Request $request)
    {
        $validated = $request->validate([
            'full_name'   => 'required|string|max:255',
            'phone'       => 'required|string|max:20|unique:customers,phone',
            'cccd_number' => 'nullable|string|max:20|unique:customers,cccd_number',
            'email'       => 'nullable|email|max:255|unique:customers,email',
            'birthday'    => 'nullable|date',
            'gender'      => 'nullable|in:male,female,other',
            'address'     => 'nullable|string|max:1000',
        ]);

        $customer = Customer::create($validated);

        return response()->json([
            'success' => true,
            'customer' => $customer
        ]);
    }

    public function getAvailableRoomsApi(Request $request)
    {
        $request->validate([
            'check_in'  => 'required|date',
            'check_out' => 'required|date|after:check_in',
        ]);

        try {
            $rooms = $this->bookingService->getAvailableRooms($request->check_in, $request->check_out);
            return response()->json($rooms);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }

    public function addService(Request $request, Booking $booking)
    {
        try {
            if ($request->has('services')) {
                $validated = $request->validate([
                    'services' => 'required|array|min:1',
                    'services.*.service_id' => 'required|exists:services,id',
                    'services.*.quantity' => 'required|integer|min:1',
                    'services.*.price' => 'required|numeric|min:0',
                ]);

                $this->bookingService->addMultipleServicesToBooking($booking, $validated['services']);
                return back()->with('success', 'Đã thêm ' . count($validated['services']) . ' dịch vụ vào bill!');
            }

            $validated = $request->validate([
                'service_id' => 'required|exists:services,id',
                'quantity'   => 'required|integer|min:1',
                'price'      => 'required|numeric|min:0',
            ]);

            $this->bookingService->addServiceToBooking($booking, $validated['service_id'], $validated['quantity'], $validated['price']);
            return back()->with('success', 'Đã thêm món vào bill!');
        } catch (Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * 8. NGHIỆP VỤ: CẬP NHẬT TRẠNG THÁI (Check-in/Out)
     */
    public function updateStatus(Request $request, Booking $booking)
    {
        $request->validate([
            'status' => 'required|in:pending,confirmed,checked_in,checked_out,cancelled'
        ]);

        try {
            $this->bookingService->updateStatus($booking, $request->status);
            return back()->with('success', 'Cập nhật trạng thái thành công!');
        } catch (Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function searchCustomers(Request $request)
    {
        $query = $request->get('q');

        if (strlen($query) < 2) return response()->json([]);

        $customers = \App\Models\Customer::where('phone', 'like', "%{$query}%")
            ->orWhere('full_name', 'like', "%{$query}%")
            ->limit(5)
            ->get();

        return response()->json($customers);
    }

    public function payInvoice(Request $request, Booking $booking)
    {
        $request->validate([
            'payment_method' => 'required|in:cash'
        ]);

        $invoice = $booking->invoice;
        if (!$invoice) {
            return back()->with('error', 'Đơn này chưa có hóa đơn ní ơi!');
        }

        $debtAmount = $invoice->total_amount - $invoice->amount_paid;

        if ($debtAmount > 0) {
            $invoice->amount_paid += $debtAmount;

            $invoice->payment_status = 'paid';
            $invoice->payment_method = 'cash';
            $invoice->paid_at = now();
            $invoice->cashier_id = auth()->id();
            $invoice->save();

            if ($booking->customer) {
                $booking->customer->increment('total_spent', $debtAmount);
            }
        } elseif (!$invoice->cashier_id && $invoice->payment_status === 'paid') {
            $invoice->cashier_id = auth()->id();
            $invoice->paid_at = $invoice->paid_at ?? now();
            $invoice->save();
        }

        return back()->with('success', '💵 Đã thu nốt ' . number_format($debtAmount) . ' VNĐ. Hóa đơn hoàn tất!');
    }

    public function processCheckIn(Request $request, Booking $booking)
    {
        $request->validate([
            'cccd' => 'required|string|max:20',
            'deposit_amount' => 'nullable|numeric|min:0',
        ]);

        if ($booking->customer) {
            $booking->customer->update(['cccd' => $request->cccd]);
        }

        if ($request->filled('deposit_amount') && (float) $request->deposit_amount > 0) {
            $this->bookingService->addDeposit($booking, (float) $request->deposit_amount);
        }

        $this->bookingService->updateStatus($booking, 'checked_in');

        return back()->with('success', '🔑 Check-in thành công! Đã giao phòng cho khách.! 🏨');
    }

    public function processCheckOut(Request $request, Booking $booking)
    {
        try {
            $this->bookingService->updateStatus($booking, 'checked_out');
            return back()->with('success', '👋 Check-out thành công! Hẹn gặp lại khách nhé! 🏨');
        } catch (Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function transferRoom(Request $request, Booking $booking)
    {
        $validated = $request->validate([
            'booking_room_id' => 'required|integer',
            'new_room_id' => 'required|exists:rooms,id',
        ]);

        try {
            $this->bookingService->transferRoom($booking, (int) $validated['booking_room_id'], (int) $validated['new_room_id']);
            return back()->with('success', 'Đã chuyển phòng thành công!');
        } catch (Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }


    public function deposit(Request $request, Booking $booking)
    {
        $request->validate([
            'deposit_amount' => 'required|numeric|min:1000'
        ]);

        try {
            $this->bookingService->addDeposit($booking, $request->deposit_amount);
            return back()->with('success', 'Đã nhận cọc và cập nhật hóa đơn thành công!');
        } catch (Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }


    public function cancel(Request $request, Booking $booking)
    {
        if (in_array($booking->status, ['checked_out', 'cancelled'])) {
            return back()->with('error', 'Đơn này không thể hủy được nữa!');
        }

        try {
            $this->bookingService->cancelBooking($booking);
            return back()->with('success', 'Đã hủy đơn! Tiền cọc (nếu có) được tính thành phí phạt doanh thu.');
        } catch (Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }


    public function updateRoomStatus(Request $request, Room $room)
    {
        $request->validate([
            'status' => 'required|in:available,occupied,cleaning,maintenance'
        ]);

        try {
            $room->update(['status' => $request->status]);

            $statusLabel = [
                'available' => 'Trống',
                'occupied' => 'Đang lưu trú',
                'cleaning' => 'Đang dọn dẹp',
                'maintenance' => 'Đang bảo trì'
            ];

            return back()->with('success', 'Cập nhật trạng thái phòng ' . $room->room_number . ' thành: ' . $statusLabel[$request->status]);
        } catch (Exception $e) {
            return back()->with('error', 'Lỗi khi cập nhật: ' . $e->getMessage());
        }
    }
}
