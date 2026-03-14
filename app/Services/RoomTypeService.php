<?php

namespace App\Services;
use App\Models\RoomType;

class RoomTypeService
{
    public function getActiveRoomTypes()
    {
        return RoomType::where('is_active', true)->get();
    }
    public function getRoomTypeSlug($slug)
    {
        return RoomType::where('slug', $slug)
            ->with('rooms')
            ->firstorFail();// Trả về 404 nếu không tìm thấy
    }

    public function getForAdmin()
    {
        return RoomType::orderBy('created_at', 'desc')->get();
    }

    public function createRoomType($data)
    {
        $data['slug'] = Str::slug($data['name']);
        return \App\models\RoomType::create($data);
    }
}