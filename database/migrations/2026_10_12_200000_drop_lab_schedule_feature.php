<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('lab_schedules');

        if (Schema::hasColumn('laboratories', 'show_in_schedule')) {
            Schema::table('laboratories', function (Blueprint $table) {
                $table->dropColumn('show_in_schedule');
            });
        }

        if (Schema::hasTable('settings')) {
            DB::table('settings')->where('id', 2)->delete();
        }

        if (Schema::hasTable('permissions')) {
            $ids = DB::table('permissions')->where(function ($query) {
                $query->where('name', 'like', '%-lab-schedules')
                    ->orWhere('name', 'manage-lab-schedules');
            })->pluck('id');

            if ($ids->isNotEmpty()) {
                DB::table('role_has_permissions')->whereIn('permission_id', $ids)->delete();
                DB::table('permissions')->whereIn('id', $ids)->delete();

                app(PermissionRegistrar::class)->forgetCachedPermissions();
            }
        }
    }

    public function down(): void
    {
        // Tidak bisa di-down secara otomatis (data jadwal & URL real-time hilang).
    }
};
