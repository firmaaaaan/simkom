<?php

namespace Database\Seeders;

use App\Models\Alat;
use App\Models\Laboratory;
use Illuminate\Database\Seeder;

class AlatSeeder extends Seeder
{
    /**
     * Inventaris alat laboratorium per ruangan.
     * Idempotent: baris dikenali lewat kode no. inventaris, jadi aman
     * dijalankan berulang (firstOrCreate).
     *
     * Lab dibuat dulu jika belum ada supaya seeder bisa jalan mandiri:
     *   php artisan db:seed --class=AlatSeeder
     */
    public function run(): void
    {
        $labs = $this->laboratories();

        $alat = [
            // Lab Komputer 1 — ruang praktikum pemrograman dasar
            ['lab' => 'LAB-KOM-01', 'name' => 'Komputer Desktop', 'code' => 'INV.KOM.001', 'merk_type' => 'Lenovo ThinkCentre M70q', 'location' => 'Meja praktikum', 'status' => 'Baik', 'description' => 'PC praktikum peserta didik'],
            ['lab' => 'LAB-KOM-01', 'name' => 'Monitor LED 24"', 'code' => 'INV.KOM.002', 'merk_type' => 'LG 24MK430H', 'location' => 'Meja praktikum', 'status' => 'Baik', 'description' => 'Monitor 24 inch FHD'],
            ['lab' => 'LAB-KOM-01', 'name' => 'Printer Laser', 'code' => 'INV.KOM.003', 'merk_type' => 'HP LaserJet M110w', 'location' => 'Meja asisten', 'status' => 'Baik', 'description' => 'Printer cetak laporan praktikum'],
            ['lab' => 'LAB-KOM-01', 'name' => 'Proyektor', 'code' => 'INV.KOM.004', 'merk_type' => 'Epson EB-X51', 'location' => 'Plafon', 'status' => 'Baik', 'description' => 'Proyektor presentasi 3600 lumen'],
            ['lab' => 'LAB-KOM-01', 'name' => 'Air Conditioner', 'code' => 'INV.KOM.005', 'merk_type' => 'Daikin FTKM25', 'location' => 'Dinding sisi utara', 'status' => 'Baik', 'description' => 'AC 1 PK inverter'],
            ['lab' => 'LAB-KOM-01', 'name' => 'UPS', 'code' => 'INV.KOM.006', 'merk_type' => 'APC Smart-UPS 1500VA', 'location' => 'Meja server', 'status' => 'Baik', 'description' => 'UPS line interactive'],

            // Lab Komputer 2 — ruang praktikum jaringan
            ['lab' => 'LAB-KOM-02', 'name' => 'Komputer Desktop', 'code' => 'INV.KOM.007', 'merk_type' => 'HP ProDesk 400 G7', 'location' => 'Meja praktikum', 'status' => 'Baik', 'description' => 'PC praktikum peserta didik'],
            ['lab' => 'LAB-KOM-02', 'name' => 'Switch Manageable 24 Port', 'code' => 'INV.KOM.008', 'merk_type' => 'Cisco C1000-24T', 'location' => 'Rak jaringan', 'status' => 'Baik', 'description' => 'Switch praktikum konfigurasi'],
            ['lab' => 'LAB-KOM-02', 'name' => 'Router', 'code' => 'INV.KOM.009', 'merk_type' => 'MikroTik RB4011', 'location' => 'Rak jaringan', 'status' => 'Baik', 'description' => 'Router praktikum routing'],
            ['lab' => 'LAB-KOM-02', 'name' => 'Crimping Tool', 'code' => 'INV.KOM.010', 'merk_type' => 'Tajima CR-1005', 'location' => 'Loker alat', 'status' => 'Baik', 'description' => 'Tang crimping RJ45'],
            ['lab' => 'LAB-KOM-02', 'name' => 'Cable Tester', 'code' => 'INV.KOM.011', 'merk_type' => 'Brennenstuhl BT-LAN', 'location' => 'Loker alat', 'status' => 'Baik', 'description' => 'Tester kabel LAN'],
            ['lab' => 'LAB-KOM-02', 'name' => 'Proyektor', 'code' => 'INV.KOM.012', 'merk_type' => 'BenQ MW560', 'location' => 'Plafon', 'status' => 'Baik', 'description' => 'Proyektor presentasi'],

            // Lab Komputer 3 — ruang praktikum basis data
            ['lab' => 'LAB-KOM-03', 'name' => 'Komputer Desktop', 'code' => 'INV.KOM.013', 'merk_type' => 'Dell OptiPlex 3090', 'location' => 'Meja praktikum', 'status' => 'Baik', 'description' => 'PC praktikum peserta didik'],
            ['lab' => 'LAB-KOM-03', 'name' => 'Server Basis Data', 'code' => 'INV.KOM.014', 'merk_type' => 'Supermicro SYS-5019C', 'location' => 'Rak server', 'status' => 'Baik', 'description' => 'Server PostgreSQL praktikum'],
            ['lab' => 'LAB-KOM-03', 'name' => 'Monitor LED 22"', 'code' => 'INV.KOM.015', 'merk_type' => 'Samsung S22R350', 'location' => 'Meja praktikum', 'status' => 'Baik', 'description' => 'Monitor 22 inch FHD'],
            ['lab' => 'LAB-KOM-03', 'name' => 'Air Conditioner', 'code' => 'INV.KOM.016', 'merk_type' => 'Panasonic CS-YN9WKJ', 'location' => 'Dinding sisi selatan', 'status' => 'Baik', 'description' => 'AC 1 PK'],
            ['lab' => 'LAB-KOM-03', 'name' => 'UPS', 'code' => 'INV.KOM.017', 'merk_type' => 'CyberPower UT1500', 'location' => 'Rak server', 'status' => 'Baik', 'description' => 'UPS 1500VA'],

            // Lab Multimedia — ruang desain grafis & editing video
            ['lab' => 'LAB-MUL', 'name' => 'Komputer Desain', 'code' => 'INV.MUL.001', 'merk_type' => 'Apple Mac mini M2', 'location' => 'Meja praktikum', 'status' => 'Baik', 'description' => 'Workstation editing'],
            ['lab' => 'LAB-MUL', 'name' => 'Monitor Color Accurate', 'code' => 'INV.MUL.002', 'merk_type' => 'BenQ SW240', 'location' => 'Meja praktikum', 'status' => 'Baik', 'description' => 'Monitor 24" 100% sRGB'],
            ['lab' => 'LAB-MUL', 'name' => 'Kamera DSLR', 'code' => 'INV.MUL.003', 'merk_type' => 'Canon EOS 90D', 'location' => 'Loker kamera', 'status' => 'Baik', 'description' => 'Kamera praktikum fotografi'],
            ['lab' => 'LAB-MUL', 'name' => 'Lampu Studio', 'code' => 'INV.MUL.004', 'merk_type' => 'Godox SL60W', 'location' => 'Loker kamera', 'status' => 'Baik', 'description' => 'Continuous light 60W'],
            ['lab' => 'LAB-MUL', 'name' => 'Drawing Tablet', 'code' => 'INV.MUL.005', 'merk_type' => 'Wacom Intuos Pro M', 'location' => 'Meja praktikum', 'status' => 'Baik', 'description' => 'Tablet gambar praktikum desain'],
            ['lab' => 'LAB-MUL', 'name' => 'Audio Interface', 'code' => 'INV.MUL.006', 'merk_type' => 'Focusrite Scarlett 2i2', 'location' => 'Meja audio', 'status' => 'Baik', 'description' => 'Interface audio praktikum'],

            // Lab Jaringan — ruang konfigurasi jaringan & server
            ['lab' => 'LAB-NET', 'name' => 'Server Rack 12U', 'code' => 'INV.NET.001', 'merk_type' => 'D-Link DESK-12U', 'location' => 'Sudut ruang', 'status' => 'Baik', 'description' => 'Rak server praktikum'],
            ['lab' => 'LAB-NET', 'name' => 'Access Point', 'code' => 'INV.NET.002', 'merk_type' => 'Ubiquiti U6-Lite', 'location' => 'Plafon', 'status' => 'Baik', 'description' => 'AP Wi-Fi 6 praktikum'],
            ['lab' => 'LAB-NET', 'name' => 'Komputer Admin', 'code' => 'INV.NET.003', 'merk_type' => 'Lenovo ThinkPad E14', 'location' => 'Meja asisten', 'status' => 'Baik', 'description' => 'PC administrator lab'],
            ['lab' => 'LAB-NET', 'name' => 'Fiber Optic Cleaver', 'code' => 'INV.NET.004', 'merk_type' => 'Fujikura CT50', 'location' => 'Loker alat', 'status' => 'Baik', 'description' => 'Alat splicing fiber optik'],
            ['lab' => 'LAB-NET', 'name' => 'Air Conditioner', 'code' => 'INV.NET.005', 'merk_type' => 'Sharp AH-XP50UHY', 'location' => 'Dinding sisi barat', 'status' => 'Perlu Pemeliharaan', 'description' => 'AC 2 PK pendingin ruang server'],

            // Lab Robotika — ruang robotika & sistem kendali
            ['lab' => 'LAB-ROB', 'name' => 'Robot Arm 6 DOF', 'code' => 'INV.ROB.001', 'merk_type' => 'Dobot Magician', 'location' => 'Meja praktikum', 'status' => 'Baik', 'description' => 'Lengan robot praktikum'],
            ['lab' => 'LAB-ROB', 'name' => 'Oscilloscope', 'code' => 'INV.ROB.002', 'merk_type' => 'Rigol DS1054Z', 'location' => 'Meja ukur', 'status' => 'Baik', 'description' => 'Oscilloscope 4 channel 50MHz'],
            ['lab' => 'LAB-ROB', 'name' => 'Multimeter Digital', 'code' => 'INV.ROB.003', 'merk_type' => 'Fluke 87V', 'location' => 'Loker alat', 'status' => 'Baik', 'description' => 'Multimeter autorange'],
            ['lab' => 'LAB-ROB', 'name' => 'Soldering Station', 'code' => 'INV.ROB.004', 'merk_type' => 'Hakko FX-888D', 'location' => 'Meja solder', 'status' => 'Baik', 'description' => 'Stasiun solder suhu terkontrol'],
            ['lab' => 'LAB-ROB', 'name' => 'Power Supply DC', 'code' => 'INV.ROB.005', 'merk_type' => 'Korad KA3005P', 'location' => 'Meja ukur', 'status' => 'Baik', 'description' => 'Power supply 30V 5A'],
            ['lab' => 'LAB-ROB', 'name' => 'Proyektor', 'code' => 'INV.ROB.006', 'merk_type' => 'Acer X118H', 'location' => 'Plafon', 'status' => 'Baik', 'description' => 'Proyektor presentasi'],
        ];

        foreach ($alat as $item) {
            $lab = $labs[$item['lab']] ?? null;
            if (! $lab) {
                continue;
            }

            Alat::firstOrCreate(
                ['code' => $item['code']],
                [
                    'laboratory_id' => $lab->id,
                    'name' => $item['name'],
                    'merk_type' => $item['merk_type'],
                    'location' => $item['location'],
                    'status' => $item['status'],
                    'description' => $item['description'],
                ]
            );
        }
    }

