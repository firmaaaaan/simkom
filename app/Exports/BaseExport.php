<?php

namespace App\Exports;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/**
 * Dasar untuk semua export modul baru: query disiapkan di controller (termasuk
 * filter yang sedang aktif), sedangkan kolom & pemetaannya diatur tiap kelas.
 */
abstract class BaseExport implements FromCollection, WithStyles
{
    public function __construct(protected Builder $query)
    {
    }

    public function collection(): Collection
    {
        return $this->query->get();
    }

    /**
     * Baris judul dibuat tebal agar mudah dibaca di Excel.
     */
    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
