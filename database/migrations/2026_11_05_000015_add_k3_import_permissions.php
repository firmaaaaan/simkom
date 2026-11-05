<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Spatie\Permission\PermissionRegistrar;

/**
 * Permission import untuk seluruh modul K3: import-<module>.
 *
 * Dibuat via migrasi agar cukup "php artisan migrate" (tanpa db:seed).
 * Backfill: role yang memegang aksi create modul terkait (plus role
 * bawaan admin/laboran) mendapat aksi import.
 */
return new class extends Migration
{
    /** module => label pada permission. */
    private const MODULES = [
        'incident-reports' => 'Laporan Insiden',
        'apar-inspections' => 'Lembar Periksa APAR',
        'hse-inspections' => 'Inspeksi HSE',
        'k3-inspection-reports' => 'Laporan Inspeksi K3',
        'accident-reports' => 'Berita Acara Kecelakaan',
        'jsa' => 'Analisis Keselamatan Pekerjaan (JSA)',
        'damage-reports' => 'Laporan Kerusakan',
        'occupational-disease-investigations' => 'Penyelidikan PAK',
    ];

    public function up(): void
    {
        $now = now();

        // Pastikan role bawaan tersedia (pada instalasi baru role dibuat
        // di sini karena RoleSeeder baru berjalan setelah migrate).
        foreach (['admin' => 'Admin', 'laboran' => 'Laboran'] as $roleName => $roleLabel) {
            if (! DB::table('roles')->where('name', $roleName)->exists()) {
                DB::table('roles')->insert([
                    'id' => (string) Str::uuid(),
                    'name' => $roleName,
                    'label' => $roleLabel,
                    'guard_name' => 'web',
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }

        foreach (self::MODULES as $module => $label) {
            $name = "import-{$module}";

            $id = DB::table('permissions')->where('name', $name)->value('id');
            if (! $id) {
                $id = (string) Str::uuid();
                DB::table('permissions')->insert([
                    'id' => $id,
                    'name' => $name,
                    'label' => 'Import '.$label,
                    'guard_name' => 'web',
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }

            // Role pemegang aksi create modul + role bawaan.
            $roleIds = DB::table('role_has_permissions')
                ->whereIn('permission_id', function ($query) use ($module) {
                    $query->select('id')->from('permissions')->where('name', "create-{$module}");
                })
                ->distinct()
                ->pluck('role_id')
                ->merge(DB::table('roles')->whereIn('name', ['admin', 'laboran'])->pluck('id'))
                ->unique()
                ->values();

            foreach ($roleIds as $roleId) {
                $attached = DB::table('role_has_permissions')
                    ->where('role_id', $roleId)
                    ->where('permission_id', $id)
                    ->exists();

                if (! $attached) {
                    DB::table('role_has_permissions')->insert([
                        'role_id' => $roleId,
                        'permission_id' => $id,
                    ]);
                }
            }
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function down(): void
    {
        $names = array_map(fn ($module) => "import-{$module}", array_keys(self::MODULES));

        $ids = DB::table('permissions')->whereIn('name', $names)->pluck('id');
        DB::table('role_has_permissions')->whereIn('permission_id', $ids)->delete();
        DB::table('permissions')->whereIn('id', $ids)->delete();

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
};
