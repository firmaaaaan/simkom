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

class AlatScanTest extends TestCase
{
    use RefreshDatabase;

    private function userWith(array $permissions): User
    {
        $role = Role::firstOrCreate(['name' => 'role-scan-'.md5(implode(',', $permissions))], ['label' => 'Role Scan']);
        foreach ($permissions as $p) {
            $role->permissions()->syncWithoutDetaching([
                Permission::firstOrCreate(['name' => $p], ['label' => $p])->id,
            ]);
        }

        $user = User::firstOrCreate(
            ['email' => 'scan-'.md5(implode(',', $permissions)).'@uji.test'],
            ['name' => 'Uji Scan', 'password' => 'rahasia123']
        );
        $user->roles()->syncWithoutDetaching($role->id);

        return $user;
    }

    private function seedAlat(): Alat
    {
        $lab = Laboratory::create(['name' => 'Lab Komputer 1', 'code' => 'LK1', 'location' => 'Gedung A', 'capacity' => 40]);

        return Alat::create([
            'laboratory_id' => $lab->id,
            'name' => 'Mikroskop',
            'code' => 'INV-001',
            'merk_type' => 'Olympus CX23',
        ]);
    }

    public function test_guest_scan_redirects_to_riwayat(): void
    {
        $alat = $this->seedAlat();

        $this->get(route('alat.scan', $alat))
            ->assertRedirect(route('alat.riwayat', $alat));
    }

    public function test_auth_scan_redirects_to_checks(): void
    {
        $alat = $this->seedAlat();

        $this->actingAs($this->userWith(['view-alat']))
            ->get(route('alat.scan', $alat))
            ->assertRedirect(route('alat.checks', $alat));
    }

    public function test_riwayat_is_public_and_shows_history(): void
    {
        $alat = $this->seedAlat();
        AlatCheck::create([
            'alat_id' => $alat->id,
            'check_date' => '2025-07-07',
            'status' => 'Rusak',
            'notes' => 'Tutup pecah 1',
        ]);

        $this->get(route('alat.riwayat', $alat))
            ->assertOk()
            ->assertSee('Riwayat Kartu Kendali')
            ->assertSee('Tutup pecah 1')
            ->assertSee('07 Juli 2025');
    }

    public function test_riwayat_shows_empty_state(): void
    {
        $alat = $this->seedAlat();

        $this->get(route('alat.riwayat', $alat))
            ->assertOk()
            ->assertSee('Belum ada riwayat kartu kendali untuk alat ini');
    }

    public function test_riwayat_does_not_show_input_form(): void
    {
        $alat = $this->seedAlat();

        $this->get(route('alat.riwayat', $alat))
            ->assertOk()
            ->assertDontSee('Input Entri Baru');
    }
}
