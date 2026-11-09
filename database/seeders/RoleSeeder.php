<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Modul => aksi yang tersedia.
     *
     * Seeder ini TIDAK membuat permission. Daftar permission dibuat oleh
     * migrasi 2026_11_03_000004_seed_module_permissions; di sini hanya
     * menyusun role dan memberikan permission yang sudah ada.
     *
     * Catatan: permission modul K3 (incident-reports, apar-inspections,
     * hse-inspections, accident-reports, jsa) dan jadwal-pemeliharaan
     * TIDAK diatur di sini — migrasi modulnya sendiri yang membuat
     * sekaligus melampirkannya ke role admin/laboran.
     */
    private const MODULES = [
        'users' => ['view', 'create', 'edit', 'delete', 'export'],
        'roles' => ['view', 'create', 'edit', 'delete', 'export'],
        'laboratories' => ['view', 'create', 'edit', 'delete', 'export'],
        'academic-years' => ['view', 'create', 'edit', 'delete', 'export'],
        'hardware' => ['view', 'create', 'edit', 'delete', 'export', 'import'],
        'software' => ['view', 'create', 'edit', 'delete', 'export', 'import'],
        'license-key' => ['view'],
        'components' => ['view', 'create', 'edit', 'delete', 'export', 'import', 'print'],
        'computers' => ['view', 'create', 'edit', 'delete', 'export', 'import', 'print'],
        'maintenance' => ['view', 'create', 'edit', 'delete', 'export', 'import', 'print'],
        'tickets' => ['view', 'create', 'edit', 'delete', 'export'],
        'borrowings' => ['view', 'edit', 'export'],
        'pengadaan' => ['view', 'create', 'edit', 'delete'],
        'incident-reports' => ['view', 'create', 'edit', 'delete'],
        'lab-usages' => ['view', 'create', 'edit', 'delete', 'export', 'print'],
        'backups' => ['view', 'edit', 'delete'],
        'reports' => ['view', 'print'],
        'signatures' => ['view', 'create', 'edit', 'delete'],
        'monitoring' => ['view', 'control'],
        'todos' => ['view', 'create', 'edit', 'delete'],
    ];

    /** Modul yang diberikan ke laboran (sinkron dengan daftar lama). */
    private const LABORAN_MODULES = [
        'hardware', 'software', 'components', 'computers',
        'maintenance', 'tickets', 'borrowings', 'backups', 'reports',
        'monitoring', 'pengadaan', 'incident-reports', 'todos',
    ];

    public function run(): void
    {
        // Create Roles
        $adminRole = Role::firstOrCreate(['name' => 'admin'], ['label' => 'Admin']);
        $laboranRole = Role::firstOrCreate(['name' => 'laboran'], ['label' => 'Laboran']);

        // Admin gets ALL permissions
        $adminRole->syncPermissions(Permission::all());

        // Laboran permissions.
        // Dipasang tanpa detach: permission modul K3 (apar-inspections,
        // hse-inspections, accident-reports, jsa) dan jadwal-pemeliharaan
        // dikelola penuh oleh migrasi masing-masing, jadi tidak boleh lepas saat re-seed.
        $laboranNames = [];
        foreach (self::LABORAN_MODULES as $module) {
            foreach (self::MODULES[$module] as $action) {
                $laboranNames[] = "{$action}-{$module}";
            }
        }
        $laboranRole->permissions()->syncWithoutDetaching(
            Permission::whereIn('name', $laboranNames)->pluck('id')->all()
        );
    }
}
