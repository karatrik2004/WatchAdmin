<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('deals', function (Blueprint $table) {
            $table->string('review_status')->default('under_review')->after('deal_status');
            $table->unsignedBigInteger('reviewed_by')->nullable()->after('review_status');
            $table->timestamp('reviewed_at')->nullable()->after('reviewed_by');
            $table->index('review_status');
        });
    }

    public function down(): void
    {
        Schema::table('deals', function (Blueprint $table) {
            $table->dropIndex(['review_status']);
            $table->dropColumn(['review_status', 'reviewed_by', 'reviewed_at']);
        });
    }
};
