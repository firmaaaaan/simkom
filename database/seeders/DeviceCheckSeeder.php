<?php

namespace Database\Seeders;

use App\Models\AcademicYear;
use App\Models\Computer;
use App\Models\DeviceCheck;
use App\Models\DeviceCheckItem;
use App\Models\Laboratory;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class DeviceCheckSeeder extends Seeder
{
    public function run(): void
    {
        $years = AcademicYear::orderByDesc('start_year')->get();
        $laboratories = Laboratory::orderBy('name')->get();
        $itemKeys = DeviceCheck::itemKeys();
        $officers = ['Budi Santoso', 'Siti Rahma', 'Andi Wijaya'];

        if ($years->isEmpty() || $laboratories->isEmpty()) {
            return;
        }

        foreach ($laboratories as $index => $laboratory) {
            $year = $years[$index % $years->count()];
            $computers = Computer::where('laboratory_id', $laboratory->id)->orderBy('code')->get();

            if ($computers->isEmpty()) {
                continue;
            }

            // Dua pengecekan per lab: awal & akhir semester tahun ajaran terpilih.
            $startDate = $year->start_date
                ? Carbon::parse($year->start_date)->addWeeks(2)
                : Carbon::create((int) $year->start_year, 9, 15);

            $endDate = $year->end_date
                ? Carbon::parse($year->end_date)->subMonths(2)
                : Carbon::create((int) $year->end_year, 2, 15);

            foreach ([$startDate, $endDate] as $i => $date) {
                $check = DeviceCheck::create([
                    'laboratory_id' => $laboratory->id,
                    'academic_year_id' => $year->id,
                    'check_date' => $date->toDateString(),
                    'officer_name' => $officers[$index % count($officers)],
                    'notes' => $i === 0
                        ? 'Pengecekan awal semester sebelum praktikum dimulai.'
                        : 'Pengecekan ulang menjelang akhir semester.',
                ]);

                $rows = [];
                $now = now();

                foreach ($computers as $computer) {
                    foreach ($itemKeys as $itemKey) {
                        $rows[] = [
                            'device_check_id' => $check->id,
                            'computer_id' => $computer->id,
                            'item_key' => $itemKey,
                            // ~90% sel baik agar laporan memiliki beberapa "masalah" yang realistis.
                            'is_checked' => mt_rand(1, 100) <= 90,
                            'created_at' => $now,
                            'updated_at' => $now,
                        ];
                    }
                }

                foreach (array_chunk($rows, 500) as $chunk) {
                    DeviceCheckItem::insert($chunk);
                }
            }
        }
    }
}
