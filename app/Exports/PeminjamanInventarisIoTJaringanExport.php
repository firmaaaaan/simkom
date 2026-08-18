<?php

namespace App\Exports;

use App\Models\PeminjamanInventarisIoTJaringan;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class PeminjamanInventarisIoTJaringanExport implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
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
            'Inventaris',
            'Kode Perangkat',
            'Kategori',
            'Jenis',
            'Nama Peminjam',
            'NPM/NIM',
            'Tanggal Pinjam',
            'Estimasi Kembali',
            'Tanggal Kembali Aktual',
            'Status',
            'Catatan',
        ];
    }

    public function map($peminjaman): array
    {
        static $no = 1;

        return [
            $no++,
            $peminjaman->inventaris->nama_inventaris ?? '-',
            $peminjaman->inventaris->kode_perangkat ?? '-',
            $peminjaman->inventaris->kategori ?? '-',
            $peminjaman->inventaris->jenis ?? '-',
            $peminjaman->nama_peminjam,
            $peminjaman->npm_nim,
            $peminjaman->tanggal_pinjam ? $peminjaman->tanggal_pinjam->format('d F Y') : '-',
            $peminjaman->tanggal_kembali_direncanakan ? $peminjaman->tanggal_kembali_direncanakan->format('d F Y') : '-',
            $peminjaman->tanggal_kembali_aktual ? $peminjaman->tanggal_kembali_aktual->format('d F Y') : '-',
            ucfirst($peminjaman->status),
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
