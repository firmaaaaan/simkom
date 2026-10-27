<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('device_check_items', function (Blueprint $table) {
            $table->string('status')->nullable()->after('item_key');
        });

        // Data lama: is_checked=true = "ada dan berfungsi"; is_checked=false
        // dulu berarti "bermasalah atau tidak ada" dan di-backfill menjadi
        // "tidak_ada" sesuai keputusan (asumsi konservatif data historis).
        DB::table('device_check_items')
            ->where('is_checked', true)
            ->update(['status' => 'berfungsi']);

        DB::table('device_check_items')
            ->where('is_checked', false)
            ->update(['status' => 'tidak_ada']);

        Schema::table('device_check_items', function (Blueprint $table) {
            $table->dropColumn('is_checked');
            $table->index(['device_check_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::table('device_check_items', function (Blueprint $table) {
            $table->boolean('is_checked')->default(false)->after('item_key');
        });

        DB::table('device_check_items')
            ->where('status', 'berfungsi')
            ->update(['is_checked' => true]);

        DB::table('device_check_items')
            ->whereIn('status', ['rusak', 'tidak_ada'])
            ->update(['is_checked' => false]);

        Schema::table('device_check_items', function (Blueprint $table) {
            $table->dropIndex(['device_check_id', 'status']);
            $table->dropColumn('status');
        });
    }
};
