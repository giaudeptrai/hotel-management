<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class RoomTypeSeeder extends Seeder
{
    public function run(): void
    {
        $types = [
            ['name' => 'Single', 'capacity_adult' => 1, 'capacity_child' => 0],
            ['name' => 'Double', 'capacity_adult' => 2, 'capacity_child' => 1],
            ['name' => 'King', 'capacity_adult' => 2, 'capacity_child' => 2],
        ];

        foreach ($types as $type) {
            $slug = Str::slug($type['name']);

            DB::table('room_types')->updateOrInsert(
                ['slug' => $slug],
                [
                    'name' => $type['name'],
                    'capacity_adult' => $type['capacity_adult'],
                    'capacity_child' => $type['capacity_child'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }
}
