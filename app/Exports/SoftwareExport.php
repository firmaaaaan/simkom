<?php

namespace App\Exports;

use App\Models\Software;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class SoftwareExport implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    public function collection()
    {
        return Software::all();
    }

    public function headings(): array
    {
        return ['Kode', 'Nama', 'Versi', 'Kategori', 'Jenis Lisensi', 'Status', 'Keterangan'];
    }

    public function map($software): array
    {
        return [
            $software->code,
            $software->name,
            $software->version,
            $software->category,
            $software->license_type,
            $software->status,
            $software->description,
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
