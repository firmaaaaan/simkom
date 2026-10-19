<?php

namespace Tests\Feature;

use App\Models\PcMonitor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TelemetryApiTest extends TestCase
{
    use RefreshDatabase;

    private function withApiKey(string $key = 'test-key'): void
    {
        config()->set('services.monitoring.api_key', $key);
    }

    private function payload(array $overrides = []): array
    {
        return array_merge([
            'hostname' => 'LAB-PC-01',
            'ip_address' => '192.168.1.10',
            'mac_address' => 'AA:BB:CC:DD:EE:FF',
            'cpu_usage' => 42.5,
            'ram_usage' => 61.2,
            'disk_usage' => 70.0,
            'active_user' => 'budi',
        ], $overrides);
    }

    public function test_telemetry_rejected_without_api_key(): void
    {
        $this->withApiKey();

        $this->postJson(route('telemetry.store'), $this->payload())
            ->assertStatus(401);

        $this->assertDatabaseCount('pc_monitors', 0);
    }

    public function test_telemetry_rejected_with_wrong_api_key(): void
    {
        $this->withApiKey('kunci-asli');

        $this->postJson(route('telemetry.store'), $this->payload(), ['X-API-KEY' => 'salah'])
            ->assertStatus(401);

        $this->assertDatabaseCount('pc_monitors', 0);
    }

    public function test_telemetry_rejected_when_server_key_not_configured(): void
    {
        // TestCase mengosongkan api key secara default — fitur dianggap belum di-set.
        $this->postJson(route('telemetry.store'), $this->payload(), ['X-API-KEY' => 'apa-pun'])
            ->assertStatus(503);

        $this->assertDatabaseCount('pc_monitors', 0);
    }

    public function test_valid_telemetry_creates_monitor_row(): void
    {
        $this->withApiKey();

        $this->postJson(route('telemetry.store'), $this->payload(), ['X-API-KEY' => 'test-key'])
            ->assertOk()
            ->assertJson(['ok' => true]);

        $this->assertDatabaseHas('pc_monitors', [
            'hostname' => 'LAB-PC-01',
            'ip_address' => '192.168.1.10',
            'mac_address' => 'AA:BB:CC:DD:EE:FF',
            'active_user' => 'budi',
            'status' => 'online',
        ]);
        $this->assertNotNull(PcMonitor::first()->last_seen_at);
    }

    public function test_telemetry_upserts_by_hostname_and_reonline_offline_pc(): void
    {
        $this->withApiKey();
        PcMonitor::create([
            'hostname' => 'LAB-PC-01',
            'ip_address' => '192.168.1.10',
            'status' => 'offline',
            'last_seen_at' => now()->subMinutes(5),
        ]);

        $this->postJson(route('telemetry.store'), $this->payload(['cpu_usage' => 88]), ['X-API-KEY' => 'test-key'])
            ->assertOk();

        $this->assertSame(1, PcMonitor::count());

        $pc = PcMonitor::first();
        $this->assertSame('online', $pc->status);
        $this->assertSame(88.0, $pc->cpu_usage);
        $this->assertTrue($pc->last_seen_at->greaterThan(now()->subMinute()));
    }

    public function test_telemetry_rejects_out_of_range_usage(): void
    {
        $this->withApiKey();

        $this->postJson(route('telemetry.store'), $this->payload(['cpu_usage' => 150]), ['X-API-KEY' => 'test-key'])
            ->assertStatus(422)
            ->assertJsonValidationErrors('cpu_usage');
    }

    public function test_telemetry_throttle_is_per_hostname_not_per_ip(): void
    {
        $this->withApiKey();

        // Kuota 60/menit dipakai habis oleh hostname A (semua dari IP test ini).
        for ($i = 0; $i < 60; $i++) {
            $this->postJson(route('telemetry.store'), $this->payload(), ['X-API-KEY' => 'test-key'])
                ->assertOk();
        }

        $this->postJson(route('telemetry.store'), $this->payload(), ['X-API-KEY' => 'test-key'])
            ->assertStatus(429);

        // Hostname B dari IP yang sama tetap lolos — kuota tidak dipakai bersama.
        $this->postJson(
            route('telemetry.store'),
            $this->payload(['hostname' => 'LAB-PC-02']),
            ['X-API-KEY' => 'test-key']
        )->assertOk();

        $this->assertTrue(PcMonitor::where('hostname', 'LAB-PC-02')->exists());
    }
}
