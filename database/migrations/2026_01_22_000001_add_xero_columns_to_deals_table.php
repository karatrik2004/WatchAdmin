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
        Schema::table('deals', function (Blueprint $table) {
            $table->string('customer_xero_id')->nullable()->after('id');
            $table->string('supplier_xero_id')->nullable()->after('customer_xero_id');
            $table->string('xero_invoice_id')->nullable()->after('supplier_xero_id');
            $table->string('xero_bill_id')->nullable()->after('xero_invoice_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('deals', function (Blueprint $table) {
            $table->dropColumn(['customer_xero_id', 'supplier_xero_id', 'xero_invoice_id', 'xero_bill_id']);
        });
    }
};
