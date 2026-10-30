<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Spatie\Permission\PermissionRegistrar;

/**
 * Permission modul Tanda Tangan Laporan sebelumnya hanya dibuat lewat
 * RoleSeeder, sehingga environment yang hanya menjalankan `migrate`
 * (mis. server produksi) tidak pernah mendapatkannya dan modul tersebut
 * tidak muncul di halaman Role.
 *
 * Migration ini membuat permission-nya secara idempoten sekaligus
 * membackfill role lama:
 * - pemegang view/print/edit-reports mendapat view-signatures;
 * - pemegang edit-reports mendapat create/edit/delete-signatures;
 * - role admin mendapat keempatnya (di lokal admin memegang semua permission).
 */
return new class extends Migration
{
    /** nama permission => label */
    private const PERMISSIONS = [
        'view-signatures' => 'Lihat Tanda Tangan Laporan',
        'create-signatures' => 'Tambah Tanda Tangan Laporan',
        'edit-signatures' => 'Ubah Tanda Tangan Laporan',
        'delete-signatures' => 'Hapus Tanda Tangan Laporan',
    ];

    public function up(): void
    {
        if (! Schema::hasTable('permissions')) {
            return;
        }

        $now = now();

        foreach (self::PERMISSIONS as $name => $label) {
            if (! DB::table('permissions')->where('name', $name)->exists()) {
                DB::table('permissions')->insert([
                    'id' => (string) Str::uuid(),
                    'name' => $name,
                    'label' => $label,
                    'guard_name' => 'web',
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }

        $ids = DB::table('permissions')
            ->whereIn('name', array_keys(self::PERMISSIONS))
            ->pluck('id', 'name');

        if (! Schema::hasTable('role_has_permissions')) {
            app(PermissionRegistrar::class)->forgetCachedPermissions();

            return;
        }

        $roleIdsWith = function (array $permissionNames): array {
            $permissionIds = DB::table('permissions')
                ->whereIn('name', $permissionNames)
                ->pluck('id');

            if ($permissionIds->isEmpty()) {
                return [];
            }

            return DB::table('role_has_permissions')
                ->whereIn('permission_id', $permissionIds)
                ->pluck('role_id')
                ->unique()
                ->values()
                ->all();
        };

        $grant = function (array $roleIds, array $permissionNames) use ($ids): void {
            foreach ($roleIds as $roleId) {
                foreach ($permissionNames as $permissionName) {
                    $permissionId = $ids[$permissionName] ?? null;
                    if (! $permissionId) {
                        continue;
                    }

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
        };

        $viewRoles = $roleIdsWith(['view-reports', 'print-reports', 'edit-reports']);
        $grant($viewRoles, ['view-signatures']);

        $editRoles = $roleIdsWith(['edit-reports']);
        $grant($editRoles, ['create-signatures', 'edit-signatures', 'delete-signatures']);

        $adminRoleId = DB::table('roles')->where('name', 'admin')->value('id');
        if ($adminRoleId) {
            $grant([$adminRoleId], array_keys(self::PERMISSIONS));
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function down(): void
    {
        $ids = DB::table('permissions')->whereIn('name', array_keys(self::PERMISSIONS))->pluck('id');

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
};
