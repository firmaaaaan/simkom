<?php

namespace Database\Seeders;

use App\Models\Laboratorium;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class LaboratoriumSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $laboratorium = [
            [
                'kode_laboratorium' => 'LAB-001',
                'nama_laboratorium' => 'Laboratorium Komputer',
                'gedung' => 'Gedung A',
                'ruangan' => 'Lantai 2',
                'kapasitas' => 40,
                'fasilitas' => 'Proyektor, AC, Whiteboard, 40 Komputer, Printer',
                'status' => 'aktif',
                'catatan' => 'Lab untuk praktikum pemrograman dan multimedia',
            ],
            [
                'kode_laboratorium' => 'LAB-002',
                'nama_laboratorium' => 'Laboratorium Jaringan',
                'gedung' => 'Gedung B',
                'ruangan' => 'Lantai 1',
                'kapasitas' => 30,
                'fasilitas' => 'Switch, Router, Kabel UTP, AC, Whiteboard',
                'status' => 'aktif',
                'catatan' => 'Lab untuk praktikum jaringan komputer dan telekomunikasi',
            ],
            [
                'kode_laboratorium' => 'LAB-003',
                'nama_laboratorium' => 'Laboratorium Elektronika',
                'gedung' => 'Gedung C',
                'ruangan' => 'Lantai 3',
                'kapasitas' => 25,
                'fasilitas' => 'Oscilloscope, Multimeter, Solder, Power Supply, AC',
                'status' => 'aktif',
                'catatan' => 'Lab untuk praktikum elektronika dan microcontroller',
            ],
            [
                'kode_laboratorium' => 'LAB-004',
                'nama_laboratorium' => 'Laboratorium Bahasa',
                'gedung' => 'Gedung A',
                'ruangan' => 'Lantai 1',
                'kapasitas' => 35,
                'fasilitas' => 'Headset, Proyektor, AC, Whiteboard, TV',
                'status' => 'aktif',
                'catatan' => 'Lab untuk praktikum bahasa asing dan listening',
            ],
            [
                'kode_laboratorium' => 'LAB-005',
                'nama_laboratorium' => 'Laboratorium Robotika',
                'gedung' => 'Gedung D',
                'ruangan' => 'Lantai 2',
                'kapasitas' => 20,
                'fasilitas' => 'Robot Kit, Arduino, Sensor, AC, Meja Kerja',
                'status' => 'non-aktif',
                'catatan' => 'Lab dalam tahap perbaikan peralatan',
            ],
            [
                'kode_laboratorium' => 'LAB-006',
                'nama_laboratorium' => 'Laboratorium Database',
                'gedung' => 'Gedung B',
                'ruangan' => 'Lantai 2',
                'kapasitas' => 40,
                'fasilitas' => '40 Komputer, Server, Proyektor, AC, Whiteboard',
                'status' => 'aktif',
                'catatan' => 'Lab untuk praktikum database dan big data',
            ],
            [
                'kode_laboratorium' => 'LAB-007',
                'nama_laboratorium' => 'Laboratorium Kecerdasan Buatan',
                'gedung' => 'Gedung C',
                'ruangan' => 'Lantai 1',
                'kapasitas' => 30,
                'fasilitas' => 'GPU Server, 30 Komputer, Proyektor, AC, Whiteboard',
                'status' => 'aktif',
                'catatan' => 'Lab untuk praktikum machine learning dan deep learning',
            ],
            [
                'kode_laboratorium' => 'LAB-008',
                'nama_laboratorium' => 'Laboratorium Desain Grafis',
                'gedung' => 'Gedung A',
                'ruangan' => 'Lantai 3',
                'kapasitas' => 35,
                'fasilitas' => 'Wacom, 35 Komputer, Proyektor, AC, Printer',
                'status' => 'aktif',
                'catatan' => 'Lab untuk praktikum desain grafis dan animasi',
            ],
            [
                'kode_laboratorium' => 'LAB-009',
                'nama_laboratorium' => 'Laboratorium Fisika',
                'gedung' => 'Gedung D',
                'ruangan' => 'Lantai 1',
                'kapasitas' => 30,
                'fasilitas' => 'Alat eksperimen fisika, Proyektor, AC, Whiteboard',
                'status' => 'aktif',
                'catatan' => 'Lab untuk praktikum fisika dasar dan modern',
            ],
            [
                'kode_laboratorium' => 'LAB-010',
                'nama_laboratorium' => 'Laboratorium Kimia',
                'gedung' => 'Gedung D',
                'ruangan' => 'Lantai 3',
                'kapasitas' => 25,
                'fasilitas' => 'Labu, Tabung reaksi, Microscope, AC, Safety Gear',
                'status' => 'non-aktif',
                'catatan' => 'Lab sedang dilengkapi peralatan baru',
            ],
        ];

        foreach ($laboratorium as $item) {
            Laboratorium::create($item);
        }
    }
}
