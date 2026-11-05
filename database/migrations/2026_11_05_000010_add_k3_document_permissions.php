<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Spatie\Permission\PermissionRegistrar;

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
            $name = "{$action}-k3-documents";

            $id = DB::table('permissions')->where('name', $name)->value('id');
            if (! $id) {
                $id = (string) Str::uuid();
                DB::table('permissions')->insert([
                    'id' => $id,
                    'name' => $name,
                    'label' => $labelWord.' Dokumen K3 Laboratorium',
                    'guard_name' => 'web',
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }

            $permissionIds[$action] = $id;
        }

        $sourceIds = DB::table('permissions')
            ->where(function ($query) {
                $query->whereIn('name', [
                    'view-incident-reports', 'view-apar-inspections', 'view-hse-inspections',
                    'view-k3-inspection-reports', 'view-accident-reports', 'view-jsa',
                ]);
            })
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
        $names = array_map(fn ($action) => "{$action}-k3-documents", array_keys(self::ACTIONS));

        $ids = DB::table('permissions')->whereIn('name', $names)->pluck('id');
        DB::table('role_has_permissions')->whereIn('permission_id', $ids)->delete();
        DB::table('permissions')->whereIn('name', $names)->delete();

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
};
