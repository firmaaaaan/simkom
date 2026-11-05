<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Spatie\Permission\PermissionRegistrar;

/**
 * Permission modul Berita Acara Kecelakaan: view/create/edit/delete-accident-reports.
 *
 * Dibuat via migrasi agar cukup "php artisan migrate" (tanpa db:seed).
 * Backfill: role yang sudah memegang aksi laporan insiden K3 (admin/laboran)
 * mendapat seluruh aksi berita acara kecelakaan.
 */
return new class extends Migration
{
    private const ACTIONS = [
        'view' => 'Lihat',
        'create' => 'Tambah',
        'edit' => 'Ubah',
        'delete' => 'Hapus',
    ];

    public function up(): void
    {
        $now = now();
        $permissionIds = [];

        foreach (self::ACTIONS as $action => $labelWord) {
            $name = "{$action}-accident-reports";

            $id = DB::table('permissions')->where('name', $name)->value('id');
            if (! $id) {
                $id = (string) Str::uuid();
                DB::table('permissions')->insert([
                    'id' => $id,
                    'name' => $name,
                    'label' => $labelWord.' Berita Acara Kecelakaan',
                    'guard_name' => 'web',
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }

            $permissionIds[$action] = $id;
        }

        $sourceIds = DB::table('permissions')
            ->whereIn('name', ['view-incident-reports', 'create-incident-reports', 'edit-incident-reports', 'delete-incident-reports'])
            ->pluck('id');

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

        // Kaitkan ke role pemegang modul pembanding + role bawaan,
        // sehingga pemberian akses tidak bergantung pada RoleSeeder.
        $roleIds = DB::table('role_has_permissions')
            ->whereIn('permission_id', $sourceIds)
            ->distinct()
            ->pluck('role_id')
            ->merge(DB::table('roles')->whereIn('name', ['admin', 'laboran'])->pluck('id'))
            ->unique()
            ->values();

        foreach ($roleIds as $roleId) {
            foreach ($permissionIds as $permissionId) {
                $attached = DB::table('role_has_permissions')
                    ->where('role_id', $roleId)
                    ->where('permission_id', $permissionId)
                    ->exists();

                if (! $attached) {
                    DB::table('role_has_permissions')->insert([
                        'role_id' => $roleId,
                        'permission_id' => $permissionId,
                    ]);
                }
            }
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function down(): void
    {
        $names = array_map(fn ($action) => "{$action}-accident-reports", array_keys(self::ACTIONS));

        $ids = DB::table('permissions')->whereIn('name', $names)->pluck('id');
        DB::table('role_has_permissions')->whereIn('permission_id', $ids)->delete();
        DB::table('permissions')->whereIn('id', $ids)->delete();

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
};
