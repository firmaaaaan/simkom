<?php

namespace Database\Seeders;

use App\Models\InventarisIoTJaringan;
use App\Models\KartuKendali;
use App\Models\Komputer;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class KartuKendaliSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $komputers = Komputer::all();
        $inventaris = InventarisIoTJaringan::all();

        if ($komputers->isNotEmpty()) {
            KartuKendali::create([
                'inspectable_type' => Komputer::class,
                'inspectable_id' => $komputers->first()->id,
                'tanggal_pemeriksaan' => '2025-01-15',
                'pemeriksa' => 'Teknisi Lab',
                'kondisi_keseluruhan' => 'baik',
                'catatan' => 'Semua komponen berfungsi dengan baik',
                'items' => [
                    ['label' => 'Processor', 'kondisi' => 'baik'],
                    ['label' => 'RAM', 'kondisi' => 'baik'],
                    ['label' => 'Storage', 'kondisi' => 'cukup'],
                    ['label' => 'Monitor', 'kondisi' => 'baik'],
                ],
            ]);

            KartuKendali::create([
                'inspectable_type' => Komputer::class,
                'inspectable_id' => $komputers->skip(1)->first()->id,
                'tanggal_pemeriksaan' => '2025-01-20',
                'pemeriksa' => 'Teknisi Lab',
                'kondisi_keseluruhan' => 'cukup',
                'catatan' => 'Perlu pengecekan ulang pada VGA',
                'items' => [
                    ['label' => 'Processor', 'kondisi' => 'baik'],
                    ['label' => 'RAM', 'kondisi' => 'baik'],
                    ['label' => 'VGA', 'kondisi' => 'cukup'],
                    ['label' => 'Storage', 'kondisi' => 'baik'],
                ],
            ]);
        }

        if ($inventaris->isNotEmpty()) {
            KartuKendali::create([
                'inspectable_type' => InventarisIoTJaringan::class,
                'inspectable_id' => $inventaris->first()->id,
                'tanggal_pemeriksaan' => '2025-02-10',
                'pemeriksa' => 'Teknisi Lab',
                'kondisi_keseluruhan' => 'baik',
                'catatan' => 'Pemeriksaan rutin perangkat jaringan',
                'items' => [
                    ['label' => 'Port fisik', 'kondisi' => 'baik'],
                    ['label' => 'Kabel', 'kondisi' => 'baik'],
                    ['label' => 'Konfigurasi', 'kondisi' => 'baik'],
                ],
            ]);
        }
    }
}
