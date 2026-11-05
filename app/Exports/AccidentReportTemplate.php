<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class AccidentReportTemplate implements FromArray, WithHeadings, WithStyles
{
    public function headings(): array
    {
        return [
            'Kode', 'Tanggal', 'Nama Pelapor', 'Jabatan Pelapor',
            'Nama Korban', 'Alamat Korban', 'Tempat Tanggal Lahir', 'Unit/Status',
            'Uraian Kejadian 1', 'Uraian Kejadian 2', 'Uraian Kejadian 3',
            'Dampak', 'Saksi I', 'Saksi II',
        ];
    }

    public function array(): array
    {
        return [[
            'Contoh', '2026-11-05', 'Koordinator K3', 'Koordinator K3 Laboratorium',
            'Budi Santoso', 'Jl. Kaliurang KM 5, Sleman', 'Yogyakarta, 12 Mei 2005', 'Praktikan',
            'Praktikan tersengat listrik saat mengganti komponen.', 'Komputer mati mendadak.', '',
            'Luka bakar ringan pada tangan kanan.', 'Siti Aminah', 'Ahmad Fauzi',
        ]];
    }

    public function styles(Worksheet $sheet): array
    {
        return [1 => ['font' => ['bold' => true]]];
    }
}
