<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\Laboratory;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ShowPageTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        $role = Role::create(['name' => 'admin', 'label' => 'Admin']);
        $role->permissions()->attach(Permission::firstOrCreate(['name' => 'manage-laboratories'], ['label' => 'Kelola Lab']));
        $role->permissions()->attach(Permission::firstOrCreate(['name' => 'manage-academic-years'], ['label' => 'Kelola Tahun Ajaran']));

        $user = User::create(['name' => 'Admin', 'email' => 'admin@show.test', 'password' => 'rahasia123']);
        $user->roles()->attach($role);

        return $user;
    }

    public function test_laboratory_show_page_renders(): void
    {
        $lab = Laboratory::create([
            'name' => 'Lab Jaringan', 'code' => 'LJ1', 'location' => 'Gedung B',
            'capacity' => 32, 'status' => 'Aktif', 'description' => 'Lab praktikum jaringan',
        ]);

        $this->actingAs($this->admin())
            ->get(route('laboratories.show', $lab))
            ->assertOk()
            ->assertSee('Lab Jaringan')
            ->assertSee('LJ1')
            ->assertSee('Gedung B');
    }

    public function test_academic_year_show_page_renders(): void
    {
        $year = AcademicYear::create([
            'name' => 'TA 2026/2027', 'start_year' => 2026, 'end_year' => 2027, 'status' => 'Aktif',
            'start_date' => '2026-08-01', 'end_date' => '2027-06-30',
        ]);

        $this->actingAs($this->admin())
            ->get(route('academic-years.show', $year))
            ->assertOk()
            ->assertSee('TA 2026/2027')
            ->assertSee('01-08-2026');
    }
}
