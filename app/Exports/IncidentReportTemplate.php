<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class IncidentReportTemplate implements FromArray, WithHeadings, WithStyles
{
    public function headings(): array
    {
        return [
            'Kode', 'Tanggal Kejadian', 'Waktu Kejadian', 'Jenis Kejadian',
            'Lokasi', 'Nama Korban', 'Jabatan', 'Asal Institusi',
            'Sebab', 'Riwayat', 'Saksi', 'Akibat',
            'Likelihood (1-5)', 'Impact (1-5)', 'Pelapor',
        ];
    }

    public function array(): array
    {
        return [
            [
                'Contoh', '2026-11-05', '13:45', 'Kecelakaan',
                'Lab Komputer 1', 'Budi Santoso', 'Praktikan', 'Universitas Aisyiyah',
                'Kabel listrik terkelupas', 'Korban menyentuh kabel saat praktik', 'Siti Aminah', 'Luka bakar ringan',
                3, 2, 'Koordinator K3',
            ],
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [1 => ['font' => ['bold' => true]]];
    }
}
