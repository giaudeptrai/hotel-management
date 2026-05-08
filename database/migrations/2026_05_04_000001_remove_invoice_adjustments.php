<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $columnsToDrop = [];

            foreach (['surcharge_amount', 'surcharge_reason', 'discount_amount'] as $column) {
                if (Schema::hasColumn('invoices', $column)) {
                    $columnsToDrop[] = $column;
                }
            }

            if (!empty($columnsToDrop)) {
                $table->dropColumn($columnsToDrop);
            }
        });
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            if (!Schema::hasColumn('invoices', 'surcharge_amount')) {
                $table->decimal('surcharge_amount', 15, 2)->default(0)->after('service_charge');
            }

            if (!Schema::hasColumn('invoices', 'surcharge_reason')) {
                $table->string('surcharge_reason')->nullable()->after('surcharge_amount');
            }

            if (!Schema::hasColumn('invoices', 'discount_amount')) {
                $table->decimal('discount_amount', 15, 2)->default(0)->after('surcharge_reason');
            }
        });
    }
};
