<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class LabSchedulePermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permission = Permission::firstOrCreate(
            ['name' => 'manage-lab-schedules'],
            ['label' => 'Kelola Jadwal Lab']
        );

        $adminRole = Role::where('name', 'admin')->first();
        if ($adminRole && !$adminRole->permissions->contains($permission)) {
            $adminRole->permissions()->attach($permission);
        }
    }
}
