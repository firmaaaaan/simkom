<?php

namespace App\Exports;

use App\Models\Hardware;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class HardwareExport implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    public function collection()
    {
        return Hardware::all();
    }

    public function headings(): array
    {
        return ['Kode', 'Nama', 'Merk', 'Model', 'Kategori', 'Keterangan'];
    }

    public function map($hardware): array
    {
        return [
            $hardware->code,
            $hardware->name,
            $hardware->brand,
            $hardware->model,
            $hardware->category,
            $hardware->description,
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
