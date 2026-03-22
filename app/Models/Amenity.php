<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Amenity extends Model
{
    protected $fillable = ['name', 'icon'];

    protected $appends = ['icon_url'];

    public function getIconUrlAttribute()
    {
        // Vì chỉ dùng file nên cứ đi qua asset() là chắc chắn lên hình trên Vue
        return $this->icon ? asset($this->icon) : null;
    }

    public function definitions()
    {
        return $this->belongsToMany(RoomDefinition::class, 'room_definition_amenity');
    }
}