<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    private const NAME = 'edit-monitoring-suhu';

    private const LABEL = 'Ubah Monitoring Suhu Ruangan';

    public function up(): void
    {
        $now = now();
        $id = DB::table('permissions')->where('name', self::NAME)->value('id');

        if (! $id) {
            $id = (string) Str::uuid();
            DB::table('permissions')->insert([
                'id' => $id,
                'name' => self::NAME,
                'label' => self::LABEL,
                'guard_name' => 'web',
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        $roleIds = DB::table('roles')->whereIn('name', ['admin', 'laboran', 'superadmin'])->pluck('id');

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

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function down(): void
    {
        $ids = DB::table('permissions')->where('name', self::NAME)->pluck('id');
        DB::table('role_has_permissions')->whereIn('permission_id', $ids)->delete();
        DB::table('permissions')->whereIn('id', $ids)->delete();

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
};
