<?php

namespace Tests\Feature;

use App\Models\PcMonitor;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class MonitoringDashboardTest extends TestCase
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

        // Attach pivot langsung tidak menyentuh cache spatie — flush manual.
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        return $user;
    }

    public function test_page_requires_login(): void
    {
        $this->get(route('monitoring-pc.index'))->assertRedirect(route('login'));
    }

    public function test_page_forbidden_without_view_permission(): void
    {
        $user = User::create([
            'name' => 'Tanpa Akses',
            'email' => 'noaccess@monitor.test',
            'password' => 'rahasia123',
        ]);

        $this->actingAs($user)
            ->get(route('monitoring-pc.index'))
            ->assertForbidden();
    }

    public function test_page_shows_summary_and_pc_list(): void
    {
        PcMonitor::create([
            'hostname' => 'LAB-PC-01',
            'ip_address' => '192.168.1.10',
            'mac_address' => 'AA:BB:CC:DD:EE:FF',
            'cpu_usage' => 40,
            'ram_usage' => 55,
            'disk_usage' => 70,
            'status' => PcMonitor::STATUS_ONLINE,
            'last_seen_at' => now(),
        ]);
        PcMonitor::create([
            'hostname' => 'LAB-PC-02',
            'ip_address' => '192.168.1.11',
            'status' => PcMonitor::STATUS_OFFLINE,
            'last_seen_at' => now()->subMinutes(10),
        ]);

        $user = $this->userWith('view-monitoring', 'control-monitoring');

        $this->actingAs($user)
            ->get(route('monitoring-pc.index'))
            ->assertOk()
            ->assertSee('Monitoring PC')
            ->assertSee('LAB-PC-01')
            ->assertSee('LAB-PC-02')
            ->assertSee('Rata-rata Penggunaan');
    }

    public function test_data_endpoint_returns_stats_and_can_power(): void
    {
        PcMonitor::create([
            'hostname' => 'LAB-PC-01',
            'ip_address' => '192.168.1.10',
            'status' => PcMonitor::STATUS_ONLINE,
            'last_seen_at' => now(),
        ]);
        PcMonitor::create([
            'hostname' => 'LAB-PC-02',
            'ip_address' => '192.168.1.11',
            'status' => PcMonitor::STATUS_ONLINE,
            'last_seen_at' => now()->subMinutes(10), // basi → offline efektif
        ]);

        $user = $this->userWith('view-monitoring');

        $response = $this->actingAs($user)
            ->getJson(route('monitoring-pc.data'))
            ->assertOk();

        $response->assertJsonPath('stats.total', 2)
            ->assertJsonPath('stats.online', 1)
            ->assertJsonPath('stats.offline', 1)
            ->assertJsonPath('can_power', false)
            ->assertJsonPath('pcs.0.software_count', 0)
            ->assertJsonCount(2, 'pcs');

        // Pengguna dengan control-monitoring mendapat can_power true.
        $controller = $this->userWith('view-monitoring', 'control-monitoring');
        $this->actingAs($controller)
            ->getJson(route('monitoring-pc.data'))
            ->assertJsonPath('can_power', true);
    }

    public function test_empty_state_shows_when_no_agent_reported(): void
    {
        $user = $this->userWith('view-monitoring');

        $this->actingAs($user)
            ->get(route('monitoring-pc.index'))
            ->assertOk()
            ->assertSee('Belum ada PC yang terhubung.');
    }

    public function test_software_endpoint_lists_inventory(): void
    {
        $pc = PcMonitor::create([
            'hostname' => 'LAB-PC-01',
            'ip_address' => '192.168.1.10',
            'status' => PcMonitor::STATUS_ONLINE,
            'last_seen_at' => now(),
            'software_synced_at' => now(),
        ]);
        $pc->softwares()->create(['name' => 'Google Chrome', 'version' => '120.0', 'publisher' => 'Google LLC']);
        $pc->softwares()->create(['name' => '7-Zip', 'version' => '23.01']);

        $user = $this->userWith('view-monitoring');

        $this->actingAs($user)
            ->getJson(route('monitoring-pc.software', $pc))
            ->assertOk()
            ->assertJsonPath('hostname', 'LAB-PC-01')
            ->assertJsonCount(2, 'items')
            ->assertJsonPath('items.0.name', '7-Zip')
            ->assertJsonPath('items.1.name', 'Google Chrome');
    }

    public function test_software_endpoint_marks_running_apps(): void
    {
        $pc = PcMonitor::create([
            'hostname' => 'LAB-PC-01',
            'ip_address' => '192.168.1.10',
            'status' => PcMonitor::STATUS_ONLINE,
            'last_seen_at' => now(),
            'software_synced_at' => now(),
            'processes_synced_at' => now(),
        ]);
        $pc->softwares()->create(['name' => 'Google Chrome', 'version' => '120.0']);
        $pc->softwares()->create(['name' => '7-Zip', 'version' => '23.01']);

        // chrome.exe berjalan → Google Chrome harus ditandai running.
        $pc->processes()->create(['name' => 'chrome.exe', 'window_title' => 'Google Chrome']);
        $pc->processes()->create(['name' => 'explorer.exe', 'window_title' => 'Program Manager']);

        $user = $this->userWith('view-monitoring');

        $response = $this->actingAs($user)
            ->getJson(route('monitoring-pc.software', $pc))
            ->assertOk()
            ->assertJsonPath('processes_synced_at', $pc->processes_synced_at->toIso8601String());

        // 7-Zip (sorted first) tidak berjalan; Google Chrome berjalan.
        $response->assertJsonPath('items.0.name', '7-Zip')
            ->assertJsonPath('items.0.running', false)
            ->assertJsonPath('items.1.name', 'Google Chrome')
            ->assertJsonPath('items.1.running', true);
    }
}
