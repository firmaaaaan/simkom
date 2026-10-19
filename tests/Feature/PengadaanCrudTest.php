<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\Pengadaan;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PengadaanCrudTest extends TestCase
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
            ['name' => 'User Pengadaan', 'password' => 'rahasia123']
        );
        $user->roles()->syncWithoutDetaching([$role->id]);

        return $user;
    }

    private function allPermissions(): array
    {
        return [
            'view-pengadaan', 'create-pengadaan', 'edit-pengadaan', 'delete-pengadaan',
        ];
    }

    private function pengadaan(array $overrides = []): Pengadaan
    {
        return Pengadaan::create(array_merge([
            'nama_barang' => 'Laptop ASUS',
            'kategori' => 'PC',
            'jumlah' => 2,
            'harga' => 7500000,
            'tanggal' => '2026-10-19',
            'keterangan' => 'Untuk praktikum',
            'status' => 'Proses',
        ], $overrides));
    }

    public function test_index_displays_pengadaan_data(): void
    {
        $user = $this->userWith(['view-pengadaan'], 'viewer-pengadaan@uji.test');
        $this->pengadaan();

        $this->actingAs($user)
            ->get(route('pengadaan.index'))
            ->assertOk()
            ->assertSee('Laptop ASUS')
            ->assertSee('PGD-');
    }

    public function test_store_creates_pengadaan_with_proses_status(): void
    {
        $user = $this->userWith(['view-pengadaan', 'create-pengadaan'], 'creator-pengadaan@uji.test');
        $activeYear = AcademicYear::create([
            'name' => 'Tahun Ajaran 2026/2027',
            'start_year' => 2026,
            'end_year' => 2027,
            'status' => 'Aktif',
        ]);

        $this->actingAs($user)
            ->from(route('pengadaan.create'))
            ->post(route('pengadaan.store'), [
                'nama_barang' => 'Monitor 24"',
                'kategori' => 'Hardware',
                'jumlah' => 5,
                'harga' => 1500000,
                'tanggal' => '2026-10-19',
                'keterangan' => 'Laboratorium Jaringan',
            ])
            ->assertRedirect(route('pengadaan.index'))
            ->assertSessionHas('success');

        $pengadaan = Pengadaan::first();
        $this->assertNotNull($pengadaan);
        $this->assertSame('Proses', $pengadaan->status);
        $this->assertSame('Hardware', $pengadaan->kategori);
        $this->assertSame($user->id, $pengadaan->created_by);
        $this->assertSame($activeYear->id, $pengadaan->academic_year_id);
        $this->assertStringStartsWith('PGD-', $pengadaan->kode);
    }

    public function test_store_rejects_invalid_kategori_and_status(): void
    {
        $user = $this->userWith(['create-pengadaan'], 'invalid-pengadaan@uji.test');

        $this->actingAs($user)
            ->post(route('pengadaan.store'), [
                'nama_barang' => 'Barang Uji',
                'kategori' => 'Lainnya',
                'jumlah' => 1,
                'harga' => 1000,
                'tanggal' => '2026-10-19',
                'status' => 'Acc',
            ])
            ->assertSessionHasErrors(['kategori']);
    }

    public function test_update_changes_pengadaan_fields(): void
    {
        $user = $this->userWith(['edit-pengadaan'], 'editor-pengadaan@uji.test');
        $pengadaan = $this->pengadaan();

        $this->actingAs($user)
            ->put(route('pengadaan.update', $pengadaan), [
                'nama_barang' => 'Keyboard Mechanical',
                'kategori' => 'Hardware',
                'jumlah' => 10,
                'harga' => 800000,
                'tanggal' => '2026-10-20',
                'keterangan' => 'Update keterangan',
            ])
            ->assertRedirect(route('pengadaan.index'))
            ->assertSessionHas('success');

        $pengadaan->refresh();
        $this->assertSame('Keyboard Mechanical', $pengadaan->nama_barang);
        $this->assertSame(10, $pengadaan->jumlah);
        $this->assertSame(800000, $pengadaan->harga);
    }

    public function test_status_can_be_set_to_acc_and_ditolak(): void
    {
        $user = $this->userWith(['view-pengadaan', 'edit-pengadaan'], 'approver-pengadaan@uji.test');
        $pengadaan = $this->pengadaan();

        $this->actingAs($user)
            ->from(route('pengadaan.index'))
            ->patch(route('pengadaan.update-status', $pengadaan), ['status' => 'Acc'])
            ->assertRedirect(route('pengadaan.index'))
            ->assertSessionHas('success');
        $this->assertSame('Acc', $pengadaan->refresh()->status);

        $this->actingAs($user)
            ->patch(route('pengadaan.update-status', $pengadaan), ['status' => 'Ditolak'])
            ->assertSessionHas('success');
        $this->assertSame('Ditolak', $pengadaan->refresh()->status);
    }

    public function test_status_rejects_unknown_value(): void
    {
        $user = $this->userWith(['edit-pengadaan'], 'badstatus-pengadaan@uji.test');
        $pengadaan = $this->pengadaan();

        $this->actingAs($user)
            ->patch(route('pengadaan.update-status', $pengadaan), ['status' => 'Sembarang'])
            ->assertSessionHasErrors(['status']);
    }

    public function test_destroy_and_bulk_destroy_remove_pengadaan(): void
    {
        $user = $this->userWith(['delete-pengadaan'], 'deleter-pengadaan@uji.test');
        $one = $this->pengadaan();
        $two = $this->pengadaan(['nama_barang' => 'Mouse']);

        $this->actingAs($user)
            ->delete(route('pengadaan.destroy', $one))
            ->assertRedirect(route('pengadaan.index'))
            ->assertSessionHas('success');
        $this->assertDatabaseMissing('pengadaans', ['id' => $one->id]);

        $this->actingAs($user)
            ->delete(route('pengadaan.bulk-destroy'), ['ids' => [$two->id]])
            ->assertRedirect(route('pengadaan.index'))
            ->assertSessionHas('success');
        $this->assertDatabaseMissing('pengadaans', ['id' => $two->id]);
    }

    public function test_routes_are_forbidden_without_permission(): void
    {
        $user = $this->userWith([], 'tanpa-akses-pengadaan@uji.test');
        $pengadaan = $this->pengadaan();

        $this->actingAs($user)->get(route('pengadaan.index'))->assertForbidden();
        $this->actingAs($user)->get(route('pengadaan.create'))->assertForbidden();
        $this->actingAs($user)->get(route('pengadaan.edit', $pengadaan))->assertForbidden();
        $this->actingAs($user)
            ->patch(route('pengadaan.update-status', $pengadaan), ['status' => 'Acc'])
            ->assertForbidden();
        $this->actingAs($user)
            ->delete(route('pengadaan.destroy', $pengadaan))
            ->assertForbidden();
    }

    public function test_index_filters_by_kategori_and_status(): void
    {
        $user = $this->userWith(['view-pengadaan'], 'filter-pengadaan@uji.test');
        $this->pengadaan(['nama_barang' => 'PC Rakitan', 'kategori' => 'PC', 'status' => 'Proses']);
        $this->pengadaan(['nama_barang' => 'Licence OS', 'kategori' => 'Software', 'status' => 'Acc']);

        $this->actingAs($user)
            ->get(route('pengadaan.index', ['kategori' => 'Software', 'status' => 'Acc']))
            ->assertOk()
            ->assertSee('Licence OS')
            ->assertDontSee('PC Rakitan');
    }
}
