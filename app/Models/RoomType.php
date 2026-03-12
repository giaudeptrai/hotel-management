<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RoomType extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'name',
        'slug',
        'price_per_night',
        'discount_price',
        'max_adults',
        'max_children',
        'bed_type',
        'view',
        'area',
        'description',
        'amenities',
        'images',
        'is_active'
    ];

    protected $casts = [
        'amenities' => 'array',
        'images' => 'array',
        'is_active' => 'boolean',
    ];

    public function rooms()
    {
        return $this->hasMany(Room::class);
    }
}
