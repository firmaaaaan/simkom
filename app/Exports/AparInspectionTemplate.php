<?php

namespace App\Exports;

use App\Models\AparInspection;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class AparInspectionTemplate implements FromArray, WithHeadings, WithStyles
{
    public function headings(): array
    {
        $headings = ['Kode', 'No. Registrasi', 'Tanggal Inspeksi', 'Lokasi', 'Diperiksa Oleh'];

        foreach (AparInspection::ITEMS as $item) {
            $headings[] = $item['label_id'];
            $headings[] = 'Ket. '.$item['label_id'];
        }

        return $headings;
    }

    public function array(): array
    {
        $row = ['Contoh', 'APAR-LAB1-01', '2026-11-05', 'Lab Komputer 1', 'Petugas K3'];

        foreach (AparInspection::ITEMS as $index => $item) {
            $row[] = $index === 2 ? 'Tidak' : 'Baik';
            $row[] = $index === 2 ? 'Penunjuk tekanan di zona merah' : '';
        }

        return [$row];
    }

    public function styles(Worksheet $sheet): array
    {
        return [1 => ['font' => ['bold' => true]]];
    }
}
