<?php

namespace Tests\Feature;

use App\Models\HseInspection;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HseInspectionTest extends TestCase
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
            ['name' => 'User HSE', 'password' => 'rahasia123']
        );
        $user->roles()->syncWithoutDetaching([$role->id]);

        return $user;
    }

    private function payload(array $overrides = []): array
    {
        return array_merge([
            'lokasi' => 'Gedung Lab Terpadu',
            'diperiksa_oleh' => 'Petugas K3',
            'tanggal_inspeksi' => '2026-11-05',
            'findings' => [
                [
                    'lokasi' => 'Lab Komputer 1',
                    'temuan' => 'Kabel terkelupas',
                    'unsafe_condition' => '1',
                    'tanggal_kejadian' => '2026-11-04',
                    'tindakan_perbaikan' => 'Kabel diganti',
                    'tanggal_perbaikan' => '2026-11-05',
                    'pic' => 'Teknisi A',
                ],
            ],
        ], $overrides);
    }

    private function inspection(array $overrides = []): HseInspection
    {
        return HseInspection::create([
            'lokasi' => 'Gedung Lab Terpadu',
            'diperiksa_oleh' => 'Petugas K3',
            'tanggal_inspeksi' => '2026-11-04',
            'findings' => HseInspection::normalizeFindings([[
                'lokasi' => 'Lab Jaringan',
                'temuan' => 'Stop kontak longgar',
                'unsafe_condition' => true,
                'tindakan_perbaikan' => 'Stop kontak diperbaiki',
                'pic' => 'Teknisi B',
            ]]),
            ...$overrides,
        ]);
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get(route('hse-inspections.index'))->assertRedirect(route('login'));
    }

    public function test_sidebar_contains_menu_link(): void
    {
        $user = $this->userWith(['view-hse-inspections'], 'menu-hse@uji.test');

        $this->actingAs($user)
            ->get(route('hse-inspections.index'))
            ->assertOk()
            ->assertSee('href="'.route('hse-inspections.index').'"', false)
            ->assertSee('Inspeksi HSE');
    }

    public function test_sidebar_hides_menu_without_permission(): void
    {
        $user = $this->userWith([], 'tanpa-menu-hse@uji.test');

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertDontSee(route('hse-inspections.index'));
    }

    public function test_index_displays_inspections(): void
    {
        $user = $this->userWith(['view-hse-inspections'], 'viewer-hse@uji.test');
        $this->inspection();

        $this->actingAs($user)
            ->get(route('hse-inspections.index'))
            ->assertOk()
            ->assertSee('Gedung Lab Terpadu')
            ->assertSee('Petugas K3')
            ->assertSee('HSE-');
    }

    public function test_store_creates_inspection_and_drops_empty_rows(): void
    {
        $user = $this->userWith(['create-hse-inspections'], 'creator-hse@uji.test');

        $payload = $this->payload();
        $payload['findings'][] = HseInspection::blankFinding();

        $this->actingAs($user)
            ->post(route('hse-inspections.store'), $payload)
            ->assertRedirect(route('hse-inspections.index'))
            ->assertSessionHas('success');

        $inspection = HseInspection::first();
        $this->assertNotNull($inspection);
        $this->assertStringStartsWith('HSE-', $inspection->kode);
        $this->assertSame($user->id, $inspection->created_by);
        $this->assertCount(1, $inspection->findings);
        $this->assertTrue($inspection->findings[0]['unsafe_condition']);
        $this->assertFalse($inspection->findings[0]['unsafe_action']);
        $this->assertSame('Kabel terkelupas', $inspection->findings[0]['temuan']);
    }

    public function test_store_allows_inspection_without_findings(): void
    {
        $user = $this->userWith(['create-hse-inspections'], 'kosong-hse@uji.test');

        $this->actingAs($user)
            ->post(route('hse-inspections.store'), $this->payload(['findings' => []]))
            ->assertRedirect(route('hse-inspections.index'));

        $inspection = HseInspection::first();
        $this->assertSame([], $inspection->findings);
        $this->assertSame(0, $inspection->jumlah_temuan);
    }

    public function test_store_requires_header_fields(): void
    {
        $user = $this->userWith(['create-hse-inspections'], 'invalid-hse@uji.test');

        $this->actingAs($user)
            ->post(route('hse-inspections.store'), $this->payload([
                'lokasi' => '',
                'diperiksa_oleh' => '',
                'tanggal_inspeksi' => '',
            ]))
            ->assertSessionHasErrors(['lokasi', 'diperiksa_oleh', 'tanggal_inspeksi']);

        $this->assertDatabaseCount('hse_inspections', 0);
    }

    public function test_update_replaces_findings(): void
    {
        $user = $this->userWith(['edit-hse-inspections'], 'editor-hse@uji.test');
        $inspection = $this->inspection();

        $payload = $this->payload([
            'lokasi' => 'Gedung B',
            'findings' => [[
                'lokasi' => 'Lab Kimia',
                'temuan' => 'APD tidak lengkap',
                'unsafe_action' => '1',
                'tindakan_perbaikan' => 'Briefing ulang',
                'pic' => 'Koordinator Lab',
            ]],
        ]);

        $this->actingAs($user)
            ->put(route('hse-inspections.update', $inspection), $payload)
            ->assertRedirect(route('hse-inspections.index'))
            ->assertSessionHas('success');

        $inspection->refresh();
        $this->assertSame('Gedung B', $inspection->lokasi);
        $this->assertCount(1, $inspection->findings);
        $this->assertTrue($inspection->findings[0]['unsafe_action']);
        $this->assertFalse($inspection->findings[0]['unsafe_condition']);
    }

    public function test_destroy_and_bulk_destroy_remove_inspections(): void
    {
        $user = $this->userWith(['delete-hse-inspections'], 'deleter-hse@uji.test');
        $one = $this->inspection();
        $two = $this->inspection(['lokasi' => 'Gedung C']);

        $this->actingAs($user)
            ->delete(route('hse-inspections.destroy', $one))
            ->assertRedirect(route('hse-inspections.index'));
        $this->assertDatabaseMissing('hse_inspections', ['id' => $one->id]);

        $this->actingAs($user)
            ->delete(route('hse-inspections.bulk-destroy'), ['ids' => [$two->id]])
            ->assertRedirect(route('hse-inspections.index'));
        $this->assertDatabaseMissing('hse_inspections', ['id' => $two->id]);
    }

    public function test_print_page_shows_form_content(): void
    {
        $user = $this->userWith(['view-hse-inspections'], 'printer-hse@uji.test');
        $inspection = $this->inspection();

        $this->actingAs($user)
            ->get(route('hse-inspections.print', $inspection))
            ->assertOk()
            ->assertSee('004/K3 Lab/UNISA/XI/2024')
            ->assertSee('INSPEKSI HSE')
            ->assertSee('Stop kontak longgar')
            ->assertSee('Dr. Dhesi Ari Astuti, S.ST., M.Kes.')
            ->assertSee('Asih Puji Utami, S.KM., M.Kes.');
    }

    public function test_routes_are_forbidden_without_permission(): void
    {
        $user = $this->userWith([], 'tanpa-akses-hse@uji.test');
        $inspection = $this->inspection();

        $this->actingAs($user)->get(route('hse-inspections.index'))->assertForbidden();
        $this->actingAs($user)->get(route('hse-inspections.create'))->assertForbidden();
        $this->actingAs($user)->get(route('hse-inspections.edit', $inspection))->assertForbidden();
        $this->actingAs($user)->get(route('hse-inspections.print', $inspection))->assertForbidden();
        $this->actingAs($user)->delete(route('hse-inspections.destroy', $inspection))->assertForbidden();
    }

    public function test_index_filters_by_month_and_search(): void
    {
        $user = $this->userWith(['view-hse-inspections'], 'filter-hse@uji.test');
        $this->inspection(['lokasi' => 'Gedung Oktober', 'tanggal_inspeksi' => '2026-10-15']);
        $this->inspection(['lokasi' => 'Gedung November', 'tanggal_inspeksi' => '2026-11-05']);

        $this->actingAs($user)
            ->get(route('hse-inspections.index', ['bulan' => 11, 'tahun' => 2026]))
            ->assertOk()
            ->assertSee('Gedung November')
            ->assertDontSee('Gedung Oktober');

        $this->actingAs($user)
            ->get(route('hse-inspections.index', ['search' => 'Oktober']))
            ->assertOk()
            ->assertSee('Gedung Oktober')
            ->assertDontSee('Gedung November');
    }
}
