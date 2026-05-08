<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('staffs', function (Blueprint $table) {
            $table->id();

            // 🎯 Liên kết với User bằng UUID
            $table->foreignUuid('user_id')->constrained('users')->cascadeOnDelete();

            $table->string('staff_code', 20)->unique(); // Mã NV (VD: NV001)
            $table->string('full_name');
            $table->string('phone', 20)->nullable();
            $table->string('cccd', 20)->nullable(); // CCCD của nhân viên
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('staffs');
    }
};