    /**
     * Laboratorium contoh per ruangan (firstOrCreate berdasarkan kode).
     *
     * @return array<string, Laboratory>
     */
    private function laboratories(): array
    {
        $labs = [
            ['name' => 'Lab Komputer 1', 'code' => 'LAB-KOM-01', 'location' => 'Gedung A Lantai 2', 'capacity' => 40, 'status' => 'Aktif', 'description' => 'Laboratorium komputer untuk praktikum pemrograman dasar'],
            ['name' => 'Lab Komputer 2', 'code' => 'LAB-KOM-02', 'location' => 'Gedung A Lantai 2', 'capacity' => 40, 'status' => 'Aktif', 'description' => 'Laboratorium komputer untuk praktikum jaringan'],
            ['name' => 'Lab Komputer 3', 'code' => 'LAB-KOM-03', 'location' => 'Gedung A Lantai 3', 'capacity' => 35, 'status' => 'Aktif', 'description' => 'Laboratorium komputer untuk praktikum basis data'],
            ['name' => 'Lab Multimedia', 'code' => 'LAB-MUL', 'location' => 'Gedung B Lantai 1', 'capacity' => 30, 'status' => 'Aktif', 'description' => 'Laboratorium untuk praktikum desain grafis dan editing video'],
            ['name' => 'Lab Jaringan', 'code' => 'LAB-NET', 'location' => 'Gedung B Lantai 2', 'capacity' => 25, 'status' => 'Maintenance', 'description' => 'Laboratorium untuk praktikum konfigurasi jaringan dan server'],
            ['name' => 'Lab Robotika', 'code' => 'LAB-ROB', 'location' => 'Gedung C Lantai 1', 'capacity' => 20, 'status' => 'Aktif', 'description' => 'Laboratorium untuk praktikum robotika dan sistem kendali'],
        ];

        $result = [];
        foreach ($labs as $lab) {
            $result[$lab['code']] = Laboratory::firstOrCreate(['code' => $lab['code']], $lab);
        }

        return $result;
    }
}
