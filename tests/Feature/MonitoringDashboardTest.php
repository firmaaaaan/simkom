<?php

namespace Tests\Feature;

use App\Models\PcMonitor;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MonitoringDashboardTest extends TestCase
{
    use RefreshDatabase;

    private function user(array $permissions = []): User
    {
        $user = User::create([
            'name' => 'Operator',
            'email' => 'op-'.uniqid().'@lab.test',
            'password' => 'rahasia123',
        ]);

        if ($permissions !== []) {
            $user->givePermissionTo($permissions);
        }

        return $user;
    }

    private function pc(array $overrides = []): PcMonitor
    {
        return PcMonitor::create(array_merge([
            'hostname' => 'LAB-PC-01',
            'ip_address' => '192.168.1.10',
            'mac_address' => 'AA:BB:CC:DD:EE:FF',
            'cpu_usage' => 42.5,
            'ram_usage' => 61.2,
            'disk_usage' => 70.0,
            'active_user' => 'mahasiswa',
            'status' => PcMonitor::STATUS_ONLINE,
            'last_seen_at' => now(),
        ], $overrides));
    }

    public function test_dashboard_requires_login(): void
    {
        $this->get('/monitoring-pc')->assertRedirect('/login');
    }

    public function test_dashboard_and_data_require_view_monitoring_permission(): void
    {
        $this->actingAs($this->user())
            ->get('/monitoring-pc')
            ->assertForbidden();

        $this->actingAs($this->user())
            ->get('/monitoring-pc/data')
            ->assertForbidden();
    }

    public function test_dashboard_shows_pc_cards_with_stats_and_polling(): void
    {
        $this->pc();

        $this->actingAs($this->user(['view-monitoring']))
            ->get('/monitoring-pc')
            ->assertOk()
            ->assertSee('LAB-PC-01')
            ->assertSee('192.168.1.10')
            ->assertSee('mahasiswa')
            ->assertSee('Rata-rata Penggunaan')
            // Halaman mem-refresh via polling JSON, bukan reload penuh.
            ->assertSee('/monitoring-pc/data', false);
    }

    public function test_data_endpoint_returns_stats_and_power_flag(): void
    {
        $this->pc();

        $this->actingAs($this->user(['view-monitoring']))
            ->get('/monitoring-pc/data')
            ->assertOk()
            ->assertJson([
                'stats' => ['total' => 1, 'online' => 1, 'offline' => 0],
                'can_power' => false,
            ])
            ->assertJsonPath('pcs.0.hostname', 'LAB-PC-01')
            ->assertJsonPath('pcs.0.status', 'online');

        $this->actingAs($this->user(['view-monitoring', 'control-monitoring']))
            ->get('/monitoring-pc/data')
            ->assertOk()
            ->assertJson(['can_power' => true]);
    }

    public function test_stale_pc_is_reported_offline_without_scheduler(): void
    {
        $this->pc(['hostname' => 'PC-STALE', 'last_seen_at' => now()->subSeconds(45)]);

        $this->actingAs($this->user(['view-monitoring']))
            ->get('/monitoring-pc/data')
            ->assertOk()
            ->assertJson([
                'stats' => ['online' => 0, 'offline' => 1],
            ])
            ->assertJsonPath('pcs.0.status', 'offline');
    }

    public function test_dashboard_shows_empty_state_when_no_pc_registered(): void
    {
        $this->actingAs($this->user(['view-monitoring']))
            ->get('/monitoring-pc')
            ->assertOk()
            ->assertSee('Belum ada PC terdaftar');
    }
}
