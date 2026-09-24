<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class LabUsagePermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permission = Permission::firstOrCreate(
            ['name' => 'manage-lab-usages'],
            ['label' => 'Kelola Penggunaan Lab']
        );

        foreach (['admin', 'laboran'] as $roleName) {
            $role = Role::where('name', $roleName)->first();
            if ($role && !$role->permissions->contains($permission)) {
                $role->permissions()->attach($permission);
            }
        }
    }
}
