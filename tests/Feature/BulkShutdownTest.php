<?php

namespace Tests\Feature;

use App\Models\PcCommand;
use App\Models\PcMonitor;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BulkShutdownTest extends TestCase
{
    use RefreshDatabase;

    private function userWith(string ...$permissions): User
    {
        $role = Role::firstOrCreate(['name' => 'admin-bulk-shutdown'], ['label' => 'Admin Bulk Shutdown']);

        foreach ($permissions as $name) {
            $role->permissions()->syncWithoutDetaching(
                Permission::firstOrCreate(['name' => $name], ['label' => $name])
            );
        }

        $user = User::create([
            'name' => 'Admin Bulk',
            'email' => 'admin-bulk@simlab.test',
            'password' => 'rahasia123',
        ]);
        $user->roles()->attach($role);

        return $user;
    }

    private function pc(string $hostname, string $status = 'online', array $overrides = []): PcMonitor
    {
        return PcMonitor::create(array_merge([
            'hostname' => $hostname,
            'ip_address' => '127.0.0.1',
            'status' => $status,
            'last_seen_at' => $status === 'online' ? now() : now()->subMinutes(10),
        ], $overrides));
    }

    public function test_shutdown_all_requires_control_permission(): void
    {
        $user = $this->userWith('view-monitoring');

        $this->actingAs($user)
            ->postJson(route('monitoring-pc.shutdown-all'))
            ->assertForbidden();
    }

    public function test_shutdown_all_creates_pending_command_for_every_online_pc(): void
    {
        $this->pc('PC-A');
        $this->pc('PC-B');
        $user = $this->userWith('control-monitoring');

        $this->actingAs($user)
            ->postJson(route('monitoring-pc.shutdown-all'))
            ->assertOk()
            ->assertJsonPath('ok', true);

        $this->assertSame(2, PcCommand::where('action', PcCommand::ACTION_SHUTDOWN)
            ->where('status', PcCommand::STATUS_PENDING)
            ->count());
        $this->assertSame($user->id, PcCommand::first()->requested_by);
    }

    public function test_shutdown_all_skips_offline_pcs(): void
    {
        $this->pc('PC-ON');
        $this->pc('PC-OFF', 'offline');
        $user = $this->userWith('control-monitoring');

        $this->actingAs($user)
            ->postJson(route('monitoring-pc.shutdown-all'))
            ->assertOk()
            ->assertJsonPath('ok', true);

        $this->assertSame(1, PcCommand::count());
        $this->assertDatabaseHas('pc_commands', ['pc_monitor_id' => PcMonitor::where('hostname', 'PC-ON')->first()->id]);

        $response = $this->actingAs($user)->postJson(route('monitoring-pc.shutdown-all'));
        // PC-OFF tetap offline, PC-ON kini punya pending → 409
        $response->assertStatus(409);
    }

    public function test_shutdown_all_skips_pcs_with_pending_command(): void
    {
        $pcA = $this->pc('PC-A');
        $this->pc('PC-B');

        PcCommand::create([
            'pc_monitor_id' => $pcA->id,
            'action' => PcCommand::ACTION_RESTART,
            'status' => PcCommand::STATUS_PENDING,
        ]);

        $user = $this->userWith('control-monitoring');

        $this->actingAs($user)
            ->postJson(route('monitoring-pc.shutdown-all'))
            ->assertOk()
            ->assertJsonPath('ok', true);

        // Hanya PC-B yang mendapat shutdown; PC-A tetap dengan restart lamanya
        $this->assertSame(1, PcCommand::where('action', PcCommand::ACTION_SHUTDOWN)->count());
        $this->assertSame(2, PcCommand::count());
    }

    public function test_shutdown_all_rejects_when_no_online_pc(): void
    {
        $this->pc('PC-OFF', 'offline');
        $user = $this->userWith('control-monitoring');

        $this->actingAs($user)
            ->postJson(route('monitoring-pc.shutdown-all'))
            ->assertStatus(409)
            ->assertJsonPath('ok', false);

        $this->assertSame(0, PcCommand::count());
    }

    public function test_shutdown_all_rejects_when_all_online_have_pending(): void
    {
        $pc = $this->pc('PC-A');
        PcCommand::create([
            'pc_monitor_id' => $pc->id,
            'action' => PcCommand::ACTION_LOCK,
            'status' => PcCommand::STATUS_PENDING,
        ]);
        $user = $this->userWith('control-monitoring');

        $this->actingAs($user)
            ->postJson(route('monitoring-pc.shutdown-all'))
            ->assertStatus(409)
            ->assertJsonPath('ok', false);

        $this->assertSame(1, PcCommand::count());
    }
}
