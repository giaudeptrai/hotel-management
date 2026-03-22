<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RoomDefinitionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void {
        // Ví dụ: Deluxe (ID: 2) + Double (ID: 2)
        $def = \App\Models\RoomDefinition::create([
            'name' => 'Deluxe Double',
            'room_category_id' => 2,
            'room_type_id' => 2,
            'base_price' => 1500000,
            'area' => 45,
            'view' => 'Hướng phố',
            'images' => ['https://images.pexels.com/photos/271618/pexels-photo-271618.jpeg']
        ]);

        // Gán 3 tiện nghi đầu tiên cho định nghĩa này
        $def->amenities()->attach([1, 2, 4]);

        // Ông có thể tạo thêm vài cái nữa tương tự ở đây...
    }
}
