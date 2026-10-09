<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /** modul => [label, aksi yang tersedia]. */
    private const MODULES = [
        'users' => ['User', ['view', 'create', 'edit', 'delete', 'export']],
        'roles' => ['Role', ['view', 'create', 'edit', 'delete', 'export']],
        'laboratories' => ['Laboratorium', ['view', 'create', 'edit', 'delete', 'export']],
        'academic-years' => ['Tahun Ajaran', ['view', 'create', 'edit', 'delete', 'export']],
        'hardware' => ['Hardware', ['view', 'create', 'edit', 'delete', 'export', 'import']],
        'software' => ['Software', ['view', 'create', 'edit', 'delete', 'export', 'import']],
        'components' => ['Komponen', ['view', 'create', 'edit', 'delete', 'export', 'import', 'print']],
        'computers' => ['Komputer', ['view', 'create', 'edit', 'delete', 'export', 'import', 'print']],
        'maintenance' => ['Pemeliharaan', ['view', 'create', 'edit', 'delete', 'export', 'import', 'print']],
        'tickets' => ['Kendala Praktikum', ['view', 'create', 'edit', 'delete', 'export']],
        'borrowings' => ['Peminjaman', ['view', 'edit', 'export']],
        'lab-usages' => ['Penggunaan Lab', ['view', 'create', 'edit', 'delete', 'export', 'print']],
        'lab-schedules' => ['Jadwal Lab', ['view', 'create', 'edit', 'delete', 'export', 'import']],
        'backups' => ['Backup', ['view', 'edit', 'delete']],
        'reports' => ['Laporan', ['view', 'print']],
    ];

    private const ACTION_LABELS = [
        'view' => 'Lihat',
        'create' => 'Tambah',
        'edit' => 'Ubah',
        'delete' => 'Hapus',
        'export' => 'Export',
        'import' => 'Import',
        'print' => 'Cetak',
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
