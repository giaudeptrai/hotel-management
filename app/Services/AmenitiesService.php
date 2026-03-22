<?php

namespace App\Services;

use App\Models\Amenity;
use Illuminate\Support\Facades\Storage;

class AmenitiesService
{
    public function getAll()
    {
        return Amenity::orderBy('created_at', 'desc')->get();
    }

    public function create(array $data)
    {
        return Amenity::create([
            'name' => $data['name'],
            'icon' => $data['icon'] ?? null,
        ]);
    }

    public function findById($id)
    {
        return Amenity::findOrFail($id);
    }

    public function update($id, array $data)
    {
        $amenity = Amenity::findOrFail($id);
        
        // Nếu có upload ảnh mới (tức là trong $data có key 'icon')
        if (isset($data['icon']) && $amenity->icon) {
            // Xóa ảnh cũ trong storage cho đỡ rác máy
            $oldPath = str_replace('/storage/', '', $amenity->icon);
            if (Storage::disk('public')->exists($oldPath)) {
                Storage::disk('public')->delete($oldPath);
            }
        }

        $amenity->update($data);
        return $amenity;
    }

    public function delete($id)
    {
        $amenity = $this->findById($id);
        
        // Xóa file vật lý trước khi xóa record
        if ($amenity->icon) {
            $path = str_replace('/storage/', '', $amenity->icon);
            Storage::disk('public')->delete($path);
        }

        return $amenity->delete();
    }
}