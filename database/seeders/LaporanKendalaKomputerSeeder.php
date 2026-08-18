<?php

namespace Database\Seeders;

use App\Models\Komputer;
use App\Models\LaporanKendalaKomputer;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class LaporanKendalaKomputerSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $komputers = Komputer::all();

        if ($komputers->isEmpty()) {
            return;
        }

        LaporanKendalaKomputer::create([
            'komputer_id' => $komputers->first()->id,
            'nama_pelapor' => 'Andi Wijaya',
            'npm_nim' => '123456789',
            'nama_prodi' => 'Teknik Informatika',
            'deskripsi_kendala' => 'Layar berkedip saat digunakan',
            'status_kendala' => 'menunggu',
            'kode_tracker' => 'LKD-' . date('Ymd') . '-ABC123',
            'tanggal_lapor' => '2025-03-01',
            'catatan_admin' => null,
        ]);

        LaporanKendalaKomputer::create([
            'komputer_id' => $komputers->skip(1)->first()->id,
            'nama_pelapor' => 'Budi Santoso',
            'npm_nim' => '987654321',
            'nama_prodi' => 'Sistem Informasi',
            'deskripsi_kendala' => 'Keyboard tidak berfungsi',
            'status_kendala' => 'diperbaiki',
            'kode_tracker' => 'LKD-' . date('Ymd') . '-XYZ789',
            'tanggal_lapor' => '2025-03-05',
            'tanggal_perbaikan' => '2025-03-06',
            'catatan_admin' => 'Keyboard sudah diganti',
        ]);
    }
}
