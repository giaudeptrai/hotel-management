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
        Schema::table('roles', function (Blueprint $table) {
            if (!Schema::hasColumn('roles', 'description')) {
                $table->string('description', 1000)->nullable()->after('slug');
            }

            if (!Schema::hasColumn('roles', 'level')) {
                $table->string('level', 20)->default('normal')->after('description');
            }

            if (!Schema::hasColumn('roles', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('level');
            }

            if (!Schema::hasColumn('roles', 'permissions')) {
                $table->json('permissions')->nullable()->after('is_active');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('roles', function (Blueprint $table) {
            $dropColumns = [];

            if (Schema::hasColumn('roles', 'permissions')) {
                $dropColumns[] = 'permissions';
            }

            if (Schema::hasColumn('roles', 'is_active')) {
                $dropColumns[] = 'is_active';
            }

            if (Schema::hasColumn('roles', 'level')) {
                $dropColumns[] = 'level';
            }

            if (Schema::hasColumn('roles', 'description')) {
                $dropColumns[] = 'description';
            }

            if (!empty($dropColumns)) {
                $table->dropColumn($dropColumns);
            }
        });
    }
};
