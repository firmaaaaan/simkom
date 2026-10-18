<?php

namespace Tests\Feature;

use App\Models\PcMonitor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AgentReportApiTest extends TestCase
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

    /** bytes JPEG palsu cukup untuk lolos magic check. */
    private function fakeJpegBase64(): string
    {
        return base64_encode("\xFF\xD8\xFF\xE0".str_repeat('x', 200));
    }

    public function test_screenshot_rejected_without_api_key(): void
    {
        $this->withApiKey();

        $this->postJson(route('agent.screenshots.store'), [
            'hostname' => 'LAB-PC-01',
            'image_base64' => $this->fakeJpegBase64(),
        ])->assertStatus(401);
    }

    public function test_screenshot_unknown_hostname_returns_404(): void
    {
        $this->withApiKey();

        $this->postJson(route('agent.screenshots.store'), [
            'hostname' => 'TIDAK-ADA',
            'image_base64' => $this->fakeJpegBase64(),
        ], ['X-API-KEY' => 'test-key'])->assertNotFound();
    }

    public function test_screenshot_rejects_invalid_base64(): void
    {
        $this->withApiKey();
        $this->pc();

        $this->postJson(route('agent.screenshots.store'), [
            'hostname' => 'LAB-PC-01',
            'image_base64' => 'bukan-base64!!',
        ], ['X-API-KEY' => 'test-key'])
            ->assertStatus(422)
            ->assertJson(['ok' => false]);
    }

    public function test_screenshot_rejects_non_jpeg_payload(): void
    {
        $this->withApiKey();
        $this->pc();

        $this->postJson(route('agent.screenshots.store'), [
            'hostname' => 'LAB-PC-01',
            'image_base64' => base64_encode('GIF89a'.str_repeat('x', 200)),
        ], ['X-API-KEY' => 'test-key'])
            ->assertStatus(422)
            ->assertJson(['ok' => false]);
    }

    public function test_screenshot_saves_file_per_hostname(): void
    {
        Storage::fake('local');
        $this->withApiKey();
        $this->pc();

        $this->postJson(route('agent.screenshots.store'), [
            'hostname' => 'LAB-PC-01',
            'image_base64' => $this->fakeJpegBase64(),
        ], ['X-API-KEY' => 'test-key'])
            ->assertOk()
            ->assertJson(['ok' => true]);

        Storage::disk('local')->assertExists('pc-screenshots/LAB-PC-01.jpg');
    }

    public function test_software_unknown_hostname_returns_404(): void
    {
        $this->withApiKey();

        $this->postJson(route('agent.software.store'), [
            'hostname' => 'TIDAK-ADA',
            'items' => [['name' => 'Chrome']],
        ], ['X-API-KEY' => 'test-key'])->assertNotFound();
    }

    public function test_software_rejects_missing_item_name(): void
    {
        $this->withApiKey();
        $this->pc();

        $this->postJson(route('agent.software.store'), [
            'hostname' => 'LAB-PC-01',
            'items' => [['version' => '1.0']],
        ], ['X-API-KEY' => 'test-key'])
            ->assertStatus(422)
            ->assertJsonValidationErrors('items.0.name');
    }

    public function test_software_sync_replaces_previous_snapshot(): void
    {
        $this->withApiKey();
        $pc = $this->pc();

        $this->postJson(route('agent.software.store'), [
            'hostname' => 'LAB-PC-01',
            'items' => [
                ['name' => 'Google Chrome', 'version' => '120.0', 'publisher' => 'Google LLC'],
                ['name' => '7-Zip', 'version' => '23.01', 'size_kb' => 1500],
            ],
        ], ['X-API-KEY' => 'test-key'])
            ->assertOk()
            ->assertJson(['ok' => true, 'count' => 2]);

        $this->assertSame(2, $pc->softwares()->count());
        $this->assertNotNull($pc->fresh()->software_synced_at);

        // Snapshot berikutnya menggantikan seluruh isi lama.
        $this->postJson(route('agent.software.store'), [
            'hostname' => 'LAB-PC-01',
            'items' => [
                ['name' => 'Notepad++', 'version' => '8.6'],
            ],
        ], ['X-API-KEY' => 'test-key'])
            ->assertOk()
            ->assertJson(['ok' => true, 'count' => 1]);

        $this->assertSame(1, $pc->softwares()->count());
        $this->assertDatabaseHas('pc_softwares', [
            'pc_monitor_id' => $pc->id,
            'name' => 'Notepad++',
        ]);
    }
}
