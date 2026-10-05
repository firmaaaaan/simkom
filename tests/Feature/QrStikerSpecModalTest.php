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

class QrStikerSpecModalTest extends TestCase
{
    use RefreshDatabase;

    private function laboran(): User
    {
        $role = Role::create(['name' => 'laboran-spec', 'label' => 'Laboran']);
        $role->permissions()->attach(Permission::firstOrCreate(['name' => 'view-computers'], ['label' => 'Lihat Komputer']));

        $user = User::create(['name' => 'Laboran', 'email' => 'lab@spec.test', 'password' => 'rahasia123']);
        $user->roles()->attach($role);

        return $user;
    }

    public function test_qr_stiker_page_shows_spec_button_and_modal(): void
    {
        $lab = Laboratory::create([
            'name' => 'Lab Jaringan', 'code' => 'LJ1', 'location' => 'Gedung B',
            'capacity' => 32, 'status' => 'Aktif', 'description' => 'Lab praktikum jaringan',
        ]);

        $computer = Computer::create([
            'code' => 'PC-SPEC-01', 'laboratory_id' => $lab->id, 'status' => 'Aktif',
        ]);

        $computer->hardware()->attach(Hardware::create([
            'name' => 'Intel Core i5', 'code' => 'HW-SPEC-001',
            'brand' => 'Intel', 'model' => 'i5-12400', 'category' => 'Processor',
        ]));

        $computer->software()->attach(Software::create([
            'name' => 'Windows 11', 'code' => 'SW-SPEC-001',
            'version' => '23H2', 'category' => 'Operating System', 'status' => 'Aktif',
        ]));

        $this->actingAs($this->laboran())
            ->get(route('computers.qr-stiker', ['laboratory_id' => $lab->id]))
            ->assertOk()
            ->assertSee('Spesifikasi Komputer')
            ->assertSee('🧾 Spesifikasi')
            ->assertSee('PC-SPEC-01')
            ->assertSee('Intel Core i5')
            ->assertDontSee('Windows 11');
    }
}
