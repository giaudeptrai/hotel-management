<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RoomSeeder extends Seeder
{
    public function run(): void
    {
        $definitionIds = DB::table('room_definitions')->orderBy('id')->pluck('id')->all();

        if (empty($definitionIds)) {
            return;
        }

        $pointer = 0;

        for ($floor = 1; $floor <= 5; $floor++) {
            for ($roomNum = 1; $roomNum <= 10; $roomNum++) {
                $roomNumber = $floor . str_pad((string) $roomNum, 2, '0', STR_PAD_LEFT);
                $roomDefinitionId = $definitionIds[$pointer % count($definitionIds)];

                DB::table('rooms')->updateOrInsert(
                    ['room_number' => $roomNumber],
                    [
                        'room_definition_id' => $roomDefinitionId,
                        'floor' => $floor,
                        'status' => 'available',
                        'is_active' => true,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );

                $pointer++;
            }
        }
    }
}
