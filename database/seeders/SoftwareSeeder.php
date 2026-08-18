<?php

namespace Database\Seeders;

use App\Models\Software;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SoftwareSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $software = [
            [
                'kode_software' => 'SOFT-001',
                'nama_software' => 'Windows 11 Pro',
                'kategori' => 'Operating System',
                'versi' => '23H2',
                'lisensi' => 'berbayar',
                'tanggal_instalasi' => '2024-01-15',
                'tanggal_expired' => '2025-01-15',
                'status' => 'aktif',
                'catatan' => 'OS utama Lab Komputer',
            ],
            [
                'kode_software' => 'SOFT-002',
                'nama_software' => 'Microsoft Office 365',
                'kategori' => 'Office',
                'versi' => '2024',
                'lisensi' => 'berbayar',
                'tanggal_instalasi' => '2024-01-15',
                'tanggal_expired' => '2025-01-15',
                'status' => 'aktif',
                'catatan' => 'Paket Office untuk seluruh PC lab',
            ],
            [
                'kode_software' => 'SOFT-003',
                'nama_software' => 'Visual Studio Code',
                'kategori' => 'Programming',
                'versi' => '1.85',
                'lisensi' => 'gratis',
                'tanggal_instalasi' => '2024-02-01',
                'tanggal_expired' => null,
                'status' => 'aktif',
                'catatan' => 'IDE untuk praktikum programming',
            ],
            [
                'kode_software' => 'SOFT-004',
                'nama_software' => 'Cisco Packet Tracer',
                'kategori' => 'Programming',
                'versi' => '8.2',
                'lisensi' => 'gratis',
                'tanggal_instalasi' => '2024-02-10',
                'tanggal_expired' => null,
                'status' => 'aktif',
                'catatan' => 'Simulator jaringan untuk praktikum',
            ],
            [
                'kode_software' => 'SOFT-005',
                'nama_software' => 'Adobe Photoshop',
                'kategori' => 'Design',
                'versi' => '2024',
                'lisensi' => 'berbayar',
                'tanggal_instalasi' => '2024-01-20',
                'tanggal_expired' => '2025-01-20',
                'status' => 'aktif',
                'catatan' => 'Software desain grafis',
            ],
            [
                'kode_software' => 'SOFT-006',
                'nama_software' => 'MySQL Workbench',
                'kategori' => 'Database',
                'versi' => '8.0',
                'lisensi' => 'gratis',
                'tanggal_instalasi' => '2024-02-05',
                'tanggal_expired' => null,
                'status' => 'aktif',
                'catatan' => 'Tools untuk praktikum database',
            ],
            [
                'kode_software' => 'SOFT-007',
                'nama_software' => 'Windows Defender',
                'kategori' => 'Antivirus',
                'versi' => '4.18',
                'lisensi' => 'gratis',
                'tanggal_instalasi' => '2024-01-01',
                'tanggal_expired' => null,
                'status' => 'aktif',
                'catatan' => 'Antivirus bawaan Windows',
            ],
            [
                'kode_software' => 'SOFT-008',
                'nama_software' => 'MATLAB',
                'kategori' => 'Programming',
                'versi' => 'R2024a',
                'lisensi' => 'edukasi',
                'tanggal_instalasi' => '2024-03-01',
                'tanggal_expired' => '2025-03-01',
                'status' => 'aktif',
                'catatan' => 'Lisensi edukasi untuk fisika',
            ],
            [
                'kode_software' => 'SOFT-009',
                'nama_software' => 'LabVIEW',
                'kategori' => 'Programming',
                'versi' => '2023',
                'lisensi' => 'trial',
                'tanggal_instalasi' => '2024-04-01',
                'tanggal_expired' => '2024-10-01',
                'status' => 'trial',
                'catatan' => 'Trial version untuk eksperimen',
            ],
            [
                'kode_software' => 'SOFT-010',
                'nama_software' => 'PyCharm Community',
                'kategori' => 'Programming',
                'versi' => '2024.1',
                'lisensi' => 'open_source',
                'tanggal_instalasi' => '2024-02-15',
                'tanggal_expired' => null,
                'status' => 'aktif',
                'catatan' => 'IDE Python untuk praktikum AI',
            ],
        ];

        foreach ($software as $item) {
            Software::create($item);
        }
    }
}
