<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Invoice;
use App\Models\Room;
use Carbon\Carbon;

class DashboardAnalyticsService
{
    public function build(): array
    {
        $rooms = $this->buildRoomStats();
        $bookings = $this->buildBookingStats();
        $finance = $this->buildFinanceStats();

        return [
            'generated_at' => now()->toDateTimeString(),
            'rooms' => $rooms,
            'bookings' => $bookings,
            'operations_today' => $this->buildTodayOps(),
            'finance' => $finance,
            'revenue_reports' => [
                'daily' => $this->buildRevenueReport('daily', 30),
                'monthly' => $this->buildRevenueReport('monthly', 12),
                'yearly' => $this->buildRevenueReport('yearly', 5),
            ],
            'recent_invoices' => $this->buildRecentInvoices(),
            'kpis' => [
                'occupancy_rate' => $rooms['occupancy_rate'],
                'collected_revenue' => $finance['collected_revenue'],
                'outstanding_revenue' => $finance['outstanding_revenue'],
                'bookings_in_house' => $bookings['status']['checked_in'] ?? 0,
            ],
        ];
    }

    private function buildRoomStats(): array
    {
        $totalRooms = Room::count();
        $available = Room::where('status', 'available')->count();
        $occupied = Room::where('status', 'occupied')->count();
        $cleaning = Room::where('status', 'cleaning')->count();
        $maintenance = Room::where('status', 'maintenance')->count();

        return [
            'total' => $totalRooms,
            'available' => $available,
            'occupied' => $occupied,
            'cleaning' => $cleaning,
            'maintenance' => $maintenance,
            'occupancy_rate' => $totalRooms > 0 ? round(($occupied / $totalRooms) * 100, 2) : 0,
        ];
    }

    private function buildBookingStats(): array
    {
        $grouped = Booking::query()
            ->select('status')
            ->selectRaw('COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        return [
            'total' => Booking::count(),
            'status' => [
                'pending' => (int) ($grouped['pending'] ?? 0),
                'confirmed' => (int) ($grouped['confirmed'] ?? 0),
                'checked_in' => (int) ($grouped['checked_in'] ?? 0),
                'checked_out' => (int) ($grouped['checked_out'] ?? 0),
                'cancelled' => (int) ($grouped['cancelled'] ?? 0),
            ],
        ];
    }

    private function buildTodayOps(): array
    {
        $start = Carbon::today()->startOfDay();
        $end = Carbon::today()->endOfDay();

        return [
            'new_bookings' => Booking::whereBetween('created_at', [$start, $end])->count(),
            'expected_check_in' => Booking::whereBetween('check_in_expected', [$start, $end])->count(),
            'expected_check_out' => Booking::whereBetween('check_out_expected', [$start, $end])->count(),
            'cancelled' => Booking::where('status', 'cancelled')->whereBetween('updated_at', [$start, $end])->count(),
        ];
    }

    private function buildFinanceStats(): array
    {
        $monthStart = Carbon::now()->startOfMonth();
        $monthEnd = Carbon::now()->endOfMonth();

        $grossRevenue = (float) Invoice::sum('total_amount');
        $collectedRevenue = (float) Invoice::sum('amount_paid');

        return [
            'gross_revenue' => $grossRevenue,
            'collected_revenue' => $collectedRevenue,
            'outstanding_revenue' => max(0, $grossRevenue - $collectedRevenue),
            'gross_this_month' => (float) Invoice::whereBetween('created_at', [$monthStart, $monthEnd])->sum('total_amount'),
            'collected_this_month' => (float) Invoice::whereBetween('created_at', [$monthStart, $monthEnd])->sum('amount_paid'),
            'paid_invoices' => Invoice::where('payment_status', 'paid')->count(),
            'open_invoices' => Invoice::whereIn('payment_status', ['unpaid', 'partial'])->count(),
        ];
    }

