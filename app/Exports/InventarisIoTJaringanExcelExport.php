<?php

namespace App\Exports;

use App\Models\InventarisIoTJaringan;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class InventarisIoTJaringanExcelExport implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
{
    protected $inventaris;

    public function __construct($inventaris)
    {
        $this->inventaris = $inventaris;
    }

    public function collection()
    {
        return $this->inventaris;
    }

    public function headings(): array
    {
        return [
            'Nama Inventaris',
            'Kategori',
            'Jenis',
            'Lokasi',
            'Status',
            'Catatan',
            'Komponen',
            'Jumlah Komponen',
        ];
    }

    public function map($inventaris): array
    {
        $komponen = $inventaris->items->map(function ($sub) {
            return $sub->komponen->nama_komponen . ' (x' . $sub->jumlah . ')';
        })->filter()->join(', ') ?: '-';

        return [
            $inventaris->nama_inventaris,
            $inventaris->kategori,
            $inventaris->jenis,
            $inventaris->lokasi ?: '-',
            ucfirst($inventaris->status),
            $inventaris->catatan ?: '-',
            $komponen,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 12]],
        ];
    }
}
