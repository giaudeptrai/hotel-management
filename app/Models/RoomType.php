<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class RoomType extends Model
{
    protected $fillable = ['name', 'slug', 'capacity_adult', 'capacity_child'];

    /**
     * Một Loại phòng có nhiều Định nghĩa phòng (Room Definitions)
     */
    public function roomDefinitions(): HasMany
    {
        return $this->hasMany(RoomDefinition::class, 'room_type_id');
    }

    /**
     * Một Loại phòng có nhiều Phòng thực tế thông qua Định nghĩa phòng
     * Đây chính là cái "rooms()" mà Controller đang réo gọi nè Giàu!
     */
    public function rooms(): HasManyThrough
    {
        return $this->hasManyThrough(
            Room::class,           // Model đích (Phòng)
            RoomDefinition::class, // Model trung gian (Định nghĩa phòng)
            'room_type_id',        // Khóa ngoại trên bảng trung gian
            'room_definition_id',  // Khóa ngoại trên bảng đích
            'id',                  // Khóa chính trên bảng Loại phòng
            'id'                   // Khóa chính trên bảng trung gian
        );
    }
}