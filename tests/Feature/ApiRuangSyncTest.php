<?php

namespace Tests\Feature;

use App\Models\ApiRuang;
use App\Models\Laboratory;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request as HttpRequest;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ApiRuangSyncTest extends TestCase
{
    use RefreshDatabase;

    private function userWith(array $permissions): User
    {
        $role = Role::firstOrCreate(
            ['name' => 'role-api-ruang-'.md5(implode(',', $permissions))],
            ['label' => 'Role Uji']
        );

        foreach ($permissions as $permission) {
            $role->permissions()->attach(
                Permission::firstOrCreate(['name' => $permission], ['label' => $permission])
            );
        }

        $user = User::create([
            'name' => 'Uji',
            'email' => 'api-ruang-'.$role->name.'@uji.test',
            'password' => 'rahasia123',
        ]);
        $user->roles()->attach($role);

        return $user;
    }

    private function payload(?array $sb = null): array
    {
        return [
            'success' => true,
            'message' => 'Berhasil mengambil master ruangan',
            'data' => $sb ?? [
                ['kdruang' => 1389, 'ruang' => 'SB.1.02 / Ruang Laboratorium Komputer 2 (52)'],
                ['kdruang' => 1421, 'ruang' => 'SM.1.20 / Ruang Laboratorium Komputer 3 (52)'],
                ['kdruang' => 1423, 'ruang' => 'SB.4.23  (20)'],
            ],
        ];
    }

    public function test_sync_stores_all_rooms_and_parses_fields(): void
    {
        Http::fake([config('jadwal.ruang_api_url') => Http::response($this->payload())]);

        $this->artisan('simptt:sync-ruang')->assertExitCode(0);

        $this->assertDatabaseCount('api_ruang', 3);
        $this->assertDatabaseHas('api_ruang', [
            'kdruang' => 1389,
            'kode' => 'SB.1.02',
            'nama' => 'Ruang Laboratorium Komputer 2',
            'kapasitas' => 52,
            'ruang_asli' => 'SB.1.02 / Ruang Laboratorium Komputer 2 (52)',
        ]);
        $this->assertDatabaseHas('api_ruang', [
            'kdruang' => 1421,
            'kode' => 'SM.1.20',
            'nama' => 'Ruang Laboratorium Komputer 3',
            'kapasitas' => 52,
        ]);
        $this->assertDatabaseHas('api_ruang', [
            'kdruang' => 1423,
            'kode' => 'SB.4.23',
            'nama' => 'SB.4.23',
            'kapasitas' => 20,
        ]);

        $this->assertDatabaseCount('laboratories', 3);
        $this->assertDatabaseHas('laboratories', [
            'code' => 'SB.1.02',
            'name' => 'Ruang Laboratorium Komputer 2',
            'capacity' => 52,
            'location' => 'Gedung SB',
            'temp_min' => 20,
            'temp_max' => 25,
        ]);
        $this->assertDatabaseHas('laboratories', [
            'code' => 'SM.1.20',
            'name' => 'Ruang Laboratorium Komputer 3',
            'capacity' => 52,
            'location' => 'Gedung SM',
        ]);

        Http::assertSent(fn (HttpRequest $request) => $request->url() === config('jadwal.ruang_api_url'));
    }

    public function test_sync_updates_existing_room_but_keeps_manual_note(): void
    {
        Laboratory::create([
            'code' => 'SB.1.02',
            'name' => 'Ruang Laboratorium Komputer 2',
            'location' => 'Lab manual',
            'capacity' => 52,
            'temp_min' => 18,
            'temp_max' => 26,
            'description' => 'Catatan manual laboratorium',
        ]);

        ApiRuang::create([
            'kdruang' => 1389,
            'kode' => 'SB.1.02',
            'nama' => 'Ruang Laboratorium Komputer 2',
            'kapasitas' => 52,
            'ruang_asli' => 'SB.1.02 / Ruang Laboratorium Komputer 2 (52)',
            'catatan' => 'Catatan manual',
        ]);

        Http::fake([config('jadwal.ruang_api_url') => Http::response($this->payload([
            ['kdruang' => 1389, 'ruang' => 'SB.1.02 / Ruang Lab Komputer 2 Revisi (50)'],
        ]))]);

        $this->artisan('simptt:sync-ruang')->assertExitCode(0);
        $this->artisan('simptt:sync-ruang')->assertExitCode(0);

        $this->assertDatabaseCount('api_ruang', 1);
        $this->assertDatabaseHas('api_ruang', [
            'kdruang' => 1389,
            'nama' => 'Ruang Lab Komputer 2 Revisi',
            'kapasitas' => 50,
            'catatan' => 'Catatan manual',
        ]);
        $this->assertDatabaseCount('laboratories', 1);
        $this->assertDatabaseHas('laboratories', [
            'code' => 'SB.1.02',
            'name' => 'Ruang Lab Komputer 2 Revisi',
            'capacity' => 50,
            'location' => 'Lab manual',
            'temp_min' => 18,
            'temp_max' => 26,
            'description' => 'Catatan manual laboratorium',
        ]);
    }

    public function test_admin_button_syncs_and_reports_result(): void
    {
        Http::fake([config('jadwal.ruang_api_url') => Http::response($this->payload())]);

        $this->actingAs($this->userWith(['view-laboratories']))
            ->post(route('api-ruang.sync'))
            ->assertForbidden();

        $this->actingAs($this->userWith(['view-laboratories', 'edit-laboratories']))
            ->from(route('laboratories.index'))
            ->post(route('api-ruang.sync'))
            ->assertRedirect(route('laboratories.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseCount('api_ruang', 3);
    }

    public function test_index_page_lists_synced_rooms(): void
    {
        ApiRuang::create([
            'kdruang' => 1389,
            'kode' => 'SB.1.02',
            'nama' => 'Ruang Laboratorium Komputer 2',
            'kapasitas' => 52,
            'ruang_asli' => 'SB.1.02 / Ruang Laboratorium Komputer 2 (52)',
        ]);

        $this->actingAs($this->userWith(['view-laboratories']))
            ->get(route('api-ruang.index'))
            ->assertOk()
            ->assertSee('SB.1.02')
            ->assertSee('Ruang Laboratorium Komputer 2');

        $this->actingAs($this->userWith(['view-laboratories', 'edit-laboratories']))
            ->get(route('laboratories.index'))
            ->assertOk()
            ->assertSee(route('api-ruang.sync'), false);
    }

    public function test_synced_rooms_are_used_by_monitoring_suhu_and_kartu_alat(): void
    {
        Http::fake([config('jadwal.ruang_api_url') => Http::response($this->payload())]);

        $this->artisan('simptt:sync-ruang')->assertExitCode(0);

        $this->actingAs($this->userWith(['view-monitoring-suhu']))
            ->get(route('monitoring-suhu.index'))
            ->assertOk()
            ->assertSee('SB.1.02')
            ->assertSee('Ruang Laboratorium Komputer 2');

        $this->actingAs($this->userWith(['view-alat']))
            ->get(route('alat.kartu'))
            ->assertOk()
            ->assertSee('Ruang Laboratorium Komputer 2');
    }

    public function test_sidebar_shows_menu_with_permission(): void
    {
        $menuUrl = route('api-ruang.index');

        $this->actingAs($this->userWith(['view-laboratories']))
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee($menuUrl, false);

        $this->actingAs($this->userWith(['view-hardware']))
            ->get(route('dashboard'))
            ->assertOk()
            ->assertDontSee($menuUrl, false);
    }

    public function test_api_failure_is_reported_without_saving_data(): void
    {
        Http::fake([config('jadwal.ruang_api_url') => Http::response('', 500)]);

        $this->artisan('simptt:sync-ruang')->assertFailed();

        $this->actingAs($this->userWith(['view-laboratories', 'edit-laboratories']))
            ->from(route('laboratories.index'))
            ->post(route('api-ruang.sync'))
            ->assertSessionHas('error');

        $this->assertDatabaseCount('api_ruang', 0);
    }
}
