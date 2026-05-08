<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CustomerSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();
        $usersByEmail = DB::table('users')->pluck('id', 'email');

        $customers = [
            [
                'id' => 'aaaaaaa1-aaaa-aaaa-aaaa-aaaaaaaaaaa1',
                'user_id' => $usersByEmail['customer@hotel.test'] ?? null,
                'full_name' => 'Online Customer',
                'phone' => '0902000001',
                'cccd_number' => '079204000001',
                'email' => 'customer@hotel.test',
                'birthday' => '1996-05-20',
                'gender' => 'female',
                'address' => 'Da Nang',
                'total_bookings' => 0,
                'total_spent' => 0,
            ],
            [
                'id' => 'aaaaaaa2-aaaa-aaaa-aaaa-aaaaaaaaaaa2',
                'user_id' => null,
                'full_name' => 'Tran Minh Quan',
                'phone' => '0902000002',
                'cccd_number' => '079204000002',
                'email' => 'quan@example.com',
                'birthday' => '1991-07-03',
                'gender' => 'female',
                'address' => 'Ho Chi Minh',
                'total_bookings' => 0,
                'total_spent' => 0,
            ],
            [
                'id' => 'aaaaaaa3-aaaa-aaaa-aaaa-aaaaaaaaaaa3',
                'user_id' => null,
                'full_name' => 'Nguyen Thu Ha',
                'phone' => '0902000003',
                'cccd_number' => '079204000003',
                'email' => 'ha@example.com',
                'birthday' => '1998-12-15',
                'gender' => 'male',
                'address' => 'Ha Noi',
                'total_bookings' => 0,
                'total_spent' => 0,
            ],
            [
                'id' => 'aaaaaaa4-aaaa-aaaa-aaaa-aaaaaaaaaaa4',
                'user_id' => null,
                'full_name' => 'Le Gia Bao',
                'phone' => '0902000004',
                'cccd_number' => '079204000004',
                'email' => 'bao@example.com',
                'birthday' => '1988-03-09',
                'gender' => 'female',
                'address' => 'Hue',
                'total_bookings' => 0,
                'total_spent' => 0,
            ],
            [
                'id' => 'aaaaaaa5-aaaa-aaaa-aaaa-aaaaaaaaaaa5',
                'user_id' => null,
                'full_name' => 'Pham Gia Huy',
                'phone' => '0902000005',
                'cccd_number' => '079204000005',
                'email' => 'huy@example.com',
                'birthday' => '1993-11-22',
                'gender' => 'male',
                'address' => 'Can Tho',
                'total_bookings' => 0,
                'total_spent' => 0,
            ],
            [
                'id' => 'aaaaaaa6-aaaa-aaaa-aaaa-aaaaaaaaaaa6',
                'user_id' => null,
                'full_name' => 'Vo Ngoc Anh',
                'phone' => '0902000006',
                'cccd_number' => '079204000006',
                'email' => 'anh@example.com',
                'birthday' => '1995-02-18',
                'gender' => 'female',
                'address' => 'Nha Trang',
                'total_bookings' => 0,
                'total_spent' => 0,
            ],
            [
                'id' => 'aaaaaaa7-aaaa-aaaa-aaaa-aaaaaaaaaaa7',
                'user_id' => null,
                'full_name' => 'Doan Minh Tam',
                'phone' => '0902000007',
                'cccd_number' => '079204000007',
                'email' => 'tam@example.com',
                'birthday' => '1989-09-14',
                'gender' => 'male',
                'address' => 'Da Lat',
                'total_bookings' => 0,
                'total_spent' => 0,
            ],
            [
                'id' => 'aaaaaaa8-aaaa-aaaa-aaaa-aaaaaaaaaaa8',
                'user_id' => null,
                'full_name' => 'Bui Khanh Linh',
                'phone' => '0902000008',
                'cccd_number' => '079204000008',
                'email' => 'linh@example.com',
                'birthday' => '1997-06-07',
                'gender' => 'female',
                'address' => 'Vung Tau',
                'total_bookings' => 0,
                'total_spent' => 0,
            ],
            [
                'id' => 'aaaaaaa9-aaaa-aaaa-aaaa-aaaaaaaaaaa9',
                'user_id' => null,
                'full_name' => 'Nguyen Quang Minh',
                'phone' => '0902000009',
                'cccd_number' => '079204000009',
                'email' => 'minhq@example.com',
                'birthday' => '1990-01-30',
                'gender' => 'male',
                'address' => 'Hai Phong',
                'total_bookings' => 0,
                'total_spent' => 0,
            ],
            [
                'id' => 'aaaaa10a-aaaa-aaaa-aaaa-aaaaaaaaaaa0',
                'user_id' => null,
                'full_name' => 'Le My Trang',
                'phone' => '0902000010',
                'cccd_number' => '079204000010',
                'email' => 'trang@example.com',
                'birthday' => '1999-08-11',
                'gender' => 'female',
                'address' => 'Quy Nhon',
                'total_bookings' => 0,
                'total_spent' => 0,
            ],
        ];

        $rows = array_map(function ($customer) use ($now) {
            return [
                'id' => $customer['id'],
                'user_id' => $customer['user_id'],
                'full_name' => $customer['full_name'],
                'phone' => $customer['phone'],
                'cccd_number' => $customer['cccd_number'],
                'email' => $customer['email'],
                'birthday' => $customer['birthday'],
                'gender' => $customer['gender'],
                'address' => $customer['address'],
                'total_bookings' => $customer['total_bookings'],
                'total_spent' => $customer['total_spent'],
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }, $customers);

        DB::table('customers')->upsert(
            $rows,
            ['phone'],
            ['user_id', 'full_name', 'cccd_number', 'email', 'birthday', 'gender', 'address', 'total_bookings', 'total_spent', 'updated_at']
        );
    }
}
