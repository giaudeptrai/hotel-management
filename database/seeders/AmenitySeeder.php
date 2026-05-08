<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AmenitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $amenities = [
            ['name' => 'Wifi tốc độ cao', 'icon' => 'fa-solid fa-wifi'],
            ['name' => 'Minibar', 'icon' => 'fa-solid fa-wine-bottle'],
            ['name' => 'Bồn tắm nằm', 'icon' => 'fa-solid fa-bath'],
            ['name' => 'Smart TV 4K', 'icon' => 'fa-solid fa-tv'],
            ['name' => 'Ban công view biển', 'icon' => 'fa-solid fa-water'],
            ['name' => 'Máy pha cà phê', 'icon' => 'fa-solid fa-mug-hot'],
        ];

        foreach ($amenities as $amenity) {
            DB::table('amenities')->updateOrInsert(
                ['name' => $amenity['name']],
                [
                    'icon' => $amenity['icon'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }
}
