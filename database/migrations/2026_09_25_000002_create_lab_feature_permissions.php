<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Permission menu fitur lab dibuat lewat migration (bukan seeder) agar
     * cukup "git pull && php artisan migrate" di server — tidak perlu
     * menjalankan db:seed manual hanya untuk menampilkan menu sidebar.
     */
    private const PERMISSIONS = [
        'manage-lab-usages' => [
            'label' => 'Kelola Penggunaan Lab',
            'roles' => ['admin', 'laboran'],
        ],
        'manage-lab-schedules' => [
            'label' => 'Kelola Jadwal Lab',
            'roles' => ['admin'],
        ],
        'manage-lab-layouts' => [
            'label' => 'Kelola Denah Lab',
            'roles' => ['admin'],
        ],
    ];

    public function up(): void
    {
        $now = now();

        foreach (self::PERMISSIONS as $name => $config) {
            $permission = DB::table('permissions')->where('name', $name)->first();

            if ($permission) {
                $permissionId = $permission->id;
            } else {
                $permissionId = (string) Str::uuid();
                DB::table('permissions')->insert([
                    'id' => $permissionId,
                    'name' => $name,
                    'label' => $config['label'],
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }

            foreach ($config['roles'] as $roleName) {
                $role = DB::table('roles')->where('name', $roleName)->first();
                if (! $role) {
                    continue;
                }

                $attached = DB::table('role_permission')
                    ->where('role_id', $role->id)
                    ->where('permission_id', $permissionId)
                    ->exists();

                if (! $attached) {
                    DB::table('role_permission')->insert([
                        'role_id' => $role->id,
                        'permission_id' => $permissionId,
                    ]);
                }
            }
        }
    }

    public function down(): void
    {
        // No-op: permission yang sudah ada sebelum migration ini (hasil seeder
        // lama) tidak dihapus saat rollback agar role yang sedang berjalan tidak
        // kehilangan akses secara tiba-tiba.
    }
};
