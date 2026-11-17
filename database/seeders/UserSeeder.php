<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            [
                'name' => 'Firmansyah, S.Kom',
                'email' => 'firmansyah@smartlabs.com',
                'password' => bcrypt('password'),
                'role' => 'admin',
            ],
            [
                'name' => 'Dewi Lestari, S.T.',
                'email' => 'dewi@smartlabs.com',
                'password' => bcrypt('password'),
                'role' => 'laboran',
            ],
            [
                'name' => 'Rudi Hartono',
                'email' => 'rudi@smartlabs.com',
                'password' => bcrypt('password'),
                'role' => 'laboran',
            ],
            [
                'name' => 'Siti Nurhaliza',
                'email' => 'siti@smartlabs.com',
                'password' => bcrypt('password'),
                'role' => 'laboran',
            ],
            [
                'name' => 'Andi Pratama',
                'email' => 'andi@smartlabs.com',
                'password' => bcrypt('password'),
                'role' => 'laboran',
            ],
        ];

        foreach ($users as $userData) {
            $role = $userData['role'];
            unset($userData['role']);

            $user = User::create($userData);
            $user->roles()->attach(Role::where('name', $role)->firstOrFail());
        }
    }
}
