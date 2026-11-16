<?php

namespace Tests\Feature;

use App\Models\Alat;
use App\Models\Laboratory;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AlatLabelTest extends TestCase
{
    use RefreshDatabase;

    private function userWith(array $permissions): User
    {
        $role = Role::firstOrCreate(['name' => 'role-label-'.md5(implode(',', $permissions))], ['label' => 'Role Label']);
        foreach ($permissions as $p) {
            $role->permissions()->syncWithoutDetaching([
                Permission::firstOrCreate(['name' => $p], ['label' => $p])->id,
            ]);
        }

        $user = User::firstOrCreate(
            ['email' => 'label-'.md5(implode(',', $permissions)).'@uji.test'],
            ['name' => 'Uji Label', 'password' => 'rahasia123']
        );
        $user->roles()->syncWithoutDetaching($role->id);

        return $user;
    }

    private function seedAlat(): array
    {
        $lab = Laboratory::create([
            'name' => 'Lab Komputer 1', 'code' => 'LK1', 'location' => 'Gedung A', 'capacity' => 40,
        ]);

        $alat = Alat::create([
            'laboratory_id' => $lab->id,
            'name' => 'Mikroskop',
            'code' => 'INV-001',
            'merk_type' => 'Olympus CX23',
            'location' => 'Meja 1',
        ]);

        return [$lab, $alat];
    }

    public function test_labels_page_requires_lab_selection(): void
    {
        $this->actingAs($this->userWith(['print-alat']))
            ->get(route('alat.labels'))
            ->assertOk()
            ->assertSee('Pilih laboratorium')
            ->assertSee('Label Alat Laboratorium');
    }

    public function test_labels_page_renders_label_with_qr_for_selected_lab(): void
    {
        [$lab, $alat] = $this->seedAlat();

        $this->actingAs($this->userWith(['print-alat']))
            ->get(route('alat.labels', ['laboratory_id' => $lab->id]))
            ->assertOk()
            ->assertSee('Mikroskop')
            ->assertSee('INV-001')
            ->assertSee('Olympus CX23')
            ->assertSee('Meja 1')
            ->assertSee($lab->name)
            ->assertSee('id="qr-'.$alat->id.'"', false)
            ->assertSee('Cetak Label');
    }

    public function test_labels_page_supports_all_laboratories(): void
    {
        [$lab, $alat] = $this->seedAlat();

        $otherLab = Laboratory::create([
            'name' => 'Lab Jaringan', 'code' => 'LJ1', 'location' => 'Gedung B', 'capacity' => 30,
        ]);
        Alat::create([
            'laboratory_id' => $otherLab->id,
            'name' => 'Switch',
            'code' => 'INV-002',
        ]);

        $this->actingAs($this->userWith(['print-alat']))
            ->get(route('alat.labels', ['laboratory_id' => 'all']))
            ->assertOk()
            ->assertSee('Mikroskop')
            ->assertSee('Switch')
            ->assertSee('Semua Laboratorium');
    }

    public function test_labels_qr_encodes_alat_scan_url(): void
    {
        [$lab, $alat] = $this->seedAlat();

        $response = $this->actingAs($this->userWith(['print-alat']))
            ->get(route('alat.labels', ['laboratory_id' => $lab->id]))
            ->assertOk();

        $escapedUrl = str_replace('/', '\\/', route('alat.scan', $alat));
        $response->assertSee('text: ', false)->assertSee($escapedUrl, false);
    }

    public function test_labels_page_forbidden_without_print_permission(): void
    {
        [$lab] = $this->seedAlat();

        $this->actingAs($this->userWith(['view-alat']))
            ->get(route('alat.labels', ['laboratory_id' => $lab->id]))
            ->assertForbidden();
    }

    public function test_labels_page_accepts_print_permission_alias(): void
    {
        [$lab] = $this->seedAlat();

        $this->actingAs($this->userWith(['print-kartu-alat']))
            ->get(route('alat.labels', ['laboratory_id' => $lab->id]))
            ->assertOk();
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get(route('alat.labels'))->assertRedirect(route('login'));
    }

    public function test_kartu_page_links_to_labels(): void
    {
        [$lab] = $this->seedAlat();

        $this->actingAs($this->userWith(['view-alat', 'print-alat']))
            ->get(route('alat.kartu', ['laboratory_id' => $lab->id]))
            ->assertOk()
            ->assertSee(route('alat.labels', ['laboratory_id' => $lab->id]), false);
    }

    public function test_index_page_links_to_labels(): void
    {
        $this->actingAs($this->userWith(['view-alat', 'print-alat']))
            ->get(route('alat.index'))
            ->assertOk()
            ->assertSee(route('alat.labels'), false);
    }
}
