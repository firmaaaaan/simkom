<?php

namespace App\Models;

use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LabUsage extends Model
{
    use HasUuids;

    protected $fillable = [
        'laboratory_id',
        'user_name',
        'user_prodi',
        'purpose',
        'day',
        'status',
        'checked_in_at',
        'validated_at',
        'validated_by',
        'exit_note',
        'source',
        'created_by',
    ];

    protected $casts = [
        'checked_in_at' => 'datetime',
        'validated_at'  => 'datetime',
    ];

    protected static function booted(): void
    {
        // Notifikasi realtime ke lonceng admin/laboran saat mahasiswa check-in via QR.
        // Input manual oleh admin tidak perlu memberi notifikasi (admin sendiri pelakunya).
        static::created(function (LabUsage $usage) {
            if ($usage->source === 'manual') {
                return;
            }

            Notification::create([
                'title'   => 'Check-in Penggunaan Lab',
                'message' => "{$usage->user_name} ({$usage->user_prodi}) masuk {$usage->laboratory->name} - {$usage->purpose}",
                'type'    => 'lab_usage',
                'url'     => route('lab-usages.index', ['status' => 'In']),
            ]);
        });
    }

    /**
     * Nama hari berbahasa Indonesia untuk tanggal tertentu — dipakai check-in QR
     * maupun pencatatan manual admin agar isi kolom "day" konsisten.
     */
    public static function dayLabel(CarbonInterface|string $date): string
    {
        $days = [
            'Sunday'    => 'Minggu',
            'Monday'    => 'Senin',
            'Tuesday'   => 'Selasa',
            'Wednesday' => 'Rabu',
            'Thursday'  => 'Kamis',
            'Friday'    => 'Jumat',
            'Saturday'  => 'Sabtu',
        ];

        // format('l') selalu memakai nama hari Inggris (tidak ikut locale Carbon).
        $english = ($date instanceof CarbonInterface ? $date : \Illuminate\Support\Carbon::parse($date))->format('l');

        return $days[$english] ?? $english;
    }

    public function laboratory(): BelongsTo
    {
        return $this->belongsTo(Laboratory::class);
    }

    public function validatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'validated_by');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getDurationAttribute(): ?string
    {
        $end = $this->validated_at ?? now();

        return $this->checked_in_at->diff($end)->format('%h jam %i menit');
    }
}
