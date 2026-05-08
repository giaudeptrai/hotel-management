<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BookingRoom extends Model
{
    use HasFactory;

    protected $fillable = [
        'booking_id',
        'room_definition_id',
        'room_id',
        'price',
    ];

    // Thuộc về Đơn đặt phòng nào
    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }

    // Khách chọn Hạng phòng nào (Online)
    public function definition()
    {
        return $this->belongsTo(RoomDefinition::class, 'room_definition_id');
    }

    // Lễ tân xếp vào Phòng vật lý nào (Thực tế)
    public function room()
    {
        return $this->belongsTo(Room::class);
    }
}
