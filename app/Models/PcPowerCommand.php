<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PcPowerCommand extends Model
{
    use HasUuids;

    public const ACTION_SHUTDOWN = 'shutdown';

    public const STATUS_PENDING = 'pending';

    public const STATUS_COMPLETED = 'completed';

    public const STATUS_FAILED = 'failed';

    /**
     * Perintah yang belum diambil agent melewati TTL ini dianggap kedaluwarsa
     * (PC bisa jadi sudah dimatikan/dinyalakan manual di antaranya).
     */
    public const TTL_SECONDS = 60;

    protected $fillable = [
        'pc_monitor_id',
        'action',
        'status',
        'requested_by',
        'executed_at',
    ];

    protected $casts = [
        'executed_at' => 'datetime',
    ];

    public function pcMonitor(): BelongsTo
    {
        return $this->belongsTo(PcMonitor::class);
    }

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    /** Perintah pending yang masih berlaku (untuk diambil agent). */
    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_PENDING)
            ->where('created_at', '>=', now()->subSeconds(self::TTL_SECONDS));
    }

    public function isExpired(): bool
    {
        return $this->status === self::STATUS_PENDING
            && $this->created_at->lte(now()->subSeconds(self::TTL_SECONDS));
    }
}
