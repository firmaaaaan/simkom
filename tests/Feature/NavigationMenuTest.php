<?php

namespace Tests\Feature;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NavigationMenuTest extends TestCase
{
    use RefreshDatabase;

    private function userWith(string $email, array $permissions): User
    {
        $role = Role::firstOrCreate(['name' => 'role-'.md5($email)], ['label' => 'Role Uji']);
        foreach ($permissions as $permission) {
            $role->permissions()->attach(Permission::firstOrCreate(['name' => $permission], ['label' => $permission]));
        }
        $user = User::create(['name' => 'Uji', 'email' => $email, 'password' => 'rahasia123']);
        $user->roles()->attach($role);

        return $user;
    }

    public function test_reports_menu_has_its_own_label(): void
    {
        $user = $this->userWith('nav-reports@uji.test', ['view-reports']);

        $this->actingAs($user)->get(route('reports.laporan'))
            ->assertOk()
            ->assertSee('Kartu Kendali')
            ->assertDontSee('Laporan Pengecekan')
            ->assertSee('>Laporan</span>', false)
            ->assertDontSee('>Transaksi</span>', false);
    }

    public function test_laporan_pengecekan_moves_to_reports_for_laboran(): void
    {
        $user = $this->userWith('nav-laboran@uji.test', ['view-maintenance']);

        $this->actingAs($user)->get(route('maintenance.index'))
            ->assertOk()
            ->assertSee('Laporan Pengecekan')
            ->assertSee('>Laporan</span>', false)
            ->assertSee('>Transaksi</span>', false);
    }

    public function test_sidebar_groups_collapse_except_the_active_one(): void
    {
        $user = $this->userWith('nav-accordion@uji.test', ['view-reports', 'view-maintenance', 'view-users']);

        $this->actingAs($user)->get(route('reports.laporan'))
            ->assertOk()
            ->assertSee('<div x-data="{ open: true }" x-cloak>', false)
            ->assertSee('<div x-data="{ open: false }" x-cloak>', false)
            ->assertSee('>Laporan</span>', false);
    }

    public function test_transaksi_menu_does_not_contain_reports(): void
    {
        $user = $this->userWith('nav-tickets@uji.test', ['view-tickets']);

        $this->actingAs($user)->get(route('tickets.index'))
            ->assertOk()
            ->assertSee('>Transaksi</span>', false)
            ->assertDontSee('>Laporan</span>', false)
            ->assertDontSee(route('reports.laporan'), false);
    }
}
