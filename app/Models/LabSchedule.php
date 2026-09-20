<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LabSchedule extends Model
{
    protected $fillable = [
        'laboratory_id',
        'day',
        'start_time',
        'end_time',
        'course_name',
        'study_program',
        'semester',
        'instructor',
        'class_group',
        'created_by',
    ];

    // Kolom TIME disimpan/dibaca sebagai string "H:i:s" agar konsisten di semua
    // driver database (MySQL maupun SQLite). Laravel tidak punya cast "time"
    // bawaan, jadi kita normalisasi manual lewat accessor di bawah.

    public function laboratory(): BelongsTo
    {
        return $this->belongsTo(Laboratory::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getStartTimeAttribute(?string $value): ?string
    {
        return static::normalizeTime($value);
    }

    public function getEndTimeAttribute(?string $value): ?string
    {
        return static::normalizeTime($value);
    }

    public function setStartTimeAttribute(?string $value): void
    {
        $this->attributes['start_time'] = static::normalizeTime($value);
    }

    public function setEndTimeAttribute(?string $value): void
    {
        $this->attributes['end_time'] = static::normalizeTime($value);
    }

    public static function timeSlots(): array
    {
        return [
            ['start' => '06:00', 'end' => '08:00', 'label' => '06:00 - 08:00'],
            ['start' => '08:00', 'end' => '10:00', 'label' => '08:00 - 10:00'],
            ['start' => '10:00', 'end' => '12:00', 'label' => '10:00 - 12:00'],
            ['start' => '12:00', 'end' => '13:00', 'label' => 'ISTIRAHAT', 'is_break' => true],
            ['start' => '13:00', 'end' => '15:00', 'label' => '13:00 - 15:00'],
            ['start' => '15:00', 'end' => '17:00', 'label' => '15:00 - 17:00'],
            ['start' => '18:00', 'end' => '20:00', 'label' => '18:00 - 20:00'],
            ['start' => '20:00', 'end' => '22:00', 'label' => '20:00 - 22:00'],
        ];
    }

    public static function days(): array
    {
        return [
            'Monday' => 'SENIN',
            'Tuesday' => 'SELASA',
            'Wednesday' => 'RABU',
            'Thursday' => 'KAMIS',
            'Friday' => 'JUMAT',
        ];
    }

    /**
     * Urutan hari kerja (Senin-Jumat) untuk sorting portabel lintas driver database.
     */
    public static function dayOrder(): array
    {
        return ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];
    }

    /**
     * Normalisasi nilai waktu ke bentuk "H:i:s".
     * Menerima "8:00", "08:00", "08:00:00", maupun "2026-09-19 08:00:00".
     */
    public static function normalizeTime(?string $value): ?string
    {
        if (! $value) {
            return null;
        }

        $value = trim($value);

        if (preg_match('/^(\d{1,2}):(\d{2})(?::(\d{2}))?$/', $value, $m)) {
            return sprintf('%02d:%02d:%02d', $m[1], $m[2], $m[3] ?? 0);
        }

        if (preg_match('/(\d{1,2}):(\d{2}):(\d{2})/', $value, $m)) {
            return sprintf('%02d:%02d:%02d', $m[1], $m[2], $m[3]);
        }

        return $value;
    }

    public function getTimeLabelAttribute(): string
    {
        if (! $this->start_time || ! $this->end_time) {
            return '-';
        }

        return substr($this->start_time, 0, 5) . ' - ' . substr($this->end_time, 0, 5);
    }

    public function getCellContentAttribute(): string
    {
        $parts = [$this->course_name];

        if ($this->study_program) {
            $parts[] = $this->study_program;
        }

        if ($this->semester) {
            $parts[] = $this->semester;
        }

        if ($this->instructor) {
            $parts[] = $this->instructor;
        }

        if ($this->class_group) {
            $parts[] = $this->class_group;
        }

        return implode(' - ', $parts);
    }

    public static function checkConflict(int $laboratoryId, string $day, string $startTime, string $endTime, int|array|null $excludeIds = null): ?self
    {
        $query = static::where('laboratory_id', $laboratoryId)
            ->where('day', $day)
            ->where('start_time', '<', static::normalizeTime($endTime))
            ->where('end_time', '>', static::normalizeTime($startTime));

        $excludeIds = $excludeIds === null ? [] : (array) $excludeIds;
        if ($excludeIds !== []) {
            // Dipakai saat pindah/tukar slot: kedua jadwal yang bertukar tidak
            // dianggap konflik terhadap posisi barunya sendiri.
            $query->whereNotIn('id', $excludeIds);
        }

        return $query->first();
    }
}
