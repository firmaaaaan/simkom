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
            ->assertSee('<p class="text-[10px] font-bold text-green-400 uppercase tracking-wider px-3 pt-4 pb-1">Laporan</p>', false)
            ->assertDontSee('<p class="text-[10px] font-bold text-green-400 uppercase tracking-wider px-3 pt-4 pb-1">Transaksi</p>', false);
    }

    public function test_laporan_pengecekan_moves_to_reports_for_laboran(): void
    {
        $user = $this->userWith('nav-laboran@uji.test', ['view-maintenance']);

        $this->actingAs($user)->get(route('maintenance.index'))
            ->assertOk()
            ->assertSee('Laporan Pengecekan')
            ->assertSee('>Laporan</p>', false)
            ->assertSee('>Transaksi</p>', false);
    }

    public function test_transaksi_menu_does_not_contain_reports(): void
    {
        $user = $this->userWith('nav-tickets@uji.test', ['view-tickets']);

        $this->actingAs($user)->get(route('tickets.index'))
            ->assertOk()
            ->assertSee('>Transaksi</p>', false)
            ->assertDontSee('>Laporan</p>', false)
            ->assertDontSee(route('reports.laporan'), false);
    }
}
