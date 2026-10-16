<?php

namespace Tests\Feature;

use App\Models\PcMonitor;
use App\Models\PcPowerCommand;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Services\WakeOnLanService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class PowerControlTest extends TestCase
{
    use RefreshDatabase;

    private function userWith(string ...$permissions): User
    {
        $role = Role::firstOrCreate(['name' => 'operator-monitor'], ['label' => 'Operator Monitoring']);

        foreach ($permissions as $name) {
            $permission = Permission::firstOrCreate(['name' => $name], ['label' => $name]);
            $role->permissions()->syncWithoutDetaching($permission->id);
        }

        $user = User::create([
            'name' => 'Operator',
            'email' => 'operator+'.uniqid().'@monitor.test',
            'password' => 'rahasia123',
        ]);
        $user->roles()->attach($role);

        // Attach pivot langsung tidak menyentuh cache spatie — flush manual.
        app(PermissionRegistrar::class)->forgetCachedPermissions();

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

    public function test_power_routes_require_control_permission(): void
    {
        $viewer = $this->userWith('view-monitoring');
        $pc = $this->pc();

        $this->actingAs($viewer)
            ->postJson(route('monitoring-pc.power-on', $pc))
            ->assertForbidden();
        $this->actingAs($viewer)
            ->postJson(route('monitoring-pc.power-off', $pc))
            ->assertForbidden();
    }

    public function test_power_on_rejected_when_pc_online(): void
    {
        $user = $this->userWith('view-monitoring', 'control-monitoring');
        $pc = $this->pc(['last_seen_at' => now()]);

        $this->actingAs($user)
            ->postJson(route('monitoring-pc.power-on', $pc))
            ->assertStatus(409)
            ->assertJson(['ok' => false]);
    }

    public function test_power_on_rejected_without_mac_address(): void
    {
        $user = $this->userWith('view-monitoring', 'control-monitoring');
        $pc = $this->pc([
            'status' => PcMonitor::STATUS_OFFLINE,
            'last_seen_at' => now()->subMinutes(5),
            'mac_address' => null,
        ]);

        $this->actingAs($user)
            ->postJson(route('monitoring-pc.power-on', $pc))
            ->assertStatus(409)
            ->assertJson(['ok' => false]);
    }

    public function test_power_on_sends_wake_on_lan_packet(): void
    {
        $user = $this->userWith('view-monitoring', 'control-monitoring');
        $pc = $this->pc([
            'status' => PcMonitor::STATUS_OFFLINE,
            'last_seen_at' => now()->subMinutes(5),
        ]);

        $wol = Mockery::mock(WakeOnLanService::class);
        $wol->shouldReceive('send')->once()->with('AA:BB:CC:DD:EE:FF', '192.168.1.10');
        app()->instance(WakeOnLanService::class, $wol);

        $this->actingAs($user)
            ->postJson(route('monitoring-pc.power-on', $pc))
            ->assertOk()
            ->assertJson(['ok' => true]);
    }

    public function test_power_on_failure_returns_500(): void
    {
        $user = $this->userWith('view-monitoring', 'control-monitoring');
        $pc = $this->pc([
            'status' => PcMonitor::STATUS_OFFLINE,
            'last_seen_at' => now()->subMinutes(5),
        ]);

        $wol = Mockery::mock(WakeOnLanService::class);
        $wol->shouldReceive('send')->once()->andThrow(new \RuntimeException('Gagal mengirim paket Wake-on-LAN.'));
        app()->instance(WakeOnLanService::class, $wol);

        $this->actingAs($user)
            ->postJson(route('monitoring-pc.power-on', $pc))
            ->assertStatus(500)
            ->assertJson(['ok' => false]);
    }

    public function test_power_off_creates_pending_command(): void
    {
        $user = $this->userWith('view-monitoring', 'control-monitoring');
        $pc = $this->pc();

        $this->actingAs($user)
            ->postJson(route('monitoring-pc.power-off', $pc))
            ->assertOk()
            ->assertJson(['ok' => true]);

        $this->assertDatabaseHas('pc_power_commands', [
            'pc_monitor_id' => $pc->id,
            'action' => PcPowerCommand::ACTION_SHUTDOWN,
            'status' => PcPowerCommand::STATUS_PENDING,
            'requested_by' => $user->id,
        ]);
    }

    public function test_power_off_rejected_when_pc_offline(): void
    {
        $user = $this->userWith('view-monitoring', 'control-monitoring');
        $pc = $this->pc([
            'status' => PcMonitor::STATUS_OFFLINE,
            'last_seen_at' => now()->subMinutes(5),
        ]);

        $this->actingAs($user)
            ->postJson(route('monitoring-pc.power-off', $pc))
            ->assertStatus(409);

        $this->assertDatabaseCount('pc_power_commands', 0);
    }

    public function test_power_off_rejected_while_command_pending(): void
    {
        $user = $this->userWith('view-monitoring', 'control-monitoring');
        $pc = $this->pc();
        PcPowerCommand::create([
            'pc_monitor_id' => $pc->id,
            'action' => PcPowerCommand::ACTION_SHUTDOWN,
            'status' => PcPowerCommand::STATUS_PENDING,
        ]);

        $this->actingAs($user)
            ->postJson(route('monitoring-pc.power-off', $pc))
            ->assertStatus(409);

        $this->assertSame(1, PcPowerCommand::count());
    }
}
