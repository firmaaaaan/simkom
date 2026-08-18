<?php

namespace Database\Seeders;

use App\Models\Hardware;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class HardwareSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $hardware = [
            [
                'kode_hardware' => 'HARD-001',
                'nama_hardware' => 'RAM DDR4 8GB',
                'kategori' => 'RAM',
                'merek' => 'Kingston',
                'spesifikasi' => 'DDR4 3200MHz, 8GB, Non-ECC',
                'jumlah' => 15,
                'lokasi' => 'Rak A1',
                'status' => 'tersedia',
                'catatan' => 'RAM untuk upgrade PC laboratorium',
            ],
            [
                'kode_hardware' => 'HARD-002',
                'nama_hardware' => 'VGA NVIDIA GTX 1660',
                'kategori' => 'VGA',
                'merek' => 'Gigabyte',
                'spesifikasi' => '6GB GDDR5, PCIe 3.0',
                'jumlah' => 5,
                'lokasi' => 'Rak A2',
                'status' => 'tersedia',
                'catatan' => 'VGA untuk rendering dan gaming',
            ],
            [
                'kode_hardware' => 'HARD-003',
                'nama_hardware' => 'SSD 256GB SATA',
                'kategori' => 'SSD',
                'merek' => 'Samsung',
                'spesifikasi' => 'SATA III, 256GB, Read 560MB/s',
                'jumlah' => 20,
                'lokasi' => 'Rak B1',
                'status' => 'tersedia',
                'catatan' => 'SSD untuk OS dan aplikasi',
            ],
            [
                'kode_hardware' => 'HARD-004',
                'nama_hardware' => 'Monitor 24 inch',
                'kategori' => 'Monitor',
                'merek' => 'LG',
                'spesifikasi' => '24MP88HV, IPS, 1920x1080, 60Hz',
                'jumlah' => 10,
                'lokasi' => 'Lab Komputer',
                'status' => 'dipinjam',
                'catatan' => 'Monitor yang sedang dipinjam mahasiswa',
            ],
            [
                'kode_hardware' => 'HARD-005',
                'nama_hardware' => 'Keyboard Mechanical',
                'kategori' => 'Keyboard',
                'merek' => 'Rexus',
                'spesifikasi' => 'Blue Switch, RGB, USB',
                'jumlah' => 25,
                'lokasi' => 'Rak C1',
                'status' => 'tersedia',
                'catatan' => 'Keyboard untuk praktikum programming',
            ],
            [
                'kode_hardware' => 'HARD-006',
                'nama_hardware' => 'Mouse Wireless',
                'kategori' => 'Mouse',
                'merek' => 'Logitech',
                'spesifikasi' => 'M330, Silent, 2.4GHz',
                'jumlah' => 25,
                'lokasi' => 'Rak C1',
                'status' => 'tersedia',
                'catatan' => 'Mouse wireless untuk laboratorium',
            ],
            [
                'kode_hardware' => 'HARD-007',
                'nama_hardware' => 'Motherboard AM4',
                'kategori' => 'Motherboard',
                'merek' => 'ASUS',
                'spesifikasi' => 'A320M-K, AM4, DDR4, M.2',
                'jumlah' => 3,
                'lokasi' => 'Rak A3',
                'status' => 'perbaikan',
                'catatan' => 'Sedang diperbaiki setelah kerusakan',
            ],
            [
                'kode_hardware' => 'HARD-008',
                'nama_hardware' => 'Processor Ryzen 5',
                'kategori' => 'Processor',
                'merek' => 'AMD',
                'spesifikasi' => 'Ryzen 5 3600, 6 Core, 3.6GHz',
                'jumlah' => 8,
                'lokasi' => 'Rak A3',
                'status' => 'tersedia',
                'catatan' => 'Processor untuk upgrade PC',
            ],
            [
                'kode_hardware' => 'HARD-009',
                'nama_hardware' => 'HDD 1TB SATA',
                'kategori' => 'HDD',
                'merek' => 'Seagate',
                'spesifikasi' => 'SATA III, 1TB, 7200RPM',
                'jumlah' => 12,
                'lokasi' => 'Rak B1',
                'status' => 'tersedia',
                'catatan' => 'HDD untuk storage tambahan',
            ],
            [
                'kode_hardware' => 'HARD-010',
                'nama_hardware' => 'RAM DDR4 16GB',
                'kategori' => 'RAM',
                'merek' => 'Corsair',
                'spesifikasi' => 'DDR4 3200MHz, 16GB, Non-ECC',
                'jumlah' => 0,
                'lokasi' => 'Rak A1',
                'status' => 'tidak_aktif',
                'catatan' => 'Stok habis, menunggu pengiriman',
            ],
        ];

        foreach ($hardware as $item) {
            Hardware::create($item);
        }
    }
}
