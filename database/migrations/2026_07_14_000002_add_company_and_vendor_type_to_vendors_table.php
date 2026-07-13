<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vendors', function (Blueprint $table) {
            if (!Schema::hasColumn('vendors', 'company_name')) {
                $table->string('company_name')->nullable();
            }
            if (!Schema::hasColumn('vendors', 'vendor_type')) {
                $table->string('vendor_type')->default('individual');
            }
        });
    }

    public function down(): void
    {
        Schema::table('vendors', function (Blueprint $table) {
            if (Schema::hasColumn('vendors', 'company_name')) {
                $table->dropColumn('company_name');
            }
            if (Schema::hasColumn('vendors', 'vendor_type')) {
                $table->dropColumn('vendor_type');
            }
        });
    }
};