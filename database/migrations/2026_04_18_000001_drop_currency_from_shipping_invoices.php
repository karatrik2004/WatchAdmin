<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('shipping_invoices') && Schema::hasColumn('shipping_invoices', 'currency')) {
            Schema::table('shipping_invoices', function (Blueprint $table) {
                $table->dropColumn('currency');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('shipping_invoices') && ! Schema::hasColumn('shipping_invoices', 'currency')) {
            Schema::table('shipping_invoices', function (Blueprint $table) {
                $table->string('currency', 10)->default('USD');
            });
        }
    }
};
