<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Invoice;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class InvoiceService
{
    
    public function getAll($filters = [])
    {
        $query = Invoice::with(['booking.customer', 'booking.bookingRooms.room', 'booking.bookingRooms.definition', 'cashier']);

        
        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where('invoice_code', 'like', "%{$search}%")
                  ->orWhereHas('booking', function($q) use ($search) {
                      $q->where('booking_code', 'like', "%{$search}%")
                        ->orWhereHas('customer', function($q2) use ($search) {
                            $q2->where('full_name', 'like', "%{$search}%")
                               ->orWhere('phone', 'like', "%{$search}%");
                        });
                  });
        }

        
        if (!empty($filters['status'])) {
            $query->where('payment_status', $filters['status']);
        }

        
        if (!empty($filters['date_type']) && !empty($filters['date_value'])) {
            $type = $filters['date_type'];
            $val = $filters['date_value'];

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

        
        if (!empty($filters['room_definition_id'])) {
            $query->whereHas('booking.bookingRooms', function ($q) use ($filters) {
                $q->where('room_definition_id', $filters['room_definition_id']);
            });
        }

        
        if (!empty($filters['room_id'])) {
            $query->whereHas('booking.bookingRooms', function ($q) use ($filters) {
                $q->where('room_id', $filters['room_id']);
            });
        }

        return $query->latest()->paginate(10)->withQueryString();

    }

    

    public function generateInvoiceForBooking(Booking $booking, $taxRate = 0.08)
    {
        return DB::transaction(function () use ($booking, $taxRate) {
            $booking->refresh();
            $booking->load(['bookingRooms', 'bookingServices']);

            $invoice = Invoice::firstOrNew(['booking_id' => $booking->id]);
            $existingPaid = (float) ($invoice->amount_paid ?? 0);
            $depositPaid = (float) ($booking->deposit_amount ?? 0);
            $stablePaidAmount = max($existingPaid, $depositPaid);

            
            
            $start = Carbon::parse($booking->check_in_actual ?? $booking->check_in_expected)->startOfDay();
            $expectedEnd = Carbon::parse($booking->check_out_expected)->setTime(12, 0, 0);

            $actualCheckout = $booking->check_out_actual
                ? Carbon::parse($booking->check_out_actual)
                : ($booking->status === 'checked_in' ? Carbon::now() : $expectedEnd->copy());

            
            $billingEndDay = $actualCheckout->copy()->startOfDay();
            $stayDuration = max(1, $start->diffInDays($billingEndDay));

            
            if ($booking->status === 'cancelled') {
                
                $totalAmount = $booking->deposit_amount;

                $invoice->fill([
                    'room_charge'     => 0,
                    'service_charge'  => 0,
                    'tax_amount'      => 0,
                    'total_amount'    => $totalAmount,
                    'amount_paid'     => $booking->deposit_amount, 
                    'payment_status'  => 'paid', 
                ]);
            } else {
                
                $roomCharge = $booking->bookingRooms->sum(fn($r) => $r->price * $stayDuration);
                $serviceCharge = $booking->bookingServices->sum('total_price');
                $subTotal = $roomCharge + $serviceCharge;
                $taxAmount = $subTotal * $taxRate;
                $totalAmount = $subTotal + $taxAmount;

                $invoice->fill([
                    'room_charge'     => $roomCharge,
                    'service_charge'  => $serviceCharge,
                    'tax_amount'      => $taxAmount,
                    'total_amount'    => $totalAmount,
                    'amount_paid'     => $stablePaidAmount,
                ]);

                
                if ($invoice->total_amount > $invoice->amount_paid) {
                    $invoice->payment_status = ($invoice->amount_paid > 0) ? 'partial' : 'unpaid';
                } else {
                    $invoice->payment_status = 'paid';
                }
            }

            if (empty($invoice->invoice_code)) {
                $invoice->invoice_code = 'INV-' . date('ymd') . '-' . strtoupper(Str::random(4));
            }

            $invoice->save();
            return $invoice;
        });
    }

    
    public function markAsPaid(Invoice $invoice)
    {
        return DB::transaction(function () use ($invoice) {

            
            $debtAmount = $invoice->total_amount - ($invoice->amount_paid ?? 0);

            if ($debtAmount > 0) {
                
                $invoice->amount_paid += $debtAmount;
            }

            $invoice->update([
                'payment_status' => 'paid',
                'payment_method' => 'cash',
                'paid_at'        => Carbon::now(),
                'cashier_id'     => auth()->id(), 
            ]);

            
            if ($invoice->booking && $invoice->booking->customer) {
                $invoice->booking->customer->increment('total_spent', $debtAmount);
            }

            return $invoice;
        });
    }
}
