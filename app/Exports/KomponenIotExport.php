<?php

namespace App\Exports;

use App\Models\KomponenIot;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class KomponenIotExport implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
{
    protected $komponen;

    public function __construct($komponen)
    {
        $this->komponen = $komponen;
    }

    public function collection()
    {
        return $this->komponen;
    }

    public function headings(): array
    {
        return [
            'Nama Komponen',
            'Merek',
            'Spesifikasi',
            'Jumlah',
            'Lokasi',
            'Status',
            'Catatan',
        ];
    }

    public function map($komponen): array
    {
        return [
            $komponen->nama_komponen,
            $komponen->merek ?: '-',
            $komponen->spesifikasi ?: '-',
            $komponen->jumlah,
            $komponen->lokasi ?: '-',
            ucfirst($komponen->status),
            $komponen->catatan ?: '-',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 12]],
        ];
    }
}
