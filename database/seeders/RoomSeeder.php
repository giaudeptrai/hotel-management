<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\RoomType;
use App\Models\Room;
use Illuminate\Support\Str;

class RoomSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Tạo dữ liệu Loại Phòng (Room Types)
        $deluxe = RoomType::create([
            'name' => 'Deluxe Ocean View',
            'slug' => Str::slug('Deluxe Ocean View'),
            'price_per_night' => 1500000,
            'discount_price' => 1200000,
            'max_adults' => 2,
            'max_children' => 1,
            'bed_type' => '1 Giường King',
            'view' => 'Hướng Biển',
            'area' => 45,
            'description' => 'Phòng Deluxe sang trọng với ban công riêng nhìn ra toàn cảnh đại dương.',
            'amenities' => ['Free Wifi', 'Smart TV 55 inch', 'Bồn tắm nằm', 'Minibar'], // Mảng tự biến thành JSON
            'images' => ['/images/rooms/deluxe-1.jpg', '/images/rooms/deluxe-2.jpg'],
            'is_active' => true,
        ]);

        $family = RoomType::create([
            'name' => 'Family Suite',
            'slug' => Str::slug('Family Suite'),
            'price_per_night' => 2500000,
            'max_adults' => 4,
            'max_children' => 2,
            'bed_type' => '2 Giường Queen',
            'view' => 'Hướng Hồ Bơi',
            'area' => 70,
            'description' => 'Không gian rộng rãi hoàn hảo cho gia đình, kết nối trực tiếp ra hồ bơi.',
            'amenities' => ['Free Wifi', 'Khu vực bếp', 'Sofa Bed', 'Máy pha cafe'],
            'images' => ['/images/rooms/family-1.jpg'],
            'is_active' => true,
        ]);

        // 2. Tạo dữ liệu Phòng thực tế (Rooms) dựa trên loại phòng vừa tạo
        // Tạo 3 phòng Deluxe ở tầng 1
        Room::insert([
            ['room_number' => '101', 'room_type_id' => $deluxe->id, 'floor' => 'Tầng 1', 'status' => 'available'],
            ['room_number' => '102', 'room_type_id' => $deluxe->id, 'floor' => 'Tầng 1', 'status' => 'occupied'],
            ['room_number' => '103', 'room_type_id' => $deluxe->id, 'floor' => 'Tầng 1', 'status' => 'cleaning'],
        ]);

        // Tạo 2 phòng Family ở tầng 2
        Room::insert([
            ['room_number' => '201', 'room_type_id' => $family->id, 'floor' => 'Tầng 2', 'status' => 'available'],
            ['room_number' => '202', 'room_type_id' => $family->id, 'floor' => 'Tầng 2', 'status' => 'maintenance'],
        ]);
    }
}