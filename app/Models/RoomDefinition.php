<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
class RoomDefinition extends Model
{
    protected $appends = ['image_urls'];
    protected $casts = [
        'images' => 'array',
    ];
    protected $fillable = [
        'name',
        'room_category_id',
        'room_type_id',
        'base_price',
        'area',
        'view',
        'images',
    ];

    public function category()
    {
        return $this->belongsTo(RoomCategory::class);
    }

    public function type()
    {
        return $this->belongsTo(RoomType::class);
    }

    public function rooms()
    {
        return $this->hasMany(Room::class);
    }

    public function amenities()
    {
        return $this->belongsToMany(Amenity::class, 'amenity_room_definition');
    }

    public function getImageUrlsAttribute()
    {
        if (!$this->images || !is_array($this->images)) return [];

        return array_map(function ($path) {
            // 1. Nếu link đã có http (ảnh mạng) thì giữ nguyên
            if (str_starts_with($path, 'http')) return $path;

            // 2. Nếu path ĐÃ CÓ chữ /storage/ ở đầu (do Storage::url sinh ra)
            // Thì mình CHỈ CẦN dùng asset($path) là xong. 
            // ĐỪNG viết asset('storage/' . $path) vì nó sẽ bị lặp từ.
            if (str_starts_with($path, '/storage/')) {
                return asset($path);
            }

            // 3. Trường hợp dự phòng nếu database chỉ lưu mỗi "room_definitions/abc.jpg"
            return asset('storage/' . $path);
        }, $this->images);
    }
}
