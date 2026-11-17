<?php

namespace Tests\Feature;

use App\Models\Laboratory;
use App\Models\Permission;
use App\Models\Role;
use App\Models\RoomTemperatureReading;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MonitoringSuhuTest extends TestCase
{
    use RefreshDatabase;

    private function userWith(array $permissions): User
    {
        $role = Role::firstOrCreate(
            ['name' => 'role-suhu-'.md5(implode(',', $permissions))],
            ['label' => 'Role Uji']
        );
        foreach ($permissions as $p) {
            $role->permissions()->syncWithoutDetaching([
                Permission::firstOrCreate(['name' => $p], ['label' => $p])->id,
            ]);
        }

        $user = User::firstOrCreate(
            ['email' => 'suhu-'.md5(implode(',', $permissions)).'@uji.test'],
            ['name' => 'Uji Suhu', 'password' => 'rahasia123']
        );
        $user->roles()->syncWithoutDetaching($role->id);

        return $user;
    }

    private function seedRoom(array $threshold = []): Laboratory
    {
        return Laboratory::create(array_merge([
            'name' => 'Lab Komputer 1',
            'code' => 'LK1',
            'location' => 'Gedung A',
            'capacity' => 40,
        ], $threshold));
    }

    private function reading(Laboratory $lab, float $temperature): RoomTemperatureReading
    {
        return RoomTemperatureReading::create([
            'laboratory_id' => $lab->id,
            'temperature' => $temperature,
            'recorded_at' => '2026-11-17 08:30:00',
        ]);
    }

    public function test_guest_scan_redirects_to_public_riwayat(): void
    {
        $lab = $this->seedRoom();

        $this->get(route('monitoring-suhu.scan', $lab))
            ->assertRedirect(route('monitoring-suhu.riwayat', $lab));
    }

    public function test_auth_scan_redirects_to_room_history(): void
    {
        $lab = $this->seedRoom();

        $this->actingAs($this->userWith(['view-monitoring-suhu']))
            ->get(route('monitoring-suhu.scan', $lab))
            ->assertRedirect(route('monitoring-suhu.show', $lab));
    }

    public function test_public_riwayat_shows_history_without_input_button(): void
    {
        $lab = $this->seedRoom(['temp_min' => 18, 'temp_max' => 26]);
        $this->reading($lab, 30.5);

        $this->get(route('monitoring-suhu.riwayat', $lab))
            ->assertOk()
            ->assertSee('Riwayat Monitoring Suhu')
            ->assertSee('30.5°C')
            ->assertSee('Panas')
            ->assertDontSee('Input Suhu');
    }

    public function test_public_riwayat_shows_empty_state(): void
    {
        $lab = $this->seedRoom();

        $this->get(route('monitoring-suhu.riwayat', $lab))
            ->assertOk()
            ->assertSee('Belum ada riwayat monitoring suhu untuk ruangan ini');
    }

    public function test_index_requires_permission(): void
    {
        $this->actingAs($this->userWith([]))
            ->get(route('monitoring-suhu.index'))
            ->assertForbidden();
    }

    public function test_index_shows_latest_reading_and_status(): void
    {
        $lab = $this->seedRoom(['temp_min' => 18, 'temp_max' => 26]);
        $this->reading($lab, 27.5);

        $this->actingAs($this->userWith(['view-monitoring-suhu']))
            ->get(route('monitoring-suhu.index'))
            ->assertOk()
            ->assertSee('Lab Komputer 1')
            ->assertSee('27.5°C')
            ->assertSee('Panas')
            ->assertSee('18–26°C');
    }

    public function test_index_status_shows_placeholder_when_threshold_missing(): void
    {
        $lab = $this->seedRoom();
        $this->reading($lab, 27.5);

        $this->actingAs($this->userWith(['view-monitoring-suhu']))
            ->get(route('monitoring-suhu.index'))
            ->assertOk()
            ->assertSee('Tidak ada ambang')
            ->assertSee('Belum diatur');
    }

    public function test_store_creates_reading_and_validates_input(): void
    {
        $lab = $this->seedRoom();
        $user = $this->userWith(['view-monitoring-suhu', 'create-monitoring-suhu']);

        $this->actingAs($user)
            ->post(route('monitoring-suhu.store'), [
                'laboratory_id' => $lab->id,
                'temperature' => 24.5,
                'recorded_at' => '2026-11-17 09:00',
            ])
            ->assertRedirect(route('monitoring-suhu.show', $lab))
            ->assertSessionHas('success');

        $this->actingAs($user)
            ->post(route('monitoring-suhu.store'), [
                'laboratory_id' => $lab->id,
                'temperature' => 25.0,
                'recorded_at' => '2026-11-17 10:00',
                'action' => 'save_another',
            ])
            ->assertRedirect(route('monitoring-suhu.show', $lab))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('room_temperature_readings', [
            'laboratory_id' => $lab->id,
            'temperature' => 24.5,
            'recorded_by' => $user->id,
        ]);

        $this->actingAs($user)
            ->post(route('monitoring-suhu.store'), [
                'laboratory_id' => $lab->id,
                'temperature' => 150,
                'recorded_at' => '2026-11-17 09:00',
            ])
            ->assertSessionHasErrors('temperature');
    }

    public function test_store_requires_permission(): void
    {
        $lab = $this->seedRoom();

        $this->actingAs($this->userWith(['view-monitoring-suhu']))
            ->post(route('monitoring-suhu.store'), [
                'laboratory_id' => $lab->id,
                'temperature' => 24.5,
                'recorded_at' => '2026-11-17 09:00',
            ])
            ->assertForbidden();

        $this->assertDatabaseCount('room_temperature_readings', 0);
    }

    public function test_show_lists_history_with_date_filter(): void
    {
        $lab = $this->seedRoom(['temp_min' => 18, 'temp_max' => 26]);
        $this->reading($lab, 20.0);
        RoomTemperatureReading::create([
            'laboratory_id' => $lab->id,
            'temperature' => 33.0,
            'recorded_at' => '2026-11-10 10:00:00',
        ]);

        $this->actingAs($this->userWith(['view-monitoring-suhu']))
            ->get(route('monitoring-suhu.show', $lab))
            ->assertOk()
            ->assertSee('20.0°C')
            ->assertSee('33.0°C')
            ->assertSee('Normal');

        $this->actingAs($this->userWith(['view-monitoring-suhu']))
            ->get(route('monitoring-suhu.show', [$lab, 'from' => '2026-11-15', 'to' => '2026-11-17']))
            ->assertOk()
            ->assertSee('20.0°C')
            ->assertDontSee('33.0°C');
    }

    public function test_delete_reading_requires_permission(): void
    {
        $lab = $this->seedRoom();
        $reading = $this->reading($lab, 21.0);

        $this->actingAs($this->userWith(['view-monitoring-suhu']))
            ->delete(route('monitoring-suhu.readings.destroy', [$lab, $reading]))
            ->assertForbidden();

        $this->actingAs($this->userWith(['delete-monitoring-suhu']))
            ->delete(route('monitoring-suhu.readings.destroy', [$lab, $reading]))
            ->assertRedirect(route('monitoring-suhu.show', $lab));

        $this->assertDatabaseMissing('room_temperature_readings', ['id' => $reading->id]);
    }

    public function test_export_and_labels_require_permissions(): void
    {
        $lab = $this->seedRoom();

        $this->actingAs($this->userWith(['view-monitoring-suhu']))
            ->get(route('monitoring-suhu.export'))
            ->assertForbidden();

        $this->actingAs($this->userWith(['view-monitoring-suhu']))
            ->get(route('monitoring-suhu.export-pdf'))
            ->assertForbidden();

        $this->actingAs($this->userWith(['view-monitoring-suhu']))
            ->get(route('monitoring-suhu.labels'))
            ->assertForbidden();

        $this->actingAs($this->userWith(['export-monitoring-suhu']))
            ->get(route('monitoring-suhu.export', ['laboratory_id' => $lab->id]))
            ->assertOk();

        $this->actingAs($this->userWith(['export-monitoring-suhu']))
            ->get(route('monitoring-suhu.export-pdf', ['laboratory_id' => $lab->id]))
            ->assertOk();

        $this->actingAs($this->userWith(['view-monitoring-suhu', 'create-monitoring-suhu']))
            ->get(route('monitoring-suhu.show', $lab))
            ->assertOk()
            ->assertSee('Input Suhu Baru')
            ->assertSee('Simpan & Tambah Lagi', false);

        $this->actingAs($this->userWith(['print-monitoring-suhu']))
            ->get(route('monitoring-suhu.labels', ['laboratory_id' => 'all']))
            ->assertOk()
            ->assertSee('Label Monitoring Suhu');
    }

    public function test_input_form_hidden_without_create_permission(): void
    {
        $lab = $this->seedRoom();

        $this->actingAs($this->userWith(['view-monitoring-suhu']))
            ->get(route('monitoring-suhu.show', $lab))
            ->assertOk()
            ->assertDontSee('Input Suhu Baru')
            ->assertDontSee('Simpan & Tambah Lagi', false);
    }

    public function test_sidebar_shows_menu_with_permission(): void
    {
        $menuUrl = route('monitoring-suhu.index');

        $this->actingAs($this->userWith(['view-monitoring-suhu']))
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee($menuUrl, false);

        $this->actingAs($this->userWith(['view-computers']))
            ->get(route('dashboard'))
            ->assertOk()
            ->assertDontSee($menuUrl, false);
    }
}
