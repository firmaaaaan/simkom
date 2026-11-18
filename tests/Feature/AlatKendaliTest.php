<?php

namespace Tests\Feature;

use App\Models\Alat;
use App\Models\AlatCheck;
use App\Models\Laboratory;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AlatKendaliTest extends TestCase
{
    use RefreshDatabase;

    private function userWith(array $permissions): User
    {
        $role = Role::firstOrCreate(['name' => 'role-kendali-'.md5(implode(',', $permissions))], ['label' => 'Role Kendali']);
        foreach ($permissions as $p) {
            $role->permissions()->syncWithoutDetaching([
                Permission::firstOrCreate(['name' => $p], ['label' => $p])->id,
            ]);
        }

        $user = User::firstOrCreate(
            ['email' => 'kendali-'.md5(implode(',', $permissions)).'@uji.test'],
            ['name' => 'Uji Kendali', 'password' => 'rahasia123']
        );
        $user->roles()->syncWithoutDetaching($role->id);

        return $user;
    }

    private function seedAlat(string $code = 'INV-001'): Alat
    {
        $lab = Laboratory::firstOrCreate(
            ['code' => 'LK1'],
            ['name' => 'Lab Komputer 1', 'location' => 'Gedung A', 'capacity' => 40]
        );

        return Alat::create([
            'laboratory_id' => $lab->id,
            'name' => 'Mikroskop',
            'code' => $code,
            'merk_type' => 'Olympus CX23',
        ]);
    }

    private function viewer(): User
    {
        return $this->userWith(['view-alat', 'create-alat', 'delete-alat', 'print-alat']);
    }

    public function test_kartu_list_shows_action_column(): void
    {
        $alat = $this->seedAlat();

        $this->actingAs($this->userWith(['view-alat']))
            ->get(route('alat.kartu', ['laboratory_id' => $alat->laboratory_id]))
            ->assertOk()
            ->assertSee('Aksi')
            ->assertSee('title="Input & lihat riwayat kartu kendali"', false)
            ->assertSee('title="Cetak kartu kendali"', false)
            ->assertDontSee('>Input<', false)
            ->assertDontSee('Riwayat (0)', false);
    }

    public function test_checks_page_shows_form_and_empty_history(): void
    {
        $alat = $this->seedAlat();

        $this->actingAs($this->viewer())
            ->get(route('alat.checks', $alat))
            ->assertOk()
            ->assertSee('Input Entri Baru')
            ->assertSee('Riwayat Kartu Kendali')
            ->assertSee('Belum ada riwayat kartu kendali untuk alat ini')
            ->assertSee('name="check_date"', false)
            ->assertSee('value="Baik"', false);
    }

    public function test_store_check_creates_entry_and_shows_in_history(): void
    {
        $alat = $this->seedAlat();

        $this->actingAs($this->viewer())
            ->post(route('alat.checks.store', $alat), [
                'check_date' => '2025-11-02',
                'status' => 'Baik',
                'notes' => 'oke',
            ])
            ->assertRedirect(route('alat.checks', $alat));

        $this->assertDatabaseHas('alat_checks', [
            'alat_id' => $alat->id,
            'check_date' => '2025-11-02',
            'status' => 'Baik',
            'notes' => 'oke',
        ]);

        $this->actingAs($this->viewer())
            ->get(route('alat.checks', $alat))
            ->assertOk()
            ->assertSee('2 November 2025')
            ->assertSee('oke');
    }

    public function test_store_check_validates_status(): void
    {
        $alat = $this->seedAlat();

        $this->actingAs($this->viewer())
            ->from(route('alat.checks', $alat))
            ->post(route('alat.checks.store', $alat), [
                'check_date' => '2025-11-02',
                'status' => 'Salah',
            ])
            ->assertRedirect(route('alat.checks', $alat));

        $this->assertSame(0, AlatCheck::count());
    }

    public function test_destroy_check_removes_entry(): void
    {
        $alat = $this->seedAlat();
        $check = AlatCheck::create([
            'alat_id' => $alat->id,
            'check_date' => '2025-07-07',
            'status' => 'Rusak',
            'notes' => 'Tutup pecah 1',
        ]);

        $this->actingAs($this->viewer())
            ->delete(route('alat.checks.destroy', [$alat, $check]))
            ->assertRedirect(route('alat.checks', $alat));

        $this->assertDatabaseMissing('alat_checks', ['id' => $check->id]);
    }

    public function test_destroy_check_of_other_alat_returns_404(): void
    {
        $alat = $this->seedAlat();
        $other = $this->seedAlat('INV-002');
        $check = AlatCheck::create([
            'alat_id' => $other->id,
            'check_date' => '2025-07-07',
            'status' => 'Baik',
        ]);

        $this->actingAs($this->viewer())
            ->delete(route('alat.checks.destroy', [$alat, $check]))
            ->assertNotFound();

        $this->assertDatabaseHas('alat_checks', ['id' => $check->id]);
    }

    public function test_print_one_fills_history_into_33_rows(): void
    {
        $alat = $this->seedAlat();
        foreach ([
            ['2024-11-02', 'Baik', 'oke'],
            ['2024-12-03', 'Baik', 'oke'],
            ['2025-07-07', 'Rusak', 'Tutup pecah 1'],
        ] as $row) {
            AlatCheck::create([
                'alat_id' => $alat->id,
                'check_date' => $row[0],
                'status' => $row[1],
                'notes' => $row[2],
            ]);
        }

        $this->actingAs($this->viewer())
            ->get(route('alat.print-one', $alat))
            ->assertOk()
            ->assertSee('02/11/2024')
            ->assertSee('07/07/2025')
            ->assertSee('Tutup pecah 1')
            ->assertSee('Terisi 3 dari 33 entri');
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $alat = $this->seedAlat();

        $this->get(route('alat.checks', $alat))->assertRedirect(route('login'));
    }

    public function test_user_without_permission_cannot_input(): void
    {
        $alat = $this->seedAlat();

        $this->actingAs($this->userWith([]))
            ->post(route('alat.checks.store', $alat), [
                'check_date' => '2025-11-02',
                'status' => 'Baik',
            ])
            ->assertForbidden();

        $this->assertSame(0, AlatCheck::count());
    }

    public function test_permission_alias_kartu_alat_can_input(): void
    {
        $alat = $this->seedAlat();

        $this->actingAs($this->userWith(['view-kartu-alat', 'create-kartu-alat']))
            ->post(route('alat.checks.store', $alat), [
                'check_date' => '2025-11-02',
                'status' => 'Baik',
                'notes' => 'oke',
            ])
            ->assertRedirect(route('alat.checks', $alat));

        $this->assertSame(1, AlatCheck::count());
    }

    public function test_create_page_has_barcode_scan_feature(): void
    {
        $this->actingAs($this->userWith(['view-alat', 'create-alat']))
            ->get(route('alat.create'))
            ->assertOk()
            ->assertSee('id="toggle-scan"', false)
            ->assertSee('id="qr-reader"', false)
            ->assertSee('id="lab-alat-list"', false)
            ->assertSee('html5-qrcode');
    }

    public function test_scan_store_saves_alat_to_selected_laboratory(): void
    {
        $lab = Laboratory::create([
            'code' => 'LAB-SCAN',
            'name' => 'Lab Scan',
            'location' => 'Gedung S',
            'capacity' => 20,
        ]);

        $this->actingAs($this->userWith(['view-alat', 'create-alat']))
            ->postJson(route('alat.scan-store'), [
                'laboratory_id' => $lab->id,
                'code' => 'INV.SCAN.001',
                'name' => 'Mikroskop Digital',
                'merk_type' => 'Olympus',
                'location' => 'Rak 1',
                'status' => 'Baik',
            ])
            ->assertCreated()
            ->assertJson(['ok' => true]);

        $this->assertDatabaseHas('alat', [
            'laboratory_id' => $lab->id,
            'code' => 'INV.SCAN.001',
            'name' => 'Mikroskop Digital',
            'merk_type' => 'Olympus',
            'status' => 'Baik',
        ]);
    }

    public function test_scan_store_uses_code_as_name_when_only_code_scanned(): void
    {
        $lab = Laboratory::create([
            'code' => 'LAB-SCAN2',
            'name' => 'Lab Scan 2',
            'location' => 'Gedung S',
            'capacity' => 20,
        ]);

        $this->actingAs($this->userWith(['view-alat', 'create-alat']))
            ->postJson(route('alat.scan-store'), [
                'laboratory_id' => $lab->id,
                'code' => 'INV.SCAN.002',
            ])
            ->assertCreated();

        $this->assertDatabaseHas('alat', [
            'code' => 'INV.SCAN.002',
            'name' => 'INV.SCAN.002',
        ]);
    }

    public function test_scan_store_requires_laboratory(): void
    {
        $this->actingAs($this->userWith(['view-alat', 'create-alat']))
            ->postJson(route('alat.scan-store'), ['code' => 'INV.NO.LAB'])
            ->assertStatus(422)
            ->assertJsonValidationErrors('laboratory_id');
    }

    public function test_scan_store_rejects_duplicate_code(): void
    {
        $alat = $this->seedAlat('INV-DUP-01');

        $this->actingAs($this->userWith(['view-alat', 'create-alat']))
            ->postJson(route('alat.scan-store'), [
                'laboratory_id' => $alat->laboratory_id,
                'code' => 'INV-DUP-01',
            ])
            ->assertStatus(422)
            ->assertJson(['ok' => false]);

        $this->assertSame(1, Alat::where('code', 'INV-DUP-01')->count());
    }

    public function test_scan_store_requires_permission(): void
    {
        $lab = Laboratory::create([
            'code' => 'LAB-SCAN3',
            'name' => 'Lab Scan 3',
            'location' => 'Gedung S',
            'capacity' => 20,
        ]);

        $this->actingAs($this->userWith(['view-alat']))
            ->postJson(route('alat.scan-store'), [
                'laboratory_id' => $lab->id,
                'code' => 'INV.FORBID',
            ])
            ->assertForbidden();

        $this->assertDatabaseMissing('alat', ['code' => 'INV.FORBID']);
    }

    public function test_lab_list_returns_only_alat_of_selected_laboratory(): void
    {
        $alat = $this->seedAlat('INV-LIST-1');

        $otherLab = Laboratory::create([
            'code' => 'LAB-OTHER',
            'name' => 'Lab Lain',
            'location' => 'Gedung X',
            'capacity' => 10,
        ]);

        Alat::create([
            'laboratory_id' => $otherLab->id,
            'name' => 'Alat Lain',
            'code' => 'INV-LIST-2',
        ]);

        $this->actingAs($this->userWith(['view-alat']))
            ->getJson(route('alat.lab-list', ['laboratory_id' => $alat->laboratory_id]))
            ->assertOk()
            ->assertJsonCount(1, 'alat')
            ->assertJsonPath('alat.0.code', 'INV-LIST-1')
            ->assertJsonPath('laboratory.name', 'Lab Komputer 1');
    }

    public function test_lab_list_requires_laboratory(): void
    {
        $this->actingAs($this->userWith(['view-alat']))
            ->getJson(route('alat.lab-list'))
            ->assertStatus(422)
            ->assertJsonValidationErrors('laboratory_id');
    }

    public function test_lab_list_requires_permission(): void
    {
        $alat = $this->seedAlat('INV-LIST-3');

        $this->actingAs($this->userWith([]))
            ->getJson(route('alat.lab-list', ['laboratory_id' => $alat->laboratory_id]))
            ->assertForbidden();
    }
}
