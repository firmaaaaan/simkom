<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
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
        'used_at' => 'datetime',
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
                'title' => 'Peminjaman Box Baru',
                'message' => "{$usage->user_name} ({$usage->user_nim}) meminjam box {$usage->box?->code}",
                'type' => 'box_usage',
                'url' => route('boxes.index'),
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

    public function damages(): HasMany
    {
        return $this->hasMany(BoxUsageDamage::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getDurationAttribute(): ?string
    {
        if ($this->used_at === null) {
            return null;
        }

        // Total jam + menit (bukan sisa jam setelah hari), dihitung sampai
        // waktu pengembalian atau waktu sekarang bila belum dikembalikan.
        $end = $this->returned_at ?? now();
        $totalMinutes = max(0, (int) $this->used_at->diffInMinutes($end));

        return sprintf('%d jam %d menit', intdiv($totalMinutes, 60), $totalMinutes % 60);
    }
}
