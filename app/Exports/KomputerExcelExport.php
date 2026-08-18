<?php

namespace App\Exports;

use App\Models\Komputer;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class KomputerExcelExport implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
{
    protected $komputers;

    public function __construct($komputers)
    {
        $this->komputers = $komputers;
    }

    public function collection()
    {
        return $this->komputers;
    }

    public function headings(): array
    {
        return [
            'No',
            'Kode Komputer',
            'Nama Komputer',
            'Laboratorium',
            'Status',
            'Spesifikasi',
            'Hardware',
            'Software',
            'Catatan',
        ];
    }

    public function map($komputer): array
    {
        static $no = 1;

        $hardware = $komputer->hardware->pluck('nama_hardware')->filter()->join(', ') ?: '-';
        $software = $komputer->software->pluck('nama_software')->filter()->join(', ') ?: '-';

        return [
            $no++,
            $komputer->kode_komputer,
            $komputer->nama_komputer,
            $komputer->laboratorium->nama_laboratorium ?? '-',
            ucfirst(str_replace('_', ' ', $komputer->status)),
            $komputer->spesifikasi ?: '-',
            $hardware,
            $software,
            $komputer->catatan ?: '-',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 12]],
        ];
    }
}
