<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RoomReviewSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        $reviews = [
            [
                'booking_code' => 'BK-260403-A001',
                'rating' => 5,
                'comment' => 'Great stay. Room was clean and the staff was very supportive.',
            ],
            [
                'booking_code' => 'BK-260403-A004',
                'rating' => 4,
                'comment' => 'Comfortable room, good breakfast, quick check-out.',
            ],
            [
                'booking_code' => 'BK-260403-A006',
                'rating' => 5,
                'comment' => 'Nice experience overall. Laundry service was fast.',
            ],
            [
                'booking_code' => 'BK-260403-A007',
                'rating' => 4,
                'comment' => 'Suite quality is good, minibar options can be improved.',
            ],
            [
                'booking_code' => 'BK-260403-A009',
                'rating' => 5,
                'comment' => 'Second time here and still excellent service quality.',
            ],
        ];

        $rows = [];

        foreach ($reviews as $review) {
            $booking = DB::table('bookings')
                ->where('booking_code', $review['booking_code'])
                ->first(['id', 'customer_id', 'status']);

            if (!$booking || $booking->status !== 'checked_out') {
                continue;
            }

            $roomDefinitionId = DB::table('booking_rooms')
                ->where('booking_id', $booking->id)
                ->value('room_definition_id');

            if (!$roomDefinitionId) {
                continue;
            }

            $rows[] = [
                'booking_id' => $booking->id,
                'customer_id' => $booking->customer_id,
                'room_definition_id' => $roomDefinitionId,
                'rating' => $review['rating'],
                'comment' => $review['comment'],
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        if (!empty($rows)) {
            DB::table('room_reviews')->upsert(
                $rows,
                ['booking_id'],
                ['customer_id', 'room_definition_id', 'rating', 'comment', 'updated_at']
            );
        }
    }
}
