<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Buat role superadmin + pastikan seluruh permission tersedia agar admin
 * bisa memberi hak akses manual lewat form /roles. Permission yang butuh
 * akses segera (manage-backups) langsung disematkan ke role yang sudah ada.
 *
 * Cukup "git pull && php artisan migrate" — tidak perlu db:seed.
 */
return new class extends Migration
{
    private const PERMISSIONS = [
        'manage-users' => 'Kelola User',
        'manage-roles' => 'Kelola Role',
        'manage-laboratories' => 'Kelola Laboratorium',
        'manage-academic-years' => 'Kelola Tahun Ajaran',
        'manage-hardware' => 'Kelola Hardware',
        'manage-software' => 'Kelola Software',
        'manage-components' => 'Kelola Komponen',
        'manage-computers' => 'Kelola Komputer',
        'manage-maintenance' => 'Kelola Pemeliharaan',
        'manage-tickets' => 'Kelola Kendala Praktikum',
        'manage-borrowings' => 'Kelola Peminjaman',
        'manage-lab-schedules' => 'Kelola Jadwal Lab',
        'view-reports' => 'Lihat Laporan',
        'manage-lab-usages' => 'Kelola Penggunaan Lab',
        'manage-backups' => 'Kelola Backup',
    ];

    /** Permission yang disematkan ke role yang sudah ada (bukan superadmin). */
    private const GRANTED_ON_CREATE = [
        'manage-backups' => ['admin', 'laboran'],
    ];

    public function up(): void
    {
        $now = now();

        // Role superadmin: sengaja tanpa permission — admin mengisi lewat form /roles.
        if (! DB::table('roles')->where('name', 'superadmin')->exists()) {
            DB::table('roles')->insert([
                'id' => (string) Str::uuid(),
                'name' => 'superadmin',
                'label' => 'Super Admin',
                'guard_name' => 'web',
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        foreach (self::PERMISSIONS as $name => $label) {
            if (DB::table('permissions')->where('name', $name)->exists()) {
                continue;
            }

            DB::table('permissions')->insert([
                'id' => (string) Str::uuid(),
                'name' => $name,
                'label' => $label,
                'guard_name' => 'web',
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        foreach (self::GRANTED_ON_CREATE as $permissionName => $roleNames) {
            $permission = DB::table('permissions')->where('name', $permissionName)->first();
            if (! $permission) {
                continue;
            }

            foreach ($roleNames as $roleName) {
                $role = DB::table('roles')->where('name', $roleName)->first();
                if (! $role) {
                    continue;
                }

                $attached = DB::table('role_has_permissions')
                    ->where('role_id', $role->id)
                    ->where('permission_id', $permission->id)
                    ->exists();

                if (! $attached) {
                    DB::table('role_has_permissions')->insert([
                        'role_id' => $role->id,
                        'permission_id' => $permission->id,
                    ]);
                }
            }
        }

        // Migrasi memakai DB mentah: flush cache permission spatie.
        app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function down(): void
    {
        // No-op: role/permission yang sudah dipakai tidak dicabut saat rollback.
    }
};
