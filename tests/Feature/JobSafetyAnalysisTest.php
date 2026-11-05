<?php

namespace Tests\Feature;

use App\Models\JobSafetyAnalysis;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class JobSafetyAnalysisTest extends TestCase
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
            ['name' => 'User JSA', 'password' => 'rahasia123']
        );
        $user->roles()->syncWithoutDetaching([$role->id]);

        return $user;
    }

    private function allPermissions(): array
    {
        return [
            'view-jsa', 'create-jsa',
            'edit-jsa', 'delete-jsa',
        ];
    }

    private function payload(array $overrides = []): array
    {
        return array_merge([
            'nama_pekerja' => 'Andi Pratama',
            'nama_pekerjaan' => 'Penggantian Thermal Paste PC',
            'tanggal_pekerjaan' => '2026-11-05',
            'tempat' => 'Lab Komputer 1',
            'apd' => ['helm' => '1', 'sarung_tangan' => '1'],
            'apd_lain' => 'Masker N95',
            'steps' => [
                [
                    'langkah' => 'Matikan dan cabut kabel PC dari sumber listrik.',
                    'bahaya' => 'Sengatan listrik.',
                    'pengendalian' => 'Pastikan PC mati total sebelum dibongkar.',
                    'penanggung_jawab' => 'Andi Pratama',
                ],
                [
                    'langkah' => 'Bersihkan thermal paste lama dengan tisu.',
                    'bahaya' => 'Iritasi kulit.',
                    'pengendalian' => 'Gunakan sarung tangan.',
                    'penanggung_jawab' => 'Budi',
                ],
            ],
        ], $overrides);
    }

    private function jsa(array $overrides = []): JobSafetyAnalysis
    {
        return JobSafetyAnalysis::create([
            'nama_pekerja' => 'Siti Nurhaliza',
            'nama_pekerjaan' => 'Perbaikan Kabel LAN Lab Jaringan',
            'tanggal_pekerjaan' => '2026-11-04',
            'tempat' => 'Lab Jaringan',
            'apd' => JobSafetyAnalysis::normalizeApd(['helm' => '1']),
            'apd_lain' => null,
            'steps' => JobSafetyAnalysis::normalizeSteps([
                ['langkah' => 'Identifikasi titik kabel rusak.', 'bahaya' => '', 'pengendalian' => '', 'penanggung_jawab' => ''],
                ['langkah' => '', 'bahaya' => '', 'pengendalian' => '', 'penanggung_jawab' => ''],
                ['langkah' => 'Ganti kabel dan uji konektivitas.', 'bahaya' => 'Tertukar port.', 'pengendalian' => 'Labeli port sebelum dilepas.', 'penanggung_jawab' => 'Siti'],
            ]),
            ...$overrides,
        ]);
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get(route('analisis-keselamatan-pekerjaan.index'))->assertRedirect(route('login'));
    }

    public function test_sidebar_contains_menu_link(): void
    {
        $user = $this->userWith(['view-jsa'], 'menu-jsa@uji.test');

        $this->actingAs($user)
            ->get(route('analisis-keselamatan-pekerjaan.index'))
            ->assertOk()
            ->assertSee('K3 Laboratorium')
            ->assertSee('href="'.route('analisis-keselamatan-pekerjaan.index').'"', false)
            ->assertSee('Analisis Keselamatan Pekerjaan');
    }

    public function test_sidebar_hides_menu_without_permission(): void
    {
        $user = $this->userWith([], 'tanpa-menu-jsa@uji.test');

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertDontSee(route('analisis-keselamatan-pekerjaan.index'));
    }

    public function test_index_displays_jsas(): void
    {
        $user = $this->userWith(['view-jsa'], 'viewer-jsa@uji.test');
        $this->jsa();

        $this->actingAs($user)
            ->get(route('analisis-keselamatan-pekerjaan.index'))
            ->assertOk()
            ->assertSee('Perbaikan Kabel LAN Lab Jaringan')
            ->assertSee('Lab Jaringan')
            ->assertSee('JSA-');
    }

    public function test_store_creates_jsa_with_normalized_data(): void
    {
        $user = $this->userWith(['view-jsa', 'create-jsa'], 'creator-jsa@uji.test');

        $payload = $this->payload();
        $payload['apd'] = ['helm' => '1', 'unknown_key' => '1'];
        $payload['steps'][] = [
            'langkah' => '   ',
            'bahaya' => '   ',
            'pengendalian' => '   ',
            'penanggung_jawab' => '   ',
        ];

        $this->actingAs($user)
            ->from(route('analisis-keselamatan-pekerjaan.create'))
            ->post(route('analisis-keselamatan-pekerjaan.store'), $payload)
            ->assertRedirect(route('analisis-keselamatan-pekerjaan.index'))
            ->assertSessionHas('success');

        $jsa = JobSafetyAnalysis::first();
        $this->assertNotNull($jsa);
        $this->assertStringStartsWith('JSA-', $jsa->kode);
        $this->assertSame($user->id, $jsa->created_by);
        $this->assertSame(['helm' => true], $jsa->apd);
        $this->assertSame('Masker N95', $jsa->apd_lain);
        $this->assertCount(2, $jsa->steps);
        $this->assertSame('Matikan dan cabut kabel PC dari sumber listrik.', $jsa->steps[0]['langkah']);
        $this->assertSame('Andi Pratama', $jsa->steps[0]['penanggung_jawab']);
    }

    public function test_store_requires_at_least_one_step_with_langkah(): void
    {
        $user = $this->userWith(['create-jsa'], 'invalid-jsa@uji.test');

        $this->actingAs($user)
            ->post(route('analisis-keselamatan-pekerjaan.store'), array_merge($this->payload(), [
                'steps' => [
                    ['langkah' => '', 'bahaya' => '', 'pengendalian' => '', 'penanggung_jawab' => ''],
                ],
            ]))
            ->assertSessionHasErrors(['steps']);

        $this->assertDatabaseCount('job_safety_analyses', 0);
    }

    public function test_store_requires_required_fields(): void
    {
        $user = $this->userWith(['create-jsa'], 'invalid2-jsa@uji.test');

        $this->actingAs($user)
            ->post(route('analisis-keselamatan-pekerjaan.store'), array_merge($this->payload(), [
                'nama_pekerjaan' => '',
                'tempat' => '',
            ]))
            ->assertSessionHasErrors(['nama_pekerjaan', 'tempat']);

        $this->assertDatabaseCount('job_safety_analyses', 0);
    }

    public function test_update_changes_jsa_fields(): void
    {
        $user = $this->userWith(['edit-jsa'], 'editor-jsa@uji.test');
        $jsa = $this->jsa();

        $payload = $this->payload();
        $payload['nama_pekerjaan'] = 'Upgrade RAM Lab Komputer 2';
        $payload['apd'] = ['kacamata' => '1'];

        $this->actingAs($user)
            ->put(route('analisis-keselamatan-pekerjaan.update', $jsa), $payload)
            ->assertRedirect(route('analisis-keselamatan-pekerjaan.index'))
            ->assertSessionHas('success');

        $jsa->refresh();
        $this->assertSame('Upgrade RAM Lab Komputer 2', $jsa->nama_pekerjaan);
        $this->assertSame(['kacamata' => true], $jsa->apd);
        $this->assertCount(2, $jsa->steps);
    }

    public function test_destroy_and_bulk_destroy_remove_jsas(): void
    {
        $user = $this->userWith(['delete-jsa'], 'deleter-jsa@uji.test');
        $one = $this->jsa();
        $two = $this->jsa(['nama_pekerjaan' => 'Pekerjaan Kedua']);

        $this->actingAs($user)
            ->delete(route('analisis-keselamatan-pekerjaan.destroy', $one))
            ->assertRedirect(route('analisis-keselamatan-pekerjaan.index'))
            ->assertSessionHas('success');
        $this->assertDatabaseMissing('job_safety_analyses', ['id' => $one->id]);

        $this->actingAs($user)
            ->delete(route('analisis-keselamatan-pekerjaan.bulk-destroy'), ['ids' => [$two->id]])
            ->assertRedirect(route('analisis-keselamatan-pekerjaan.index'))
            ->assertSessionHas('success');
        $this->assertDatabaseMissing('job_safety_analyses', ['id' => $two->id]);
    }

    public function test_print_page_shows_official_form_content(): void
    {
        $user = $this->userWith(['view-jsa'], 'printer-jsa@uji.test');
        $jsa = $this->jsa();

        $this->actingAs($user)
            ->get(route('analisis-keselamatan-pekerjaan.print', $jsa))
            ->assertOk()
            ->assertSee('Formulir Analisis Keselamatan Pekerjaan / Job Safety Analysis (JSA)')
            ->assertSee('Siti Nurhaliza')
            ->assertSee('Perbaikan Kabel LAN Lab Jaringan')
            ->assertSee('Identifikasi titik kabel rusak.')
            ->assertSee('006/K3 Lab/UNISA/XI/2024');
    }

    public function test_routes_are_forbidden_without_permission(): void
    {
        $user = $this->userWith([], 'tanpa-akses-jsa@uji.test');
        $jsa = $this->jsa();

        $this->actingAs($user)->get(route('analisis-keselamatan-pekerjaan.index'))->assertForbidden();
        $this->actingAs($user)->get(route('analisis-keselamatan-pekerjaan.create'))->assertForbidden();
        $this->actingAs($user)->get(route('analisis-keselamatan-pekerjaan.edit', $jsa))->assertForbidden();
        $this->actingAs($user)->get(route('analisis-keselamatan-pekerjaan.print', $jsa))->assertForbidden();
        $this->actingAs($user)->delete(route('analisis-keselamatan-pekerjaan.destroy', $jsa))->assertForbidden();
    }

    public function test_index_filters_by_month_and_search(): void
    {
        $user = $this->userWith(['view-jsa'], 'filter-jsa@uji.test');
        $this->jsa(['nama_pekerjaan' => 'Pekerjaan Oktober', 'tanggal_pekerjaan' => '2026-10-15']);
        $this->jsa(['nama_pekerjaan' => 'Pekerjaan November', 'tanggal_pekerjaan' => '2026-11-05']);

        $this->actingAs($user)
            ->get(route('analisis-keselamatan-pekerjaan.index', ['bulan' => 11, 'tahun' => 2026]))
            ->assertOk()
            ->assertSee('Pekerjaan November')
            ->assertDontSee('Pekerjaan Oktober');

        $this->actingAs($user)
            ->get(route('analisis-keselamatan-pekerjaan.index', ['search' => 'Pekerjaan Oktober']))
            ->assertOk()
            ->assertSee('Pekerjaan Oktober')
            ->assertDontSee('Pekerjaan November');
    }
}
