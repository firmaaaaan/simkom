<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/** Sheet "Langkah Pekerjaan" pada template JSA (rujuk Kode JSA). */
class JsaStepTemplate implements FromArray, WithHeadings, WithStyles, WithTitle
{
    public function title(): string
    {
        return 'Langkah Pekerjaan';
    }

    public function headings(): array
    {
        return [
            'Kode JSA', 'Urutan Langkah Pekerjaan', 'Identifikasi Bahaya', 'Pengendalian', 'Penanggung Jawab',
        ];
    }

    public function array(): array
    {
        return [
            [
                'Contoh',
                'Matikan dan cabut kabel PC dari sumber listrik.',
                'Sengatan listrik.',
                'Pastikan PC mati total sebelum dibongkar.',
                'Andi Pratama',
            ],
            [
                'Contoh',
                'Bersihkan thermal paste lama dengan tisu.',
                'Iritasi kulit.',
                'Gunakan sarung tangan.',
                'Andi Pratama',
            ],
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        $sheet->getStyle('A1:E1')->getFont()->setBold(true);
        $sheet->getColumnDimension('B')->setWidth(40);
        $sheet->getColumnDimension('C')->setWidth(30);
        $sheet->getColumnDimension('D')->setWidth(34);

        return [];
    }
}
