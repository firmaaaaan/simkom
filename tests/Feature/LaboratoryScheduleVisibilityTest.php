<?php

namespace Tests\Feature;

use App\Models\Laboratory;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LaboratoryScheduleVisibilityTest extends TestCase
{
    use RefreshDatabase;

    private function lab(array $attributes = []): Laboratory
    {
        return Laboratory::create(array_merge([
            'name' => 'Lab Tampil',
            'code' => 'LT1',
            'location' => 'Gedung A',
            'capacity' => 40,
        ], $attributes));
    }

    private function userWith(string $permission): User
    {
        $role = Role::create(['name' => "role-{$permission}", 'label' => 'Role '.ucfirst($permission)]);
        $role->permissions()->attach(
            Permission::firstOrCreate(['name' => $permission], ['label' => ucfirst($permission)])
        );

        $user = User::create([
            'name' => 'Admin '.ucfirst($permission),
            'email' => "{$permission}@lab.test",
            'password' => 'rahasia123',
        ]);
        $user->roles()->attach($role);

        return $user;
    }

    public function test_show_in_schedule_defaults_to_true(): void
    {
        $lab = $this->lab();

        $this->assertTrue($lab->show_in_schedule);
    }

    public function test_hidden_lab_is_not_listed_on_public_schedule(): void
    {
        $visible = $this->lab();
        $hidden = $this->lab([
            'name' => 'Lab Rahasia',
            'code' => 'LR1',
            'show_in_schedule' => false,
        ]);

        $this->get(route('jadwal-lab.index'))
            ->assertOk()
            ->assertSee($visible->name)
            ->assertDontSee($hidden->name)
            ->assertDontSee($hidden->code);
    }

    public function test_hidden_lab_id_query_falls_back_to_all_labs_view(): void
    {
        $visible = $this->lab();
        $hidden = $this->lab([
            'name' => 'Lab Rahasia',
            'code' => 'LR1',
            'show_in_schedule' => false,
        ]);

        $this->get(route('jadwal-lab.index', ['laboratory_id' => $hidden->id]))
            ->assertOk()
            ->assertSee($visible->name)
            ->assertDontSee($hidden->name);
    }

    public function test_admin_schedule_grid_still_shows_hidden_lab(): void
    {
        $hidden = $this->lab([
            'name' => 'Lab Rahasia',
            'code' => 'LR1',
            'show_in_schedule' => false,
        ]);

        $user = $this->userWith('manage-lab-schedules');

        $this->actingAs($user)
            ->get(route('lab-schedules.index'))
            ->assertOk()
            ->assertSee($hidden->name)
            ->assertSee($hidden->code);
    }

    public function test_store_and_update_persist_visibility_flag(): void
    {
        $user = $this->userWith('manage-laboratories');

        $payload = [
            'name' => 'Lab Baru',
            'code' => 'LB1',
            'location' => 'Gedung C',
            'capacity' => 20,
            'status' => 'Aktif',
            'show_in_schedule' => '0',
            'description' => '',
        ];

        $this->actingAs($user)
            ->post(route('laboratories.store'), $payload)
            ->assertRedirect(route('laboratories.index'));

        $lab = Laboratory::where('code', 'LB1')->firstOrFail();
        $this->assertFalse($lab->show_in_schedule);

        $this->actingAs($user)
            ->put(route('laboratories.update', $lab), array_merge($payload, ['show_in_schedule' => '1']))
            ->assertRedirect(route('laboratories.index'));

        $this->assertTrue($lab->fresh()->show_in_schedule);
    }

    public function test_laboratories_index_shows_schedule_badge(): void
    {
        $user = $this->userWith('manage-laboratories');
        $this->lab();
        $this->lab([
            'name' => 'Lab Rahasia',
            'code' => 'LR1',
            'show_in_schedule' => false,
        ]);

        $this->actingAs($user)
            ->get(route('laboratories.index'))
            ->assertOk()
            ->assertSee('Tampil')
            ->assertSee('Tidak');
    }
}
