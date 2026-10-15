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

    public const STATUS_DONE = 'done';

    public const STATUS_FAILED = 'failed';

    public const STATUS_EXPIRED = 'expired';

    /** Detik perintah masih dianggap berlaku sebelum diabaikan agent. */
    public const TTL_SECONDS = 120;

    protected $fillable = [
        'pc_monitor_id',
        'action',
        'status',
        'message',
        'requested_by',
        'executed_at',
    ];

    protected $casts = [
        'executed_at' => 'datetime',
    ];

    public function pc(): BelongsTo
    {
        return $this->belongsTo(PcMonitor::class, 'pc_monitor_id');
    }

    public function requestedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    /**
     * True bila perintah sudah lewat masa berlaku (TTL) dan tidak boleh
     * dieksekusi agent lagi.
     */
    public function isExpired(): bool
    {
        return $this->created_at->lt(now()->subSeconds(self::TTL_SECONDS));
    }
}
