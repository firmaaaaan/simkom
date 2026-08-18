<?php

namespace App\Exports;

use App\Models\PeminjamanKomputer;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class PeminjamanKomputerExport implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
{
    protected $peminjaman;

    public function __construct($peminjaman)
    {
        $this->peminjaman = $peminjaman;
    }

    public function collection()
    {
        return $this->peminjaman;
    }

    public function headings(): array
    {
        return [
            'No',
            'Komputer',
            'Kode Komputer',
            'Peminjam',
            'NPM/NIM',
            'Kode Tracker',
            'Tanggal Pinjam',
            'Jam Mulai',
            'Jam Selesai',
            'Status Peminjaman',
            'Status Komputer',
            'Catatan',
        ];
    }

    public function map($peminjaman): array
    {
        static $no = 1;

        return [
            $no++,
            $peminjaman->komputer->nama_komputer ?? '-',
            $peminjaman->komputer->kode_komputer ?? '-',
            $peminjaman->nama_peminjam,
            $peminjaman->npm_nim,
            $peminjaman->kode_tracker,
            $peminjaman->tanggal_pinjam ? \Carbon\Carbon::parse($peminjaman->tanggal_pinjam)->translatedFormat('d F Y') : '-',
            $peminjaman->jam_mulai,
            $peminjaman->jam_selesai,
            ucfirst($peminjaman->status_peminjaman),
            ucfirst($peminjaman->komputer->status ?? '-'),
            $peminjaman->catatan ?? '-',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 12]],
        ];
    }
}
