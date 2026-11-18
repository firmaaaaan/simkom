<?php

namespace Tests\Feature;

use App\Models\Laboratory;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LaboratoryBulkStatusTest extends TestCase
{
    use RefreshDatabase;

    private function userWith(array $permissions): User
    {
        $role = Role::firstOrCreate(
            ['name' => 'role-lab-status-'.md5(implode(',', $permissions))],
            ['label' => 'Role Uji Lab']
        );
        foreach ($permissions as $permission) {
            $role->permissions()->syncWithoutDetaching([
                Permission::firstOrCreate(['name' => $permission], ['label' => $permission])->id,
            ]);
        }

        $user = User::firstOrCreate(
            ['email' => 'lab-status-'.md5(implode(',', $permissions)).'@uji.test'],
            ['name' => 'Uji Lab Status', 'password' => 'rahasia123']
        );
        $user->roles()->syncWithoutDetaching($role->id);

        return $user;
    }

    private function seedLab(string $code, string $status = 'Aktif'): Laboratory
    {
        return Laboratory::create([
            'name' => 'Lab '.$code,
            'code' => $code,
            'location' => 'Gedung Uji',
            'capacity' => 10,
            'status' => $status,
        ]);
    }

    public function test_bulk_status_requires_edit_permission(): void
    {
        $lab = $this->seedLab('LAB-SEC');

        $this->actingAs($this->userWith(['view-laboratories']))
            ->put(route('laboratories.bulk-status'), [
                'status' => 'Maintenance',
                'scope' => 'all',
            ])
            ->assertForbidden();

        $this->assertSame('Aktif', $lab->fresh()->status);
    }

    public function test_bulk_status_updates_selected_labs_only(): void
    {
        $first = $this->seedLab('LAB-SEL-1');
        $second = $this->seedLab('LAB-SEL-2');
        $user = $this->userWith(['view-laboratories', 'edit-laboratories']);

        $this->actingAs($user)
            ->from(route('laboratories.index'))
            ->put(route('laboratories.bulk-status'), [
                'status' => 'Maintenance',
                'scope' => 'selected',
                'ids' => [$first->id],
            ])
            ->assertRedirect(route('laboratories.index'))
            ->assertSessionHas('success');

        $this->assertSame('Maintenance', $first->fresh()->status);
        $this->assertSame('Aktif', $second->fresh()->status);
    }

    public function test_bulk_status_scope_all_updates_every_lab(): void
    {
        $first = $this->seedLab('LAB-ALL-1');
        $second = $this->seedLab('LAB-ALL-2');
        $user = $this->userWith(['view-laboratories', 'edit-laboratories']);

        $this->actingAs($user)
            ->from(route('laboratories.index'))
            ->put(route('laboratories.bulk-status'), [
                'status' => 'Tidak Aktif',
                'scope' => 'all',
                'ids' => [$first->id],
            ])
            ->assertRedirect(route('laboratories.index'))
            ->assertSessionHas('success', 'Seluruh 2 laboratorium diubah statusnya menjadi Tidak Aktif.');

        $this->assertSame('Tidak Aktif', $first->fresh()->status);
        $this->assertSame('Tidak Aktif', $second->fresh()->status);
    }

    public function test_bulk_status_selected_without_ids_shows_error(): void
    {
        $lab = $this->seedLab('LAB-NONE');
        $user = $this->userWith(['view-laboratories', 'edit-laboratories']);

        $this->actingAs($user)
            ->from(route('laboratories.index'))
            ->put(route('laboratories.bulk-status'), [
                'status' => 'Maintenance',
                'scope' => 'selected',
            ])
            ->assertRedirect(route('laboratories.index'))
            ->assertSessionHas('error');

        $this->assertSame('Aktif', $lab->fresh()->status);
    }

    public function test_bulk_status_rejects_invalid_status_and_scope(): void
    {
        $lab = $this->seedLab('LAB-INVALID');
        $user = $this->userWith(['view-laboratories', 'edit-laboratories']);

        $this->actingAs($user)
            ->put(route('laboratories.bulk-status'), [
                'status' => 'Dingin',
                'scope' => 'all',
            ])
            ->assertSessionHasErrors('status');

        $this->actingAs($user)
            ->put(route('laboratories.bulk-status'), [
                'status' => 'Maintenance',
                'scope' => 'lainnya',
            ])
            ->assertSessionHasErrors('scope');

        $this->assertSame('Aktif', $lab->fresh()->status);
    }

    public function test_index_toggle_reflects_overall_lab_status(): void
    {
        $lab = $this->seedLab('LAB-TG-1');
        $user = $this->userWith(['view-laboratories', 'edit-laboratories']);

        $this->actingAs($user)
            ->get(route('laboratories.index'))
            ->assertOk()
            ->assertSee('allActive: true', false)
            ->assertSee('role="switch"', false)
            ->assertSee('Seluruh Lab');

        $lab->update(['status' => 'Maintenance']);

        $this->actingAs($user)
            ->get(route('laboratories.index'))
            ->assertOk()
            ->assertSee('allActive: false', false);
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
