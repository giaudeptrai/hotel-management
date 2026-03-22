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
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Accessor: Lấy tên hiển thị thông minh
     * Gọi $customer->display_name nó sẽ tự hiểu
     */
    public function getDisplayNameAttribute()
    {
        return $this->user ? $this->user->name : $this->full_name;
    }
}