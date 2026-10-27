<?php

namespace Database\Seeders;

use App\Models\AcademicYear;
use App\Models\Computer;
use App\Models\Laboratory;
use App\Models\MaintenanceChecklist;
use App\Models\MaintenanceChecklistItem;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class MaintenanceCheckSeeder extends Seeder
{
    public function run(): void
    {
        $years = AcademicYear::orderByDesc('start_year')->get();
        $laboratories = Laboratory::orderBy('name')->get();
        $checklistItems = MaintenanceChecklist::getChecklistItems();
        $inspectors = ['Budi Santoso', 'Siti Rahma', 'Andi Wijaya'];

        if ($years->isEmpty() || $laboratories->isEmpty()) {
            return;
        }

        foreach ($laboratories as $index => $laboratory) {
            $year = $years[$index % $years->count()];
            $computers = Computer::where('laboratory_id', $laboratory->id)->orderBy('code')->get();

            if ($computers->isEmpty()) {
                continue;
            }

            // Dua pemeliharaan per lab: awal & akhir semester tahun ajaran terpilih.
            $startDate = Carbon::create((int) $year->start_year, 9, 20);
            $endDate = Carbon::create((int) $year->end_year, 2, 20);

            foreach ([$startDate, $endDate] as $i => $date) {
                $checklist = MaintenanceChecklist::create([
                    'laboratory_id' => $laboratory->id,
                    'academic_year_id' => $year->id,
                    'maintenance_date' => $date->toDateString(),
                    'inspector_name' => $inspectors[$index % count($inspectors)],
                    'notes_computer' => $i === 0
                        ? 'Pembersihan debu casing dan pengecekan komponen dalam.'
                        : 'Pengecekan akhir semester sebelum libur.',
                ]);

                $rows = [];
                $now = now();

                foreach ($computers as $computer) {
                    foreach ($checklistItems as $category => $categoryData) {
                        foreach ($categoryData['items'] as $itemIndex => $question) {
                            // ~85% sudah, ~10% belum, ~5% tidak ada agar
                            // laporan memiliki kasus yang realistis.
                            $roll = mt_rand(1, 100);
                            $status = $roll <= 85
                                ? MaintenanceChecklistItem::STATUS_SUDAH
                                : ($roll <= 95 ? MaintenanceChecklistItem::STATUS_BELUM : MaintenanceChecklistItem::STATUS_TIDAK_ADA);

                            $rows[] = [
                                'id' => (string) Str::uuid(),
                                'maintenance_checklist_id' => $checklist->id,
                                'computer_id' => $computer->id,
                                'category' => $category,
                                'item_number' => $itemIndex + 1,
                                'status' => $status,
                                'checked_by' => $inspectors[$index % count($inspectors)],
                                'saved_at' => $now,
                                'created_at' => $now,
                                'updated_at' => $now,
                            ];
                        }
                    }
                }

                foreach (array_chunk($rows, 500) as $chunk) {
                    MaintenanceChecklistItem::insert($chunk);
                }
            }
        }
    }
}
