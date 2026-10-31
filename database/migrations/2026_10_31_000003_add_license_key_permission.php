<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Spatie\Permission\PermissionRegistrar;

/**
 * Permission untuk melihat product key lisensi software secara utuh.
 * Key disamarkan secara default; hanya role dengan izin ini yang bisa
 * melakukan reveal lewat endpoint khusus.
 *
 * Dibuat lewat migrasi agar environment yang hanya menjalankan `migrate`
 * tetap mendapatkannya. Backfill: role admin saja.
 */
return new class extends Migration
{
    private const NAME = 'view-license-key';

    private const LABEL = 'Lihat Product Key Lisensi';

    public function up(): void
    {
        if (! Schema::hasTable('permissions')) {
            return;
        }

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

        if (Schema::hasTable('role_has_permissions')) {
            $adminRoleId = DB::table('roles')->where('name', 'admin')->value('id');

            if ($adminRoleId) {
                $attached = DB::table('role_has_permissions')
                    ->where('role_id', $adminRoleId)
                    ->where('permission_id', $id)
                    ->exists();

                if (! $attached) {
                    DB::table('role_has_permissions')->insert([
                        'role_id' => $adminRoleId,
                        'permission_id' => $id,
                    ]);
                }
            }
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function down(): void
    {
        $id = DB::table('permissions')->where('name', self::NAME)->value('id');

        if (! $id) {
            return;
        }

        if (Schema::hasTable('role_has_permissions')) {
            DB::table('role_has_permissions')->where('permission_id', $id)->delete();
        }

        if (Schema::hasTable('model_has_permissions')) {
            DB::table('model_has_permissions')->where('permission_id', $id)->delete();
        }

        DB::table('permissions')->where('id', $id)->delete();

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
};
