<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RoomCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void {
        $categories = [
            ['name' => 'Standard', 'slug' => 'standard', 'description' => 'Hạng phòng tiêu chuẩn...'],
            ['name' => 'Deluxe', 'slug' => 'deluxe', 'description' => 'Hạng phòng cao cấp...'],
            ['name' => 'Suite', 'slug' => 'suite', 'description' => 'Phòng thượng hạng...'],
            ['name' => 'Presidential', 'slug' => 'presidential', 'description' => 'Đẳng cấp hoàng gia...'],
        ];

        foreach ($categories as $c) {
            // Kiểm tra dựa trên 'slug', nếu khớp thì update các cột còn lại
            \App\Models\RoomCategory::updateOrCreate(['slug' => $c['slug']], $c);
        }
    }
}
