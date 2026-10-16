<?php

namespace Tests\Feature;

use App\Models\PcMonitor;
use App\Models\PcPowerCommand;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AgentCommandApiTest extends TestCase
{
    use RefreshDatabase;

    private function withApiKey(string $key = 'test-key'): void
    {
        config()->set('services.monitoring.api_key', $key);
    }

    private function pc(string $hostname = 'LAB-PC-01'): PcMonitor
    {
        return PcMonitor::create([
            'hostname' => $hostname,
            'ip_address' => '192.168.1.10',
            'status' => 'online',
            'last_seen_at' => now(),
        ]);
    }

    public function test_commands_rejected_without_api_key(): void
    {
        $this->withApiKey();

        $this->getJson(route('commands.index', ['hostname' => 'LAB-PC-01']))
            ->assertStatus(401);
    }

    public function test_unknown_hostname_returns_empty_commands(): void
    {
        $this->withApiKey();

        $this->getJson(route('commands.index', ['hostname' => 'TIDAK-ADA']), ['X-API-KEY' => 'test-key'])
            ->assertOk()
            ->assertJson(['commands' => []]);
    }

    public function test_pending_command_is_returned_to_agent(): void
    {
        $this->withApiKey();
        $pc = $this->pc();
        $command = PcPowerCommand::create([
            'pc_monitor_id' => $pc->id,
            'action' => PcPowerCommand::ACTION_SHUTDOWN,
            'status' => PcPowerCommand::STATUS_PENDING,
        ]);

        $this->getJson(route('commands.index', ['hostname' => 'LAB-PC-01']), ['X-API-KEY' => 'test-key'])
            ->assertOk()
            ->assertJsonCount(1, 'commands')
            ->assertJsonPath('commands.0.id', $command->id)
            ->assertJsonPath('commands.0.action', 'shutdown');
    }

    public function test_expired_and_finished_commands_are_not_returned(): void
    {
        $this->withApiKey();
        $pc = $this->pc();

        // Kedaluwarsa: melewati TTL.
        PcPowerCommand::create([
            'pc_monitor_id' => $pc->id,
            'action' => PcPowerCommand::ACTION_SHUTDOWN,
            'status' => PcPowerCommand::STATUS_PENDING,
            'created_at' => now()->subSeconds(PcPowerCommand::TTL_SECONDS + 5),
        ]);
        // Sudah selesai.
        PcPowerCommand::create([
            'pc_monitor_id' => $pc->id,
            'action' => PcPowerCommand::ACTION_SHUTDOWN,
            'status' => PcPowerCommand::STATUS_COMPLETED,
        ]);

        $this->getJson(route('commands.index', ['hostname' => 'LAB-PC-01']), ['X-API-KEY' => 'test-key'])
            ->assertOk()
            ->assertJson(['commands' => []]);
    }

    public function test_ack_marks_command_completed(): void
    {
        $this->withApiKey();
        $pc = $this->pc();
        $command = PcPowerCommand::create([
            'pc_monitor_id' => $pc->id,
            'action' => PcPowerCommand::ACTION_SHUTDOWN,
            'status' => PcPowerCommand::STATUS_PENDING,
        ]);

        $this->postJson(route('commands.ack', $command), [
            'status' => 'completed',
        ], ['X-API-KEY' => 'test-key'])
            ->assertOk()
            ->assertJson(['ok' => true]);

        $this->assertDatabaseHas('pc_power_commands', [
            'id' => $command->id,
            'status' => 'completed',
        ]);
        $this->assertNotNull($command->fresh()->executed_at);
    }

    public function test_ack_rejects_invalid_status(): void
    {
        $this->withApiKey();
        $pc = $this->pc();
        $command = PcPowerCommand::create([
            'pc_monitor_id' => $pc->id,
            'action' => PcPowerCommand::ACTION_SHUTDOWN,
            'status' => PcPowerCommand::STATUS_PENDING,
        ]);

        $this->postJson(route('commands.ack', $command), [
            'status' => 'maybe',
        ], ['X-API-KEY' => 'test-key'])
            ->assertStatus(422)
            ->assertJsonValidationErrors('status');
    }
}
