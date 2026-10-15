<?php

namespace Tests\Feature;

use App\Models\PcMonitor;
use App\Models\PcPowerCommand;
use App\Models\User;
use App\Services\WakeOnLanService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class PowerControlTest extends TestCase
{
    use RefreshDatabase;

    private function user(array $permissions): User
    {
        $user = User::create([
            'name' => 'Operator',
            'email' => 'op-'.uniqid().'@lab.test',
            'password' => 'rahasia123',
        ]);
        $user->givePermissionTo($permissions);

        return $user;
    }

    private function pc(array $overrides = []): PcMonitor
    {
        return PcMonitor::create(array_merge([
            'hostname' => 'LAB-PC-01',
            'ip_address' => '192.168.1.10',
            'mac_address' => 'AA:BB:CC:DD:EE:FF',
            'status' => PcMonitor::STATUS_ONLINE,
            'last_seen_at' => now(),
        ], $overrides));
    }

    public function test_power_actions_require_control_permission(): void
    {
        $pc = $this->pc();

        $this->actingAs($this->user(['view-monitoring']))
            ->post("/monitoring-pc/{$pc->id}/power-off")
            ->assertForbidden();

        $this->actingAs($this->user(['view-monitoring']))
            ->post("/monitoring-pc/{$pc->id}/power-on")
            ->assertForbidden();

        $this->assertDatabaseCount('pc_power_commands', 0);
    }

    public function test_power_off_creates_pending_command_for_online_pc(): void
    {
        $user = $this->user(['view-monitoring', 'control-monitoring']);
        $pc = $this->pc();

        $this->actingAs($user)
            ->post("/monitoring-pc/{$pc->id}/power-off")
            ->assertOk()
            ->assertJson(['ok' => true]);

        $this->assertDatabaseHas('pc_power_commands', [
            'pc_monitor_id' => $pc->id,
            'action' => PcPowerCommand::ACTION_SHUTDOWN,
            'status' => PcPowerCommand::STATUS_PENDING,
            'requested_by' => $user->id,
        ]);
    }

    public function test_power_off_rejects_offline_pc(): void
    {
        $pc = $this->pc([
            'status' => PcMonitor::STATUS_OFFLINE,
            'last_seen_at' => now()->subMinutes(5),
        ]);

        $this->actingAs($this->user(['control-monitoring']))
            ->post("/monitoring-pc/{$pc->id}/power-off")
            ->assertStatus(409);

        $this->assertDatabaseCount('pc_power_commands', 0);
    }

    public function test_power_off_rejects_duplicate_pending_command(): void
    {
        $pc = $this->pc();
        PcPowerCommand::create([
            'pc_monitor_id' => $pc->id,
            'action' => PcPowerCommand::ACTION_SHUTDOWN,
            'status' => PcPowerCommand::STATUS_PENDING,
        ]);

        $this->actingAs($this->user(['control-monitoring']))
            ->post("/monitoring-pc/{$pc->id}/power-off")
            ->assertStatus(409);

        $this->assertDatabaseCount('pc_power_commands', 1);
    }

    public function test_power_on_sends_wake_on_lan_packet(): void
    {
        $pc = $this->pc([
            'status' => PcMonitor::STATUS_OFFLINE,
            'last_seen_at' => now()->subMinutes(5),
        ]);

        $wol = Mockery::mock(WakeOnLanService::class);
        $wol->shouldReceive('send')->once()->with('AA:BB:CC:DD:EE:FF', '192.168.1.10');
        $this->app->instance(WakeOnLanService::class, $wol);

        $this->actingAs($this->user(['control-monitoring']))
            ->post("/monitoring-pc/{$pc->id}/power-on")
            ->assertOk()
            ->assertJson(['ok' => true]);
    }

    public function test_power_on_rejects_online_pc(): void
    {
        $pc = $this->pc();

        $this->actingAs($this->user(['control-monitoring']))
            ->post("/monitoring-pc/{$pc->id}/power-on")
            ->assertStatus(409);
    }

    public function test_power_on_rejects_pc_without_mac_address(): void
    {
        $pc = $this->pc([
            'mac_address' => null,
            'status' => PcMonitor::STATUS_OFFLINE,
            'last_seen_at' => now()->subMinutes(5),
        ]);

        $this->actingAs($this->user(['control-monitoring']))
            ->post("/monitoring-pc/{$pc->id}/power-on")
            ->assertStatus(409)
            ->assertJsonPath('message', 'MAC address LAB-PC-01 belum diketahui. Jalankan agent dulu.');
    }

    public function test_power_off_command_is_visible_to_agent_polling(): void
    {
        $pc = $this->pc();
        config(['services.monitoring.key' => 'test-monitoring-key']);

        $this->actingAs($this->user(['control-monitoring']))
            ->post("/monitoring-pc/{$pc->id}/power-off")
            ->assertOk();

        $this->withHeaders(['X-API-KEY' => 'test-monitoring-key'])
            ->getJson('/api/v1/commands?hostname=LAB-PC-01')
            ->assertOk()
            ->assertJsonCount(1, 'commands')
            ->assertJsonPath('commands.0.action', 'shutdown');
    }
}
