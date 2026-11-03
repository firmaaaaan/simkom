<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Spatie\Permission\PermissionRegistrar;

/**
 * Permission modul Laporan Insiden: view/create/edit/delete-incident-reports.
 *
 * Dibuat via migrasi agar cukup "git pull && php artisan migrate"
 * (tanpa db:seed). Backfill: role yang sudah punya aksi tickets
 * (indikator admin/laboran) mendapat seluruh aksi laporan insiden.
 * RoleSeeder juga sudah mendaftarkan modul ini untuk instalasi baru.
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
            $name = "{$action}-incident-reports";

            $id = DB::table('permissions')->where('name', $name)->value('id');
            if (! $id) {
                $id = (string) Str::uuid();
                DB::table('permissions')->insert([
                    'id' => $id,
                    'name' => $name,
                    'label' => $labelWord.' Laporan Insiden',
                    'guard_name' => 'web',
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }

            $permissionIds[$action] = $id;
        }

        $sourceIds = DB::table('permissions')
            ->whereIn('name', ['view-tickets', 'create-tickets', 'edit-tickets', 'delete-tickets'])
            ->pluck('id');

        $roleIds = DB::table('role_has_permissions')
            ->whereIn('permission_id', $sourceIds)
            ->distinct()
            ->pluck('role_id');

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
        $names = array_map(fn ($action) => "{$action}-incident-reports", array_keys(self::ACTIONS));

        $ids = DB::table('permissions')->whereIn('name', $names)->pluck('id');
        DB::table('role_has_permissions')->whereIn('permission_id', $ids)->delete();
        DB::table('permissions')->whereIn('id', $ids)->delete();

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
};
