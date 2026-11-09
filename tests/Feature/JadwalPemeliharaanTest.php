<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\Computer;
use App\Models\JadwalPemeliharaan;
use App\Models\Laboratory;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class JadwalPemeliharaanTest extends TestCase
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
            ['name' => 'User Jadwal', 'password' => 'rahasia123']
        );
        $user->roles()->syncWithoutDetaching([$role->id]);

        return $user;
    }

    private function allPermissions(): array
    {
        return [
            'view-jadwal-pemeliharaan', 'create-jadwal-pemeliharaan',
            'edit-jadwal-pemeliharaan', 'delete-jadwal-pemeliharaan',
            'print-jadwal-pemeliharaan',
        ];
    }

    private function academicYear(): AcademicYear
    {
        return AcademicYear::firstOrCreate(
            ['name' => 'Semester Genap 2025/2026'],
            ['start_year' => 2026, 'end_year' => 2026, 'status' => 'Aktif']
        );
    }

    private function payload(array $overrides = []): array
    {
        return array_merge([
            'laboratorium_name' => 'Lab Komputer 1',
            'academic_year_id' => $this->academicYear()->id,
            'pic' => 'Budi Hartono',
            'tanggal_penyusunan' => '2026-09-01',
            'disusun_oleh' => 'Budi Hartono',
            'items' => [
                ['nama_alat_bahan' => 'AC 2 PK', 'kode_inventaris' => ['INV-AC-001', 'INV-AC-002'], 'lokasi' => 'Ruang 1', 'jenis_pemeliharaan' => 'Pembersihan', 'frekuensi' => 'Bulanan', 'jadwal' => '2026-09-05', 'pic' => 'Budi', 'status' => 'Terjadwal', 'catatan_hasil' => 'OK'],
                ['nama_alat_bahan' => 'Microscope', 'kode_inventaris' => ['INV-MIC-002'], 'lokasi' => 'Lab B', 'jenis_pemeliharaan' => 'Pengecekan fungsi', 'frekuensi' => '6 Bulanan', 'jadwal' => '2026-09-10', 'pic' => 'Budi', 'status' => 'Selesai', 'catatan_hasil' => 'Baik'],
            ],
        ], $overrides);
    }

    private function jadwal(array $overrides = []): JadwalPemeliharaan
    {
        $year = $this->academicYear();
        $j = JadwalPemeliharaan::create(array_merge([
            'kode_dokumen' => JadwalPemeliharaan::DOC_NUMBER,
            'laboratorium_name' => 'Lab Komputer 1',
            'academic_year_id' => $year->id,
            'periode' => $year->name,
            'pic' => 'Budi Hartono',
            'tanggal_penyusunan' => '2026-09-01',
        ], $overrides));
        $j->items()->create(['nama_alat_bahan' => 'AC 2 PK', 'status' => 'Terjadwal']);

        return $j;
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get(route('jadwal-pemeliharaan.index'))->assertRedirect(route('login'));
    }

    public function test_sidebar_contains_menu_link(): void
    {
        $user = $this->userWith(['view-jadwal-pemeliharaan'], 'menu-jadwal@uji.test');

        $this->actingAs($user)
            ->get(route('jadwal-pemeliharaan.index'))
            ->assertOk()
            ->assertSee('Menu')
            ->assertSee('href="'.route('jadwal-pemeliharaan.index').'"', false)
            ->assertSee('Jadwal Pemeliharaan Alat', false);
    }

    public function test_sidebar_hides_menu_without_permission(): void
    {
        $user = $this->userWith([], 'tanpa-menu-jadwal@uji.test');

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertDontSee(route('jadwal-pemeliharaan.index'));
    }

    public function test_index_displays_jadwal(): void
    {
        $user = $this->userWith(['view-jadwal-pemeliharaan'], 'viewer-jadwal@uji.test');
        $this->jadwal();

        $this->actingAs($user)
            ->get(route('jadwal-pemeliharaan.index'))
            ->assertOk()
            ->assertSee('Semester Genap 2025/2026')
            ->assertSee('Lab Komputer 1')
            ->assertSee('Terjadwal');
    }

    public function test_store_creates_jadwal_with_items(): void
    {
        $user = $this->userWith(['view-jadwal-pemeliharaan', 'create-jadwal-pemeliharaan'], 'creator-jadwal@uji.test');

        $this->actingAs($user)
            ->from(route('jadwal-pemeliharaan.create'))
            ->post(route('jadwal-pemeliharaan.store'), $this->payload())
            ->assertRedirect()
            ->assertSessionHas('success');

        $jadwal = JadwalPemeliharaan::first();
        $this->assertNotNull($jadwal);
        $this->assertSame(JadwalPemeliharaan::DOC_NUMBER, $jadwal->kode_dokumen);
        $this->assertSame('Semester Genap 2025/2026', $jadwal->periode);
        $this->assertCount(2, $jadwal->items);
        $this->assertSame($user->id, $jadwal->created_by);
        $first = $jadwal->items->firstWhere('nama_alat_bahan', 'AC 2 PK');
        $this->assertSame(['INV-AC-001', 'INV-AC-002'], $first->kode_list);
    }

    public function test_store_requires_item_name(): void
    {
        $user = $this->userWith(['create-jadwal-pemeliharaan'], 'invalid-jadwal@uji.test');
        $payload = $this->payload();
        $payload['items'] = [['nama_alat_bahan' => '', 'status' => 'Terjadwal']];

        $this->actingAs($user)
            ->post(route('jadwal-pemeliharaan.store'), $payload)
            ->assertSessionHasErrors(['items.0.nama_alat_bahan']);

        $this->assertDatabaseCount('jadwal_pemeliharaans', 0);
    }

    public function test_update_and_destroy(): void
    {
        $user = $this->userWith(['edit-jadwal-pemeliharaan', 'delete-jadwal-pemeliharaan'], 'editor-jadwal@uji.test');
        $jadwal = $this->jadwal();

        $year2 = AcademicYear::firstOrCreate(
            ['name' => 'Semester Ganjil 2026/2027'],
            ['start_year' => 2026, 'end_year' => 2027, 'status' => 'Non Aktif']
        );
        $payload = $this->payload();
        $payload['academic_year_id'] = $year2->id;
        $this->actingAs($user)
            ->put(route('jadwal-pemeliharaan.update', $jadwal), $payload)
            ->assertSessionHas('success');

        $jadwal->refresh();
        $this->assertSame($year2->id, $jadwal->academic_year_id);
        $this->assertSame('Semester Ganjil 2026/2027', $jadwal->periode);
        $this->assertCount(2, $jadwal->items);

        $this->actingAs($user)
            ->delete(route('jadwal-pemeliharaan.destroy', $jadwal))
            ->assertRedirect(route('jadwal-pemeliharaan.index'))
            ->assertSessionHas('success');
        $this->assertDatabaseMissing('jadwal_pemeliharaans', ['id' => $jadwal->id]);
        $this->assertDatabaseCount('jadwal_pemeliharaan_items', 0);
    }

    public function test_print_shows_fm02_content(): void
    {
        $user = $this->userWith(['view-jadwal-pemeliharaan', 'print-jadwal-pemeliharaan'], 'printer-jadwal@uji.test');
        $jadwal = $this->jadwal();

        $this->actingAs($user)
            ->get(route('jadwal-pemeliharaan.print', $jadwal))
            ->assertOk()
            ->assertSee('Jadwal Pemeliharaan Alat dan Bahan Laboratorium')
            ->assertSee(JadwalPemeliharaan::DOC_NUMBER)
            ->assertSee('AC 2 PK')
            ->assertSee('Disusun oleh')
            ->assertSee('Diperiksa oleh')
            ->assertSee('Mengetahui');
    }

    public function test_routes_are_forbidden_without_permission(): void
    {
        $user = $this->userWith([], 'tanpa-akses-jadwal@uji.test');
        $jadwal = $this->jadwal();

        $this->actingAs($user)->get(route('jadwal-pemeliharaan.index'))->assertForbidden();
        $this->actingAs($user)->get(route('jadwal-pemeliharaan.create'))->assertForbidden();
        $this->actingAs($user)->get(route('jadwal-pemeliharaan.edit', $jadwal))->assertForbidden();
        $this->actingAs($user)->get(route('jadwal-pemeliharaan.print', $jadwal))->assertForbidden();
        $this->actingAs($user)->delete(route('jadwal-pemeliharaan.destroy', $jadwal))->assertForbidden();
    }

    public function test_create_and_edit_render_without_error(): void
    {
        $user = $this->userWith(['create-jadwal-pemeliharaan', 'edit-jadwal-pemeliharaan'], 'form-jadwal@uji.test');
        $jadwal = $this->jadwal();

        $this->actingAs($user)->get(route('jadwal-pemeliharaan.create'))
            ->assertOk()
            ->assertSee('Identitas Dokumen')
            ->assertSee('Pilih Tahun Ajaran', false);

        $this->actingAs($user)->get(route('jadwal-pemeliharaan.edit', $jadwal))
            ->assertOk()
            ->assertSee('Identitas Dokumen');
    }

    public function test_inventaris_returns_computers_of_lab(): void
    {
        $user = $this->userWith(['view-jadwal-pemeliharaan'], 'inv-jadwal@uji.test');
        $lab = Laboratory::create(['name' => 'Lab Uji Inventaris', 'code' => 'LAB-INV', 'location' => 'Gedung A']);
        Computer::create(['code' => 'PC-INV-001', 'laboratory_id' => $lab->id, 'status' => 'Aktif']);
        Computer::create(['code' => 'PC-INV-002', 'laboratory_id' => $lab->id, 'status' => 'Aktif']);

        $this->actingAs($user)
            ->getJson(route('jadwal-pemeliharaan.inventaris', ['laboratory_id' => $lab->id]))
            ->assertOk()
            ->assertJsonPath('data.0.code', 'PC-INV-001')
            ->assertJsonPath('data.1.code', 'PC-INV-002');
    }

    public function test_store_requires_academic_year(): void
    {
        $user = $this->userWith(['create-jadwal-pemeliharaan'], 'year-jadwal@uji.test');
        $payload = $this->payload();
        unset($payload['academic_year_id']);

        $this->actingAs($user)
            ->post(route('jadwal-pemeliharaan.store'), $payload)
            ->assertSessionHasErrors(['academic_year_id']);

        $this->assertDatabaseCount('jadwal_pemeliharaans', 0);
    }
}
