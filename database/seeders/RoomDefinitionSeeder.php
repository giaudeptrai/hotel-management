<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RoomDefinitionSeeder extends Seeder
{
    public function run(): void
    {
        $definitions = [
            [
                'name' => 'Standard Single City View',
                'category_slug' => 'standard',
                'type_slug' => 'single',
                'description' => 'Phong tieu chuan danh cho 1 nguoi, khong gian gon gang va day du tien nghi co ban.',
                'base_price' => 700000,
                'area' => 24,
                'view' => 'Hướng phố',
                'images' => ['https://images.pexels.com/photos/271624/pexels-photo-271624.jpeg'],
                'amenities' => ['Wifi tốc độ cao', 'Smart TV 4K'],
            ],
            [
                'name' => 'Deluxe Double City View',
                'category_slug' => 'deluxe',
                'type_slug' => 'double',
                'description' => 'Phong doi cao cap voi khong gian rong hon, phu hop cho cap doi hoac gia dinh nho.',
                'base_price' => 1200000,
                'area' => 35,
                'view' => 'Hướng phố',
                'images' => ['https://images.pexels.com/photos/271618/pexels-photo-271618.jpeg'],
                'amenities' => ['Wifi tốc độ cao', 'Minibar', 'Smart TV 4K'],
            ],
            [
                'name' => 'Suite King Ocean View',
                'category_slug' => 'suite',
                'type_slug' => 'king',
                'description' => 'Suite huong bien voi giuong king-size, ban cong rieng va khong gian nghi duong sang trong.',
                'base_price' => 2500000,
                'area' => 58,
                'view' => 'Hướng biển',
                'images' => ['https://images.pexels.com/photos/1457842/pexels-photo-1457842.jpeg'],
                'amenities' => ['Wifi tốc độ cao', 'Minibar', 'Bồn tắm nằm', 'Ban công view biển', 'Máy pha cà phê'],
            ],
            [
                'name' => 'Presidential King Panorama',
                'category_slug' => 'presidential',
                'type_slug' => 'king',
                'description' => 'Phong tong thong cao cap nhat, tam nhin toan canh va day du dich vu cao cap chuan VIP.',
                'base_price' => 5500000,
                'area' => 120,
                'view' => 'Toàn cảnh thành phố',
                'images' => ['https://images.pexels.com/photos/164595/pexels-photo-164595.jpeg'],
                'amenities' => ['Wifi tốc độ cao', 'Minibar', 'Bồn tắm nằm', 'Smart TV 4K', 'Ban công view biển', 'Máy pha cà phê'],
            ],
        ];

        foreach ($definitions as $item) {
            $categoryId = DB::table('room_categories')->where('slug', $item['category_slug'])->value('id');
            $typeId = DB::table('room_types')->where('slug', $item['type_slug'])->value('id');

            if (!$categoryId || !$typeId) {
                continue;
            }

            DB::table('room_definitions')->updateOrInsert(
                ['name' => $item['name']],
                [
                    'room_category_id' => $categoryId,
                    'room_type_id' => $typeId,
                    'description' => $item['description'],
                    'base_price' => $item['base_price'],
                    'area' => $item['area'],
                    'view' => $item['view'],
                    'images' => json_encode($item['images']),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );

            $definitionId = DB::table('room_definitions')->where('name', $item['name'])->value('id');
            if (!$definitionId) {
                continue;
            }

            $amenityIds = DB::table('amenities')->whereIn('name', $item['amenities'])->pluck('id')->all();

            DB::table('amenity_room_definition')->where('room_definition_id', $definitionId)->delete();

            $pivotRows = array_map(function ($amenityId) use ($definitionId) {
                return [
                    'room_definition_id' => $definitionId,
                    'amenity_id' => $amenityId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }, $amenityIds);

            if (!empty($pivotRows)) {
                DB::table('amenity_room_definition')->insert($pivotRows);
            }
        }
    }
}
