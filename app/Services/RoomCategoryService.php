<?php

namespace App\Services;

use App\Models\RoomCategory;
use Illuminate\Support\Str;

class RoomCategoryService
{
    public function getAll()
    {
        return RoomCategory::orderBy('created_at', 'desc')->get();
    }

    public function create(array $data)
    {
        $data['slug'] = Str::slug($data['name']);
        return RoomCategory::create($data);
    }

    public function findById($id)
    {
        return RoomCategory::findOrFail($id);
    }

    public function update($id, array $data)
    {
        $category = $this->findById($id);
        if (isset($data['name'])) {
            $data['slug'] = Str::slug($data['name']);
        }
        return $category->update($data);
    }

    public function delete($id)
    {
        return $this->findById($id)->delete();
    }
}