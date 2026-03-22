<?php

namespace App\Services;

use App\Models\RoomType;
use Illuminate\Support\Str;

class RoomTypeService
{
    /**
     * Lấy danh sách loại phòng kèm theo số lượng phòng thực tế (nếu có)
     */
    public function getAllForAdmin()
    {
        // Sử dụng withCount để lấy số lượng phòng thuộc loại này mà không tốn tài nguyên
        return RoomType::withCount('rooms')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Tạo loại phòng mới và tự động sinh Slug
     */
    public function createRoomType(array $data)
    {
        $data['slug'] = Str::slug($data['name']);
        return RoomType::create($data);
    }

    /**
     * Tìm loại phòng theo ID
     */
    public function findById($id)
    {
        return RoomType::findOrFail($id);
    }

    /**
     * Cập nhật loại phòng
     */
    public function updateRoomType($id, array $data)
    {
        $roomType = $this->findById($id);
        
        // Nếu đổi tên thì cập nhật lại slug
        if (isset($data['name'])) {
            $data['slug'] = Str::slug($data['name']);
        }
        
        $roomType->update($data);
        return $roomType;
    }

    /**
     * Xóa loại phòng
     */
    public function deleteRoomType($id)
    {
        $roomType = $this->findById($id);
        return $roomType->delete();
    }
}