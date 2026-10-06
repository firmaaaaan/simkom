<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class AcademicYearExport extends BaseExport implements WithHeadings, WithMapping
{
    public function headings(): array
    {
        return [
            'Nama Tahun Ajaran',
            'Tahun Mulai',
            'Tahun Selesai',
            'Periode',
            'Status',
        ];
    }

    public function map($academicYear): array
    {
        return [
            $academicYear->name,
            $academicYear->start_year,
            $academicYear->end_year,
            $academicYear->periodLabel(),
            $academicYear->status,
        ];
    }
}
