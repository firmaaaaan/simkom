<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Spatie\Permission\PermissionRegistrar;

/**
 * Permission untuk halaman Monitoring PC:
 * - view-monitoring   : membuka halaman dashboard monitoring;
 * - control-monitoring: menekan tombol nyalakan (Wake-on-LAN) / matikan PC.
 *
 * Dipisah agar operator boleh memantau tanpa berhak mematikan PC lab.
 * Backfill menjaga perilaku lama: role pemegang view-computers mendapat
 * view-monitoring, role pemegang manage-computers mendapat control-monitoring.
 * Cukup "php artisan migrate" — tidak perlu db:seed.
 */
return new class extends Migration
{
    /** nama permission => [label, permission sumber backfill] */
    private const PERMISSIONS = [
        'view-monitoring' => ['Lihat Monitoring PC', 'view-computers'],
        'control-monitoring' => ['Kendalikan Power PC', 'manage-computers'],
    ];

    public function up(): void
    {
        $now = now();

        foreach (self::PERMISSIONS as $name => [$label, $source]) {
            $id = DB::table('permissions')->where('name', $name)->value('id');
            if (! $id) {
                $id = (string) Str::uuid();
                DB::table('permissions')->insert([
                    'id' => $id,
                    'name' => $name,
                    'label' => $label,
                    'guard_name' => 'web',
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }

            $sourceId = DB::table('permissions')->where('name', $source)->value('id');
            if (! $sourceId) {
                continue;
            }

            $roleIds = DB::table('role_has_permissions')
                ->where('permission_id', $sourceId)
                ->pluck('role_id');

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

        // Migrasi memakai DB mentah: flush cache permission spatie.
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function down(): void
    {
        $ids = DB::table('permissions')->whereIn('name', array_keys(self::PERMISSIONS))->pluck('id');
        DB::table('role_has_permissions')->whereIn('permission_id', $ids)->delete();
        DB::table('permissions')->whereIn('id', $ids)->delete();

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
};