    private function buildRevenueReport(string $mode, int $periodCount): array
    {
        [$start, $end] = $this->resolveRange($mode, $periodCount);

        $invoices = Invoice::query()
            ->whereBetween('created_at', [$start, $end])
            ->get([
                'id',
                'booking_id',
                'room_charge',
                'service_charge',
                'tax_amount',
                'total_amount',
                'amount_paid',
                'created_at',
            ]);

        $grouped = $invoices->groupBy(function ($invoice) use ($mode) {
            return $this->formatPeriodKey(Carbon::parse($invoice->created_at), $mode);
        });

        $rows = [];
        $cursor = $start->copy();

        while ($cursor->lte($end)) {
            $periodKey = $this->formatPeriodKey($cursor, $mode);
            $bucket = $grouped->get($periodKey, collect());

            $gross = (float) $bucket->sum('total_amount');
            $collected = (float) $bucket->sum('amount_paid');

            $rows[] = [
                'period_key' => $periodKey,
                'period_label' => $this->formatPeriodLabel($cursor, $mode),
                'invoice_count' => $bucket->count(),
                'booking_count' => $bucket->pluck('booking_id')->filter()->unique()->count(),
                'room_revenue' => (float) $bucket->sum('room_charge'),
                'service_revenue' => (float) $bucket->sum('service_charge'),
                'tax_total' => (float) $bucket->sum('tax_amount'),
                'gross_revenue' => $gross,
                'collected_revenue' => $collected,
                'outstanding_revenue' => max(0, $gross - $collected),
            ];

            $cursor = $this->nextPeriod($cursor, $mode);
        }

        $totals = [
            'invoice_count' => array_sum(array_column($rows, 'invoice_count')),
            'booking_count' => array_sum(array_column($rows, 'booking_count')),
            'gross_revenue' => array_sum(array_column($rows, 'gross_revenue')),
            'collected_revenue' => array_sum(array_column($rows, 'collected_revenue')),
            'outstanding_revenue' => array_sum(array_column($rows, 'outstanding_revenue')),
            'room_revenue' => array_sum(array_column($rows, 'room_revenue')),
            'service_revenue' => array_sum(array_column($rows, 'service_revenue')),
            'tax_total' => array_sum(array_column($rows, 'tax_total')),
        ];

        return [
            'mode' => $mode,
            'from' => $start->toDateString(),
            'to' => $end->toDateString(),
            'rows' => $rows,
            'totals' => $totals,
        ];
    }

    private function buildRecentInvoices(): array
    {
        return Invoice::query()
            ->with(['booking.customer:id,full_name'])
            ->latest()
            ->limit(8)
            ->get([
                'id',
                'booking_id',
                'invoice_code',
                'payment_status',
                'total_amount',
                'amount_paid',
                'created_at',
            ])
            ->map(function (Invoice $invoice) {
                return [
                    'id' => $invoice->id,
                    'invoice_code' => $invoice->invoice_code,
                    'booking_id' => $invoice->booking_id,
                    'booking_code' => optional($invoice->booking)->booking_code,
                    'customer_name' => optional(optional($invoice->booking)->customer)->full_name,
                    'payment_status' => $invoice->payment_status,
                    'total_amount' => (float) $invoice->total_amount,
                    'amount_paid' => (float) $invoice->amount_paid,
                    'created_at' => optional($invoice->created_at)->toDateTimeString(),
                ];
            })
            ->values()
            ->all();
    }

    private function resolveRange(string $mode, int $periodCount): array
    {
        if ($mode === 'daily') {
            $start = Carbon::today()->subDays($periodCount - 1)->startOfDay();
            $end = Carbon::today()->endOfDay();

            return [$start, $end];
        }

        if ($mode === 'monthly') {
            $start = Carbon::now()->subMonths($periodCount - 1)->startOfMonth();
            $end = Carbon::now()->endOfMonth();

            return [$start, $end];
        }

        $start = Carbon::now()->subYears($periodCount - 1)->startOfYear();
        $end = Carbon::now()->endOfYear();

        return [$start, $end];
    }

    private function nextPeriod(Carbon $cursor, string $mode): Carbon
    {
        if ($mode === 'daily') {
            return $cursor->copy()->addDay();
        }

        if ($mode === 'monthly') {
            return $cursor->copy()->addMonth();
        }

        return $cursor->copy()->addYear();
    }

    private function formatPeriodKey(Carbon $date, string $mode): string
    {
        if ($mode === 'daily') {
            return $date->format('Y-m-d');
        }

        if ($mode === 'monthly') {
            return $date->format('Y-m');
        }

        return $date->format('Y');
    }

    private function formatPeriodLabel(Carbon $date, string $mode): string
    {
        if ($mode === 'daily') {
            return $date->format('d/m');
        }

        if ($mode === 'monthly') {
            return 'Thg ' . $date->format('m/Y');
        }

        return 'Nam ' . $date->format('Y');
    }
}
