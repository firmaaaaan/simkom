<?php

namespace Database\Seeders;

use App\Models\Komputer;
use App\Models\PemeliharaanKomputer;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PemeliharaanKomputerSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $komputers = Komputer::all();

        if ($komputers->isEmpty()) {
            return;
        }

        $pemeliharaan = [
            [
                'komputer_id' => $komputers->first()->id,
                'tahun_ajaran' => '2024/2025',
                'tanggal_pemeliharaan' => '2024-08-15',
                'jenis_pemeliharaan' => 'preventif',
                'deskripsi' => 'Pembersihan debu, pengecekan kipas, dan penggantian pasta termal',
                'biaya' => 150000,
                'pic' => 'Teknisi Lab',
            ],
            [
                'komputer_id' => $komputers->first()->id,
                'tahun_ajaran' => '2025/2026',
                'tanggal_pemeliharaan' => '2025-08-20',
                'jenis_pemeliharaan' => 'korektif',
                'deskripsi' => 'Penggantian RAM yang error dan reinstalasi sistem operasi',
                'biaya' => 750000,
                'pic' => 'Teknisi Lab',
            ],
            [
                'komputer_id' => $komputers->skip(1)->first()->id,
                'tahun_ajaran' => '2024/2025',
                'tanggal_pemeliharaan' => '2024-09-10',
                'jenis_pemeliharaan' => 'preventif',
                'deskripsi' => 'Pembersihan umum dan cek konektor',
                'biaya' => 100000,
                'pic' => 'Teknisi Lab',
            ],
            [
                'komputer_id' => $komputers->skip(1)->first()->id,
                'tahun_ajaran' => '2025/2026',
                'tanggal_pemeliharaan' => '2025-09-05',
                'jenis_pemeliharaan' => 'upgrade',
                'deskripsi' => 'Upgrade SSD dari 256GB ke 512GB',
                'biaya' => 650000,
                'pic' => 'Teknisi Lab',
            ],
        ];

        foreach ($pemeliharaan as $item) {
            PemeliharaanKomputer::create($item);
        }
    }
}
