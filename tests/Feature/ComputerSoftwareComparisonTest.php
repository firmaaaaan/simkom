<?php

namespace Tests\Feature;

use App\Models\Computer;
use App\Models\Laboratory;
use App\Models\PcMonitor;
use App\Models\PcSoftware;
use App\Models\Permission;
use App\Models\Role;
use App\Models\Software;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ComputerSoftwareComparisonTest extends TestCase
{
    use RefreshDatabase;

    private function viewer(): User
    {
        $role = Role::firstOrCreate(['name' => 'viewer-komputer-uji'], ['label' => 'Viewer Komputer Uji']);
        $permission = Permission::firstOrCreate(['name' => 'view-computers'], ['label' => 'Lihat Komputer']);
        $role->permissions()->syncWithoutDetaching([$permission->id]);

        $user = User::firstOrCreate(
            ['email' => 'viewer-komputer@uji.test'],
            ['name' => 'Viewer Komputer', 'password' => 'rahasia123']
        );
        $user->roles()->syncWithoutDetaching([$role->id]);

        return $user;
    }

    private function computer(string $code = 'SB-099'): Computer
    {
        $lab = Laboratory::firstOrCreate(
            ['code' => 'LAB-UJI-KOM'],
            ['name' => 'Lab Uji Komputer', 'location' => 'Gedung Uji', 'capacity' => 20]
        );

        return Computer::create([
            'code' => $code,
            'laboratory_id' => $lab->id,
            'status' => 'Aktif',
        ]);
    }

    public function test_matched_extra_and_missing_are_classified(): void
    {
        $user = $this->viewer();
        $computer = $this->computer();

        $vscode = Software::create(['name' => 'Visual Studio Code', 'code' => 'SW-T01', 'category' => 'Umum']);
        $chrome = Software::create(['name' => 'Google Chrome', 'code' => 'SW-T02', 'category' => 'Umum']);
        $zoom = Software::create(['name' => 'Zoom', 'code' => 'SW-T03', 'category' => 'Umum']);
        $computer->software()->sync([$vscode->id, $chrome->id, $zoom->id]);

        $pc = PcMonitor::create([
            'hostname' => 'sb-099', // case-insensitive: code "SB-099"
            'ip_address' => '127.0.0.1',
            'status' => 'online',
            'software_synced_at' => now(),
        ]);
        PcSoftware::create(['pc_monitor_id' => $pc->id, 'name' => 'Microsoft Visual Studio Code 1.85.1', 'version' => '1.85.1']);
        PcSoftware::create(['pc_monitor_id' => $pc->id, 'name' => 'Google Chrome', 'version' => '130.0']);
        PcSoftware::create(['pc_monitor_id' => $pc->id, 'name' => 'Adobe Acrobat Reader MUI', 'version' => '24.1']);

        $this->actingAs($user)
            ->get(route('computers.show', $computer))
            ->assertOk()
            ->assertSee('Software Terdeteksi (Agent)')
            ->assertSee('Sesuai spesifikasi (2)')
            ->assertSee('Terdeteksi tambahan (1)')
            ->assertSee('Adobe Acrobat Reader MUI')
            ->assertSee('Tidak terdeteksi (1)')
            ->assertSee('Zoom');
    }

    public function test_all_matched_shows_empty_missing_state(): void
    {
        $user = $this->viewer();
        $computer = $this->computer('SB-100');

        $chrome = Software::create(['name' => 'Google Chrome', 'code' => 'SW-T04', 'category' => 'Umum']);
        $computer->software()->sync([$chrome->id]);

        $pc = PcMonitor::create([
            'hostname' => 'SB-100',
            'ip_address' => '127.0.0.1',
            'status' => 'online',
            'software_synced_at' => now(),
        ]);
        PcSoftware::create(['pc_monitor_id' => $pc->id, 'name' => 'Google Chrome', 'version' => '130.0']);

        $this->actingAs($user)
            ->get(route('computers.show', $computer))
            ->assertOk()
            ->assertSee('Semua software spesifikasi terdeteksi')
            ->assertSee('Tidak ada software di luar spesifikasi');
    }

    public function test_computer_without_agent_shows_hint(): void
    {
        $user = $this->viewer();
        $computer = $this->computer('SB-101');

        $this->actingAs($user)
            ->get(route('computers.show', $computer))
            ->assertOk()
            ->assertSee('Belum ada data monitoring untuk hostname')
            ->assertSee('SB-101');
    }

    public function test_agent_online_but_never_synced_shows_hint(): void
    {
        $user = $this->viewer();
        $computer = $this->computer('SB-102');

        PcMonitor::create([
            'hostname' => 'SB-102',
            'ip_address' => '127.0.0.1',
            'status' => 'online',
            'software_synced_at' => null,
        ]);

        $this->actingAs($user)
            ->get(route('computers.show', $computer))
            ->assertOk()
            ->assertSee('belum pernah sync');
    }
}
