<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Invoice;
use App\Models\Room;
use App\Models\RoomDefinition;
use App\Services\InvoiceService;
use Illuminate\Http\Request;
use Inertia\Inertia;

use App\Exports\InvoicesExport;
use Maatwebsite\Excel\Facades\Excel;

class InvoiceController extends Controller
{
    protected $invoiceService;

    public function __construct(InvoiceService $invoiceService)
    {
        $this->invoiceService = $invoiceService;
    }

    public function index(Request $request)
    {
        return Inertia::render('Admin/Invoices/Index', [
            'invoices' => $this->invoiceService->getAll($request->all()),
            'filters'  => $request->only(['search', 'status', 'date_type', 'date_value', 'room_id', 'room_definition_id']),
            'rooms'    => Room::select('id', 'room_number')->get(),
            'roomDefinitions' => RoomDefinition::select('id', 'name')->get(),
        ]);
    }

    
    public function show($id)
    {
        
        $invoice = Invoice::with([
            'booking.customer',
            'booking.bookingRooms.room',
            'booking.bookingRooms.definition',
            'booking.bookingServices.service',
            'cashier'
        ])->findOrFail($id);

        return Inertia::render('Admin/Invoices/Show', [
            'invoice' => $invoice
        ]);
    }

    
    public function generate(Request $request, Booking $booking)
    {
        
        if (!in_array($booking->status, ['checked_in', 'checked_out'])) {
            return back()->withErrors(['error' => 'Chỉ có thể xuất hóa đơn cho phòng đang ở hoặc đã trả!']);
        }

        
        if ($booking->invoice) {
            return back()->withErrors(['error' => 'Hóa đơn cho đơn đặt phòng này đã tồn tại!']);
        }

        $this->invoiceService->generateInvoiceForBooking($booking);

        return back()->with('success', 'Đã chốt hóa đơn thành công! Vui lòng tiến hành thanh toán.');
    }

    
    public function pay(Request $request, Invoice $invoice)
    {
        $request->validate([
            'payment_method' => 'required|in:cash'
        ]);

        if ($invoice->payment_status === 'paid') {
            return back()->withErrors(['error' => 'Hóa đơn này đã được thanh toán trước đó!']);
        }

        $this->invoiceService->markAsPaid($invoice);

        return back()->with('success', 'Thanh toán thành công! Đã ghi nhận doanh thu.');
    }

    
    public function export(Request $request)
    {
        
        $filters = $request->only(['search', 'status', 'date_type', 'date_value', 'room_id', 'room_definition_id']);

        
        $fileName = 'Bao_Cao_Doanh_Thu';
        $dateType = $request->input('date_type');
        $dateVal = $request->input('date_value');

        if (!empty($dateVal)) {
            if ($dateType === 'day') {
                
                $fileName .= '_Ngay_' . date('d-m-Y', strtotime($dateVal));
            } elseif ($dateType === 'month') {
                
                $fileName .= '_Thang_' . date('m-Y', strtotime($dateVal . '-01'));
            } elseif ($dateType === 'year') {
                
                $fileName .= '_Nam_' . $dateVal;
            }
        } else {
            
            $fileName .= '_Tong_Hop_' . date('d-m-Y_H-i');
        }

        $fileName .= '.xlsx';

        
        return \Maatwebsite\Excel\Facades\Excel::download(
            new \App\Exports\InvoicesExport($filters),
            $fileName
        );
    }


    public function handleNoShow(Booking $booking)
    {
        
        if ($booking->status !== 'confirmed') {
            return back()->withErrors(['error' => 'Trạng thái đặt phòng không hợp lệ để xử lý No-show!']);
        }

        DB::transaction(function () use ($booking) {
            
            $booking->update(['status' => 'cancelled']);

            
            $this->invoiceService->generateInvoiceForBooking($booking);
        });

        return back()->with('success', 'Đã hủy đơn và chuyển tiền cọc thành doanh thu!');
    }
}
