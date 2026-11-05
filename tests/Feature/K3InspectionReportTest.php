<?php

namespace Tests\Feature;

use App\Models\K3InspectionReport;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class K3InspectionReportTest extends TestCase
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
            ['name' => 'User K3', 'password' => 'rahasia123']
        );
        $user->roles()->syncWithoutDetaching([$role->id]);

        return $user;
    }

    private function payload(array $overrides = []): array
    {
        $checklist = [];
        foreach (K3InspectionReport::defaultChecklist() as $idx => $row) {
            $checklist[$idx] = [
                'ya' => ($idx === 0) ? '1' : null,
                'tidak' => null,
                'na' => null,
                'catatan' => ($idx === 0) ? 'Sudah terpampang' : '',
            ];
        }

        return array_merge([
            'hari' => 'Senin',
            'tanggal_ba' => '2026-11-05',
            'bulan_ba' => 'November',
            'tahun_ba' => '2026',
            'bulan_inspeksi' => 'November 2026',
            'lokasi' => 'UPT Laboratorium Unisa Yogyakarta',
            'pemeriksa_nama' => K3InspectionReport::PEMERIKSA_NAMA,
            'pemeriksa_jabatan' => K3InspectionReport::PEMERIKSA_JABATAN,
            'checklist' => $checklist,
            'findings' => [[
                'temuan' => 'APAR habis masa pakai',
                'dokumentasi' => 'Foto',
                'saran_perbaikan' => 'Ganti APAR',
                'pelaksana' => 'Teknisi',
                'target' => 'Akhir Bulan',
                'catatan' => 'Segera ditindaklanjuti',
            ]],
        ], $overrides);
    }

    private function report(array $overrides = []): K3InspectionReport
    {
        return K3InspectionReport::create([
            'hari' => 'Selasa',
            'tanggal_ba' => '2026-11-04',
            'bulan_ba' => 'November',
            'tahun_ba' => '2026',
            'bulan_inspeksi' => 'Oktober 2026',
            'lokasi' => 'UPT Laboratorium Unisa Yogyakarta',
            'pemeriksa_nama' => K3InspectionReport::PEMERIKSA_NAMA,
            'pemeriksa_jabatan' => K3InspectionReport::PEMERIKSA_JABATAN,
            'checklist_items' => K3InspectionReport::normalizeChecklist([]),
            'findings' => K3InspectionReport::normalizeFindings([[
                'temuan' => 'P3K kurang lengkap',
                'saran_perbaikan' => 'Lengkapi isi kotak P3K',
                'pelaksana' => 'Laboran',
            ]]),
            ...$overrides,
        ]);
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get(route('k3-inspection-reports.index'))->assertRedirect(route('login'));
    }

    public function test_sidebar_contains_menu_link(): void
    {
        $user = $this->userWith(['view-k3-inspection-reports'], 'menu-k3insp@uji.test');

        $this->actingAs($user)
            ->get(route('k3-inspection-reports.index'))
            ->assertOk()
            ->assertSee('href="'.route('k3-inspection-reports.index').'"', false)
            ->assertSee('Laporan Inspeksi K3');
    }

    public function test_sidebar_hides_menu_without_permission(): void
    {
        $user = $this->userWith([], 'tanpa-menu-k3insp@uji.test');

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertDontSee(route('k3-inspection-reports.index'));
    }

    public function test_index_displays_reports(): void
    {
        $user = $this->userWith(['view-k3-inspection-reports'], 'viewer-k3insp@uji.test');
        $this->report();

        $this->actingAs($user)
            ->get(route('k3-inspection-reports.index'))
            ->assertOk()
            ->assertSee('Oktober 2026')
            ->assertSee('K3INSP-');
    }

    public function test_store_creates_report_with_checklist_and_findings(): void
    {
        $user = $this->userWith(['create-k3-inspection-reports'], 'creator-k3insp@uji.test');

        $this->actingAs($user)
            ->post(route('k3-inspection-reports.store'), $this->payload())
            ->assertRedirect(route('k3-inspection-reports.index'))
            ->assertSessionHas('success');

        $report = K3InspectionReport::first();
        $this->assertNotNull($report);
        $this->assertStringStartsWith('K3INSP-', $report->kode);
        $this->assertSame($user->id, $report->created_by);
        $this->assertCount(33, $report->checklist_items);
        $this->assertTrue($report->checklist_items[0]['ya']);
        $this->assertCount(1, $report->findings);
        $this->assertSame('APAR habis masa pakai', $report->findings[0]['temuan']);
    }

    public function test_store_drops_empty_findings(): void
    {
        $user = $this->userWith(['create-k3-inspection-reports'], 'emptyfind-k3insp@uji.test');

        $payload = $this->payload();
        $payload['findings'][] = K3InspectionReport::blankFinding();

        $this->actingAs($user)
            ->post(route('k3-inspection-reports.store'), $payload)
            ->assertRedirect(route('k3-inspection-reports.index'));

        $this->assertCount(1, K3InspectionReport::first()->findings);
    }

    public function test_update_changes_report(): void
    {
        $user = $this->userWith(['edit-k3-inspection-reports'], 'editor-k3insp@uji.test');
        $report = $this->report();

        $payload = $this->payload(['lokasi' => 'Gedung Baru', 'bulan_inspeksi' => 'Desember 2026']);

        $this->actingAs($user)
            ->put(route('k3-inspection-reports.update', $report), $payload)
            ->assertRedirect(route('k3-inspection-reports.index'))
            ->assertSessionHas('success');

        $report->refresh();
        $this->assertSame('Gedung Baru', $report->lokasi);
        $this->assertSame('Desember 2026', $report->bulan_inspeksi);
    }

    public function test_destroy_and_bulk_destroy(): void
    {
        $user = $this->userWith(['delete-k3-inspection-reports'], 'deleter-k3insp@uji.test');
        $one = $this->report();
        $two = $this->report(['bulan_inspeksi' => 'September 2026']);

        $this->actingAs($user)
            ->delete(route('k3-inspection-reports.destroy', $one))
            ->assertRedirect(route('k3-inspection-reports.index'));
        $this->assertDatabaseMissing('k3_inspection_reports', ['id' => $one->id]);

        $this->actingAs($user)
            ->delete(route('k3-inspection-reports.bulk-destroy'), ['ids' => [$two->id]])
            ->assertRedirect(route('k3-inspection-reports.index'));
        $this->assertDatabaseMissing('k3_inspection_reports', ['id' => $two->id]);
    }

    public function test_print_page_shows_content(): void
    {
        $user = $this->userWith(['view-k3-inspection-reports'], 'printer-k3insp@uji.test');
        $report = $this->report();

        $this->actingAs($user)
            ->get(route('k3-inspection-reports.print', $report))
            ->assertOk()
            ->assertSee('005/K3 Lab/UNISA/XI/2024')
            ->assertSee('BERITA ACARA')
            ->assertSee('CHECKLIST INSPEKSI K3')
            ->assertSee('FORM LAPORAN INSPEKSI K3')
            ->assertSee('Asih Puji Utami, S.KM., M.Kes.')
            ->assertSee('Dr. Dhesi Ari Astuti, S.ST., M.Kes.');
    }

    public function test_routes_forbidden_without_permission(): void
    {
        $user = $this->userWith([], 'tanpa-k3insp@uji.test');
        $report = $this->report();

        $this->actingAs($user)->get(route('k3-inspection-reports.index'))->assertForbidden();
        $this->actingAs($user)->get(route('k3-inspection-reports.create'))->assertForbidden();
        $this->actingAs($user)->get(route('k3-inspection-reports.edit', $report))->assertForbidden();
        $this->actingAs($user)->get(route('k3-inspection-reports.print', $report))->assertForbidden();
        $this->actingAs($user)->delete(route('k3-inspection-reports.destroy', $report))->assertForbidden();
    }

    public function test_index_filters(): void
    {
        $user = $this->userWith(['view-k3-inspection-reports'], 'filter-k3insp@uji.test');
        $this->report(['bulan_inspeksi' => 'Januari 2026', 'tahun_ba' => '2026']);
        $this->report(['bulan_inspeksi' => 'Februari 2027', 'tahun_ba' => '2027']);

        $this->actingAs($user)
            ->get(route('k3-inspection-reports.index', ['tahun' => 2027]))
            ->assertOk()
            ->assertSee('Februari 2027')
            ->assertDontSee('Januari 2026');
    }
}
