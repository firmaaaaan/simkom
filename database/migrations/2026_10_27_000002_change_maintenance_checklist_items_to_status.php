<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('maintenance_checklist_items', 'status')) {
            Schema::table('maintenance_checklist_items', function (Blueprint $table) {
                $table->string('status')->nullable()->after('item_number');
            });
        }

        // Data lama: is_checked=true = pertanyaan sudah dikerjakan; false
        // berarti "belum" — di-backfill demikian karena item pemeliharaan
        // berupa pertanyaan ya/tidak, bukan status komponen.
        if (Schema::hasColumn('maintenance_checklist_items', 'is_checked')) {
            DB::table('maintenance_checklist_items')
                ->where('is_checked', true)
                ->update(['status' => 'sudah']);

            DB::table('maintenance_checklist_items')
                ->where('is_checked', false)
                ->update(['status' => 'belum']);

            Schema::table('maintenance_checklist_items', function (Blueprint $table) {
                $table->dropColumn('is_checked');
            });
        }

        if (!Schema::hasIndex('maintenance_checklist_items', 'idx_maint_chk_items_chk_id_status')) {
            Schema::table('maintenance_checklist_items', function (Blueprint $table) {
                $table->index(['maintenance_checklist_id', 'status'], 'idx_maint_chk_items_chk_id_status');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('maintenance_checklist_items', 'status')) {
            DB::table('maintenance_checklist_items')
                ->where('status', 'sudah')
                ->update(['is_checked' => true]);

            DB::table('maintenance_checklist_items')
                ->whereIn('status', ['belum', 'tidak_ada'])
                ->update(['is_checked' => false]);

            Schema::table('maintenance_checklist_items', function (Blueprint $table) {
                $table->dropIndex('idx_maint_chk_items_chk_id_status');
                $table->dropColumn('status');
            });

            Schema::table('maintenance_checklist_items', function (Blueprint $table) {
                $table->boolean('is_checked')->default(false)->after('item_number');
            });
        }
    }
};
