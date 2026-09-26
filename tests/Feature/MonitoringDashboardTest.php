<?php

namespace Tests\Feature;

use App\Models\PcMonitor;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MonitoringDashboardTest extends TestCase
{
    use RefreshDatabase;

    private function user(): User
    {
        return User::create(['name' => 'Operator', 'email' => 'op@lab.test', 'password' => 'rahasia123']);
    }

    public function test_dashboard_requires_login(): void
    {
        $this->get('/monitoring-pc')->assertRedirect('/login');
    }

    public function test_dashboard_shows_pc_cards_with_stats_and_auto_reload(): void
    {
        PcMonitor::create([
            'hostname' => 'LAB-PC-01',
            'ip_address' => '192.168.1.10',
            'cpu_usage' => 42.5,
            'ram_usage' => 61.2,
            'disk_usage' => 70.0,
            'active_user' => 'mahasiswa',
            'status' => PcMonitor::STATUS_ONLINE,
            'last_seen_at' => now(),
        ]);

        $this->actingAs($this->user())
            ->get('/monitoring-pc')
            ->assertOk()
            ->assertSee('LAB-PC-01')
            ->assertSee('192.168.1.10')
            ->assertSee('mahasiswa')
            ->assertSee('Online')
            ->assertSee('Rata-rata Penggunaan')
            ->assertSee('location.reload()', false);
    }

    public function test_dashboard_shows_empty_state_when_no_pc_registered(): void
    {
        $this->actingAs($this->user())
            ->get('/monitoring-pc')
            ->assertOk()
            ->assertSee('Belum ada PC terdaftar');
    }
}
