<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AmenitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void {
        $amenities = ['Wifi tốc độ cao', 'Minibar', 'Bồn tắm nằm', 'Smart TV 4K', 'Ban công view biển', 'Máy pha cà phê'];
        foreach ($amenities as $a) \App\Models\Amenity::create(['name' => $a, 'icon' => 'star']);
    }
}
