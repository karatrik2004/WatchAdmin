<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasColumn('deals', 'watch_id')) {
            Schema::table('deals', function (Blueprint $table) {
                $table->string('watch_id')->nullable()->unique()->after('id');
            });
        }

        $deals = DB::table('deals')
            ->whereNull('watch_id')
            ->orWhere('watch_id', '')
            ->get();

        foreach ($deals as $deal) {
            DB::table('deals')
                ->where('id', $deal->id)
                ->update([
                    'watch_id' => 'WATCH-' . str_pad((int) $deal->id, 6, '0', STR_PAD_LEFT),
                ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('deals', 'watch_id')) {
            Schema::table('deals', function (Blueprint $table) {
                $table->dropColumn('watch_id');
            });
        }
    }
};
