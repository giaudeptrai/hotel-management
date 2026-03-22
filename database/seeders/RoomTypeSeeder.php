<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\RoomType;
use Illuminate\Support\Str;

class RoomTypeSeeder extends Seeder
{
   public function run(): void {
        $types = [
            ['name' => 'Single', 'capacity_adult' => 1, 'capacity_child' => 0],
            ['name' => 'Double', 'capacity_adult' => 2, 'capacity_child' => 1],
            ['name' => 'King', 'capacity_adult' => 2, 'capacity_child' => 2],
        ];
        foreach ($types as $t) {
        $t['slug'] = \Illuminate\Support\Str::slug($t['name']);

        \App\Models\RoomType::create($t);
        }
   }
}