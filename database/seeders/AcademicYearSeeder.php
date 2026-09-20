<?php

namespace Database\Seeders;

use App\Models\AcademicYear;
use Illuminate\Database\Seeder;

class AcademicYearSeeder extends Seeder
{
    public function run(): void
    {
        $years = [
            [
                'name' => 'Tahun Ajaran 2023/2024',
                'start_year' => 2023,
                'end_year' => 2024,
                'status' => 'Non Aktif',
                'start_date' => '2023-08-01',
                'end_date' => '2024-06-30',
            ],
            [
                'name' => 'Tahun Ajaran 2024/2025',
                'start_year' => 2024,
                'end_year' => 2025,
                'status' => 'Non Aktif',
                'start_date' => '2024-08-01',
                'end_date' => '2025-06-30',
            ],
            [
                'name' => 'Tahun Ajaran 2025/2026',
                'start_year' => 2025,
                'end_year' => 2026,
                'status' => 'Aktif',
                'start_date' => '2025-08-01',
                'end_date' => '2026-06-30',
            ],
            [
                'name' => 'Tahun Ajaran 2026/2027',
                'start_year' => 2026,
                'end_year' => 2027,
                'status' => 'Non Aktif',
                'start_date' => '2026-08-01',
                'end_date' => '2027-06-30',
            ],
        ];

        foreach ($years as $year) {
            AcademicYear::create($year);
        }
    }
}
