<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            AuthSeeder::class,
            RoomCategorySeeder::class,
            RoomTypeSeeder::class,
            AmenitySeeder::class,
            RoomDefinitionSeeder::class,
            RoomSeeder::class,
            StaffSeeder::class,
            CustomerSeeder::class,
            ServiceSeeder::class,
            BookingDataSeeder::class,
            ContactRequestSeeder::class,
            RoomReviewSeeder::class,
        ]);
    }
}
