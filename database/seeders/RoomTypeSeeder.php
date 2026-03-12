<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\RoomType;
use Illuminate\Support\Str;

class RoomTypeSeeder extends Seeder
{
    public function run(): void
    {
        $roomTypes = [
            [
                'name' => 'Phòng Standard Single',
                'price_per_night' => 500000,
                'discount_price' => 450000,
                'max_adults' => 1,
                'max_children' => 0,
                'bed_type' => '1 Giường đơn',
                'view' => 'Hướng phố',
                'area' => 25,
                'description' => 'Phòng tiêu chuẩn dành cho khách đi công tác đơn lẻ, đầy đủ tiện nghi cơ bản.',
                'amenities' => ['Wifi', 'Tivi', 'Điều hòa', 'Máy sấy tóc'],
                'images' => ['https://images.pexels.com/photos/271618/pexels-photo-271618.jpeg'],
            ],
            [
                'name' => 'Phòng Deluxe Double',
                'price_per_night' => 1200000,
                'discount_price' => 990000,
                'max_adults' => 2,
                'max_children' => 1,
                'bed_type' => '1 Giường đôi lớn',
                'view' => 'Hướng vườn',
                'area' => 40,
                'description' => 'Không gian rộng rãi với tầm nhìn ra khu vườn xanh mát, phù hợp cho cặp đôi.',
                'amenities' => ['Wifi', 'Tivi 4K', 'Bồn tắm', 'Minibar', 'Ban công'],
                'images' => ['https://images.pexels.com/photos/164595/pexels-photo-164595.jpeg'],
            ],
            [
                'name' => 'Phòng Executive Suite',
                'price_per_night' => 3500000,
                'discount_price' => 2800000,
                'max_adults' => 2,
                'max_children' => 2,
                'bed_type' => '1 Giường King Size',
                'view' => 'Hướng biển (Ocean View)',
                'area' => 85,
                'description' => 'Hạng phòng cao cấp nhất với nội thất xa hoa, phòng khách riêng và view biển trọn vẹn.',
                'amenities' => ['Wifi tốc độ cao', 'Loa Bluetooth', 'Máy pha cà phê', 'Dịch vụ phòng 24/7', 'Hồ bơi riêng'],
                'images' => ['https://images.pexels.com/photos/271624/pexels-photo-271624.jpeg'],
            ],
            [
                'name' => 'Phòng Family Connecting',
                'price_per_night' => 2200000,
                'discount_price' => null,
                'max_adults' => 4,
                'max_children' => 2,
                'bed_type' => '2 Giường đôi',
                'view' => 'Hướng thành phố',
                'area' => 60,
                'description' => 'Thiết kế thông nhau giữa hai phòng, mang lại sự riêng tư nhưng vẫn kết nối cho gia đình.',
                'amenities' => ['Wifi', '2 Tivi', 'Khu vực bếp nhỏ', 'Bàn ăn'],
                'images' => ['https://images.pexels.com/photos/279746/pexels-photo-279746.jpeg'],
            ],
        ];

        foreach ($roomTypes as $type) {
            RoomType::create([
                'name' => $type['name'],
                'slug' => Str::slug($type['name']), // Tự động tạo slug: phòng-standard-single
                'price_per_night' => $type['price_per_night'],
                'discount_price' => $type['discount_price'],
                'max_adults' => $type['max_adults'],
                'max_children' => $type['max_children'],
                'bed_type' => $type['bed_type'],
                'view' => $type['view'],
                'area' => $type['area'],
                'description' => $type['description'],
                'amenities' => $type['amenities'], // Laravel tự chuyển thành JSON nhờ $casts trong Model
                'images' => $type['images'],
                'is_active' => true,
            ]);
        }
    }
}