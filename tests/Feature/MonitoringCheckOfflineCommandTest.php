<?php

namespace Tests\Feature;

use App\Models\PcMonitor;
use App\Models\PcPowerCommand;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MonitoringCheckOfflineCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_marks_stale_pcs_offline_and_expires_old_commands(): void
    {
        $online = PcMonitor::create([
            'hostname' => 'LAB-PC-ON',
            'ip_address' => '192.168.1.10',
            'status' => PcMonitor::STATUS_ONLINE,
            'last_seen_at' => now(),
        ]);
        $stale = PcMonitor::create([
            'hostname' => 'LAB-PC-OFF',
            'ip_address' => '192.168.1.11',
            'status' => PcMonitor::STATUS_ONLINE,
            'last_seen_at' => now()->subSeconds(120),
        ]);

        $freshCommand = PcPowerCommand::create([
            'pc_monitor_id' => $online->id,
            'action' => PcPowerCommand::ACTION_SHUTDOWN,
            'status' => PcPowerCommand::STATUS_PENDING,
        ]);
        $oldCommand = PcPowerCommand::create([
            'pc_monitor_id' => $stale->id,
            'action' => PcPowerCommand::ACTION_SHUTDOWN,
            'status' => PcPowerCommand::STATUS_PENDING,
        ]);
        // created_at tidak di-fillable — atur langsung lalu save.
        $oldCommand->created_at = now()->subSeconds(PcPowerCommand::TTL_SECONDS + 30);
        $oldCommand->save();

        $this->artisan('monitoring:check-offline')
            ->expectsOutputToContain('1 PC ditandai offline, 1 perintah power kedaluwarsa')
            ->assertSuccessful();

        $this->assertSame(PcMonitor::STATUS_OFFLINE, $stale->fresh()->status);
        $this->assertSame(PcMonitor::STATUS_ONLINE, $online->fresh()->status);

        $this->assertSame(PcPowerCommand::STATUS_FAILED, $oldCommand->fresh()->status);
        $this->assertSame(PcPowerCommand::STATUS_PENDING, $freshCommand->fresh()->status);
    }
}
