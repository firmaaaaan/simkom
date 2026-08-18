<?php

namespace App\Exports;

use App\Models\Software;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class SoftwareExport implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
{
    protected $software;

    public function __construct($software)
    {
        $this->software = $software;
    }

    public function collection()
    {
        return $this->software;
    }

    public function headings(): array
    {
        return [
            'Nama Software',
            'Kategori',
            'Versi',
            'Lisensi',
            'Tanggal Instalasi',
            'Tanggal Expired',
            'Status',
            'Catatan',
        ];
    }

    public function map($software): array
    {
        return [
            $software->nama_software,
            $software->kategori,
            $software->versi ?: '-',
            ucfirst($software->lisensi),
            $software->tanggal_instalasi ? $software->tanggal_instalasi->format('d-m-Y') : '-',
            $software->tanggal_expired ? $software->tanggal_expired->format('d-m-Y') : '-',
            ucfirst($software->status),
            $software->catatan ?: '-',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 12]],
        ];
    }
}
