<?php

namespace Tests\Feature;

use App\Models\Computer;
use App\Models\Hardware;
use App\Models\Laboratory;
use App\Models\Permission;
use App\Models\Role;
use App\Models\Software;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KartuKendaliSpecModalTest extends TestCase
{
    use RefreshDatabase;

    private function computerWithSpec(): Computer
    {
        $lab = Laboratory::create([
            'name' => 'Lab Jaringan', 'code' => 'LJ1', 'location' => 'Gedung B',
            'capacity' => 32, 'status' => 'Aktif', 'description' => 'Lab praktikum jaringan',
        ]);

        $computer = Computer::create([
            'code' => 'PC-SPEC-02', 'laboratory_id' => $lab->id, 'status' => 'Aktif',
        ]);

        $computer->hardware()->attach(Hardware::create([
            'name' => 'Intel Core i5', 'code' => 'HW-SPEC-101',
            'brand' => 'Intel', 'model' => 'i5-12400', 'category' => 'Processor',
        ]));

        $computer->software()->attach(Software::create([
            'name' => 'Windows 11', 'code' => 'SW-SPEC-101',
            'version' => '23H2', 'category' => 'Operating System', 'status' => 'Aktif',
        ]));

        return $computer;
    }

    public function test_public_kartu_page_shows_spec_button_and_modal(): void
    {
        $computer = $this->computerWithSpec();

        $this->get(route('kartu.show', $computer))
            ->assertOk()
            ->assertSee('Lihat Spesifikasi')
            ->assertSee('Spesifikasi Komputer')
            ->assertSee('Intel Core i5')
            ->assertDontSee('Windows 11');
    }

    public function test_admin_kartu_page_shows_spec_button_and_modal(): void
    {
        $computer = $this->computerWithSpec();

        $role = Role::create(['name' => 'laboran-kartu', 'label' => 'Laboran']);
        $role->permissions()->attach(Permission::firstOrCreate(['name' => 'view-computers'], ['label' => 'Lihat Komputer']));
        $user = User::create(['name' => 'Laboran', 'email' => 'lab@kartu.test', 'password' => 'rahasia123']);
        $user->roles()->attach($role);

        $this->actingAs($user)
            ->get(route('computers.card', $computer))
            ->assertOk()
            ->assertSee('Lihat Spesifikasi')
            ->assertSee('Spesifikasi Komputer')
            ->assertSee('Intel Core i5');
    }
}
