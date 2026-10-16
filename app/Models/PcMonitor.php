<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PcMonitor extends Model
{
    use HasUuids;

    public const STATUS_ONLINE = 'online';

    public const STATUS_OFFLINE = 'offline';

    /** Detik tanpa laporan telemetri sebelum PC dianggap offline. */
    public const STALE_SECONDS = 30;

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

    public function powerCommands(): HasMany
    {
        return $this->hasMany(PcPowerCommand::class);
    }

    /**
     * Status efektif dihitung dari last_seen_at sehingga akurat walau
     * kolom status belum disinkronkan command monitoring:check-offline.
     */
    public function isStale(int $seconds = self::STALE_SECONDS): bool
    {
        return $this->last_seen_at === null
            || $this->last_seen_at->lte(now()->subSeconds($seconds));
    }

    public function effectiveStatus(): string
    {
        return $this->isStale() ? self::STATUS_OFFLINE : self::STATUS_ONLINE;
    }

    public function secondsSinceLastSeen(): ?int
    {
        if ($this->last_seen_at === null) {
            return null;
        }

        return max(0, now()->diffInSeconds($this->last_seen_at, true));
    }

    /** Perintah power pending yang masih berlaku (dalam TTL). */
    public function hasPendingPower(): bool
    {
        return $this->powerCommands()->pending()->exists();
    }

    public function scopeStale(Builder $query, int $seconds = self::STALE_SECONDS): Builder
    {
        return $query->where(function (Builder $q) use ($seconds) {
            $q->whereNull('last_seen_at')
                ->orWhere('last_seen_at', '<=', now()->subSeconds($seconds));
        });
    }
}
