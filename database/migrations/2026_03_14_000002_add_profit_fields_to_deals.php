<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('deals', function (Blueprint $table) {
            $table->decimal('sale_to_purchase_rate', 20, 8)->nullable()->after('purchase_price');
            $table->decimal('sale_in_purchase_currency', 12, 2)->nullable()->after('sale_to_purchase_rate');
            $table->decimal('profit_amount', 12, 2)->nullable()->after('sale_in_purchase_currency');
            $table->string('profit_currency', 10)->nullable()->after('profit_amount');
            $table->boolean('is_loss')->default(false)->after('profit_currency');
        });
    }

    public function down()
    {
        Schema::table('deals', function (Blueprint $table) {
            $table->dropColumn([
                'sale_to_purchase_rate',
                'sale_in_purchase_currency',
                'profit_amount',
                'profit_currency',
                'is_loss'
            ]);
        });
    }
};