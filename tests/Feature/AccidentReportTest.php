<?php

namespace Tests\Feature;

use App\Models\AccidentReport;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AccidentReportTest extends TestCase
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
            ['name' => 'User Berita Acara', 'password' => 'rahasia123']
        );
        $user->roles()->syncWithoutDetaching([$role->id]);

        return $user;
    }

    private function allPermissions(): array
    {
        return [
            'view-accident-reports', 'create-accident-reports',
            'edit-accident-reports', 'delete-accident-reports',
        ];
    }

    private function payload(array $overrides = []): array
    {
        return array_merge([
            'tanggal' => '2026-11-05',
            'nama_pelapor' => 'Koordinator K3',
            'jabatan_pelapor' => 'Koordinator K3 Laboratorium',
            'nama_korban' => 'Budi Santoso',
            'alamat_korban' => 'Jl. Kaliurang KM 5, Sleman',
            'ttl_korban' => 'Yogyakarta, 12 Mei 2005',
            'unit_status_korban' => 'Praktikan',
            'uraian_kejadian' => [
                'Praktikan tersengat listrik saat mengganti komponen.',
                'Komputer mati mendadak saat praktik berlangsung.',
            ],
            'dampak' => 'Luka bakar ringan pada tangan kanan.',
            'saksi_satu' => 'Siti Aminah',
            'saksi_dua' => 'Ahmad Fauzi',
        ], $overrides);
    }

    private function report(array $overrides = []): AccidentReport
    {
        return AccidentReport::create([
            'tanggal' => '2026-11-04',
            'nama_pelapor' => 'Koordinator K3',
            'jabatan_pelapor' => 'Koordinator K3 Laboratorium',
            'nama_korban' => 'Andi Wijaya',
            'alamat_korban' => 'Jl. Gejayan, Sleman',
            'ttl_korban' => 'Solo, 3 Maret 2004',
            'unit_status_korban' => 'Peneliti',
            'uraian_kejadian' => AccidentReport::normalizeUraian([
                'Tumpahan bahan kimia di meja kerja.',
                '',
                'Korban terpeleset saat membersihkan.',
            ]),
            'dampak' => 'Terkilir pada pergelangan kaki.',
            'saksi_satu' => 'Dewi Lestari',
            'saksi_dua' => 'Rudi Hartono',
            ...$overrides,
        ]);
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get(route('berita-acara-kecelakaan.index'))->assertRedirect(route('login'));
    }

    public function test_sidebar_contains_menu_link(): void
    {
        $user = $this->userWith(['view-accident-reports'], 'menu-bak@uji.test');

        $this->actingAs($user)
            ->get(route('berita-acara-kecelakaan.index'))
            ->assertOk()
            ->assertSee('K3 Laboratorium')
            ->assertSee('href="'.route('berita-acara-kecelakaan.index').'"', false)
            ->assertSee('Berita Acara Kecelakaan');
    }

    public function test_sidebar_hides_menu_without_permission(): void
    {
        $user = $this->userWith([], 'tanpa-menu-bak@uji.test');

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertDontSee(route('berita-acara-kecelakaan.index'));
    }

    public function test_index_displays_reports(): void
    {
        $user = $this->userWith(['view-accident-reports'], 'viewer-bak@uji.test');
        $this->report();

        $this->actingAs($user)
            ->get(route('berita-acara-kecelakaan.index'))
            ->assertOk()
            ->assertSee('Andi Wijaya')
            ->assertSee('Peneliti')
            ->assertSee('BAK-');
    }

    public function test_store_creates_report_with_normalized_uraian(): void
    {
        $user = $this->userWith(['view-accident-reports', 'create-accident-reports'], 'creator-bak@uji.test');

        $payload = $this->payload();
        $payload['uraian_kejadian'] = [
            '  Praktikan tersengat listrik.  ',
            '',
            'Komputer mati mendadak.',
        ];

        $this->actingAs($user)
            ->from(route('berita-acara-kecelakaan.create'))
            ->post(route('berita-acara-kecelakaan.store'), $payload)
            ->assertRedirect(route('berita-acara-kecelakaan.index'))
            ->assertSessionHas('success');

        $report = AccidentReport::first();
        $this->assertNotNull($report);
        $this->assertStringStartsWith('BAK-', $report->kode);
        $this->assertSame($user->id, $report->created_by);
        $this->assertSame(
            ['Praktikan tersengat listrik.', 'Komputer mati mendadak.'],
            $report->uraian_kejadian
        );
    }

    public function test_store_requires_required_fields(): void
    {
        $user = $this->userWith(['create-accident-reports'], 'invalid-bak@uji.test');

        $this->actingAs($user)
            ->post(route('berita-acara-kecelakaan.store'), array_merge($this->payload(), [
                'nama_korban' => '',
                'uraian_kejadian' => [''],
            ]))
            ->assertSessionHasErrors(['nama_korban', 'uraian_kejadian']);

        $this->assertDatabaseCount('accident_reports', 0);
    }

    public function test_update_changes_report_fields(): void
    {
        $user = $this->userWith(['edit-accident-reports'], 'editor-bak@uji.test');
        $report = $this->report();

        $payload = $this->payload();
        $payload['nama_korban'] = 'Budi Santoso, S.Kom.';
        $payload['saksi_satu'] = 'Saksi Baru';

        $this->actingAs($user)
            ->put(route('berita-acara-kecelakaan.update', $report), $payload)
            ->assertRedirect(route('berita-acara-kecelakaan.index'))
            ->assertSessionHas('success');

        $report->refresh();
        $this->assertSame('Budi Santoso, S.Kom.', $report->nama_korban);
        $this->assertSame('Saksi Baru', $report->saksi_satu);
    }

    public function test_destroy_and_bulk_destroy_remove_reports(): void
    {
        $user = $this->userWith(['delete-accident-reports'], 'deleter-bak@uji.test');
        $one = $this->report();
        $two = $this->report(['nama_korban' => 'Korban Kedua']);

        $this->actingAs($user)
            ->delete(route('berita-acara-kecelakaan.destroy', $one))
            ->assertRedirect(route('berita-acara-kecelakaan.index'))
            ->assertSessionHas('success');
        $this->assertDatabaseMissing('accident_reports', ['id' => $one->id]);

        $this->actingAs($user)
            ->delete(route('berita-acara-kecelakaan.bulk-destroy'), ['ids' => [$two->id]])
            ->assertRedirect(route('berita-acara-kecelakaan.index'))
            ->assertSessionHas('success');
        $this->assertDatabaseMissing('accident_reports', ['id' => $two->id]);
    }

    public function test_print_page_shows_official_form_content(): void
    {
        $user = $this->userWith(['view-accident-reports'], 'printer-bak@uji.test');
        $report = $this->report();

        $this->actingAs($user)
            ->get(route('berita-acara-kecelakaan.print', $report))
            ->assertOk()
            ->assertSee('Berita Acara Kecelakaan Kerja')
            ->assertSee('Andi Wijaya')
            ->assertSee('Terkilir pada pergelangan kaki.')
            ->assertSee('Dewi Lestari')
            ->assertSee('Rudi Hartono')
            ->assertSee('003/K3 Lab/UNISA/XI/2024');
    }

    public function test_routes_are_forbidden_without_permission(): void
    {
        $user = $this->userWith([], 'tanpa-akses-bak@uji.test');
        $report = $this->report();

        $this->actingAs($user)->get(route('berita-acara-kecelakaan.index'))->assertForbidden();
        $this->actingAs($user)->get(route('berita-acara-kecelakaan.create'))->assertForbidden();
        $this->actingAs($user)->get(route('berita-acara-kecelakaan.edit', $report))->assertForbidden();
        $this->actingAs($user)->get(route('berita-acara-kecelakaan.print', $report))->assertForbidden();
        $this->actingAs($user)->delete(route('berita-acara-kecelakaan.destroy', $report))->assertForbidden();
    }

    public function test_index_filters_by_month_and_search(): void
    {
        $user = $this->userWith(['view-accident-reports'], 'filter-bak@uji.test');
        $this->report(['nama_korban' => 'Korban Oktober', 'tanggal' => '2026-10-15']);
        $this->report(['nama_korban' => 'Korban November', 'tanggal' => '2026-11-05']);

        $this->actingAs($user)
            ->get(route('berita-acara-kecelakaan.index', ['bulan' => 11, 'tahun' => 2026]))
            ->assertOk()
            ->assertSee('Korban November')
            ->assertDontSee('Korban Oktober');

        $this->actingAs($user)
            ->get(route('berita-acara-kecelakaan.index', ['search' => 'Korban Oktober']))
            ->assertOk()
            ->assertSee('Korban Oktober')
            ->assertDontSee('Korban November');
    }
}
