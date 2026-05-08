<?php

namespace App\Services;

use App\Models\Amenity;
use Illuminate\Support\Facades\Storage;

class AmenitiesService
{
    public function getAll($filters = [])
    {
        return Amenity::when($filters['search'] ?? null, function ($query, $search) {
                $query->where('name', 'like', "%{$search}%");
            })
            ->orderBy('created_at', 'desc')
            ->paginate(12)
            ->withQueryString();
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

        
        if (isset($data['icon']) && $amenity->icon) {
            
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

        
        if ($amenity->icon) {
            $path = str_replace('/storage/', '', $amenity->icon);
            Storage::disk('public')->delete($path);
        }

        return $amenity->delete();
    }
}
