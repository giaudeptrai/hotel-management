<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    use HasFactory;

    protected $fillable = [
    'booking_id',
    'invoice_code',
    'cashier_id', // thu ngân
    'room_charge',
    'service_charge',
    'tax_amount',
    'total_amount',
    'amount_paid',
    'payment_status',
    'payment_method',
    'paid_at'
    ];

    protected $casts = [
        'paid_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->invoice_code)) {
                $model->invoice_code = 'INV-' . date('ymd') . '-' . strtoupper(substr(uniqid(), -4));
            }
        });
    }

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }

    public function cashier()
    {
        return $this->belongsTo(User::class, 'cashier_id');
    }
}
