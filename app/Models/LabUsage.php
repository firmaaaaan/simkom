<?php

namespace App\Models;

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
    ];

    protected $casts = [
        'checked_in_at' => 'datetime',
        'validated_at'  => 'datetime',
    ];

    protected static function booted(): void
    {
        // Notifikasi realtime ke lonceng admin/laboran saat mahasiswa check-in via QR
        static::created(function (LabUsage $usage) {
            Notification::create([
                'title'   => 'Check-in Penggunaan Lab',
                'message' => "{$usage->user_name} ({$usage->user_prodi}) masuk {$usage->laboratory->name} - {$usage->purpose}",
                'type'    => 'lab_usage',
                'url'     => route('lab-usages.index', ['status' => 'In']),
            ]);
        });
    }

    public function laboratory(): BelongsTo
    {
        return $this->belongsTo(Laboratory::class);
    }

    public function validatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'validated_by');
    }

    public function getDurationAttribute(): ?string
    {
        $end = $this->validated_at ?? now();

        return $this->checked_in_at->diff($end)->format('%h jam %i menit');
    }
}
