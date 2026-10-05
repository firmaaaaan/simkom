<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class BoxUsage extends Model
{
    use HasUuids;
    protected $fillable = [
        'box_id',
        'user_name',
        'user_nim',
        'user_kelas',
        'status',
        'used_at',
        'returned_at',
        'source',
        'created_by',
    ];

    protected $casts = [
        'used_at'     => 'datetime',
        'returned_at' => 'datetime',
    ];

    // Notifikasi realtime ke lonceng admin/laboran saat box dipinjam lewat scan QR.
    // Input manual oleh admin tidak perlu memberi notifikasi (admin sendiri pelakunya).
    protected static function booted(): void
    {
        static::created(function (BoxUsage $usage) {
            if ($usage->source === 'manual') {
                return;
            }

            Notification::create([
                'title'   => 'Peminjaman Box Baru',
                'message' => "{$usage->user_name} ({$usage->user_nim}) meminjam box {$usage->box?->code}",
                'type'    => 'box_usage',
                'url'     => route('boxes.index'),
            ]);
        });
    }

    public function box(): BelongsTo
    {
        return $this->belongsTo(Box::class);
    }

    public function returnNote(): HasOne
    {
        return $this->hasOne(BoxReturnNote::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getDurationAttribute(): ?string
    {
        if (!$this->returned_at) {
            return $this->used_at->diffForHumans(null, true);
        }

        return $this->used_at->diff($this->returned_at)->format('%h jam %i menit');
    }
}
