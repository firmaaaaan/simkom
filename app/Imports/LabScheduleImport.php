<?php

namespace App\Imports;

use App\Models\LabSchedule;
use App\Models\Laboratory;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;

class LabScheduleImport implements ToModel, WithHeadingRow, WithValidation, WithBatchInserts, WithChunkReading
{
    protected int $importedCount = 0;
    protected array $errors = [];

    public function model(array $row)
    {
        $dayMap = [
            'senin' => 'Monday',
            'selasa' => 'Tuesday',
            'rabu' => 'Wednesday',
            'kamis' => 'Thursday',
            'jumat' => 'Friday',
            'monday' => 'Monday',
            'tuesday' => 'Tuesday',
            'wednesday' => 'Wednesday',
            'thursday' => 'Thursday',
            'friday' => 'Friday',
        ];

        $dayInput = strtolower(trim($row['hari'] ?? ''));
        $day = $dayMap[$dayInput] ?? null;

        $startTime = $this->parseTime($row['jam_mulai'] ?? '');
        $endTime = $this->parseTime($row['jam_selesai'] ?? '');

        $labCode = trim($row['lab_kode'] ?? '');
        $laboratory = Laboratory::where('code', $labCode)->first();

        if (!$day || !$startTime || !$endTime || !$laboratory) {
            $this->errors[] = 'Baris ' . ($this->importedCount + 1) . ': Data tidak lengkap (hari, jam, atau lab tidak valid)';
            return null;
        }

        $conflict = LabSchedule::checkConflict($laboratory->id, $day, $startTime, $endTime);
        if ($conflict) {
            $this->errors[] = 'Baris ' . ($this->importedCount + 1) . ': Bentrok dengan jadwal ' . $conflict->course_name . ' (' . $conflict->time_label . ') di ' . $conflict->laboratory->name;
            return null;
        }

        $this->importedCount++;

        return new LabSchedule([
            'laboratory_id' => $laboratory->id,
            'day' => $day,
            'start_time' => $startTime,
            'end_time' => $endTime,
            'course_name' => trim($row['mata_kuliah'] ?? ''),
            'study_program' => trim($row['prodi'] ?? ''),
            'semester' => trim($row['semester'] ?? '') ?: null,
            'instructor' => trim($row['dosen'] ?? '') ?: null,
            'class_group' => trim($row['kelas'] ?? '') ?: null,
            'created_by' => Auth::id(),
        ]);
    }

    public function rules(): array
    {
        return [
            'hari' => 'required|string',
            'jam_mulai' => 'required',
            'jam_selesai' => 'required',
            'lab_kode' => 'required|string',
            'mata_kuliah' => 'required|string|max:255',
            'prodi' => 'required|string|max:255',
            'semester' => 'nullable|string|max:50',
            'dosen' => 'nullable|string|max:255',
            'kelas' => 'nullable|string|max:100',
        ];
    }

    public function customValidationMessages(): array
    {
        return [
            'hari.required' => 'Hari wajib diisi',
            'jam_mulai.required' => 'Jam mulai wajib diisi',
            'jam_selesai.required' => 'Jam selesai wajib diisi',
            'lab_kode.required' => 'Kode laboratorium wajib diisi',
            'mata_kuliah.required' => 'Mata kuliah wajib diisi',
            'prodi.required' => 'Prodi wajib diisi',
        ];
    }

    public function batchSize(): int
    {
        return 100;
    }

    public function chunkSize(): int
    {
        return 100;
    }

    public function getImportedCount(): int
    {
        return $this->importedCount;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    protected function parseTime(string $time): ?string
    {
        $time = trim($time);
        if (preg_match('/^(\d{1,2}):(\d{2})$/', $time, $matches)) {
            $hour = (int) $matches[1];
            $minute = (int) $matches[2];
            if ($hour >= 0 && $hour <= 23 && $minute >= 0 && $minute <= 59) {
                return sprintf('%02d:%02d:00', $hour, $minute);
            }
        }
        if (preg_match('/^(\d{1,2}):(\d{2}):(\d{2})$/', $time, $matches)) {
            $hour = (int) $matches[1];
            $minute = (int) $matches[2];
            if ($hour >= 0 && $hour <= 23 && $minute >= 0 && $minute <= 59) {
                return sprintf('%02d:%02d:00', $hour, $minute);
            }
        }
        return null;
    }
}