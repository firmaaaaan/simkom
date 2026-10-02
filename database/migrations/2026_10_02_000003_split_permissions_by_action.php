<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Spatie\Permission\PermissionRegistrar;

/**
 * Pecah permission per-modul (manage-*) menjadi per-aksi:
 * view-/create-/edit-/delete-<modul>.
 *
 * Role yang memegang manage-X otomatis mendapat semua aksi modul X,
 * sehingga perilaku akses tidak berubah. Permission lama dihapus agar
 * tidak muncul ganda di form Role.
 *
 * Cukup "git pull && php artisan migrate" — tidak perlu db:seed.
 */
return new class extends Migration
{
    /** modul => [label, aksi yang tersedia (punya route)]. */
    private const MODULES = [
        'users' => ['User', ['view', 'create', 'edit', 'delete']],
        'roles' => ['Role', ['view', 'create', 'edit', 'delete']],
        'laboratories' => ['Laboratorium', ['view', 'create', 'edit', 'delete']],
        'academic-years' => ['Tahun Ajaran', ['view', 'create', 'edit', 'delete']],
        'hardware' => ['Hardware', ['view', 'create', 'edit', 'delete']],
        'software' => ['Software', ['view', 'create', 'edit', 'delete']],
        'components' => ['Komponen', ['view', 'create', 'edit', 'delete']],
        'computers' => ['Komputer', ['view', 'create', 'edit', 'delete']],
        'maintenance' => ['Pemeliharaan', ['view', 'create', 'edit', 'delete']],
        'tickets' => ['Kendala Praktikum', ['view', 'create', 'edit', 'delete']],
        'borrowings' => ['Peminjaman', ['view', 'edit']],
        'lab-usages' => ['Penggunaan Lab', ['view', 'create', 'edit', 'delete']],
        'lab-schedules' => ['Jadwal Lab', ['view', 'create', 'edit', 'delete']],
        'backups' => ['Backup', ['view', 'edit', 'delete']],
        'reports' => ['Laporan', ['view']],
    ];

    /** permission lama => modul tujuan. */
    private const LEGACY = [
        'manage-users' => 'users',
        'manage-roles' => 'roles',
        'manage-laboratories' => 'laboratories',
        'manage-academic-years' => 'academic-years',
        'manage-hardware' => 'hardware',
        'manage-software' => 'software',
        'manage-components' => 'components',
        'manage-computers' => 'computers',
        'manage-maintenance' => 'maintenance',
        'manage-tickets' => 'tickets',
        'manage-borrowings' => 'borrowings',
        'manage-lab-usages' => 'lab-usages',
        'manage-lab-schedules' => 'lab-schedules',
        'manage-backups' => 'backups',
    ];

    private const ACTION_LABELS = [
        'view' => 'Lihat',
        'create' => 'Tambah',
        'edit' => 'Ubah',
        'delete' => 'Hapus',
    ];

    public function up(): void
    {
        $now = now();
        $actionIds = [];

        foreach (self::MODULES as $module => [$label, $actions]) {
            foreach ($actions as $action) {
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

                $actionIds[$name] = $id;
            }
        }

        // Role pemegang permission lama mendapat semua aksi modulnya.
        foreach (self::LEGACY as $legacyName => $module) {
            $legacyId = DB::table('permissions')->where('name', $legacyName)->value('id');
            if (! $legacyId) {
                continue;
            }

            $roleIds = DB::table('role_has_permissions')
                ->where('permission_id', $legacyId)
                ->pluck('role_id');

            foreach ($roleIds as $roleId) {
                foreach (self::MODULES[$module][1] as $action) {
                    $newId = $actionIds["{$action}-{$module}"];

                    $attached = DB::table('role_has_permissions')
                        ->where('role_id', $roleId)
                        ->where('permission_id', $newId)
                        ->exists();

                    if (! $attached) {
                        DB::table('role_has_permissions')->insert([
                            'role_id' => $roleId,
                            'permission_id' => $newId,
                        ]);
                    }
                }
            }
        }

        // Buang permission lama (view-reports dipertahankan: sudah nama per-aksi).
        $legacyIds = DB::table('permissions')->whereIn('name', array_keys(self::LEGACY))->pluck('id');
        DB::table('role_has_permissions')->whereIn('permission_id', $legacyIds)->delete();
        DB::table('permissions')->whereIn('id', $legacyIds)->delete();

        // Migrasi memakai DB mentah: flush cache permission spatie.
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function down(): void
    {
        $now = now();

        foreach (self::LEGACY as $legacyName => $module) {
            $label = 'Kelola '.self::MODULES[$module][0];

            $legacyId = DB::table('permissions')->where('name', $legacyName)->value('id');
            if (! $legacyId) {
                $legacyId = (string) Str::uuid();
                DB::table('permissions')->insert([
                    'id' => $legacyId,
                    'name' => $legacyName,
                    'label' => $label,
                    'guard_name' => 'web',
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }

            $roleIds = DB::table('role_has_permissions')
                ->whereIn('permission_id', function ($query) use ($module) {
                    $names = array_map(fn ($action) => "{$action}-{$module}", self::MODULES[$module][1]);
                    $query->select('id')->from('permissions')->whereIn('name', $names);
                })
                ->pluck('role_id')
                ->unique();

            foreach ($roleIds as $roleId) {
                $attached = DB::table('role_has_permissions')
                    ->where('role_id', $roleId)
                    ->where('permission_id', $legacyId)
                    ->exists();

                if (! $attached) {
                    DB::table('role_has_permissions')->insert([
                        'role_id' => $roleId,
                        'permission_id' => $legacyId,
                    ]);
                }
            }

            $names = array_map(fn ($action) => "{$action}-{$module}", self::MODULES[$module][1]);
            $actionIds = DB::table('permissions')->whereIn('name', $names)->pluck('id');
            DB::table('role_has_permissions')->whereIn('permission_id', $actionIds)->delete();
            DB::table('permissions')->whereIn('id', $actionIds)->delete();
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
};
