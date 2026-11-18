<?php

namespace Tests\Feature;

use App\Models\Laboratory;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LaboratoryBulkVisibilityTest extends TestCase
{
    use RefreshDatabase;

    private function userWith(array $permissions): User
    {
        $role = Role::firstOrCreate(
            ['name' => 'role-lab-visibility-'.md5(implode(',', $permissions))],
            ['label' => 'Role Uji Lab']
        );
        foreach ($permissions as $permission) {
            $role->permissions()->syncWithoutDetaching([
                Permission::firstOrCreate(['name' => $permission], ['label' => $permission])->id,
            ]);
        }

        $user = User::firstOrCreate(
            ['email' => 'lab-visibility-'.md5(implode(',', $permissions)).'@uji.test'],
            ['name' => 'Uji Lab Visibility', 'password' => 'rahasia123']
        );
        $user->roles()->syncWithoutDetaching($role->id);

        return $user;
    }

    private function seedLab(string $code, bool $visible = true): Laboratory
    {
        return Laboratory::create([
            'name' => 'Lab '.$code,
            'code' => $code,
            'location' => 'Gedung Uji',
            'capacity' => 10,
            'status' => 'Aktif',
            'show_in_computer_list' => $visible,
        ]);
    }

    public function test_bulk_visibility_requires_edit_permission(): void
    {
        $lab = $this->seedLab('LAB-SEC');

        $this->actingAs($this->userWith(['view-laboratories']))
            ->put(route('laboratories.bulk-visibility'), ['visible' => 0])
            ->assertForbidden();

        $this->assertTrue($lab->fresh()->show_in_computer_list);
    }

    public function test_bulk_visibility_hides_every_lab(): void
    {
        $first = $this->seedLab('LAB-HIDE-1');
        $second = $this->seedLab('LAB-HIDE-2');
        $user = $this->userWith(['view-laboratories', 'edit-laboratories']);

        $this->actingAs($user)
            ->from(route('laboratories.index'))
            ->put(route('laboratories.bulk-visibility'), ['visible' => 0])
            ->assertRedirect(route('laboratories.index'))
            ->assertSessionHas('success', '2 laboratorium disembunyikan dari daftar komputer.');

        $this->assertFalse($first->fresh()->show_in_computer_list);
        $this->assertFalse($second->fresh()->show_in_computer_list);
    }

    public function test_bulk_visibility_shows_every_lab(): void
    {
        $first = $this->seedLab('LAB-SHOW-1', false);
        $second = $this->seedLab('LAB-SHOW-2', false);
        $user = $this->userWith(['view-laboratories', 'edit-laboratories']);

        $this->actingAs($user)
            ->from(route('laboratories.index'))
            ->put(route('laboratories.bulk-visibility'), ['visible' => 1])
            ->assertRedirect(route('laboratories.index'))
            ->assertSessionHas('success', '2 laboratorium ditampilkan di daftar komputer.');

        $this->assertTrue($first->fresh()->show_in_computer_list);
        $this->assertTrue($second->fresh()->show_in_computer_list);
    }

    public function test_bulk_visibility_rejects_invalid_value(): void
    {
        $lab = $this->seedLab('LAB-INVALID');
        $user = $this->userWith(['view-laboratories', 'edit-laboratories']);

        $this->actingAs($user)
            ->put(route('laboratories.bulk-visibility'), ['visible' => 'ya'])
            ->assertSessionHasErrors('visible');

        $this->assertTrue($lab->fresh()->show_in_computer_list);
    }

    public function test_index_toggle_reflects_overall_visibility(): void
    {
        $lab = $this->seedLab('LAB-TG-1');
        $user = $this->userWith(['view-laboratories', 'edit-laboratories']);

        $this->actingAs($user)
            ->get(route('laboratories.index'))
            ->assertOk()
            ->assertSee('allVisible: true', false)
            ->assertSee('role="switch"', false)
            ->assertSee('Seluruh Lab');

        $lab->update(['show_in_computer_list' => false]);

        $this->actingAs($user)
            ->get(route('laboratories.index'))
            ->assertOk()
            ->assertSee('allVisible: false', false);
    }

    public function test_index_hides_toggle_without_edit_permission(): void
    {
        $this->seedLab('LAB-TG-2');

        $this->actingAs($this->userWith(['view-laboratories']))
            ->get(route('laboratories.index'))
            ->assertOk()
            ->assertDontSee('role="switch"', false);
    }
}
