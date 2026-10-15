<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\PermissionRegistrar;

/**
 * Fitur Monitoring PC dihapus; bersihkan tabel telemetri/perintah power
 * beserta permission view-monitoring & control-monitoring.
 *
 * Guard agar aman untuk fresh install (tabel/permission-nya tidak pernah
 * dibuat) maupun DB yang sudah menjalankan migration 2026_10_15_*.
 */
return new class extends Migration
{
    private const PERMISSIONS = ['view-monitoring', 'control-monitoring'];

    public function up(): void
    {
        if (Schema::hasTable('pc_power_commands')) {
            Schema::dropIfExists('pc_power_commands');
        }

        if (Schema::hasTable('pc_monitors')) {
            Schema::dropIfExists('pc_monitors');
        }

        $ids = DB::table('permissions')->whereIn('name', self::PERMISSIONS)->pluck('id');
        DB::table('role_has_permissions')->whereIn('permission_id', $ids)->delete();
        DB::table('permissions')->whereIn('id', $ids)->delete();

        // Migrasi memakai DB mentah: flush cache permission spatie.
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function down(): void
    {
        // Tidak dikembalikan: tabel & permission hasil fitur yang sudah dihapus dari kode.
    }
};
