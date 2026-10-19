<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Rate limit agent per-hostname, bukan per-IP: beberapa PC bisa
        // mencapai server dari IP sumber yang sama (NAT/proxy), sehingga
        // kuota per-IP membuat antar agent saling menekan (telemetri
        // ditolak 429 → last_seen tersendat → status online/offline flap).
        RateLimiter::for('agent-telemetry', function (Request $request) {
            return Limit::perMinute(60)->by('telemetry:'.$this->agentKey($request->input('hostname'), $request));
        });

        RateLimiter::for('agent-commands', function (Request $request) {
            return Limit::perMinute(60)->by('commands:'.$this->agentKey($request->query('hostname'), $request));
        });

        RateLimiter::for('agent-screenshots', function (Request $request) {
            return Limit::perMinute(6)->by('screenshots:'.$this->agentKey($request->input('hostname'), $request));
        });

        RateLimiter::for('agent-software', function (Request $request) {
            return Limit::perMinute(3)->by('software:'.$this->agentKey($request->input('hostname'), $request));
        });

        RateLimiter::for('agent-processes', function (Request $request) {
            return Limit::perMinute(6)->by('processes:'.$this->agentKey($request->input('hostname'), $request));
        });
    }

    /** Kunci rate limit: hostname agent (fallback IP bila tidak ada). */
    private function agentKey(mixed $hostname, Request $request): string
    {
        $hostname = is_string($hostname) ? trim($hostname) : '';

        return $hostname !== '' ? strtolower($hostname) : (string) $request->ip();
    }
}
