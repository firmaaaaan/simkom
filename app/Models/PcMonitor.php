<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class PcMonitor extends Model
{
    use HasUuids;

    public const STATUS_ONLINE = 'online';

    public const STATUS_OFFLINE = 'offline';

    protected $fillable = [
        'hostname',
        'ip_address',
        'mac_address',
        'cpu_usage',
        'ram_usage',
        'disk_usage',
        'active_user',
        'status',
        'last_seen_at',
    ];

    protected $casts = [
        'cpu_usage' => 'float',
        'ram_usage' => 'float',
        'disk_usage' => 'float',
        'last_seen_at' => 'datetime',
    ];

    public function scopeOnline(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_ONLINE);
    }

    public function scopeOffline(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_OFFLINE);
    }

    public function isOnline(): bool
    {
        return $this->status === self::STATUS_ONLINE;
    }

    /**
     * True bila PC belum mengirim telemetri selama $seconds terakhir
     * (termasuk yang belum pernah terlihat sama sekali).
     */
    public function isStale(int $seconds = 30): bool
    {
        if (! $this->last_seen_at) {
            return true;
        }

        return $this->last_seen_at->lt(now()->subSeconds($seconds));
    }

    /**
     * Detik sejak terakhir terlihat (0 bila belum pernah).
     */
    public function secondsSinceLastSeen(): int
    {
        if (! $this->last_seen_at) {
            return 0;
        }

        return max(0, now()->timestamp - $this->last_seen_at->timestamp);
    }
}
