<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('shipping_invoices')) {
            return;
        }

        if (DB::getDriverName() === 'mysql') {
            // Re-run in case 2026_04_20 failed; MODIFY is idempotent
            DB::statement('ALTER TABLE `shipping_invoices` MODIFY `total_amount` DECIMAL(12,2) NULL');
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('shipping_invoices')) {
            return;
        }

        if (DB::getDriverName() === 'mysql') {
            DB::table('shipping_invoices')->whereNull('total_amount')->update(['total_amount' => 0]);
            DB::statement('ALTER TABLE `shipping_invoices` MODIFY `total_amount` DECIMAL(12,2) NOT NULL DEFAULT 0');
        }
    }
};
