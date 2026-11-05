<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class OccupationalDiseaseInvestigationTemplate implements FromArray, WithHeadings, WithStyles
{
    public function headings(): array
    {
        return [
            'Kode', 'Nama', 'NIP', 'Bagian', 'Jabatan', 'Jenis Kelamin', 'Lama Bekerja',
            'Keluhan', 'Riwayat Penyakit', 'Riwayat Penyakit Keluarga', 'Riwayat Pekerjaan',
            'Pemeriksaan Fisik', 'Pemeriksaan Radiologi', 'Pemeriksaan Laboratorium',
            'Pemeriksaan Penunjang', 'Pemeriksaan Patologi Anatomi',
            'Perbandingan Pemeriksaan Awal', 'Perbandingan Pemeriksaan Berkala',
            'Tanggal Pemeriksaan', 'Diperiksa Oleh',
        ];
    }

    public function array(): array
    {
        return [[
            'Contoh', 'Andi Pratama', '1912345678', 'UPT Laboratorium', 'Laboran', 'Laki-laki', '5 tahun',
            'Nyeri punggung berulang', 'Tidak ada', 'Tidak ada', 'Operator komputer 8 jam/hari',
            'Postur normal, nyeri tekan regio lumbal', 'Tidak ditemukan kelainan', 'Dalam batas normal',
            'Tidak diperlukan', 'Tidak diperlukan',
            'Nyeri punggung sejak 2024', 'Membaik setelah terapi',
            '2026-11-05', 'Dr. Dhesi Ari Astuti',
        ]];
    }

    public function styles(Worksheet $sheet): array
    {
        return [1 => ['font' => ['bold' => true]]];
    }
}
