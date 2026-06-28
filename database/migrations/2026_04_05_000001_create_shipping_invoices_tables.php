<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shipping_invoices', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_number', 50)->unique();
            $table->date('invoice_date');
            $table->string('currency', 10)->default('USD');
            $table->decimal('total_amount', 12, 2)->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('shipping_invoice_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('shipping_invoice_id');
            $table->unsignedBigInteger('deal_id');
            $table->text('description');
            $table->decimal('unit_price', 12, 2)->default(0);
            $table->string('gst_type', 50)->nullable();
            $table->decimal('amount', 12, 2)->default(0);
            $table->timestamps();

            $table->foreign('shipping_invoice_id')->references('id')->on('shipping_invoices')->onDelete('cascade');
            $table->foreign('deal_id')->references('id')->on('deals')->onDelete('restrict');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shipping_invoice_items');
        Schema::dropIfExists('shipping_invoices');
    }
};