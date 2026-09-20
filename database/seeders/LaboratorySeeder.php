<?php

namespace Database\Seeders;

use App\Models\Laboratory;
use Illuminate\Database\Seeder;

class LaboratorySeeder extends Seeder
{
    public function run(): void
    {
        $labs = [
            [
                'name' => 'Lab Komputer 1',
                'code' => 'LAB-KOM-01',
                'location' => 'Gedung A Lantai 2',
                'capacity' => 40,
                'status' => 'Aktif',
                'description' => 'Laboratorium komputer untuk praktikum pemrograman dasar',
            ],
            [
                'name' => 'Lab Komputer 2',
                'code' => 'LAB-KOM-02',
                'location' => 'Gedung A Lantai 2',
                'capacity' => 40,
                'status' => 'Aktif',
                'description' => 'Laboratorium komputer untuk praktikum jaringan',
            ],
            [
                'name' => 'Lab Komputer 3',
                'code' => 'LAB-KOM-03',
                'location' => 'Gedung A Lantai 3',
                'capacity' => 35,
                'status' => 'Aktif',
                'description' => 'Laboratorium komputer untuk praktikum basis data',
            ],
            [
                'name' => 'Lab Multimedia',
                'code' => 'LAB-MUL',
                'location' => 'Gedung B Lantai 1',
                'capacity' => 30,
                'status' => 'Aktif',
                'description' => 'Laboratorium untuk praktikum desain grafis dan editing video',
            ],
            [
                'name' => 'Lab Jaringan',
                'code' => 'LAB-NET',
                'location' => 'Gedung B Lantai 2',
                'capacity' => 25,
                'status' => 'Maintenance',
                'description' => 'Laboratorium untuk praktikum konfigurasi jaringan dan server',
            ],
            [
                'name' => 'Lab Robotika',
                'code' => 'LAB-ROB',
                'location' => 'Gedung C Lantai 1',
                'capacity' => 20,
                'status' => 'Aktif',
                'description' => 'Laboratorium untuk praktikum robotika dan sistem kendali',
            ],
        ];

        foreach ($labs as $lab) {
            Laboratory::create($lab);
        }
    }
}
