<?php

namespace App\Services;

use App\Models\RoomType;
use Illuminate\Support\Str;

class RoomTypeService
{
    
    public function getAllForAdmin($filters = [])
    {
        
        return RoomType::withCount('rooms')
            ->when($filters['search'] ?? null, function ($query, $search) {
                $query->where(function ($subQuery) use ($search) {
                    $subQuery->where('name', 'like', "%{$search}%")
                        ->orWhere('slug', 'like', "%{$search}%");
                });
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10)
            ->withQueryString();
    }

    
    public function createRoomType(array $data)
    {
        $data['slug'] = Str::slug($data['name']);
        return RoomType::create($data);
    }

    
    public function findById($id)
    {
        return RoomType::findOrFail($id);
    }

    
    public function updateRoomType($id, array $data)
    {
        $roomType = $this->findById($id);

        
        if (isset($data['name'])) {
            $data['slug'] = Str::slug($data['name']);
        }

        $roomType->update($data);
        return $roomType;
    }

    
    public function deleteRoomType($id)
    {
        $roomType = $this->findById($id);
        return $roomType->delete();
    }
}
