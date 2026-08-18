<?php

namespace Database\Seeders;

use App\Models\InventarisIoTJaringan;
use App\Models\InventarisIoTJaringanItem;
use App\Models\KomponenIotJaringan;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class InventarisIoTJaringanSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $komponen = KomponenIotJaringan::all()->keyBy('nama_komponen');

        $inventaris = [
            [
                'nama_inventaris' => 'Switch Cisco Catalyst 2960-01',
                'kategori' => 'Jaringan',
                'jenis' => 'Satuan',
                'lokasi' => 'Rak Jaringan LAB-002',
                'status' => 'tersedia',
                'catatan' => 'Switch untuk praktikum jaringan',
                'items' => [
                    ['komponen' => $komponen['Switch Cisco Catalyst 2960']->id ?? null, 'jumlah' => 1],
                ],
            ],
            [
                'nama_inventaris' => 'Router TP-Link Archer-01',
                'kategori' => 'Jaringan',
                'jenis' => 'Satuan',
                'lokasi' => 'Rak Jaringan LAB-002',
                'status' => 'tersedia',
                'catatan' => 'Router wireless untuk praktikum',
                'items' => [
                    ['komponen' => $komponen['Router TP-Link Archer']->id ?? null, 'jumlah' => 1],
                ],
            ],
            [
                'nama_inventaris' => 'Kit IoT Basic',
                'kategori' => 'IoT',
                'jenis' => 'Paket',
                'lokasi' => 'Rak IoT 1',
                'status' => 'tersedia',
                'catatan' => 'Paket dasar IoT untuk praktikum',
                'items' => [
                    ['komponen' => $komponen['Arduino Uno R3']->id ?? null, 'jumlah' => 5],
                    ['komponen' => $komponen['Sensor DHT11']->id ?? null, 'jumlah' => 10],
                    ['komponen' => $komponen['ESP32 DevKit V1']->id ?? null, 'jumlah' => 5],
                ],
            ],
            [
                'nama_inventaris' => 'Paket Sensor IoT',
                'kategori' => 'IoT',
                'jenis' => 'Box',
                'lokasi' => 'Rak Sensor',
                'status' => 'tersedia',
                'catatan' => 'Koleksi sensor untuk proyek IoT',
                'items' => [
                    ['komponen' => $komponen['Sensor DHT11']->id ?? null, 'jumlah' => 15],
                    ['komponen' => $komponen['Sensor Ultrasonik HC-SR04']->id ?? null, 'jumlah' => 10],
                ],
            ],
            [
                'nama_inventaris' => 'Modul Komunikasi',
                'kategori' => 'IoT',
                'jenis' => 'Paket',
                'lokasi' => 'Rak Komunikasi',
                'status' => 'perbaikan',
                'catatan' => 'Sedang diperbaiki setelah penggunaan',
                'items' => [
                    ['komponen' => $komponen['LoRa Module SX1278']->id ?? null, 'jumlah' => 5],
                    ['komponen' => $komponen['Bluetooth HC-05']->id ?? null, 'jumlah' => 5],
                ],
            ],
        ];

        foreach ($inventaris as $data) {
            $items = $data['items'];
            unset($data['items']);

            $record = InventarisIoTJaringan::create($data);

            foreach ($items as $item) {
                if ($item['komponen']) {
                    InventarisIoTJaringanItem::create([
                        'inventaris_iot_jaringan_id' => $record->id,
                        'komponen_iot_jaringan_id' => $item['komponen'],
                        'jumlah' => $item['jumlah'],
                    ]);
                }
            }
        }
    }
}
