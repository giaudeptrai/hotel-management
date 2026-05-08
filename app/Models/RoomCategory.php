<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RoomCategory extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'description',
    ];

    public function definition()
    {
        return $this->hasMany(RoomDefinition::class);
    }

}
