<?php

namespace App\Services;
use App\Models\RoomDefinition;
use Illuminate\Support\Facades\Storage;

class RoomDefinitionsService {
    public function getAll($filters = []) {
        return RoomDefinition::with('category', 'type', 'amenities')
            ->when($filters['search'] ?? null, function ($query, $search) {
                $query->where('name', 'like', "%{$search}%");
            })
            ->when($filters['room_category_id'] ?? null, function ($query, $categoryId) {
                $query->where('room_category_id', $categoryId);
            })
            ->when($filters['room_type_id'] ?? null, function ($query, $typeId) {
                $query->where('room_type_id', $typeId);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(8)
            ->withQueryString();
    }

    public function findById($id) {
        return RoomDefinition::with('category', 'type', 'amenities')->findOrFail($id);
    }

    public function create(array $data){
        $definition = RoomDefinition::create([
            'name' => $data['name'],
            'room_category_id' => $data['room_category_id'],
            'room_type_id' => $data['room_type_id'],
            'base_price' => $data['base_price'],
            'area' => $data['area'],
            'view' => $data['view'] ?? null,
            'images' => $data['images'] ?? [],
        ]);
        if(isset($data['amenity_ids'])){
            $definition->amenities()->sync($data['amenity_ids']);
        }
        return $definition;
    }
    public function update($id, array $data){
        $definition = RoomDefinition::findOrFail($id);

        if (array_key_exists('images', $data)) {
            $currentImages = is_array($definition->images) ? $definition->images : [];
            $nextImages = is_array($data['images']) ? $data['images'] : [];
            $removedImages = array_values(array_diff($currentImages, $nextImages));

            $this->cleanupImages($removedImages);
        }

        $definition->update($data);
        if(isset($data['amenity_ids'])){
            $definition->amenities()->sync($data['amenity_ids']);
        }
        return $definition;
    }

    public function delete($id){
        $definition = $this->findById($id);
        $this->cleanupImages($definition->images);
        return $definition->delete();
    }

    private function cleanupImages($images){
        if(is_array($images)){
            foreach ($images as $path) {
                $path = str_replace('/storage/', '', $path);
                if (Storage::disk('public')->exists($path)) {
                    Storage::disk('public')->delete($path);
                }
            }
        }
    }
}
