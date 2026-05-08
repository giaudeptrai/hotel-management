<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ContactRequestSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();
        $usersByEmail = DB::table('users')->pluck('id', 'email');

        $contactRequests = [
            [
                'name' => 'Online Customer',
                'phone' => '0902000001',
                'email' => 'customer@hotel.test',
                'subject' => 'Need airport transfer details',
                'message' => 'Please share pickup options and price from airport to hotel.',
                'status' => 'resolved',
                'source' => 'client_contact_page',
                'ip_address' => '203.113.10.20',
                'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) Chrome/124.0',
                'handled_at' => Carbon::now()->subDays(2)->setTime(10, 30),
                'user_email' => 'customer@hotel.test',
            ],
            [
                'name' => 'Tran Minh Quan',
                'phone' => '0902000002',
                'email' => 'quan@example.com',
                'subject' => 'Early check-in support',
                'message' => 'I may arrive before 11am, can I check in early?',
                'status' => 'new',
                'source' => 'client_contact_page',
                'ip_address' => '203.113.10.21',
                'user_agent' => 'Mozilla/5.0 (iPhone; CPU iPhone OS 17_0 like Mac OS X) Mobile',
                'handled_at' => null,
                'user_email' => null,
            ],
            [
                'name' => 'Nguyen Thu Ha',
                'phone' => '0902000003',
                'email' => 'ha@example.com',
                'subject' => 'Invoice request',
                'message' => 'Can you send VAT invoice after checkout?',
                'status' => 'in_progress',
                'source' => 'client_contact_page',
                'ip_address' => '203.113.10.22',
                'user_agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 13_6) Safari/605.1.15',
                'handled_at' => null,
                'user_email' => null,
            ],
            [
                'name' => 'Le Gia Bao',
                'phone' => '0902000004',
                'email' => 'bao@example.com',
                'subject' => 'Room decoration package',
                'message' => 'Do you have birthday room decoration service?',
                'status' => 'resolved',
                'source' => 'client_contact_page',
                'ip_address' => '203.113.10.23',
                'user_agent' => 'Mozilla/5.0 (Linux; Android 14) Chrome/124.0 Mobile',
                'handled_at' => Carbon::now()->subDay()->setTime(16, 45),
                'user_email' => null,
            ],
        ];

        $rows = array_map(function ($item) use ($usersByEmail, $now) {
            return [
                'user_id' => !empty($item['user_email']) ? ($usersByEmail[$item['user_email']] ?? null) : null,
                'name' => $item['name'],
                'phone' => $item['phone'],
                'email' => $item['email'],
                'subject' => $item['subject'],
                'message' => $item['message'],
                'status' => $item['status'],
                'source' => $item['source'],
                'ip_address' => $item['ip_address'],
                'user_agent' => $item['user_agent'],
                'handled_at' => $item['handled_at'],
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }, $contactRequests);

        DB::table('contact_requests')->upsert(
            $rows,
            ['phone', 'subject'],
            ['user_id', 'name', 'email', 'message', 'status', 'source', 'ip_address', 'user_agent', 'handled_at', 'updated_at']
        );
    }
}
