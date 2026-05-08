<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Thêm Loại Dịch Vụ vào bảng services
        Schema::table('services', function (Blueprint $table) {
            if (!Schema::hasColumn('services', 'type')) {
                // type: food (Đồ ăn), drink (Thức uống), spa (Spa/Massage), laundry (Giặt ủi), other (Khác)
                $table->string('type', 50)->default('other')->after('name');
            }
        });

        // 2. Thêm Mô tả vào bảng room_definitions (Nếu chưa có)
        Schema::table('room_definitions', function (Blueprint $table) {
            if (!Schema::hasColumn('room_definitions', 'description')) {
                $table->text('description')->nullable()->after('name');
            }
        });
    }

    public function down(): void
    {
        Schema::table('services', function (Blueprint $table) {
            $table->dropColumn('type');
        });
        Schema::table('room_definitions', function (Blueprint $table) {
            $table->dropColumn('description');
        });
    }
};
