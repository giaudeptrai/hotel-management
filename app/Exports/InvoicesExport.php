<?php

namespace App\Exports;

use App\Models\Invoice;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class InvoicesExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    protected $filters;

    public function __construct($filters)
    {
        $this->filters = $filters;
    }

    // 1. Keo du lieu tu Database va ap dung cac bo loc (Search, Status, Date, Room)
    public function query()
    {
        // Load kem cac quan he de hien thi thong tin chi tiet phong va khach hang
        $query = Invoice::with([
            'booking.customer',
            'booking.bookingRooms.room',
            'booking.bookingRooms.definition',
            'cashier'
        ])->latest();

        // Loc theo tu khoa (Ma HD, Ma Booking, Ten KH, SDT)
        if (!empty($this->filters['search'])) {
            $search = $this->filters['search'];
            $query->where('invoice_code', 'like', "%{$search}%")
                  ->orWhereHas('booking', function($q) use ($search) {
                      $q->where('booking_code', 'like', "%{$search}%")
                        ->orWhereHas('customer', function($q2) use ($search) {
                            $q2->where('full_name', 'like', "%{$search}%")
                               ->orWhere('phone', 'like', "%{$search}%");
                        });
                  });
        }

        // Loc theo trang thai thanh toan
        if (!empty($this->filters['status'])) {
            $query->where('payment_status', $this->filters['status']);
        }

        // Loc theo Hang phong (Room Definition)
        if (!empty($this->filters['room_definition_id'])) {
            $query->whereHas('booking.bookingRooms', function ($q) {
                $q->where('room_definition_id', $this->filters['room_definition_id']);
            });
        }

        // Loc theo So phong cu the (Room)
        if (!empty($this->filters['room_id'])) {
            $query->whereHas('booking.bookingRooms', function ($q) {
                $q->where('room_id', $this->filters['room_id']);
            });
        }

        // Loc theo thoi gian: Ngay / Thang / Nam
        if (!empty($this->filters['date_type']) && !empty($this->filters['date_value'])) {
            $type = $this->filters['date_type'];
            $val = $this->filters['date_value'];

            if ($type === 'day') {
                $query->whereDate('created_at', $val);
            } elseif ($type === 'month') {
                $parts = explode('-', $val);
                if (count($parts) == 2) {
                    $query->whereYear('created_at', $parts[0])
                          ->whereMonth('created_at', $parts[1]);
                }
            } elseif ($type === 'year') {
                $query->whereYear('created_at', $val);
            }
        }

        return $query;
    }

    // 2. Dinh nghia cac cot tieu de trong file Excel
    public function headings(): array
    {
        return [
            'Mã Hóa Đơn',
            'Mã Booking',
            'Phòng',
            'Hạng Phòng',
            'Khách Hàng',
            'Số Điện Thoại',
            'Tổng Mức (VNĐ)',
            'Đã Thu (VNĐ)',
            'Công Nợ (VNĐ)',
            'Trạng Thái',
            'Thu Ngân',
            'Ngày Lập',
        ];
    }

    // 3. Anh xa du lieu tu Model vao cac cot Excel
    public function map($invoice): array
    {
        $debt = max(0, $invoice->total_amount - ($invoice->amount_paid ?? 0));

        // Lay danh sach so phong (Booking co the co nhieu phong)
        $roomNumbers = $invoice->booking->bookingRooms->map(function($br) {
            return $br->room->room_number ?? 'N/A';
        })->implode(', ');

        // Lay danh sach ten hang phong
        $roomDefs = $invoice->booking->bookingRooms->map(function($br) {
            return $br->definition->name ?? 'N/A';
        })->unique()->implode(', ');

        $statusMap = [
            'paid' => 'Đã tất toán',
            'unpaid' => 'Đang nợ / Chưa thu',
            'partial' => 'Thu một phần',
        ];

        return [
            $invoice->invoice_code,
            $invoice->booking->booking_code ?? 'N/A',
            $roomNumbers,
            $roomDefs,
            $invoice->booking->customer->full_name ?? 'Khách lẻ',
            $invoice->booking->customer->phone ?? 'N/A',
            $invoice->total_amount,
            $invoice->amount_paid ?? 0,
            $debt,
            $statusMap[$invoice->payment_status] ?? $invoice->payment_status,
            $invoice->cashier->name ?? 'Hệ thống Auto',
            $invoice->created_at->format('d/m/Y H:i'),
        ];
    }

    // 4. Trang tri file Excel (Header in dam, nen toi, chu trang)
    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFF']],
                'fill' => ['fillType' => 'solid', 'color' => ['argb' => '0F172A']]
            ],
        ];
    }
}
