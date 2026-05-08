<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BookingDataSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        $customers = DB::table('customers')->pluck('id', 'phone');
        $definitions = DB::table('room_definitions')->pluck('id', 'name');
        $roomsByDefinition = DB::table('rooms')
            ->orderBy('id')
            ->get(['id', 'room_definition_id'])
            ->groupBy('room_definition_id');
        $services = DB::table('services')->pluck('id', 'name');
        $servicePricesById = DB::table('services')->pluck('price', 'id');
        $usersByEmail = DB::table('users')->pluck('id', 'email');

        $bookingSeed = [
            [
                'booking_code' => 'BK-260403-A001',
                'customer_phone' => '0902000001',
                'check_in_expected' => Carbon::now()->subDays(4)->setTime(14, 0),
                'check_out_expected' => Carbon::now()->subDays(2)->setTime(12, 0),
                'check_in_actual' => Carbon::now()->subDays(4)->setTime(14, 30),
                'check_out_actual' => Carbon::now()->subDays(2)->setTime(11, 30),
                'status' => 'checked_out',
                'source' => 'online',
                'special_requests' => 'Phong yen tinh',
                'admin_note' => 'Khach VIP online',
                'rooms' => [
                    ['definition' => 'Deluxe Double City View', 'price' => 1200000],
                ],
                'services' => [
                    ['name' => 'Breakfast Buffet', 'quantity' => 2],
                    ['name' => 'Laundry', 'quantity' => 1],
                ],
                'invoice' => [
                    'invoice_code' => 'INV-260403-A001',
                    'amount_paid' => null,
                    'payment_method' => 'cash',
                    'payment_status' => 'paid',
                    'cashier_email' => 'cashier@hotel.test',
                    'paid_at' => Carbon::now()->subDays(2)->setTime(11, 45),
                ],
            ],
            [
                'booking_code' => 'BK-260403-A002',
                'customer_phone' => '0902000002',
                'check_in_expected' => Carbon::now()->addDays(1)->setTime(14, 0),
                'check_out_expected' => Carbon::now()->addDays(3)->setTime(12, 0),
                'check_in_actual' => null,
                'check_out_actual' => null,
                'status' => 'confirmed',
                'source' => 'walk_in',
                'special_requests' => 'Them giuong em be',
                'admin_note' => 'Dat tai quay',
                'rooms' => [
                    ['definition' => 'Standard Single City View', 'price' => 700000],
                    ['definition' => 'Deluxe Double City View', 'price' => 1200000],
                ],
                'services' => [
                    ['name' => 'Airport Pickup', 'quantity' => 1],
                ],
                'invoice' => [
                    'invoice_code' => 'INV-260403-A002',
                    'amount_paid' => 600000,
                    'payment_method' => 'cash',
                    'payment_status' => 'partial',
                    'cashier_email' => 'cashier@hotel.test',
                    'paid_at' => Carbon::now()->subHours(4),
                ],
            ],
            [
                'booking_code' => 'BK-260403-A003',
                'customer_phone' => '0902000003',
                'check_in_expected' => Carbon::now()->subDays(1)->setTime(14, 0),
                'check_out_expected' => Carbon::now()->addDay()->setTime(12, 0),
                'check_in_actual' => Carbon::now()->subDays(1)->setTime(14, 10),
                'check_out_actual' => null,
                'status' => 'checked_in',
                'source' => 'online',
                'special_requests' => null,
                'admin_note' => 'Khach dang luu tru',
                'rooms' => [
                    ['definition' => 'Suite King Ocean View', 'price' => 2500000],
                ],
                'services' => [
                    ['name' => 'Mini Bar Combo', 'quantity' => 2],
                    ['name' => 'Spa Relax', 'quantity' => 1],
                ],
                'invoice' => [
                    'invoice_code' => 'INV-260403-A003',
                    'amount_paid' => 0,
                    'payment_method' => 'cash',
                    'payment_status' => 'unpaid',
                    'cashier_email' => null,
                    'paid_at' => null,
                ],
            ],
            [
                'booking_code' => 'BK-260403-A004',
                'customer_phone' => '0902000004',
                'check_in_expected' => Carbon::now()->subDays(6)->setTime(14, 0),
                'check_out_expected' => Carbon::now()->subDays(4)->setTime(12, 0),
                'check_in_actual' => Carbon::now()->subDays(6)->setTime(14, 15),
                'check_out_actual' => Carbon::now()->subDays(4)->setTime(11, 55),
                'status' => 'checked_out',
                'source' => 'walk_in',
                'special_requests' => 'Tang phong co view dep',
                'admin_note' => 'Khach doanh nhan',
                'rooms' => [
                    ['definition' => 'Standard Single City View', 'price' => 700000],
                    ['definition' => 'Standard Single City View', 'price' => 700000],
                ],
                'services' => [
                    ['name' => 'Breakfast Buffet', 'quantity' => 2],
                ],
                'invoice' => [
                    'invoice_code' => 'INV-260403-A004',
                    'amount_paid' => null,
                    'payment_method' => 'cash',
                    'payment_status' => 'paid',
                    'cashier_email' => 'cashier@hotel.test',
                    'paid_at' => Carbon::now()->subDays(4)->setTime(12, 10),
                ],
            ],
            [
                'booking_code' => 'BK-260403-A005',
                'customer_phone' => '0902000005',
                'check_in_expected' => Carbon::now()->addDays(5)->setTime(14, 0),
                'check_out_expected' => Carbon::now()->addDays(8)->setTime(12, 0),
                'check_in_actual' => null,
                'check_out_actual' => null,
                'status' => 'confirmed',
                'source' => 'online',
                'special_requests' => 'Can phong yen tinh va do an sang',
                'admin_note' => 'Khach dat som',
                'rooms' => [
                    ['definition' => 'Presidential King Panorama', 'price' => 5500000],
                ],
                'services' => [
                    ['name' => 'Airport Pickup', 'quantity' => 1],
                ],
                'invoice' => [
                    'invoice_code' => 'INV-260403-A005',
                    'amount_paid' => 0,
                    'payment_method' => 'cash',
                    'payment_status' => 'unpaid',
                    'cashier_email' => null,
                    'paid_at' => null,
                ],
            ],
            [
                'booking_code' => 'BK-260403-A006',
                'customer_phone' => '0902000006',
                'check_in_expected' => Carbon::now()->subDays(2)->setTime(14, 0),
                'check_out_expected' => Carbon::now()->setTime(12, 0),
                'check_in_actual' => Carbon::now()->subDays(2)->setTime(13, 45),
                'check_out_actual' => Carbon::now()->setTime(12, 15),
                'status' => 'checked_out',
                'source' => 'online',
                'special_requests' => 'Uu tien khu vuc yen tinh',
                'admin_note' => 'Gia dinh 3 nguoi',
                'rooms' => [
                    ['definition' => 'Deluxe Double City View', 'price' => 1200000],
                ],
                'services' => [
                    ['name' => 'Breakfast Buffet', 'quantity' => 1],
                    ['name' => 'Laundry', 'quantity' => 2],
                ],
                'invoice' => [
                    'invoice_code' => 'INV-260403-A006',
                    'amount_paid' => null,
                    'payment_method' => 'cash',
                    'payment_status' => 'paid',
                    'cashier_email' => 'cashier@hotel.test',
                    'paid_at' => Carbon::now()->setTime(12, 20),
                ],
            ],
            [
                'booking_code' => 'BK-260403-A007',
                'customer_phone' => '0902000007',
                'check_in_expected' => Carbon::now()->subDays(3)->setTime(14, 0),
                'check_out_expected' => Carbon::now()->subDay()->setTime(12, 0),
                'check_in_actual' => Carbon::now()->subDays(3)->setTime(14, 20),
                'check_out_actual' => Carbon::now()->subDay()->setTime(13, 30),
                'status' => 'checked_out',
                'source' => 'walk_in',
                'special_requests' => 'Spa va minibar',
                'admin_note' => 'Khach dung dich vu cao cap',
                'rooms' => [
                    ['definition' => 'Suite King Ocean View', 'price' => 2500000],
                ],
                'services' => [
                    ['name' => 'Mini Bar Combo', 'quantity' => 3],
                    ['name' => 'Spa Relax', 'quantity' => 1],
                ],
                'invoice' => [
                    'invoice_code' => 'INV-260403-A007',
                    'amount_paid' => 2500000,
                    'payment_method' => 'cash',
                    'payment_status' => 'partial',
                    'cashier_email' => 'cashier@hotel.test',
                    'paid_at' => Carbon::now()->subDay()->setTime(13, 35),
                ],
            ],
            [
                'booking_code' => 'BK-260403-A008',
                'customer_phone' => '0902000008',
                'check_in_expected' => Carbon::now()->addDays(2)->setTime(14, 0),
                'check_out_expected' => Carbon::now()->addDays(4)->setTime(12, 0),
                'check_in_actual' => null,
                'check_out_actual' => null,
                'status' => 'confirmed',
                'source' => 'online',
                'special_requests' => 'Phong tang cao',
                'admin_note' => 'Khach chua den',
                'rooms' => [
                    ['definition' => 'Standard Single City View', 'price' => 700000],
                ],
                'services' => [],
                'invoice' => [
                    'invoice_code' => 'INV-260403-A008',
                    'amount_paid' => 0,
                    'payment_method' => 'cash',
                    'payment_status' => 'unpaid',
                    'cashier_email' => null,
                    'paid_at' => null,
                ],
            ],
            [
                'booking_code' => 'BK-260403-A009',
                'customer_phone' => '0902000009',
                'check_in_expected' => Carbon::now()->subDays(8)->setTime(14, 0),
                'check_out_expected' => Carbon::now()->subDays(6)->setTime(12, 0),
                'check_in_actual' => Carbon::now()->subDays(8)->setTime(13, 50),
                'check_out_actual' => Carbon::now()->subDays(6)->setTime(12, 20),
                'status' => 'checked_out',
                'source' => 'online',
                'special_requests' => 'Uu tien check-in som',
                'admin_note' => 'Khach quay lai lan 2',
                'rooms' => [
                    ['definition' => 'Deluxe Double City View', 'price' => 1200000],
                ],
                'services' => [
                    ['name' => 'Breakfast Buffet', 'quantity' => 3],
                    ['name' => 'Spa Relax', 'quantity' => 1],
                ],
                'invoice' => [
                    'invoice_code' => 'INV-260403-A009',
                    'amount_paid' => null,
                    'payment_method' => 'cash',
                    'payment_status' => 'paid',
                    'cashier_email' => 'cashier@hotel.test',
                    'paid_at' => Carbon::now()->subDays(6)->setTime(12, 25),
                ],
            ],
            [
                'booking_code' => 'BK-260403-A010',
                'customer_phone' => '0902000010',
                'check_in_expected' => Carbon::now()->subDays(1)->setTime(14, 0),
                'check_out_expected' => Carbon::now()->addDay()->setTime(12, 0),
                'check_in_actual' => Carbon::now()->subDays(1)->setTime(14, 10),
                'check_out_actual' => null,
                'status' => 'checked_in',
                'source' => 'online',
                'special_requests' => null,
                'admin_note' => 'Khach dang luu tru',
                'rooms' => [
                    ['definition' => 'Suite King Ocean View', 'price' => 2500000],
                ],
                'services' => [
                    ['name' => 'Mini Bar Combo', 'quantity' => 1],
                ],
                'invoice' => [
                    'invoice_code' => 'INV-260403-A010',
                    'amount_paid' => 0,
                    'payment_method' => 'cash',
                    'payment_status' => 'unpaid',
                    'cashier_email' => null,
                    'paid_at' => null,
                ],
            ],
        ];

        foreach ($bookingSeed as $bookingData) {
            $customerId = $customers[$bookingData['customer_phone']] ?? null;
            if (!$customerId) {
                continue;
            }

            $checkIn = Carbon::parse($bookingData['check_in_expected']);
            $checkOut = Carbon::parse($bookingData['check_out_expected']);
            $nights = max(1, $checkIn->diffInDays($checkOut));

            $roomCharge = 0;
            foreach ($bookingData['rooms'] as $room) {
                $roomCharge += ((float) $room['price']) * $nights;
            }

            $serviceCharge = 0;
            foreach ($bookingData['services'] as $serviceItem) {
                $serviceId = $services[$serviceItem['name']] ?? null;
                if (!$serviceId) {
                    continue;
                }
                $servicePrice = (float) ($servicePricesById[$serviceId] ?? 0);
                $serviceCharge += $servicePrice * (int) $serviceItem['quantity'];
            }

            $subTotal = $roomCharge + $serviceCharge;
            $tax = round($subTotal * 0.08, 2);
            $totalAmount = round($subTotal + $tax, 2);
            $depositAmount = round($totalAmount * 0.3, 2);

            DB::table('bookings')->updateOrInsert(
                ['booking_code' => $bookingData['booking_code']],
                [
                    'customer_id' => $customerId,
                    'check_in_expected' => $bookingData['check_in_expected'],
                    'check_out_expected' => $bookingData['check_out_expected'],
                    'check_in_actual' => $bookingData['check_in_actual'],
                    'check_out_actual' => $bookingData['check_out_actual'],
                    'total_amount' => $totalAmount,
                    'deposit_amount' => $depositAmount,
                    'status' => $bookingData['status'],
                    'source' => $bookingData['source'],
                    'special_requests' => $bookingData['special_requests'],
                    'admin_note' => $bookingData['admin_note'],
                    'updated_at' => $now,
                    'created_at' => $now,
                ]
            );

            $bookingId = DB::table('bookings')->where('booking_code', $bookingData['booking_code'])->value('id');
            if (!$bookingId) {
                continue;
            }

            DB::table('booking_rooms')->where('booking_id', $bookingId)->delete();
            DB::table('booking_services')->where('booking_id', $bookingId)->delete();

            foreach ($bookingData['rooms'] as $roomItem) {
                $definitionId = $definitions[$roomItem['definition']] ?? null;
                if (!$definitionId) {
                    continue;
                }

                $roomId = optional(($roomsByDefinition[$definitionId] ?? collect())->first())->id;

                DB::table('booking_rooms')->insert([
                    'booking_id' => $bookingId,
                    'room_definition_id' => $definitionId,
                    'room_id' => $roomId,
                    'price' => $roomItem['price'],
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }

            foreach ($bookingData['services'] as $serviceItem) {
                $serviceId = $services[$serviceItem['name']] ?? null;
                if (!$serviceId) {
                    continue;
                }

                $price = (float) ($servicePricesById[$serviceId] ?? 0);
                $quantity = (int) $serviceItem['quantity'];

                DB::table('booking_services')->insert([
                    'booking_id' => $bookingId,
                    'service_id' => $serviceId,
                    'quantity' => $quantity,
                    'price' => $price,
                    'total_price' => $price * $quantity,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }

            $cashierId = null;
            if (!empty($bookingData['invoice']['cashier_email'])) {
                $cashierId = $usersByEmail[$bookingData['invoice']['cashier_email']] ?? null;
            }

            $amountPaid = $bookingData['invoice']['amount_paid'];
            if ($amountPaid === null && $bookingData['invoice']['payment_status'] === 'paid') {
                $amountPaid = $totalAmount;
            }

            DB::table('invoices')->updateOrInsert(
                ['invoice_code' => $bookingData['invoice']['invoice_code']],
                [
                    'booking_id' => $bookingId,
                    'cashier_id' => $cashierId,
                    'room_charge' => $roomCharge,
                    'service_charge' => $serviceCharge,
                    'tax_amount' => $tax,
                    'total_amount' => $totalAmount,
                    'amount_paid' => $amountPaid ?? 0,
                    'payment_method' => $bookingData['invoice']['payment_method'] ?? 'cash',
                    'payment_status' => $bookingData['invoice']['payment_status'],
                    'paid_at' => $bookingData['invoice']['paid_at'],
                    'updated_at' => $now,
                    'created_at' => $now,
                ]
            );
        }

        DB::table('customers')
            ->select('id')
            ->orderBy('id')
            ->get()
            ->each(function ($customer) use ($now) {
                $totalBookings = DB::table('bookings')->where('customer_id', $customer->id)->count();
                $totalSpent = DB::table('invoices')
                    ->join('bookings', 'bookings.id', '=', 'invoices.booking_id')
                    ->where('bookings.customer_id', $customer->id)
                    ->sum('invoices.amount_paid');

                DB::table('customers')->where('id', $customer->id)->update([
                    'total_bookings' => $totalBookings,
                    'total_spent' => $totalSpent,
                    'updated_at' => $now,
                ]);
            });
    }
}
