<?php

namespace Database\Seeders;

use App\Models\InventarisIoTJaringan;
use App\Models\PeminjamanInventarisIoTJaringan;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PeminjamanInventarisIoTJaringanSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $inventaris = InventarisIoTJaringan::all();

        if ($inventaris->isEmpty()) {
            return;
        }

        PeminjamanInventarisIoTJaringan::create([
            'inventaris_iot_jaringan_id' => $inventaris->first()->id,
            'nama_peminjam' => 'Andi Wijaya',
            'npm_nim' => '123456789',
            'tanggal_pinjam' => '2025-03-01',
            'tanggal_kembali_direncanakan' => '2025-03-08',
            'tanggal_kembali_aktual' => '2025-03-07',
            'status' => 'dikembalikan',
            'catatan' => 'Dikembalikan dalam kondisi baik',
        ]);

        PeminjamanInventarisIoTJaringan::create([
            'inventaris_iot_jaringan_id' => $inventaris->skip(1)->first()->id,
            'nama_peminjam' => 'Budi Santoso',
            'npm_nim' => '987654321',
            'tanggal_pinjam' => '2025-03-10',
            'tanggal_kembali_direncanakan' => '2025-03-17',
            'tanggal_kembali_aktual' => null,
            'status' => 'dipinjam',
            'catatan' => 'Untuk proyek akhir semester',
        ]);
    }
}
