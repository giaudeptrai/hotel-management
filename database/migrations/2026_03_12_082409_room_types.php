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
        Schema::create('room_types', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();

            // giá
            $table->decimal('price_per_night', 15, 2);
            $table->decimal('discount_price', 15, 2)->nullable();

            //sức chứa và không gian
            $table->integer('max_adults')->default(2);
            $table->integer('max_children')->default(0);
            $table->string('bed_type')->nullable();
            $table->string('view')->nullable();
            $table->integer('area')->nullable();

            // mô tả
            $table->text('description')->nullable();
            $table->json('amenities')->nullable();
            $table->json('images')->nullable();

            // quản lý hiển thị
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
