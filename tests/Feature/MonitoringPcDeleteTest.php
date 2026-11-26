<?php

namespace Tests\Feature;

use App\Models\PcCommand;
use App\Models\PcMonitor;
use App\Models\PcProcess;
use App\Models\PcSoftware;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class MonitoringPcDeleteTest extends TestCase
{
    use RefreshDatabase;

    private function userWith(string ...$permissions): User
    {
        $role = Role::firstOrCreate(['name' => 'viewer-monitor'], ['label' => 'Viewer Monitoring']);

        foreach ($permissions as $name) {
            $permission = Permission::firstOrCreate(['name' => $name], ['label' => $name]);
            $role->permissions()->syncWithoutDetaching($permission->id);
        }

        $user = User::create([
            'name' => 'Pemantau',
            'email' => 'viewer+'.uniqid().'@monitor.test',
            'password' => 'rahasia123',
        ]);
        $user->roles()->attach($role);

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        return $user;
    }

    private function pc(array $overrides = []): PcMonitor
    {
        return PcMonitor::create(array_merge([
            'hostname' => 'LAB-PC-01',
            'ip_address' => '127.0.0.1',
            'status' => 'offline',
        ], $overrides));
    }

    public function test_delete_forbidden_without_delete_monitoring_permission(): void
    {
        $user = $this->userWith('view-monitoring');
        $pc = $this->pc();

        $this->actingAs($user)
            ->deleteJson(route('monitoring-pc.destroy', $pc))
            ->assertForbidden();

        $this->assertDatabaseHas('pc_monitors', ['id' => $pc->id]);
    }

    public function test_delete_removes_pc_and_cascades_related_data(): void
    {
        $user = $this->userWith('view-monitoring', 'delete-monitoring');
        $pc = $this->pc();

        PcCommand::create([
            'pc_monitor_id' => $pc->id,
            'action' => 'shutdown',
            'status' => 'pending',
        ]);
        PcSoftware::create([
            'pc_monitor_id' => $pc->id,
            'name' => 'Google Chrome',
        ]);
        PcProcess::create([
            'pc_monitor_id' => $pc->id,
            'name' => 'chrome.exe',
        ]);

        $this->actingAs($user)
            ->deleteJson(route('monitoring-pc.destroy', $pc))
            ->assertOk()
            ->assertJson(['ok' => true]);

        $this->assertDatabaseMissing('pc_monitors', ['id' => $pc->id]);
        $this->assertDatabaseCount('pc_commands', 0);
        $this->assertDatabaseCount('pc_softwares', 0);
        $this->assertDatabaseCount('pc_processes', 0);
    }

    public function test_delete_removes_screenshot_file(): void
    {
        $user = $this->userWith('view-monitoring', 'delete-monitoring');
        $pc = $this->pc(['hostname' => 'LAB-PC-01']);

        $disk = Storage::disk('local');
        $disk->put('pc-screenshots/LAB-PC-01.jpg', 'fake-image-bytes');
        $this->assertTrue($disk->exists('pc-screenshots/LAB-PC-01.jpg'));

        $this->actingAs($user)
            ->deleteJson(route('monitoring-pc.destroy', $pc))
            ->assertOk();

        $this->assertFalse($disk->exists('pc-screenshots/LAB-PC-01.jpg'));
    }

    public function test_delete_returns_success_json(): void
    {
        $user = $this->userWith('view-monitoring', 'delete-monitoring');
        $pc = $this->pc(['hostname' => 'PC-TES']);

        $this->actingAs($user)
            ->deleteJson(route('monitoring-pc.destroy', $pc))
            ->assertOk()
            ->assertJson(['ok' => true, 'message' => 'PC PC-TES dihapus dari monitoring.']);
    }

    public function test_payload_exposes_can_delete_flag(): void
    {
        $user = $this->userWith('view-monitoring', 'delete-monitoring');

        $this->actingAs($user)
            ->getJson(route('monitoring-pc.data'))
            ->assertOk()
            ->assertJsonPath('can_delete', true);
    }
}
