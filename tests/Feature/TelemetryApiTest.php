<?php

namespace Tests\Feature;

use App\Models\PcMonitor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TelemetryApiTest extends TestCase
{
    use RefreshDatabase;

    private const API_KEY = 'test-monitoring-key';

    protected function setUp(): void
    {
        parent::setUp();

        config(['services.monitoring.key' => self::API_KEY]);
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
            'active_user' => 'mahasiswa',
        ], $overrides);
    }

    public function test_request_without_api_key_is_forbidden(): void
    {
        $this->postJson('/api/v1/telemetry', $this->payload())
            ->assertForbidden();

        $this->assertDatabaseCount('pc_monitors', 0);
    }

    public function test_request_with_wrong_api_key_is_forbidden(): void
    {
        $this->withHeaders(['X-API-KEY' => 'salah'])
            ->postJson('/api/v1/telemetry', $this->payload())
            ->assertForbidden();

        $this->assertDatabaseCount('pc_monitors', 0);
    }

    public function test_valid_telemetry_creates_online_pc(): void
    {
        $this->withHeaders(['X-API-KEY' => self::API_KEY])
            ->postJson('/api/v1/telemetry', $this->payload())
            ->assertOk()
            ->assertJsonPath('status', 'ok')
            ->assertJsonPath('hostname', 'LAB-PC-01');

        $this->assertDatabaseHas('pc_monitors', [
            'hostname' => 'LAB-PC-01',
            'ip_address' => '192.168.1.10',
            'status' => 'online',
            'active_user' => 'mahasiswa',
        ]);

        $pc = PcMonitor::where('hostname', 'LAB-PC-01')->firstOrFail();
        $this->assertNotNull($pc->last_seen_at);
    }

    public function test_second_telemetry_updates_same_hostname_instead_of_duplicating(): void
    {
        $this->withHeaders(['X-API-KEY' => self::API_KEY])
            ->postJson('/api/v1/telemetry', $this->payload());

        $this->withHeaders(['X-API-KEY' => self::API_KEY])
            ->postJson('/api/v1/telemetry', $this->payload(['cpu_usage' => 88.8, 'active_user' => 'dosen']))
            ->assertOk();

        $this->assertDatabaseCount('pc_monitors', 1);
        $this->assertDatabaseHas('pc_monitors', [
            'hostname' => 'LAB-PC-01',
            'cpu_usage' => 88.8,
            'active_user' => 'dosen',
            'status' => 'online',
        ]);
    }

    public function test_invalid_payload_returns_422(): void
    {
        $this->withHeaders(['X-API-KEY' => self::API_KEY])
            ->postJson('/api/v1/telemetry', ['hostname' => 'LAB-PC-02'])
            ->assertStatus(422);

        $this->assertDatabaseCount('pc_monitors', 0);
    }

    public function test_empty_api_key_config_rejects_all_requests(): void
    {
        config(['services.monitoring.key' => '']);

        $this->withHeaders(['X-API-KEY' => ''])
            ->postJson('/api/v1/telemetry', $this->payload())
            ->assertForbidden();
    }
}
