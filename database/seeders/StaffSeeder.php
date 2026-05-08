<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class StaffSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        $usersByEmail = DB::table('users')->pluck('id', 'email');

        $staffs = [
            [
                'staff_code' => 'NV001',
                'user_email' => 'admin@hotel.test',
                'full_name' => 'System Admin',
                'phone' => '0901000001',
                'cccd' => '001001000001',
            ],
            [
                'staff_code' => 'NV002',
                'user_email' => 'reception@hotel.test',
                'full_name' => 'Linh Reception',
                'phone' => '0901000002',
                'cccd' => '001001000002',
            ],
            [
                'staff_code' => 'NV003',
                'user_email' => 'cashier@hotel.test',
                'full_name' => 'Khanh Cashier',
                'phone' => '0901000003',
                'cccd' => '001001000003',
            ],
        ];

        $rows = [];
        foreach ($staffs as $staff) {
            $userId = $usersByEmail[$staff['user_email']] ?? null;
            if (!$userId) {
                continue;
            }

            $rows[] = [
                'user_id' => $userId,
                'staff_code' => $staff['staff_code'],
                'full_name' => $staff['full_name'],
                'phone' => $staff['phone'],
                'cccd' => $staff['cccd'],
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        if (!empty($rows)) {
            DB::table('staffs')->upsert(
                $rows,
                ['staff_code'],
                ['user_id', 'full_name', 'phone', 'cccd', 'is_active', 'updated_at']
            );
        }
    }
}
