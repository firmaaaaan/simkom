<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class LabScheduleExport extends BaseExport implements WithHeadings, WithMapping, WithStyles
{
    public function collection()
    {
        $dayOrder = array_flip(\App\Models\LabSchedule::dayOrder());

        // Sort di PHP agar urutan hari Senin-Jumat (bukan alfabetis) dan
        // tetap konsisten di semua driver database.
        return $this->query
            ->with(['laboratory', 'creator'])
            ->get()
            ->sortBy(function ($schedule) use ($dayOrder) {
                return sprintf(
                    '%02d|%s|%05d',
                    $dayOrder[$schedule->day] ?? 99,
                    $schedule->start_time,
                    $schedule->laboratory_id
                );
            })
            ->values();
    }

    public function headings(): array
    {
        return [
            'Hari',
            'Jam',
            'Laboratorium',
            'Mata Kuliah',
            'Prodi',
            'Semester',
            'Dosen',
            'Kelas',
            'Dibuat Oleh',
            'Tanggal Dibuat',
        ];
    }

    public function map($schedule): array
    {
        $dayLabels = [
            'Monday' => 'Senin',
            'Tuesday' => 'Selasa',
            'Wednesday' => 'Rabu',
            'Thursday' => 'Kamis',
            'Friday' => 'Jumat',
        ];

        return [
            $dayLabels[$schedule->day] ?? $schedule->day,
            substr($schedule->start_time, 0, 5) . ' - ' . substr($schedule->end_time, 0, 5),
            $schedule->laboratory->name ?? '-',
            $schedule->course_name,
            $schedule->study_program,
            $schedule->semester ?? '-',
            $schedule->instructor ?? '-',
            $schedule->class_group ?? '-',
            $schedule->creator->name ?? '-',
            $schedule->created_at->format('d/m/Y H:i'),
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
