<?php

namespace Tests\Feature;

use App\Models\AparInspection;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AparInspectionTest extends TestCase
{
    use RefreshDatabase;

    private function userWith(array $permissions, string $email): User
    {
        $role = Role::firstOrCreate(['name' => 'role-'.md5($email)], ['label' => 'Role Uji']);
        foreach ($permissions as $name) {
            $permission = Permission::firstOrCreate(['name' => $name], ['label' => $name]);
            $role->permissions()->syncWithoutDetaching([$permission->id]);
        }

        $user = User::firstOrCreate(
            ['email' => $email],
            ['name' => 'User APAR', 'password' => 'rahasia123']
        );
        $user->roles()->syncWithoutDetaching([$role->id]);

        return $user;
    }

    private function allPermissions(): array
    {
        return [
            'view-apar-inspections', 'create-apar-inspections',
            'edit-apar-inspections', 'delete-apar-inspections',
        ];
    }

    private function payload(array $overrides = []): array
    {
        $items = [];
        foreach (array_keys(AparInspection::ITEMS) as $key) {
            $items[$key] = ['status' => 'baik', 'keterangan' => ''];
        }

        return array_merge([
            'nomor_registrasi' => 'APAR-LAB1-01',
            'tanggal_inspeksi' => '2026-11-05',
            'lokasi' => 'Lab Komputer 1',
            'diperiksa_oleh' => 'Petugas K3',
            'items' => $items,
        ], $overrides);
    }

    private function inspection(array $overrides = []): AparInspection
    {
        return AparInspection::create([
            'nomor_registrasi' => 'APAR-LAB2-01',
            'tanggal_inspeksi' => '2026-11-04',
            'lokasi' => 'Lab Jaringan',
            'items' => AparInspection::normalizeItems([]),
            'diperiksa_oleh' => 'Petugas K3',
            ...$overrides,
        ]);
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get(route('apar-inspections.index'))->assertRedirect(route('login'));
    }

    public function test_sidebar_contains_menu_link(): void
    {
        $user = $this->userWith(['view-apar-inspections'], 'menu-apar@uji.test');

        $this->actingAs($user)
            ->get(route('apar-inspections.index'))
            ->assertOk()
            ->assertSee('K3 Laboratorium')
            ->assertSee('href="'.route('apar-inspections.index').'"', false)
            ->assertSee('Lembar Periksa APAR');
    }

    public function test_sidebar_hides_menu_without_permission(): void
    {
        $user = $this->userWith([], 'tanpa-menu-apar@uji.test');

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertDontSee(route('apar-inspections.index'));
    }

    public function test_index_displays_inspections(): void
    {
        $user = $this->userWith(['view-apar-inspections'], 'viewer-apar@uji.test');
        $this->inspection();

        $this->actingAs($user)
            ->get(route('apar-inspections.index'))
            ->assertOk()
            ->assertSee('APAR-LAB2-01')
            ->assertSee('Lab Jaringan')
            ->assertSee('APAR-');
    }

    public function test_store_creates_inspection_with_seven_items(): void
    {
        $user = $this->userWith(['view-apar-inspections', 'create-apar-inspections'], 'creator-apar@uji.test');

        $payload = $this->payload();
        $payload['items']['pressure_gauge'] = ['status' => 'tidak', 'keterangan' => 'Tekanan rendah'];

        $this->actingAs($user)
            ->from(route('apar-inspections.create'))
            ->post(route('apar-inspections.store'), $payload)
            ->assertRedirect(route('apar-inspections.index'))
            ->assertSessionHas('success');

        $inspection = AparInspection::first();
        $this->assertNotNull($inspection);
        $this->assertStringStartsWith('APAR-', $inspection->kode);
        $this->assertSame($user->id, $inspection->created_by);
        $this->assertCount(7, $inspection->items);
        $this->assertSame('tidak', $inspection->items['pressure_gauge']['status']);
        $this->assertSame('Tekanan rendah', $inspection->items['pressure_gauge']['keterangan']);
        $this->assertSame(1, $inspection->jumlah_tidak_baik);
        $this->assertSame('Ada Temuan', $inspection->kondisi);
    }

    public function test_store_requires_status_for_every_item(): void
    {
        $user = $this->userWith(['create-apar-inspections'], 'invalid-apar@uji.test');
        $payload = $this->payload();
        unset($payload['items']['bottle_condition']);

        $this->actingAs($user)
            ->post(route('apar-inspections.store'), $payload)
            ->assertSessionHasErrors(['items.bottle_condition.status']);

        $this->assertDatabaseCount('apar_inspections', 0);
    }

    public function test_update_changes_inspection_fields(): void
    {
        $user = $this->userWith(['edit-apar-inspections'], 'editor-apar@uji.test');
        $inspection = $this->inspection();

        $payload = $this->payload();
        $payload['nomor_registrasi'] = 'APAR-LAB2-02';
        $payload['items']['hoses_condition'] = ['status' => 'tidak', 'keterangan' => 'Selang retak'];

        $this->actingAs($user)
            ->put(route('apar-inspections.update', $inspection), $payload)
            ->assertRedirect(route('apar-inspections.index'))
            ->assertSessionHas('success');

        $inspection->refresh();
        $this->assertSame('APAR-LAB2-02', $inspection->nomor_registrasi);
        $this->assertSame('tidak', $inspection->items['hoses_condition']['status']);
        $this->assertSame('Selang retak', $inspection->items['hoses_condition']['keterangan']);
    }

    public function test_destroy_and_bulk_destroy_remove_inspections(): void
    {
        $user = $this->userWith(['delete-apar-inspections'], 'deleter-apar@uji.test');
        $one = $this->inspection();
        $two = $this->inspection(['nomor_registrasi' => 'APAR-LAB3-01']);

        $this->actingAs($user)
            ->delete(route('apar-inspections.destroy', $one))
            ->assertRedirect(route('apar-inspections.index'))
            ->assertSessionHas('success');
        $this->assertDatabaseMissing('apar_inspections', ['id' => $one->id]);

        $this->actingAs($user)
            ->delete(route('apar-inspections.bulk-destroy'), ['ids' => [$two->id]])
            ->assertRedirect(route('apar-inspections.index'))
            ->assertSessionHas('success');
        $this->assertDatabaseMissing('apar_inspections', ['id' => $two->id]);
    }

    public function test_print_page_shows_form_content(): void
    {
        $user = $this->userWith(['view-apar-inspections'], 'printer-apar@uji.test');
        $inspection = $this->inspection();

        $this->actingAs($user)
            ->get(route('apar-inspections.print', $inspection))
            ->assertOk()
            ->assertSee('APAR-LAB2-01')
            ->assertSee('Pressure Gauge')
            ->assertSee('Koordinator K3 Laboratorium');
    }

    public function test_routes_are_forbidden_without_permission(): void
    {
        $user = $this->userWith([], 'tanpa-akses-apar@uji.test');
        $inspection = $this->inspection();

        $this->actingAs($user)->get(route('apar-inspections.index'))->assertForbidden();
        $this->actingAs($user)->get(route('apar-inspections.create'))->assertForbidden();
        $this->actingAs($user)->get(route('apar-inspections.edit', $inspection))->assertForbidden();
        $this->actingAs($user)->get(route('apar-inspections.print', $inspection))->assertForbidden();
        $this->actingAs($user)->delete(route('apar-inspections.destroy', $inspection))->assertForbidden();
    }

    public function test_index_filters_by_month_and_search(): void
    {
        $user = $this->userWith(['view-apar-inspections'], 'filter-apar@uji.test');
        $this->inspection(['nomor_registrasi' => 'APAR-OKT-01', 'tanggal_inspeksi' => '2026-10-15']);
        $this->inspection(['nomor_registrasi' => 'APAR-NOV-01', 'tanggal_inspeksi' => '2026-11-05']);

        $this->actingAs($user)
            ->get(route('apar-inspections.index', ['bulan' => 11, 'tahun' => 2026]))
            ->assertOk()
            ->assertSee('APAR-NOV-01')
            ->assertDontSee('APAR-OKT-01');

        $this->actingAs($user)
            ->get(route('apar-inspections.index', ['search' => 'APAR-OKT-01']))
            ->assertOk()
            ->assertSee('APAR-OKT-01')
            ->assertDontSee('APAR-NOV-01');
    }
}
