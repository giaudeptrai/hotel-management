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
        Schema::create('customers', function (Blueprint $table) {
            
            $table->uuid('id')->primary();

            
            $table->foreignUuid('user_id')->nullable()->constrained()->nullOnDelete();

           
            $table->string('full_name');
            $table->string('phone', 20)->unique(); 
            $table->string('cccd_number', 20)->nullable()->unique(); 
            $table->string('email')->nullable();
            
    
            $table->date('birthday')->nullable();
            $table->enum('gender', ['male', 'female', 'other'])->nullable();
            $table->text('address')->nullable();


            $table->integer('total_bookings')->default(0);
            $table->decimal('total_spent', 15, 2)->default(0); 

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};