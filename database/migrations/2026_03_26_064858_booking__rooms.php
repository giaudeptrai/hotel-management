<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('booking_rooms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained('bookings')->onDelete('cascade');

            // Khách đặt loại phòng nào (Bắt buộc)
            $table->foreignId('room_definition_id')->constrained('room_definitions')->onDelete('restrict');

            // Lễ tân xếp vào phòng vật lý nào (Có thể trống cho tới khi Check-in)
            $table->foreignId('room_id')->nullable()->constrained('rooms')->onDelete('set null');

            $table->decimal('price', 15, 2); // Giá phòng tại thời điểm đặt (Tránh trường hợp sau này đổi giá thì đơn cũ bị ảnh hưởng)

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('booking_rooms');
    }
};
