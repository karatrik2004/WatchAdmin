<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Per-line `currency` was removed: currency is read from each deal (buyer) instead.
 * This migration only makes `total_amount` nullable (used when invoice is multi-currency).
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::getConnection()->getDriverName() === 'mysql' && Schema::hasTable('shipping_invoices')) {
            // Multi-currency invoices use NULL for total (see 2026_04_22_000002 if this early install failed)
            DB::statement('ALTER TABLE `shipping_invoices` MODIFY `total_amount` DECIMAL(12,2) NULL');
        }
    }

    public function down(): void
    {
        if (Schema::getConnection()->getDriverName() === 'mysql' && Schema::hasTable('shipping_invoices')) {
            DB::table('shipping_invoices')->whereNull('total_amount')->update(['total_amount' => 0]);
            try {
                DB::statement('ALTER TABLE `shipping_invoices` MODIFY `total_amount` DECIMAL(12,2) NOT NULL DEFAULT 0');
            } catch (\Throwable $e) {
            }
        }
    }
};
