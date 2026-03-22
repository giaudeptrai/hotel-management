<?php

namespace App\Services;
use App\Models\RoomDefinition;
use Illuminate\Support\Facades\Storage;

class RoomDefinitionsService {
    public function getAll() {
        return RoomDefinition::with('category', 'type', 'amenities')->orderBy('created_at', 'desc')->get();
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
        if (isset($data['images'])&& !empty($data['images'])){
            $this->cleanupImages($definition->images);
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