<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Spatie\Permission\PermissionRegistrar;

/**
 * Permission modul To-Do List: view/create/edit/delete-todos.
 *
 * Dibuat via migrasi agar cukup "git pull && php artisan migrate"
 * (tanpa db:seed). Backfill: SEMUA role yang sudah ada mendapat seluruh
 * aksi todos — To-Do List saat ini terbuka untuk semua user login, jadi
 * tidak ada role yang boleh kehilangan akses setelah route diproteksi
 * middleware. RoleSeeder juga mendaftarkan modul ini untuk instalasi baru.
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
            $name = "{$action}-todos";

            $id = DB::table('permissions')->where('name', $name)->value('id');
            if (! $id) {
                $id = (string) Str::uuid();
                DB::table('permissions')->insert([
                    'id' => $id,
                    'name' => $name,
                    'label' => $labelWord.' To-Do List',
                    'guard_name' => 'web',
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }

            $permissionIds[$action] = $id;
        }

        // Backfill: SEMUA role yang sudah ada mendapat aksi todos.
        // To-Do List bersifat pribadi & sebelumnya terbuka untuk semua
        // user login — tanpa backfill ini, role non-admin/laboran akan
        // 403 mendadak setelah route diproteksi.
        $roleIds = DB::table('roles')->pluck('id');

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
        $names = array_map(fn ($action) => "{$action}-todos", array_keys(self::ACTIONS));

        $ids = DB::table('permissions')->whereIn('name', $names)->pluck('id');
        DB::table('role_has_permissions')->whereIn('permission_id', $ids)->delete();
        DB::table('permissions')->whereIn('id', $ids)->delete();

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
};
