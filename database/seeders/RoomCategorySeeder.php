<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class RoomCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Standard', 'description' => 'Hạng phòng tiêu chuẩn, gọn gàng và thoải mái.'],
            ['name' => 'Deluxe', 'description' => 'Hạng phòng cao cấp với không gian rộng hơn.'],
            ['name' => 'Suite', 'description' => 'Phòng thượng hạng dành cho khách ưu tiên.'],
            ['name' => 'Presidential', 'description' => 'Hạng phòng cao cấp nhất với trải nghiệm sang trọng.'],
        ];

        foreach ($categories as $category) {
            $slug = Str::slug($category['name']);

            DB::table('room_categories')->updateOrInsert(
                ['slug' => $slug],
                [
                    'name' => $category['name'],
                    'description' => $category['description'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }
}
