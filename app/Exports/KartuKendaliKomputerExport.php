<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class KartuKendaliKomputerExport implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
{
    protected $kartuKendali;

    public function __construct($kartuKendali)
    {
        $this->kartuKendali = $kartuKendali;
    }

    public function collection()
    {
        return $this->kartuKendali;
    }

    public function headings(): array
    {
        return [
            'No',
            'Kode Komputer',
            'Nama Komputer',
            'Laboratorium',
            'Tanggal Pemeriksaan',
            'Tahun Ajaran',
            'Kondisi Keseluruhan',
            'Catatan',
            'Pemeriksa',
            'Detail Pemeriksaan',
        ];
    }

    public function map($kartu): array
    {
        static $no = 1;

        $detailItems = collect($kartu->items ?? [])->map(function ($item) {
            return ($item['nama'] ?? '-') . ' (' . ucfirst($item['kondisi'] ?? '-') . ')';
        })->join('; ') ?: '-';

        return [
            $no++,
            $kartu->inspectable->kode_komputer ?? '-',
            $kartu->inspectable->nama_komputer ?? '-',
            $kartu->inspectable->laboratorium->nama_laboratorium ?? '-',
            $kartu->tanggal_pemeriksaan->translatedFormat('d F Y'),
            $kartu->tahunAjaran->nama ?? '-',
            ucfirst($kartu->kondisi_keseluruhan),
            $kartu->catatan ?: '-',
            $kartu->pemeriksa,
            $detailItems,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 12]],
        ];
    }
}
