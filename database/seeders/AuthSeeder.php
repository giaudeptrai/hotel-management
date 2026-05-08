<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AuthSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();
        $routePermissions = config('admin_permissions.route_permissions', []);
        $allPermissions = array_values(array_unique(array_values($routePermissions)));

        $roles = [
            [
                'name' => 'Admin',
                'slug' => 'admin',
                'description' => 'Quản trị toàn bộ hệ thống khách sạn',
                'level' => 'super',
                'is_active' => true,
                'permissions' => $allPermissions,
            ],
            [
                'name' => 'Receptionist',
                'slug' => 'receptionist',
                'description' => 'Lễ tân xử lý đặt phòng, khách hàng và check-in/check-out',
                'level' => 'staff',
                'is_active' => true,
                'permissions' => [
                    'dashboard.view',
                    'booking.view',
                    'booking.create',
                    'booking.update',
                    'booking.checkin_checkout',
                    'customer.view',
                    'customer.create',
                    'customer.update',
                    'invoice.view',
                    'invoice.pay',
                    'rooms.manage',
                ],
            ],
            [
                'name' => 'Cashier',
                'slug' => 'cashier',
                'description' => 'Thu ngân xử lý hóa đơn và thanh toán',
                'level' => 'staff',
                'is_active' => true,
                'permissions' => [
                    'dashboard.view',
                    'booking.view',
                    'invoice.view',
                    'invoice.pay',
                ],
            ],
            [
                'name' => 'Customer',
                'slug' => 'customer',
                'description' => 'Tài khoản khách hàng đặt phòng trực tuyến',
                'level' => 'customer',
                'is_active' => true,
                'permissions' => [
                    'dashboard.view',
                    'booking.view',
                ],
            ],
        ];

        $roleRows = array_map(function ($role) use ($now) {
            return [
                'name' => $role['name'],
                'slug' => $role['slug'],
                'description' => $role['description'],
                'level' => $role['level'],
                'is_active' => $role['is_active'],
                'permissions' => json_encode($role['permissions'], JSON_UNESCAPED_UNICODE),
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }, $roles);

        DB::table('roles')->upsert(
            $roleRows,
            ['slug'],
            ['name', 'description', 'level', 'is_active', 'permissions', 'updated_at']
        );

        $roleIds = DB::table('roles')->pluck('id', 'slug');

        $users = [
            [
                'id' => '11111111-1111-1111-1111-111111111111',
                'name' => 'System Admin',
                'email' => 'admin@hotel.test',
                'role_slug' => 'admin',
                'password' => 'password',
            ],
            [
                'id' => '22222222-2222-2222-2222-222222222222',
                'name' => 'Linh Reception',
                'email' => 'reception@hotel.test',
                'role_slug' => 'receptionist',
                'password' => 'password',
            ],
            [
                'id' => '33333333-3333-3333-3333-333333333333',
                'name' => 'Khanh Cashier',
                'email' => 'cashier@hotel.test',
                'role_slug' => 'cashier',
                'password' => 'password',
            ],
            [
                'id' => '44444444-4444-4444-4444-444444444444',
                'name' => 'Online Customer',
                'email' => 'customer@hotel.test',
                'role_slug' => 'customer',
                'password' => 'password',
            ],
        ];

        $userRows = array_map(function ($user) use ($roleIds, $now) {
            return [
                'id' => $user['id'],
                'name' => $user['name'],
                'email' => $user['email'],
                'password' => Hash::make($user['password']),
                'role_id' => $roleIds[$user['role_slug']] ?? null,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }, $users);

        DB::table('users')->upsert(
            $userRows,
            ['email'],
            ['name', 'password', 'role_id', 'is_active', 'updated_at']
        );
    }
}
