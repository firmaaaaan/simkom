<?php
namespace Tests\Feature;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AlatSidebarTest extends TestCase
{
    use RefreshDatabase;

    private function userWith(array $perms): User
    {
        $role = Role::firstOrCreate(['name' => 'role-alat'], ['label' => 'Role Alat']);
        foreach ($perms as $p) {
            $role->permissions()->syncWithoutDetaching([
                Permission::firstOrCreate(['name' => $p], ['label' => $p])->id,
            ]);
        }
        $u = User::create(['name' => 'Uji', 'email' => 'alat@uji.test', 'password' => 'rahasia123']);
        $u->roles()->attach($role);
        return $u;
    }

    public function test_alat_menus_shown_for_view_alat_user(): void
    {
        $this->actingAs($this->userWith(['view-alat']))
            ->get(route('alat.index'))
            ->assertOk()
            ->assertSee('>Data Master Lab</p>', false)
            ->assertSee(route('alat.index'), false)
            ->assertSee('Alat Laboratorium')
            ->assertSee('Kartu Kendali Alat');
    }

    public function test_kartu_alat_permission_alias_also_shows_menus(): void
    {
        $this->actingAs($this->userWith(['view-kartu-alat']))
            ->get(route('alat.kartu'))
            ->assertOk()
            ->assertSee('Alat Laboratorium')
            ->assertSee('Kartu Kendali Alat');
    }

    public function test_menus_hidden_without_permission(): void
    {
        $role = Role::firstOrCreate(['name' => 'role-none'], ['label' => 'None']);
        $u = User::create(['name' => 'No', 'email' => 'none@uji.test', 'password' => 'rahasia123']);
        $u->roles()->attach($role);

        $this->actingAs($u)->get(route('dashboard'))
            ->assertOk()
            ->assertDontSee(route('alat.index'), false)
            ->assertDontSee('Alat Laboratorium')
            ->assertDontSee('Kartu Kendali Alat');
    }
}
