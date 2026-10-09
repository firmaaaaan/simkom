<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Spatie\Permission\PermissionRegistrar;

/**
 * Tambah permission per-aksi export-/import-/print-<modul> agar kegiatan
 * ekspor, impor, dan cetak (termasuk QR stiker/label) bisa diatur terpisah
 * dari aksi Lihat/Tambah/Ubah/Hapus.
 *
 * Backfill menjaga perilaku lama tidak berubah: setiap role yang sebelumnya
 * punya akses ke route (lihat sumber di masing-masing modul) otomatis
 * mendapat aksi barunya. Cukup "php artisan migrate" — tidak perlu db:seed.
 */
return new class extends Migration
{
    /**
     * modul => [label, [aksi baru => aksi lama sumber yang menjaga route]]:
     * - export & print disimpan dari route yang dulu di grup view-<modul>;
     * - import disimpan dari route yang dulu di grup create-<modul> /
     *   edit-<modul> (hardware/software/components/lab-schedules = create,
     *   computers/maintenance = edit — lihat routes/web.php lama).
     */
    private const MODULES = [
        'users' => ['User', ['export' => 'view']],
        'roles' => ['Role', ['export' => 'view']],
        'laboratories' => ['Laboratorium', ['export' => 'view']],
        'academic-years' => ['Tahun Ajaran', ['export' => 'view']],
        'hardware' => ['Hardware', ['export' => 'view', 'import' => 'create']],
        'software' => ['Software', ['export' => 'view', 'import' => 'create']],
        'components' => ['Komponen', ['export' => 'view', 'import' => 'create', 'print' => 'view']],
        'computers' => ['Komputer', ['export' => 'view', 'import' => 'edit', 'print' => 'view']],
        'maintenance' => ['Pemeliharaan', ['export' => 'view', 'import' => 'edit', 'print' => 'view']],
        'tickets' => ['Kendala Praktikum', ['export' => 'view']],
        'borrowings' => ['Peminjaman', ['export' => 'view']],
        'lab-usages' => ['Penggunaan Lab', ['export' => 'view', 'print' => 'view']],
        'lab-schedules' => ['Jadwal Lab', ['export' => 'view', 'import' => 'create']],
        'reports' => ['Laporan', ['print' => 'view']],
    ];

    private const ACTION_LABELS = [
        'export' => 'Export',
        'import' => 'Import',
        'print' => 'Cetak',
    ];

    public function up(): void
    {
        $now = now();

        foreach (self::MODULES as $module => [$label, $extras]) {
            $actionIds = [];

            foreach ($extras as $action => $source) {
                $name = "{$action}-{$module}";

                $id = DB::table('permissions')->where('name', $name)->value('id');
                if (! $id) {
                    $id = (string) Str::uuid();
                    DB::table('permissions')->insert([
                        'id' => $id,
                        'name' => $name,
                        'label' => self::ACTION_LABELS[$action].' '.$label,
                        'guard_name' => 'web',
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);
                }

                $actionIds[$action] = $id;
            }

            // Backfill per aksi: role pemegang sumbernya (mis. view-computers)
            // mendapat aksi baru (export-computers), jadi akses lama tidak berubah.
            foreach ($extras as $action => $source) {
                $sourceId = DB::table('permissions')->where('name', "{$source}-{$module}")->value('id');
                if (! $sourceId) {
                    continue;
                }

                $roleIds = DB::table('role_has_permissions')
                    ->where('permission_id', $sourceId)
                    ->pluck('role_id');

                foreach ($roleIds as $roleId) {
                    $attached = DB::table('role_has_permissions')
                        ->where('role_id', $roleId)
                        ->where('permission_id', $actionIds[$action])
                        ->exists();

                    if (! $attached) {
                        DB::table('role_has_permissions')->insert([
                            'role_id' => $roleId,
                            'permission_id' => $actionIds[$action],
                        ]);
                    }
                }
            }
        }

        // Migrasi memakai DB mentah: flush cache permission spatie.
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function down(): void
    {
        foreach (self::MODULES as $module => [, $extras]) {
            $names = array_map(fn ($action) => "{$action}-{$module}", array_keys($extras));

            $ids = DB::table('permissions')->whereIn('name', $names)->pluck('id');
            DB::table('role_has_permissions')->whereIn('permission_id', $ids)->delete();
            DB::table('permissions')->whereIn('id', $ids)->delete();
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
};
