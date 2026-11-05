<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/** Sheet "Laporan" pada template laporan kerusakan/kehilangan/pencurian. */
class DamageSheetTemplate implements FromArray, WithHeadings, WithStyles, WithTitle
{
    public function title(): string
    {
        return 'Laporan';
    }

    public function headings(): array
    {
        return [
            'Kode', 'Nama Pelapor', 'Ruangan', 'Telepon',
            'Tanggal Insiden', 'Waktu Kejadian', 'Tanggal Dilaporkan', 'Waktu Dilaporkan',
            'Lokasi Spesifik', 'Jenis Kejadian', 'Deskripsi Kejadian',
            'Perkiraan Biaya', 'Penanggung Jawab', 'Dilaporkan ke Polisi',
            'Berkas Polisi', 'Petugas Polisi', 'Unit Kepolisian', 'Telepon Polisi',
        ];
    }

    public function array(): array
    {
        return [[
            'Contoh', 'Siti Aminah', 'Lab Komputer 2', '081234567890',
            '2026-11-05', '14:30', '2026-11-05', '16:00',
            'Ruang server belakang', 'Kerusakan', 'Monitor PC 5 pecah terkena benturan.',
            'Rp 1.500.000', 'Andi Pratama', 'Tidak',
            null, null, null, null,
        ]];
    }

    public function styles(Worksheet $sheet): array
    {
        $sheet->getStyle('A1:R1')->getFont()->setBold(true);
        $sheet->getColumnDimension('J')->setWidth(16);
        $sheet->getColumnDimension('K')->setWidth(42);

        return [];
    }
}
