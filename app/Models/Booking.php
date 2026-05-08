<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Invoice;
use App\Models\RoomReview;
use Carbon\Carbon;

class Booking extends Model
{
    use HasFactory;

    protected $fillable = [
        'booking_code',
        'customer_id',
        'check_in_expected',
        'check_out_expected',
        'check_in_actual',
        'check_out_actual',
        'total_amount',
        'deposit_amount',
        'status',
        'source',
        'special_requests',
        'admin_note',
    ];

    protected $casts = [
        'check_in_expected' => 'datetime',
        'check_out_expected' => 'datetime',
        'check_in_actual' => 'datetime',
        'check_out_actual' => 'datetime',
    ];

    /**
     * Tự động sinh mã Booking khi tạo mới
     */
    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->booking_code)) {
                // Tạo mã dạng: BK-YYMMDD-XXXX
                $model->booking_code = 'BK-' . date('ymd') . '-' . strtoupper(substr(uniqid(), -4));
            }
        });
    }

    /**
     * Thuộc về 1 khách hàng
     */
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Có nhiều phòng trong 1 booking
     */
    public function bookingRooms()
    {
        return $this->hasMany(BookingRoom::class);
    }

    /**
     * Có nhiều dịch vụ đi kèm
     */
    public function bookingServices()
    {
        return $this->hasMany(BookingService::class);
    }

    /**
     * Có 1 hóa đơn thanh toán
     */
    public function invoice()
    {
        return $this->hasOne(Invoice::class);
    }

    public function review()
    {
        return $this->hasOne(RoomReview::class);
    }

    /**
     * Accessor: Tính số đêm lưu trú
     */
    public function getStayDurationAttribute()
    {
        $in = Carbon::parse($this->check_in_expected);
        $out = Carbon::parse($this->check_out_expected);
        return $in->diffInDays($out) > 0 ? $in->diffInDays($out) : 1; // Ít nhất 1 đêm
    }

}
