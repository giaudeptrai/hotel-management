<?php

namespace App\Models;
use App\Models\RoomDefinition;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Room extends Model
{
    protected $fillable = [
        'room_definition_id',
        'room_number',
        'floor',
        'status',
        'is_active'
    ];

    public function definition()
    {
        return $this->belongsTo(RoomDefinition::class, 'room_definition_id');
    }
}
