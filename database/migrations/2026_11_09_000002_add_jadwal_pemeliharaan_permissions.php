<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    private const MODULE = 'jadwal-pemeliharaan';
    private const LABEL = 'Jadwal Pemeliharaan Alat & Bahan';

    private const ACTIONS = [
        'view' => 'Lihat',
        'create' => 'Tambah',
        'edit' => 'Ubah',
        'delete' => 'Hapus',
        'print' => 'Cetak',
    ];

    public function up(): void
    {
        $now = now();
        $permissionIds = [];

        foreach (self::ACTIONS as $action => $labelWord) {
            $name = "{$action}-".self::MODULE;
            $id = DB::table('permissions')->where('name', $name)->value('id');
            if (! $id) {
                $id = (string) Str::uuid();
                DB::table('permissions')->insert([
                    'id' => $id,
                    'name' => $name,
                    'label' => $labelWord.' '.self::LABEL,
                    'guard_name' => 'web',
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
            $permissionIds[$action] = $id;
        }

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

        $roleIds = DB::table('roles')->whereIn('name', ['admin', 'laboran', 'superadmin'])->pluck('id')->values();

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
        $names = array_map(fn ($action) => "{$action}-".self::MODULE, array_keys(self::ACTIONS));
        $ids = DB::table('permissions')->whereIn('name', $names)->pluck('id');
        DB::table('role_has_permissions')->whereIn('permission_id', $ids)->delete();
        DB::table('permissions')->whereIn('id', $ids)->delete();

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
};
