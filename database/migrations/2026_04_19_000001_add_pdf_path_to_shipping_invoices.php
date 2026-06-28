<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('shipping_invoices') && ! Schema::hasColumn('shipping_invoices', 'pdf_path')) {
            Schema::table('shipping_invoices', function (Blueprint $table) {
                $table->string('pdf_path', 500)->nullable()->after('notes');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('shipping_invoices') && Schema::hasColumn('shipping_invoices', 'pdf_path')) {
            Schema::table('shipping_invoices', function (Blueprint $table) {
                $table->dropColumn('pdf_path');
            });
        }
    }
};
