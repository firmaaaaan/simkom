<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class PcMonitor extends Model
{
    use HasUuids;

    public const STATUS_ONLINE = 'online';

    public const STATUS_OFFLINE = 'offline';

    /**
     * Detik tanpa laporan telemetri sebelum PC dianggap offline.
     * 45 detik = margin 4,5 siklus agent (interval 10 dtk) supaya satu-dua
     * POST yang tertahan (429/timeout) tidak membuat status flap.
     */
    public const STALE_SECONDS = 45;

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
        'software_synced_at',
        'processes_synced_at',
    ];

    protected $casts = [
        'cpu_usage' => 'float',
        'ram_usage' => 'float',
        'disk_usage' => 'float',
        'last_seen_at' => 'datetime',
        'software_synced_at' => 'datetime',
        'processes_synced_at' => 'datetime',
    ];

    public function commands(): HasMany
    {
        return $this->hasMany(PcCommand::class);
    }

    public function softwares(): HasMany
    {
        return $this->hasMany(PcSoftware::class);
    }

    public function processes(): HasMany
    {
        return $this->hasMany(PcProcess::class);
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

    /** Perintah pending yang masih berlaku (dalam TTL). */
    public function hasPendingCommand(): bool
    {
        return $this->commands()->pending()->exists();
    }

    public function scopeStale(Builder $query, int $seconds = self::STALE_SECONDS): Builder
    {
        return $query->where(function (Builder $q) use ($seconds) {
            $q->whereNull('last_seen_at')
                ->orWhere('last_seen_at', '<=', now()->subSeconds($seconds));
        });
    }

    /** Path file screenshot terakhir (belum tentu ada di disk). */
    public function screenshotPath(): string
    {
        $safe = preg_replace('/[^A-Za-z0-9\-_.]/', '_', $this->hostname);

        return Storage::disk('local')->path('pc-screenshots/'.$safe.'.jpg');
    }

    public function hasScreenshot(): bool
    {
        return is_file($this->screenshotPath());
    }
}
