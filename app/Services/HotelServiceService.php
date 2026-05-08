<?php

namespace App\Services;

use App\Models\Service; 

class HotelServiceService
{
    public function getAll($filters = [])
    {
        return Service::query()
            ->when($filters['search'] ?? null, function ($query, $search) {
                $query->where('name', 'like', "%{$search}%");
            })
            ->when($filters['type'] ?? null, function ($query, $type) {
                $query->where('type', $type);
            })
            ->when(isset($filters['status']) && $filters['status'] !== '', function ($query) use ($filters) {
                $query->where('is_active', (bool) $filters['status']);
            })
            ->latest()
            ->paginate(15);
    }

    public function create(array $data)
    {
        return Service::create([
            'name'      => $data['name'],
            'type'      => $data['type'] ?? 'other', 
            'price'     => $data['price'],
            'unit'      => $data['unit'],
            'is_active' => $data['is_active'] ?? true,
        ]);
    }

    public function update(Service $service, array $data)
    {
        $service->update($data);
        return $service;
    }

    public function delete(Service $service)
    {
        
        if ($service->bookingServices()->exists()) {
            
            $service->update(['is_active' => false]);
            return false;
        }

        return $service->delete();
    }
}
