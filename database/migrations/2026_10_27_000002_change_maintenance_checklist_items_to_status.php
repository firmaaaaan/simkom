<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('maintenance_checklist_items', function (Blueprint $table) {
            $table->string('status')->nullable()->after('item_number');
        });

        // Data lama: is_checked=true = pertanyaan sudah dikerjakan; false
        // berarti "belum" — di-backfill demikian karena item pemeliharaan
        // berupa pertanyaan ya/tidak, bukan status komponen.
        DB::table('maintenance_checklist_items')
            ->where('is_checked', true)
            ->update(['status' => 'sudah']);

        DB::table('maintenance_checklist_items')
            ->where('is_checked', false)
            ->update(['status' => 'belum']);

        Schema::table('maintenance_checklist_items', function (Blueprint $table) {
            $table->dropColumn('is_checked');
            $table->index(['maintenance_checklist_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::table('maintenance_checklist_items', function (Blueprint $table) {
            $table->boolean('is_checked')->default(false)->after('item_number');
        });

        DB::table('maintenance_checklist_items')
            ->where('status', 'sudah')
            ->update(['is_checked' => true]);

        DB::table('maintenance_checklist_items')
            ->whereIn('status', ['belum', 'tidak_ada'])
            ->update(['is_checked' => false]);

        Schema::table('maintenance_checklist_items', function (Blueprint $table) {
            $table->dropIndex(['maintenance_checklist_id', 'status']);
            $table->dropColumn('status');
        });
    }
};
