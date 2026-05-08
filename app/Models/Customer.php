<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'user_id',
        'full_name',
        'phone',
        'cccd_number',
        'email',
        'birthday',
        'gender',
        'address',
        'total_bookings',
        'total_spent',
    ];

    /**
     * Mối quan hệ với bảng Users (Tài khoản)
     */
    // app/Models/Customer.php

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Accessor: Tự động lấy tên thông minh
     * Gọi: $customer->display_name
     */
    public function getDisplayNameAttribute()
    {
        // Nếu có user_id (online), ưu tiên lấy tên từ bảng users
        if ($this->user_id && $this->user) {
            return $this->user->name;
        }
        // Ngược lại lấy tên nhập tại quầy
        return $this->full_name;
    }

    /**
     * Accessor: Tự động lấy Email thông minh
     */
    public function getDisplayEmailAttribute()
    {
        return ($this->user_id && $this->user) ? $this->user->email : $this->email;
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    public function roomReviews()
    {
        return $this->hasMany(RoomReview::class);
    }
}
