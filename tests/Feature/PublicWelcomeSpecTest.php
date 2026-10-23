<?php

namespace Tests\Feature;

use App\Models\Computer;
use App\Models\Laboratory;
use App\Models\PcMonitor;
use App\Models\PcSoftware;
use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicWelcomeSpecTest extends TestCase
{
    use RefreshDatabase;

    private function computer(string $code): Computer
    {
        $lab = Laboratory::firstOrCreate(
            ['code' => 'LAB-UJI-WEL'],
            ['name' => 'Lab Uji Welcome', 'location' => 'Gedung Uji', 'capacity' => 20]
        );

        return Computer::create([
            'code' => $code,
            'laboratory_id' => $lab->id,
            'status' => 'Aktif',
        ]);
    }

    public function test_denah_shows_agent_detected_software(): void
    {
        $this->computer('SB-201');

        $pc = PcMonitor::create([
            'hostname' => 'sb-201', // case-insensitive terhadap code "SB-201"
            'ip_address' => '127.0.0.1',
            'status' => 'online',
            'software_synced_at' => now(),
        ]);
        PcSoftware::create(['pc_monitor_id' => $pc->id, 'name' => 'Google Chrome', 'version' => '130.0']);
        PcSoftware::create(['pc_monitor_id' => $pc->id, 'name' => 'Visual Studio Code', 'version' => '1.85.1']);

        Setting::setPublicSoftwareWhitelist(['Google Chrome', 'Visual Studio Code']);

        $this->get('/daftar-komputer')
            ->assertOk()
            ->assertSee('Lihat Software')
            ->assertSee('Software Terdeteksi (Agent)')
            ->assertSee('Google Chrome')
            ->assertSee('Visual Studio Code');
    }

    public function test_computer_without_agent_shows_hint(): void
    {
        $this->computer('SB-202');
        Setting::setPublicSoftwareWhitelist(['Google Chrome']);

        $this->get('/daftar-komputer')
            ->assertOk()
            ->assertSee('Belum ada data agent untuk PC ini');
    }

    public function test_agent_online_but_never_synced_shows_hint(): void
    {
        $this->computer('SB-203');

        PcMonitor::create([
            'hostname' => 'SB-203',
            'ip_address' => '127.0.0.1',
            'status' => 'online',
            'software_synced_at' => null,
        ]);

        Setting::setPublicSoftwareWhitelist(['Google Chrome']);

        $this->get('/daftar-komputer')
            ->assertOk()
            ->assertSee('belum pernah sync')
            ->assertSee('Inventaris software belum pernah terkirim');
    }

    public function test_spec_hidden_when_public_spec_disabled(): void
    {
        $this->computer('SB-204');
        Setting::set(Setting::PUBLIC_SPEC_ID, 'false');
        Setting::setPublicSoftwareWhitelist(['Google Chrome']);

        $this->get('/daftar-komputer')
            ->assertOk()
            ->assertDontSee('Lihat Software')
            ->assertDontSee('Software Terdeteksi (Agent)');
    }
}
