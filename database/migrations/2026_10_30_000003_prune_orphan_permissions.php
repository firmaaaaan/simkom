<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\PermissionRegistrar;

/**
 * Bersihkan permission yatim sisa fitur yang sudah dihapus dari kode:
 * - manage-lab-layouts            (fitur Denah Lab)
 * - remote-power-on-computers     (fitur Remote Power lama)
 * - remote-power-off-computers    (fitur Remote Power lama)
 * - view-remote-power-logs-computers (fitur Remote Power lama)
 *
 * Baris-baris ini masih tampil di pemilih permission role karena
 * RoleController memuat seluruh isi tabel permissions. Fiturnya sendiri
 * sudah tidak ada (tanpa route/kode), jadi pivot & permission-nya dibuang.
 *
 * Guard agar aman untuk fresh install maupun DB yang sudah berjalan.
 */
return new class extends Migration
{
    private const ORPHANS = [
        'manage-lab-layouts',
        'remote-power-on-computers',
        'remote-power-off-computers',
        'view-remote-power-logs-computers',
    ];

    public function up(): void
    {
        $ids = DB::table('permissions')->whereIn('name', self::ORPHANS)->pluck('id');

        if ($ids->isEmpty()) {
            return;
        }

        if (Schema::hasTable('role_has_permissions')) {
            DB::table('role_has_permissions')->whereIn('permission_id', $ids)->delete();
        }

        if (Schema::hasTable('model_has_permissions')) {
            DB::table('model_has_permissions')->whereIn('permission_id', $ids)->delete();
        }

        DB::table('permissions')->whereIn('id', $ids)->delete();

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function down(): void
    {
        // Tidak dikembalikan: permission hasil fitur yang sudah dihapus dari kode.
    }
};
