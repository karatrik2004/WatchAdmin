<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('deal_buyer_details', function (Blueprint $table) {
            $table->decimal('buyer_profit_amount', 12, 2)->nullable()->after('buyer_sale_price_in_purchase_currency');
            $table->string('buyer_profit_currency', 10)->nullable()->after('buyer_profit_amount');
            $table->boolean('buyer_is_loss')->default(false)->after('buyer_profit_currency');
        });
    }

    public function down()
    {
        Schema::table('deal_buyer_details', function (Blueprint $table) {
            $table->dropColumn(['buyer_profit_amount', 'buyer_profit_currency', 'buyer_is_loss']);
        });
    }
};