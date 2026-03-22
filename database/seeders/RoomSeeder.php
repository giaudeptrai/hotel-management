<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\RoomType;
use App\Models\Room;
use Illuminate\Support\Str;

class RoomSeeder extends Seeder
{
    public function run(): void {
        for ($floor = 1; $floor <= 5; $floor++) {
            for ($roomNum = 1; $roomNum <= 10; $roomNum++) {
                \App\Models\Room::create([
                    'room_definition_id' => rand(1, 1), // Tạm thời trỏ vào định nghĩa đầu tiên
                    'room_number' => $floor . str_pad($roomNum, 2, '0', STR_PAD_LEFT), // 101, 102...
                    'floor' => "" . $floor,
                    'status' => 'available'
                ]);
            }
        }
    }
}