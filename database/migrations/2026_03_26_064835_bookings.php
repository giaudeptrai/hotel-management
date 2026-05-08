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
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->string('booking_code', 20)->unique(); // Mã đặt phòng (VD: BK-20260326)

            // Liên kết với bảng Customer (Vì Customers dùng UUID nên ở đây phải dùng foreignUuid)
            $table->foreignUuid('customer_id')->constrained('customers')->onDelete('restrict');

            // Thời gian lưu trú
            $table->dateTime('check_in_expected'); // Ngày giờ dự kiến đến
            $table->dateTime('check_out_expected'); // Ngày giờ dự kiến đi
            $table->dateTime('check_in_actual')->nullable(); // Giờ khách thực tế nhận phòng
            $table->dateTime('check_out_actual')->nullable(); // Giờ khách thực tế trả phòng

            // Tài chính
            $table->decimal('total_amount', 15, 2)->default(0); // Tổng tiền dự kiến
            $table->decimal('deposit_amount', 15, 2)->default(0); // Tiền cọc

            // Trạng thái và Nguồn
            $table->enum('status', ['pending', 'confirmed', 'checked_in', 'checked_out', 'cancelled'])->default('pending');
            $table->enum('source', ['online', 'walk_in'])->default('online');

            $table->text('special_requests')->nullable(); // Ghi chú của khách
            $table->text('admin_note')->nullable(); // Ghi chú nội bộ của lễ tân

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
