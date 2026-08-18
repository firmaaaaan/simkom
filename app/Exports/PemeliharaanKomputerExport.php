<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class PemeliharaanKomputerExport implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
{
    protected $pemeliharaan;

    public function __construct($pemeliharaan)
    {
        $this->pemeliharaan = $pemeliharaan;
    }

    public function collection()
    {
        return $this->pemeliharaan;
    }

    public function headings(): array
    {
        return [
            'No',
            'Komputer',
            'Laboratorium',
            'Tahun Ajaran',
            'Tanggal Pemeliharaan',
            'Jenis Pemeliharaan',
            'Deskripsi',
            'Biaya',
            'PIC',
        ];
    }

    public function map($pemeliharaan): array
    {
        static $no = 1;

        return [
            $no++,
            $pemeliharaan->komputer->nama_komputer ?? '-',
            $pemeliharaan->komputer->laboratorium->nama_laboratorium ?? '-',
            $pemeliharaan->tahunAjaran->nama ?? '-',
            $pemeliharaan->tanggal_pemeliharaan->translatedFormat('d F Y'),
            ucfirst($pemeliharaan->jenis_pemeliharaan),
            $pemeliharaan->deskripsi,
            $pemeliharaan->biaya ? 'Rp ' . number_format($pemeliharaan->biaya, 0, ',', '.') : '-',
            $pemeliharaan->pic ?: '-',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 12]],
        ];
    }
}
