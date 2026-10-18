<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PcCommand extends Model
{
    use HasUuids;

    public const ACTION_SHUTDOWN = 'shutdown';

    public const ACTION_RESTART = 'restart';

    public const ACTION_LOCK = 'lock';

    public const ACTION_LOGOUT = 'logout';

    public const ACTION_SCREENSHOT = 'screenshot';

    /** Whitelist aksi yang dikenali dashboard & agent. */
    public const ACTIONS = [
        self::ACTION_SHUTDOWN,
        self::ACTION_RESTART,
        self::ACTION_LOCK,
        self::ACTION_LOGOUT,
        self::ACTION_SCREENSHOT,
    ];

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
        'message',
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

    /** Label Bahasa Indonesia per aksi (untuk pesan toast dashboard). */
    public static function actionLabel(string $action): string
    {
        return match ($action) {
            self::ACTION_SHUTDOWN => 'mematikan',
            self::ACTION_RESTART => 'merestart',
            self::ACTION_LOCK => 'mengunci layar',
            self::ACTION_LOGOUT => 'mengeluarkan user',
            self::ACTION_SCREENSHOT => 'mengambil screenshot',
            default => $action,
        };
    }
}
