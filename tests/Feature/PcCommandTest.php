<?php

namespace Tests\Feature;

use App\Models\PcCommand;
use App\Models\PcMonitor;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class PcCommandTest extends TestCase
{
    use RefreshDatabase;

    private function userWith(string ...$permissions): User
    {
        $role = Role::firstOrCreate(['name' => 'operator-pc-command'], ['label' => 'Operator PC Command']);

        foreach ($permissions as $name) {
            $permission = Permission::firstOrCreate(['name' => $name], ['label' => $name]);
            $role->permissions()->syncWithoutDetaching($permission->id);
        }

        $user = User::create([
            'name' => 'Operator',
            'email' => 'operator+'.uniqid().'@command.test',
            'password' => 'rahasia123',
        ]);
        $user->roles()->attach($role);

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        return $user;
    }

    private function pc(array $overrides = []): PcMonitor
    {
        return PcMonitor::create(array_merge([
            'hostname' => 'LAB-PC-'.uniqid(),
            'ip_address' => '192.168.1.10',
            'mac_address' => 'AA:BB:CC:DD:EE:FF',
            'status' => PcMonitor::STATUS_ONLINE,
            'last_seen_at' => now(),
        ], $overrides));
    }

    public function test_command_route_requires_control_permission(): void
    {
        $viewer = $this->userWith('view-monitoring');
        $pc = $this->pc();

        $this->actingAs($viewer)
            ->postJson(route('monitoring-pc.command', $pc), ['action' => 'lock'])
            ->assertForbidden();
    }

    public function test_all_supported_actions_create_pending_command(): void
    {
        $user = $this->userWith('view-monitoring', 'control-monitoring');

        foreach (PcCommand::ACTIONS as $action) {
            $pc = $this->pc();

            $this->actingAs($user)
                ->postJson(route('monitoring-pc.command', $pc), ['action' => $action])
                ->assertOk()
                ->assertJson(['ok' => true]);

            $this->assertDatabaseHas('pc_commands', [
                'pc_monitor_id' => $pc->id,
                'action' => $action,
                'status' => PcCommand::STATUS_PENDING,
                'requested_by' => $user->id,
            ]);
        }
    }

    public function test_unknown_action_rejected(): void
    {
        $user = $this->userWith('view-monitoring', 'control-monitoring');
        $pc = $this->pc();

        $this->actingAs($user)
            ->postJson(route('monitoring-pc.command', $pc), ['action' => 'format'])
            ->assertStatus(422)
            ->assertJsonValidationErrors('action');
    }

    public function test_command_rejected_when_pc_offline(): void
    {
        $user = $this->userWith('view-monitoring', 'control-monitoring');
        $pc = $this->pc([
            'status' => PcMonitor::STATUS_OFFLINE,
            'last_seen_at' => now()->subMinutes(5),
        ]);

        $this->actingAs($user)
            ->postJson(route('monitoring-pc.command', $pc), ['action' => 'restart'])
            ->assertStatus(409);

        $this->assertDatabaseCount('pc_commands', 0);
    }

    public function test_command_rejected_while_pending(): void
    {
        $user = $this->userWith('view-monitoring', 'control-monitoring');
        $pc = $this->pc();
        PcCommand::create([
            'pc_monitor_id' => $pc->id,
            'action' => PcCommand::ACTION_SHUTDOWN,
            'status' => PcCommand::STATUS_PENDING,
        ]);

        $this->actingAs($user)
            ->postJson(route('monitoring-pc.command', $pc), ['action' => 'lock'])
            ->assertStatus(409);

        $this->assertSame(1, PcCommand::count());
    }

    public function test_screenshot_route_404_before_any_capture(): void
    {
        $user = $this->userWith('view-monitoring');
        $pc = $this->pc();

        $this->actingAs($user)
            ->get(route('monitoring-pc.screenshot', $pc))
            ->assertNotFound();
    }

    public function test_screenshot_route_serves_saved_file(): void
    {
        Storage::fake('local');

        $user = $this->userWith('view-monitoring');
        $pc = $this->pc();
        Storage::disk('local')->put(
            'pc-screenshots/'.$pc->hostname.'.jpg',
            base64_decode('/9j/4AAQSkZJRgABAQAAAQABAAD/2wBDAAgGBgcGBQgHBwcJCQgKDBQNDAsLDBkSEw8UHRofHh0aHBwgJC4nICIsIxwcKDcpLDAxNDQ0Hyc5PTgyPC4zNDL/wAALCAABAAEBAREA/8QAFAABAAAAAAAAAAAAAAAAAAAACf/EABQQAQAAAAAAAAAAAAAAAAAAAAD/2gAIAQEAAD8AVN//2Q==')
        );
        $pc->touch();

        $this->actingAs($user)
            ->get(route('monitoring-pc.screenshot', $pc))
            ->assertOk()
            ->assertHeader('Content-Type', 'image/jpeg');
    }

    public function test_payload_last_command_null_without_history(): void
    {
        $user = $this->userWith('view-monitoring');
        $this->pc();

        $this->actingAs($user)
            ->getJson(route('monitoring-pc.data'))
            ->assertOk()
            ->assertJsonPath('pcs.0.last_command', null);
    }

    public function test_payload_last_command_shows_latest_failed_with_message(): void
    {
        $user = $this->userWith('view-monitoring');
        $pc = $this->pc();

        $earlier = PcCommand::create([
            'pc_monitor_id' => $pc->id,
            'action' => PcCommand::ACTION_SHUTDOWN,
            'status' => PcCommand::STATUS_COMPLETED,
            'executed_at' => now()->subMinutes(2),
        ]);
        $earlier->created_at = now()->subMinutes(2);
        $earlier->save();

        PcCommand::create([
            'pc_monitor_id' => $pc->id,
            'action' => PcCommand::ACTION_LOCK,
            'status' => PcCommand::STATUS_FAILED,
            'message' => 'tidak ada sesi user aktif',
            'executed_at' => now(),
        ]);

        $this->actingAs($user)
            ->getJson(route('monitoring-pc.data'))
            ->assertOk()
            ->assertJsonPath('pcs.0.last_command.action', 'lock')
            ->assertJsonPath('pcs.0.last_command.action_label', 'mengunci layar')
            ->assertJsonPath('pcs.0.last_command.status', 'failed')
            ->assertJsonPath('pcs.0.last_command.message', 'tidak ada sesi user aktif')
            ->assertJsonPath('pcs.0.last_command.at', now()->toIso8601String());
    }
}
