<?php

namespace Tests\Feature;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RolePermissionPickerTest extends TestCase
{
    use RefreshDatabase;

    public function test_alat_permission_aliases_are_merged_into_one_card(): void
    {
        $role = Role::create([
            'name' => 'role-picker',
            'label' => 'Role Picker',
            'guard_name' => 'web',
        ]);

        foreach (['view-roles', 'create-roles'] as $name) {
            $permission = Permission::firstOrCreate(['name' => $name], ['label' => $name]);
            $role->permissions()->attach($permission);
        }

        $user = User::create([
            'name' => 'Uji',
            'email' => 'picker@uji.test',
            'password' => 'rahasia123',
        ]);
        $user->roles()->attach($role);

        $alias = Permission::where('name', 'view-kartu-alat')->firstOrFail();

        $content = $this->actingAs($user)
            ->get(route('roles.create'))
            ->assertOk()
            ->getContent();

        $this->assertSame(1, substr_count($content, '>Kartu Kendali Alat Laboratorium</p>'));
        $this->assertStringContainsString('name="permissions[]" value="'.$alias->id.'"', $content);
    }
}
