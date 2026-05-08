<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_code', 20)->unique();
            $table->foreignId('booking_id')->constrained('bookings')->onDelete('restrict');

            //  AI LÀ NGƯỜI THU TIỀN? (Dùng foreignUuid vì User xài UUID)
            $table->foreignUuid('cashier_id')->nullable()->constrained('users')->nullOnDelete();

            // Tổng hợp tài chính
            $table->decimal('room_charge', 15, 2);
            $table->decimal('service_charge', 15, 2)->default(0);
            $table->decimal('tax_amount', 15, 2)->default(0);
            $table->decimal('total_amount', 15, 2);
            $table->decimal('amount_paid', 15, 2)->default(0);

            $table->string('payment_method')->default('cash');
            $table->enum('payment_status', ['unpaid', 'partial', 'paid'])->default('unpaid');

            $table->dateTime('paid_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
