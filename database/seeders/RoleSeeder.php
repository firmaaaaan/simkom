<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /** modul => [label, aksi yang tersedia]. */
    private const MODULES = [
        'users' => ['User', ['view', 'create', 'edit', 'delete']],
        'roles' => ['Role', ['view', 'create', 'edit', 'delete']],
        'laboratories' => ['Laboratorium', ['view', 'create', 'edit', 'delete']],
        'academic-years' => ['Tahun Ajaran', ['view', 'create', 'edit', 'delete']],
        'hardware' => ['Hardware', ['view', 'create', 'edit', 'delete']],
        'software' => ['Software', ['view', 'create', 'edit', 'delete']],
        'components' => ['Komponen', ['view', 'create', 'edit', 'delete']],
        'computers' => ['Komputer', ['view', 'create', 'edit', 'delete']],
        'maintenance' => ['Pemeliharaan', ['view', 'create', 'edit', 'delete']],
        'tickets' => ['Kendala Praktikum', ['view', 'create', 'edit', 'delete']],
        'borrowings' => ['Peminjaman', ['view', 'edit']],
        'lab-usages' => ['Penggunaan Lab', ['view', 'create', 'edit', 'delete']],
        'lab-schedules' => ['Jadwal Lab', ['view', 'create', 'edit', 'delete']],
        'backups' => ['Backup', ['view', 'edit', 'delete']],
        'reports' => ['Laporan', ['view']],
    ];

    private const ACTION_LABELS = [
        'view' => 'Lihat',
        'create' => 'Tambah',
        'edit' => 'Ubah',
        'delete' => 'Hapus',
    ];

    /** Modul yang diberikan ke laboran (sinkron dengan daftar lama). */
    private const LABORAN_MODULES = [
        'hardware', 'software', 'components', 'computers',
        'maintenance', 'tickets', 'borrowings', 'backups', 'reports',
    ];

    public function run(): void
    {
        // Create Permissions (firstOrCreate: aman bila sudah dibuat migration)
        foreach (self::MODULES as $module => [$label, $actions]) {
            foreach ($actions as $action) {
                Permission::firstOrCreate(
                    ['name' => "{$action}-{$module}"],
                    ['label' => self::ACTION_LABELS[$action].' '.$label]
                );
            }
        }

        // Create Roles
        $adminRole = Role::firstOrCreate(['name' => 'admin'], ['label' => 'Admin']);
        $laboranRole = Role::firstOrCreate(['name' => 'laboran'], ['label' => 'Laboran']);

        // Admin gets ALL permissions
        $adminRole->syncPermissions(Permission::all());

        // Laboran permissions
        $laboranNames = [];
        foreach (self::LABORAN_MODULES as $module) {
            foreach (self::MODULES[$module][1] as $action) {
                $laboranNames[] = "{$action}-{$module}";
            }
        }
        $laboranRole->syncPermissions(
            Permission::whereIn('name', $laboranNames)->get()
        );
    }
}
