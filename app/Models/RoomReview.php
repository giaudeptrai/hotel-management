<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RoomReview extends Model
{
    use HasFactory;

    protected $fillable = [
        'booking_id',
        'customer_id',
        'room_definition_id',
        'rating',
        'comment',
    ];

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function roomDefinition()
    {
        return $this->belongsTo(RoomDefinition::class);
    }
}
