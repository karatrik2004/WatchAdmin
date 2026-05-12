<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('deal_buyer_details', function (Blueprint $table) {
            $table->decimal('buyer_sale_price_purchase_rate', 20, 8)->nullable()->after('buyer_exchange_rate');
            $table->decimal('buyer_sale_price_in_purchase_currency', 12, 2)->nullable()->after('buyer_sale_price_purchase_rate');
        });
    }

    public function down()
    {
        Schema::table('deal_buyer_details', function (Blueprint $table) {
            $table->dropColumn(['buyer_sale_price_purchase_rate', 'buyer_sale_price_in_purchase_currency']);
        });
    }
};