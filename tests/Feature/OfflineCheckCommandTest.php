<?php

namespace Tests\Feature;

use App\Models\PcMonitor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OfflineCheckCommandTest extends TestCase
{
    use RefreshDatabase;

    private function pc(array $overrides = []): PcMonitor
    {
        return PcMonitor::create(array_merge([
            'hostname' => 'LAB-PC-01',
            'ip_address' => '192.168.1.10',
            'cpu_usage' => 10,
            'ram_usage' => 20,
            'disk_usage' => 30,
            'status' => PcMonitor::STATUS_ONLINE,
            'last_seen_at' => now(),
        ], $overrides));
    }

    public function test_stale_online_pc_is_marked_offline_after_30_seconds(): void
    {
        $stale = $this->pc(['hostname' => 'PC-STALE', 'last_seen_at' => now()->subSeconds(40)]);
        $fresh = $this->pc(['hostname' => 'PC-FRESH', 'last_seen_at' => now()->subSeconds(5)]);
        $neverSeen = $this->pc(['hostname' => 'PC-NEVER', 'last_seen_at' => null]);
        $alreadyOffline = $this->pc([
            'hostname' => 'PC-OFFLINE',
            'status' => PcMonitor::STATUS_OFFLINE,
            'last_seen_at' => now()->subSeconds(120),
        ]);

        $this->artisan('monitoring:check-offline')->assertSuccessful();

        $this->assertSame(PcMonitor::STATUS_OFFLINE, $stale->fresh()->status);
        $this->assertSame(PcMonitor::STATUS_ONLINE, $fresh->fresh()->status);
        $this->assertSame(PcMonitor::STATUS_OFFLINE, $neverSeen->fresh()->status);
        $this->assertSame(PcMonitor::STATUS_OFFLINE, $alreadyOffline->fresh()->status);
    }

    public function test_threshold_can_be_overridden_with_seconds_option(): void
    {
        $pc = $this->pc(['last_seen_at' => now()->subSeconds(20)]);

        $this->artisan('monitoring:check-offline', ['--seconds' => 10])->assertSuccessful();

        $this->assertSame(PcMonitor::STATUS_OFFLINE, $pc->fresh()->status);
    }
}
