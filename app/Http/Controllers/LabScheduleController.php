<?php

namespace App\Http\Controllers;

use App\Exports\LabScheduleExport;
use App\Imports\LabScheduleImport;
use App\Models\LabSchedule;
use App\Models\Laboratory;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class LabScheduleController extends Controller
{
    public function index(Request $request)
    {
        $laboratories = Laboratory::where('status', 'Aktif')
            ->orderBy('name')
            ->get();

        $dayOrder = array_flip(LabSchedule::dayOrder());

        $schedules = LabSchedule::with('laboratory')
            ->get()
            // Urutan hari portabel (tanpa FIELD() yang hanya ada di MySQL).
            ->sortBy(function ($item) use ($dayOrder) {
                return sprintf(
                    '%02d|%s|%05d',
                    $dayOrder[$item->day] ?? 99,
                    $item->start_time,
                    $item->laboratory_id
                );
            })
            ->values()
            // Kunci sel harus pakai format H:i agar cocok dengan slot di blade.
            ->groupBy(function ($item) {
                return $item->day . '_' . substr($item->start_time, 0, 5) . '_' . $item->laboratory_id;
            })
            // Blade mengharapkan model tunggal per sel, bukan collection grup.
            ->map(fn (Collection $group) => $group->first());

        $timeSlots = LabSchedule::timeSlots();
        $days = LabSchedule::days();
        $realtimeUrl = Setting::realtimeScheduleUrl();

        return view('lab-schedules.index', compact('laboratories', 'schedules', 'timeSlots', 'days', 'realtimeUrl'));
    }

    public function show(LabSchedule $schedule)
    {
        return response()->json([
            'success' => true,
            'schedule' => $this->formatSchedule($schedule),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'laboratory_id' => 'required|exists:laboratories,id',
            'day' => 'required|in:Monday,Tuesday,Wednesday,Thursday,Friday',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'course_name' => 'required|string|max:255',
            'study_program' => 'required|string|max:255',
            'semester' => 'nullable|string|max:50',
            'instructor' => 'nullable|string|max:255',
            'class_group' => 'nullable|string|max:100',
        ]);

        $conflict = LabSchedule::checkConflict(
            $validated['laboratory_id'],
            $validated['day'],
            $validated['start_time'],
            $validated['end_time']
        );

        if ($conflict) {
            return $this->conflictResponse($conflict);
        }

        $schedule = LabSchedule::create([
            ...$validated,
            'created_by' => auth()->id(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Jadwal berhasil ditambahkan',
            'data' => $this->formatSchedule($schedule),
        ]);
    }

    public function update(Request $request, LabSchedule $schedule)
    {
        $validated = $request->validate([
            'laboratory_id' => 'required|exists:laboratories,id',
            'day' => 'required|in:Monday,Tuesday,Wednesday,Thursday,Friday',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'course_name' => 'required|string|max:255',
            'study_program' => 'required|string|max:255',
            'semester' => 'nullable|string|max:50',
            'instructor' => 'nullable|string|max:255',
            'class_group' => 'nullable|string|max:100',
        ]);

        $conflict = LabSchedule::checkConflict(
            $validated['laboratory_id'],
            $validated['day'],
            $validated['start_time'],
            $validated['end_time'],
            $schedule->id
        );

        if ($conflict) {
            return $this->conflictResponse($conflict);
        }

        $schedule->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Jadwal berhasil diperbarui',
            'data' => $this->formatSchedule($schedule),
        ]);
    }

    public function destroy(LabSchedule $schedule)
    {
        $schedule->delete();

        return response()->json([
            'success' => true,
            'message' => 'Jadwal berhasil dihapus',
        ]);
    }

    /**
     * Pindahkan jadwal ke slot kosong, atau tukar dengan jadwal lain di slot tujuan.
     * Dipakai fitur drag & drop di grid jadwal.
     */
    public function move(Request $request, LabSchedule $schedule)
    {
        $validated = $request->validate([
            'laboratory_id' => 'required|exists:laboratories,id',
            'day' => 'required|in:Monday,Tuesday,Wednesday,Thursday,Friday',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'target_id' => 'nullable|exists:lab_schedules,id',
        ]);

        $target = isset($validated['target_id'])
            ? LabSchedule::find($validated['target_id'])
            : null;

        // Kadang JS mengirim target_id jadwal yang barusan dihapus; abaikan bila
        // posisinya sudah tidak persis sama dengan slot tujuan.
        if ($target && ($target->laboratory_id != $validated['laboratory_id'] || $target->day !== $validated['day'] || substr($target->start_time, 0, 5) !== $validated['start_time'])) {
            $target = null;
        }

        $swap = $target !== null && $target->id !== $schedule->id;

        // Konflik jadwal LAIN di slot tujuan (kedua jadwal yang terlibat dikecualikan).
        $excludeIds = $swap ? [$schedule->id, $target->id] : [$schedule->id];
        $conflict = LabSchedule::checkConflict(
            $validated['laboratory_id'],
            $validated['day'],
            $validated['start_time'],
            $validated['end_time'],
            $excludeIds
        );

        if ($conflict) {
            return $this->conflictResponse($conflict);
        }

        // Saat tukar slot, jadwal lawan pindah ke slot asal penggerak —
        // pastikan slot asal itu juga bersih dari jadwal lain.
        if ($swap) {
            $reverseConflict = LabSchedule::checkConflict(
                $schedule->laboratory_id,
                $schedule->day,
                $schedule->start_time,
                $schedule->end_time,
                [$schedule->id, $target->id]
            );

            if ($reverseConflict) {
                return $this->conflictResponse($reverseConflict);
            }
        }

        DB::transaction(function () use ($schedule, $target, $swap, $validated) {
            if (! $swap) {
                $schedule->update([
                    'laboratory_id' => $validated['laboratory_id'],
                    'day' => $validated['day'],
                    'start_time' => $validated['start_time'],
                    'end_time' => $validated['end_time'],
                ]);

                return;
            }

            // Tukar utuh: lab, hari, dan jam kedua jadwal saling bertukar.
            $scheduleOld = [
                'laboratory_id' => $schedule->laboratory_id,
                'day' => $schedule->day,
                'start_time' => $schedule->start_time,
                'end_time' => $schedule->end_time,
            ];
            $targetOld = [
                'laboratory_id' => $target->laboratory_id,
                'day' => $target->day,
                'start_time' => $target->start_time,
                'end_time' => $target->end_time,
            ];

            // Langkah parkir sementara: unique(laboratory_id, day, start_time)
            // akan dilanggar bila dua jadwal saling menukar slot secara langsung,
            // karena slot asal masih ditempati penggerak saat target pindah
            // duluan. Parkir target dulu, lalu tukar posisi keduanya.
            $temp = $this->tempFreeSlot($target->laboratory_id, $target->day, [$target->id, $schedule->id]);
            $target->update(['start_time' => $temp, 'end_time' => $temp]);

            $schedule->update($targetOld);
            $target->update($scheduleOld);
        });

        return response()->json([
            'success' => true,
            'message' => $swap
                ? 'Jadwal berhasil ditukar dengan ' . $target->course_name
                : 'Jadwal berhasil dipindahkan',
            'swapped' => $swap,
        ]);
    }

    public function export(Request $request)
    {
        $query = LabSchedule::query();

        if ($labId = $request->laboratory_id) {
            $query->where('laboratory_id', $labId);
        }

        if ($day = $request->day) {
            $query->where('day', $day);
        }

        return Excel::download(
            new LabScheduleExport($query),
            'jadwal-lab-' . now()->format('Y-m-d') . '.xlsx'
        );
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv',
        ]);

        $import = new LabScheduleImport();
        Excel::import($import, $request->file('file'));

        $message = "Berhasil mengimpor {$import->getImportedCount()} jadwal";
        if ($errors = $import->getErrors()) {
            $message .= '. ' . count($errors) . ' baris gagal: ' . implode('; ', array_slice($errors, 0, 5));
            if (count($errors) > 5) {
                $message .= ' dan ' . (count($errors) - 5) . ' lagi...';
            }
        }

        return response()->json([
            'success' => true,
            'message' => $message,
            'imported' => $import->getImportedCount(),
            'errors' => $import->getErrors(),
        ]);
    }

    private function conflictResponse(LabSchedule $conflict)
    {
        return response()->json([
            'success' => false,
            'conflict' => true,
            'message' => "Jadwal bentrok dengan: {$conflict->course_name} ({$conflict->time_label}) di {$conflict->laboratory->name}",
        ], 422);
    }

    /**
     * Cari slot waktu kosong sementara (dari 23:59 mundur) pada lab & hari
     * tertentu, dipakai sebagai posisi parkir saat penukaran dua jadwal.
     */
    private function tempFreeSlot(int $laboratoryId, string $day, array $excludeIds): string
    {
        for ($minutes = 23 * 60 + 59; $minutes >= 0; $minutes--) {
            $candidate = LabSchedule::normalizeTime(sprintf('%02d:%02d', intdiv($minutes, 60), $minutes % 60));

            $exists = LabSchedule::where('laboratory_id', $laboratoryId)
                ->where('day', $day)
                ->where('start_time', $candidate)
                ->whereNotIn('id', $excludeIds)
                ->exists();

            if (! $exists) {
                return $candidate;
            }
        }

        // Praktis mustahil tercapai (butuh 1440 jadwal di satu hari).
        abort(422, 'Tidak ada slot waktu kosong untuk menukar jadwal.');
    }

    private function formatSchedule(LabSchedule $schedule): array
    {
        $schedule->load('laboratory');

        return [
            'id' => $schedule->id,
            'laboratory_id' => $schedule->laboratory_id,
            'laboratory_name' => $schedule->laboratory->name ?? '-',
            'day' => $schedule->day,
            'start_time' => substr($schedule->start_time, 0, 5),
            'end_time' => substr($schedule->end_time, 0, 5),
            'time_label' => $schedule->time_label,
            'course_name' => $schedule->course_name,
            'study_program' => $schedule->study_program,
            'semester' => $schedule->semester,
            'instructor' => $schedule->instructor,
            'class_group' => $schedule->class_group,
            'cell_content' => $schedule->cell_content,
        ];
    }
}
