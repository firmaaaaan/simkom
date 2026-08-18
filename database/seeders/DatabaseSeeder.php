<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        $this->call([
            LaboratoriumSeeder::class,
            HardwareSeeder::class,
            SoftwareSeeder::class,
            KomponenIotJaringanSeeder::class,
            KomputerSeeder::class,
            InventarisIoTJaringanSeeder::class,
            KartuKendaliSeeder::class,
            PemeliharaanKomputerSeeder::class,
            PeminjamanInventarisIoTJaringanSeeder::class,
            LaporanKendalaKomputerSeeder::class,
            UserSeeder::class,
        ]);
    }
}
