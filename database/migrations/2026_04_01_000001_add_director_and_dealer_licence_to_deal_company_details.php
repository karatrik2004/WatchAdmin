<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('deal_company_details', function (Blueprint $table) {
            $table->string('director_name')->nullable()->after('abn_number');
            $table->string('dealer_licence_number')->nullable()->after('director_name');
        });
    }

    public function down(): void
    {
        Schema::table('deal_company_details', function (Blueprint $table) {
            $table->dropColumn(['director_name', 'dealer_licence_number']);
        });
    }
};
