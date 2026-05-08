<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ServiceSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        $services = [
            ['name' => 'Breakfast Buffet', 'type' => 'food', 'price' => 180000, 'unit' => 'suất', 'is_active' => true],
            ['name' => 'Laundry', 'type' => 'laundry', 'price' => 50000, 'unit' => 'kg', 'is_active' => true],
            ['name' => 'Airport Pickup', 'type' => 'other', 'price' => 300000, 'unit' => 'lần', 'is_active' => true],
            ['name' => 'Mini Bar Combo', 'type' => 'drink', 'price' => 120000, 'unit' => 'gói', 'is_active' => true],
            ['name' => 'Spa Relax', 'type' => 'spa', 'price' => 450000, 'unit' => 'lần', 'is_active' => true],
        ];

        $rows = array_map(function ($service) use ($now) {
            return [
                'name' => $service['name'],
                'type' => $service['type'],
                'price' => $service['price'],
                'unit' => $service['unit'],
                'is_active' => $service['is_active'],
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }, $services);

        DB::table('services')->upsert($rows, ['name'], ['type', 'price', 'unit', 'is_active', 'updated_at']);
    }
}
