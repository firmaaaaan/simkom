<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
            // Idempoten: aman dipanggil ulang untuk DB yang sudah ada sebelum
            // permission manage-lab-schedules diperkenalkan.
            LabSchedulePermissionSeeder::class,
        ]);

        $admin = User::factory()->create([
            'name' => 'Admin',
            'email' => 'admin@simkom.com',
            'password' => bcrypt('password'),
        ]);
        $admin->roles()->attach(Role::where('name', 'admin')->firstOrFail());

        $this->call([
            UserSeeder::class,
            // LaboratorySeeder::class,
            // AcademicYearSeeder::class,
            // HardwareSeeder::class,
            // SoftwareSeeder::class,
            // ComponentSeeder::class,
            // BoxSeeder::class,
            // BoxUsageSeeder::class,
            // ComputerSeeder::class,
            // TicketSeeder::class,
            // DeviceCheckSeeder::class,
        ]);
    }
}
