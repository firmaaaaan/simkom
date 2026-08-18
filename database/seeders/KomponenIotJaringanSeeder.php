<?php

namespace Database\Seeders;

use App\Models\KomponenIotJaringan;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class KomponenIotJaringanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $komponen = [
            [
                'kode_komponen' => 'IOT-001',
                'nama_komponen' => 'Arduino Uno R3',
                'kategori' => 'Mikrokontroler',
                'merek' => 'Arduino',
                'spesifikasi' => 'ATmega328P, 16MHz, 32KB Flash, 14 Digital I/O',
                'jumlah' => 15,
                'lokasi' => 'Rak IoT 1',
                'status' => 'tersedia',
                'catatan' => 'Mikrokontroler utama untuk praktikum IoT',
            ],
            [
                'kode_komponen' => 'IOT-002',
                'nama_komponen' => 'ESP32 DevKit V1',
                'kategori' => 'IoT',
                'merek' => 'Espressif',
                'spesifikasi' => 'Dual-core, WiFi + Bluetooth, 240MHz',
                'jumlah' => 20,
                'lokasi' => 'Rak IoT 1',
                'status' => 'tersedia',
                'catatan' => 'Modul IoT dengan built-in WiFi dan Bluetooth',
            ],
            [
                'kode_komponen' => 'IOT-003',
                'nama_komponen' => 'Raspberry Pi 4 Model B',
                'kategori' => 'IoT',
                'merek' => 'Raspberry Pi',
                'spesifikasi' => '4GB RAM, Quad-core 64-bit, Gigabit Ethernet',
                'jumlah' => 8,
                'lokasi' => 'Rak IoT 2',
                'status' => 'tersedia',
                'catatan' => 'Single-board computer untuk edge computing',
            ],
            [
                'kode_komponen' => 'IOT-004',
                'nama_komponen' => 'Sensor DHT11',
                'kategori' => 'Sensor',
                'merek' => 'Generic',
                'spesifikasi' => 'Suhu & Kelembaban, Range 0-50°C, 20-90% RH',
                'jumlah' => 30,
                'lokasi' => 'Rak Sensor',
                'status' => 'tersedia',
                'catatan' => 'Sensor temperatur dan kelembaban dasar',
            ],
            [
                'kode_komponen' => 'IOT-005',
                'nama_komponen' => 'Sensor Ultrasonik HC-SR04',
                'kategori' => 'Sensor',
                'merek' => 'Generic',
                'spesifikasi' => 'Jarak 2cm - 400cm, Akurasi 3mm',
                'jumlah' => 25,
                'lokasi' => 'Rak Sensor',
                'status' => 'tersedia',
                'catatan' => 'Sensor jarak ultrasonik',
            ],
            [
                'kode_komponen' => 'IOT-006',
                'nama_komponen' => 'Switch Cisco Catalyst 2960',
                'kategori' => 'Jaringan',
                'merek' => 'Cisco',
                'spesifikasi' => '24 Port Fast Ethernet, 2 Gigabit Uplink',
                'jumlah' => 4,
                'lokasi' => 'Rak Jaringan',
                'status' => 'tersedia',
                'catatan' => 'Switch untuk praktikum jaringan komputer',
            ],
            [
                'kode_komponen' => 'IOT-007',
                'nama_komponen' => 'Router TP-Link Archer',
                'kategori' => 'Jaringan',
                'merek' => 'TP-Link',
                'spesifikasi' => 'AC1200, Dual-Band, 4 Port LAN',
                'jumlah' => 6,
                'lokasi' => 'Rak Jaringan',
                'status' => 'tersedia',
                'catatan' => 'Router wireless untuk praktikum',
            ],
            [
                'kode_komponen' => 'IOT-008',
                'nama_komponen' => 'ESP8266 NodeMCU',
                'kategori' => 'IoT',
                'merek' => 'NodeMCU',
                'spesifikasi' => 'WiFi 802.11 b/g/n, 80MHz, 4MB Flash',
                'jumlah' => 18,
                'lokasi' => 'Rak IoT 1',
                'status' => 'dipinjam',
                'catatan' => 'Modul WiFi untuk proyek mahasiswa',
            ],
            [
                'kode_komponen' => 'IOT-009',
                'nama_komponen' => 'LoRa Module SX1278',
                'kategori' => 'Komunikasi',
                'merek' => 'Semtech',
                'spesifikasi' => '433MHz, Long Range, Low Power',
                'jumlah' => 10,
                'lokasi' => 'Rak Komunikasi',
                'status' => 'tersedia',
                'catatan' => 'Modul komunikasi long range',
            ],
            [
                'kode_komponen' => 'IOT-010',
                'nama_komponen' => 'Bluetooth HC-05',
                'kategori' => 'Komunikasi',
                'merek' => 'HC-05',
                'spesifikasi' => 'Bluetooth 2.0, UART Interface, Range 10m',
                'jumlah' => 12,
                'lokasi' => 'Rak Komunikasi',
                'status' => 'perbaikan',
                'catatan' => 'Sedang diperbaiki setelah kerusakan pin',
            ],
        ];

        foreach ($komponen as $item) {
            KomponenIotJaringan::create($item);
        }
    }
}
