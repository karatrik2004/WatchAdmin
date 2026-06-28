<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('shipping_invoice_items') && ! Schema::hasColumn('shipping_invoice_items', 'currency')) {
            Schema::table('shipping_invoice_items', function (Blueprint $table) {
                $table->string('currency', 10)->nullable()->after('deal_id');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('shipping_invoice_items') && Schema::hasColumn('shipping_invoice_items', 'currency')) {
            Schema::table('shipping_invoice_items', function (Blueprint $table) {
                $table->dropColumn('currency');
            });
        }
    }
};
