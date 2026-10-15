<?php

namespace Tests\Feature;

use App\Models\PcMonitor;
use App\Models\PcPowerCommand;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AgentCommandApiTest extends TestCase
{
    use RefreshDatabase;

    private const API_KEY = 'test-monitoring-key';

    protected function setUp(): void
    {
        parent::setUp();

        config(['services.monitoring.key' => self::API_KEY]);
    }

    private function pc(array $overrides = []): PcMonitor
    {
        return PcMonitor::create(array_merge([
            'hostname' => 'LAB-PC-01',
            'ip_address' => '192.168.1.10',
            'status' => PcMonitor::STATUS_ONLINE,
            'last_seen_at' => now(),
        ], $overrides));
    }

    private function command(PcMonitor $pc, array $overrides = []): PcPowerCommand
    {
        return PcPowerCommand::create(array_merge([
            'pc_monitor_id' => $pc->id,
            'action' => PcPowerCommand::ACTION_SHUTDOWN,
            'status' => PcPowerCommand::STATUS_PENDING,
        ], $overrides));
    }

    public function test_commands_require_api_key(): void
    {
        $this->pc();

        $this->getJson('/api/v1/commands?hostname=LAB-PC-01')->assertForbidden();

        $this->withHeaders(['X-API-KEY' => 'salah'])
            ->getJson('/api/v1/commands?hostname=LAB-PC-01')
            ->assertForbidden();
    }

    public function test_returns_pending_commands_for_hostname(): void
    {
        $pc = $this->pc();
        $this->command($pc);
        $this->command($pc, ['status' => PcPowerCommand::STATUS_DONE]);

        $this->withHeaders(['X-API-KEY' => self::API_KEY])
            ->getJson('/api/v1/commands?hostname=LAB-PC-01')
            ->assertOk()
            ->assertJsonCount(1, 'commands')
            ->assertJsonPath('commands.0.action', 'shutdown');
    }

    public function test_unknown_hostname_returns_empty_commands(): void
    {
        $this->withHeaders(['X-API-KEY' => self::API_KEY])
            ->getJson('/api/v1/commands?hostname=TIDAK-ADA')
            ->assertOk()
            ->assertJson(['commands' => []]);
    }

    public function test_expired_commands_are_marked_and_not_returned(): void
    {
        $pc = $this->pc();
        $expired = $this->command($pc);
        $expired->forceFill(['created_at' => now()->subMinutes(5)])->save();

        $this->withHeaders(['X-API-KEY' => self::API_KEY])
            ->getJson('/api/v1/commands?hostname=LAB-PC-01')
            ->assertOk()
            ->assertJson(['commands' => []]);

        $this->assertSame(PcPowerCommand::STATUS_EXPIRED, $expired->fresh()->status);
    }

    public function test_ack_marks_command_done(): void
    {
        $pc = $this->pc();
        $command = $this->command($pc);

        $this->withHeaders(['X-API-KEY' => self::API_KEY])
            ->postJson("/api/v1/commands/{$command->id}/ack", [
                'hostname' => 'LAB-PC-01',
                'status' => 'done',
                'message' => 'shutdown dijalankan',
            ])
            ->assertOk()
            ->assertJson(['status' => 'ok', 'command_status' => 'done']);

        $command->refresh();
        $this->assertSame(PcPowerCommand::STATUS_DONE, $command->status);
        $this->assertNotNull($command->executed_at);
    }

    public function test_ack_from_wrong_hostname_is_forbidden(): void
    {
        $pc = $this->pc();
        $command = $this->command($pc);

        $this->withHeaders(['X-API-KEY' => self::API_KEY])
            ->postJson("/api/v1/commands/{$command->id}/ack", [
                'hostname' => 'PC-LAIN',
                'status' => 'done',
            ])
            ->assertForbidden();

        $this->assertSame(PcPowerCommand::STATUS_PENDING, $command->fresh()->status);
    }

    public function test_ack_ignores_already_processed_command(): void
    {
        $pc = $this->pc();
        $command = $this->command($pc, [
            'status' => PcPowerCommand::STATUS_DONE,
            'executed_at' => now(),
        ]);

        $this->withHeaders(['X-API-KEY' => self::API_KEY])
            ->postJson("/api/v1/commands/{$command->id}/ack", [
                'hostname' => 'LAB-PC-01',
                'status' => 'failed',
            ])
            ->assertOk()
            ->assertJson(['status' => 'ignored']);

        $this->assertSame(PcPowerCommand::STATUS_DONE, $command->fresh()->status);
    }
}
