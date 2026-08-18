<?php

namespace App\Exports;

use App\Models\Hardware;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class HardwareExport implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
{
    protected $hardware;

    public function __construct($hardware)
    {
        $this->hardware = $hardware;
    }

    public function collection()
    {
        return $this->hardware;
    }

    public function headings(): array
    {
        return [
            'Nama Hardware',
            'Kategori',
            'Merek',
            'Spesifikasi',
            'Jumlah',
            'Lokasi',
            'Status',
            'Catatan',
        ];
    }

    public function map($hardware): array
    {
        return [
            $hardware->nama_hardware,
            $hardware->kategori,
            $hardware->merek ?: '-',
            $hardware->spesifikasi ?: '-',
            $hardware->jumlah,
            $hardware->lokasi ?: '-',
            ucfirst($hardware->status),
            $hardware->catatan ?: '-',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 12]],
        ];
    }
}
