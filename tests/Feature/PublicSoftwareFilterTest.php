<?php

namespace Tests\Feature;

use App\Models\Computer;
use App\Models\Laboratory;
use App\Models\PcMonitor;
use App\Models\PcSoftware;
use App\Models\Permission;
use App\Models\Role;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicSoftwareFilterTest extends TestCase
{
    use RefreshDatabase;

    private function computerWithSoftware(string $code, array $software): Computer
    {
        $lab = Laboratory::firstOrCreate(
            ['code' => 'LAB-WL'],
            ['name' => 'Lab Whitelist', 'location' => 'Gedung Uji', 'capacity' => 20]
        );

        $computer = Computer::create([
            'code' => $code,
            'laboratory_id' => $lab->id,
            'status' => 'Aktif',
        ]);

        $pc = PcMonitor::create([
            'hostname' => strtolower($code),
            'ip_address' => '127.0.0.1',
            'status' => 'online',
            'software_synced_at' => now(),
        ]);

        foreach ($software as $name) {
            PcSoftware::create(['pc_monitor_id' => $pc->id, 'name' => $name, 'version' => '1.0']);
        }

        return $computer;
    }

    private function userWith(string ...$permissions): User
    {
        $role = Role::firstOrCreate(['name' => 'admin-wl'], ['label' => 'Admin Whitelist']);

        foreach ($permissions as $name) {
            $role->permissions()->syncWithoutDetaching(
                Permission::firstOrCreate(['name' => $name], ['label' => $name])
            );
        }

        $user = User::create([
            'name' => 'Admin WL',
            'email' => 'admin-wl@simlab.test',
            'password' => 'rahasia123',
        ]);
        $user->roles()->attach($role);

        return $user;
    }

    public function test_whitelist_filters_public_software_list(): void
    {
        $this->computerWithSoftware('SB-301', ['Google Chrome', 'WinRAR', 'Visual Studio Code']);
        Setting::setPublicSoftwareWhitelist(['Google Chrome', 'Visual Studio Code']);

        $this->get('/daftar-komputer')
            ->assertOk()
            ->assertSee('Google Chrome')
            ->assertSee('Visual Studio Code')
            ->assertDontSee('WinRAR');
    }

    public function test_empty_whitelist_hides_all_software(): void
    {
        $this->computerWithSoftware('SB-302', ['Google Chrome', 'WinRAR']);
        Setting::setPublicSoftwareWhitelist([]);

        $this->get('/daftar-komputer')
            ->assertOk()
            ->assertDontSee('Google Chrome')
            ->assertDontSee('WinRAR');
    }

    public function test_filter_is_case_insensitive(): void
    {
        $this->computerWithSoftware('SB-303', ['google chrome']);
        Setting::setPublicSoftwareWhitelist(['Google Chrome']);

        $this->get('/daftar-komputer')
            ->assertOk()
            ->assertSee('google chrome');
    }

    public function test_update_saves_whitelist_and_merges_manual_names(): void
    {
        $user = $this->userWith('edit-computers');

        $this->actingAs($user)
            ->put(route('settings.public-software.update'), [
                'names' => ['Google Chrome', 'WinRAR'],
                'manual_names' => "Microsoft Office\n  Figma  ",
            ])
            ->assertRedirect(route('settings.public-software.edit'));

        $whitelist = Setting::publicSoftwareWhitelist();

        $this->assertEqualsCanonicalizing(
            ['Google Chrome', 'WinRAR', 'Microsoft Office', 'Figma'],
            $whitelist
        );
    }

    public function test_update_removes_software_not_submitted(): void
    {
        Setting::setPublicSoftwareWhitelist(['Google Chrome', 'WinRAR']);
        $user = $this->userWith('edit-computers');

        $this->actingAs($user)
            ->put(route('settings.public-software.update'), ['names' => ['WinRAR']])
            ->assertRedirect(route('settings.public-software.edit'));

        $this->assertSame(['WinRAR'], Setting::publicSoftwareWhitelist());
    }

    public function test_edit_page_lists_detected_software_with_whitelist_state(): void
    {
        $this->computerWithSoftware('SB-304', ['Google Chrome', 'WinRAR']);
        Setting::setPublicSoftwareWhitelist(['Google Chrome']);
        $user = $this->userWith('edit-computers');

        $this->actingAs($user)
            ->get(route('settings.public-software.edit'))
            ->assertOk()
            ->assertSee('Google Chrome')
            ->assertSee('WinRAR')
            ->assertSee('Filter Software Publik');
    }

    public function test_update_requires_edit_computers_permission(): void
    {
        $user = $this->userWith('view-computers');

        $this->actingAs($user)
            ->put(route('settings.public-software.update'), ['names' => ['WinRAR']])
            ->assertForbidden();

        $this->assertSame([], Setting::publicSoftwareWhitelist());
    }
}
