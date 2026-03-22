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
        Schema::create('room_definitions', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('room_category_id')->constrained('room_categories')->onDelete('cascade');
            $table->foreignId('room_type_id')->constrained('room_types')->onDelete('cascade');
            $table->decimal('base_price', 15, 2);
            $table->integer('area');
            $table->string('view')->nullable();
            $table->json('images')->nullable(); // Lưu trữ đường dẫn ảnh dưới dạng JSON
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('room_definitions');
    }
};
