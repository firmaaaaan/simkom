<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        // Create Permissions
        $permissions = [
            ['name' => 'manage-users',          'label' => 'Kelola User'],
            ['name' => 'manage-roles',          'label' => 'Kelola Role'],
            ['name' => 'manage-laboratories',   'label' => 'Kelola Laboratorium'],
            ['name' => 'manage-academic-years', 'label' => 'Kelola Tahun Ajaran'],
            ['name' => 'manage-hardware',       'label' => 'Kelola Hardware'],
            ['name' => 'manage-software',       'label' => 'Kelola Software'],
            ['name' => 'manage-components',     'label' => 'Kelola Komponen'],
            ['name' => 'manage-computers',      'label' => 'Kelola Komputer'],
            ['name' => 'manage-maintenance',    'label' => 'Kelola Pemeliharaan'],
            ['name' => 'manage-tickets',        'label' => 'Kelola Kendala Praktikum'],
            ['name' => 'manage-borrowings',     'label' => 'Kelola Peminjaman'],
            ['name' => 'manage-lab-schedules',  'label' => 'Kelola Jadwal Lab'],
            ['name' => 'view-reports',          'label' => 'Lihat Laporan'],
        ];

        foreach ($permissions as $perm) {
            Permission::create($perm);
        }

        // Create Roles
        $adminRole = Role::create(['name' => 'admin', 'label' => 'Admin']);
        $laboranRole = Role::create(['name' => 'laboran', 'label' => 'Laboran']);

        // Admin gets ALL permissions
        $adminRole->permissions()->attach(Permission::all());

        // Laboran permissions
        $laboranPermissions = [
            'manage-hardware',
            'manage-software',
            'manage-components',
            'manage-computers',
            'manage-maintenance',
            'manage-tickets',
            'manage-borrowings',
            'view-reports',
        ];
        $laboranRole->permissions()->attach(
            Permission::whereIn('name', $laboranPermissions)->get()
        );
    }
}
