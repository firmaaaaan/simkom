<?php

namespace Tests\Feature;

use App\Models\Laboratory;
use App\Models\LabSchedule;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LabScheduleEntryVisibilityTest extends TestCase
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

    /** Nama permission lama (manage-*) dipetakan ke sekumpulan permission per-aksi baru. */
    private const PERMISSION_MAP = [
        'manage-lab-schedules' => ['view-lab-schedules', 'create-lab-schedules', 'edit-lab-schedules', 'delete-lab-schedules'],
        'manage-laboratories' => ['view-laboratories', 'create-laboratories', 'edit-laboratories', 'delete-laboratories'],
    ];

    private function userWith(string $permission): User
    {
        $names = self::PERMISSION_MAP[$permission] ?? [$permission];
        $role = Role::create(['name' => "role-{$permission}", 'label' => 'Role '.ucfirst($permission)]);
        foreach ($names as $name) {
            $role->permissions()->attach(
                Permission::firstOrCreate(['name' => $name], ['label' => ucfirst($name)])
            );
        }

        $user = User::create([
            'name' => 'Admin '.ucfirst($permission),
            'email' => "{$permission}@lab.test",
            'password' => 'rahasia123',
        ]);
        $user->roles()->attach($role);

        return $user;
    }

    private function schedule(Laboratory $lab, User $user, array $attributes = []): LabSchedule
    {
        return LabSchedule::create(array_merge([
            'laboratory_id' => $lab->id,
            'day' => 'Monday',
            'start_time' => '08:00',
            'end_time' => '10:00',
            'course_name' => 'Pemrograman Web',
            'study_program' => 'Teknik Informatika',
            'semester' => '5',
            'instructor' => 'Budi',
            'class_group' => 'A',
            'created_by' => $user->id,
        ], $attributes));
    }

    public function test_show_in_schedule_defaults_to_true(): void
    {
        $user = $this->userWith('manage-lab-schedules');
        $entry = $this->schedule($this->lab(), $user);

        $this->assertTrue($entry->show_in_schedule);
    }

    public function test_hidden_entry_is_not_shown_on_public_schedule(): void
    {
        $user = $this->userWith('manage-lab-schedules');
        $lab = $this->lab();

        $visible = $this->schedule($lab, $user, [
            'start_time' => '08:00',
            'end_time' => '10:00',
            'course_name' => 'Mata Kuliah Terlihat',
        ]);
        $hidden = $this->schedule($lab, $user, [
            'start_time' => '10:00',
            'end_time' => '12:00',
            'course_name' => 'Mata Kuliah Rahasia',
            'show_in_schedule' => false,
        ]);

        $this->get(route('jadwal-lab.index', ['day' => 'all']))
            ->assertOk()
            ->assertSee($visible->course_name)
            ->assertDontSee($hidden->course_name);
    }

    public function test_hidden_entry_still_shown_on_admin_grid(): void
    {
        $user = $this->userWith('manage-lab-schedules');
        $hidden = $this->schedule($this->lab(), $user, [
            'course_name' => 'Mata Kuliah Rahasia',
            'show_in_schedule' => false,
        ]);

        $this->actingAs($user)
            ->get(route('lab-schedules.index'))
            ->assertOk()
            ->assertSee($hidden->course_name);
    }

    public function test_toggle_endpoint_flips_flag(): void
    {
        $user = $this->userWith('manage-lab-schedules');
        $entry = $this->schedule($this->lab(), $user);

        $this->actingAs($user)
            ->patchJson(route('lab-schedules.toggle-visibility', $entry))
            ->assertOk()
            ->assertJsonPath('success', true);
        $this->assertFalse($entry->fresh()->show_in_schedule);

        $this->actingAs($user)
            ->patchJson(route('lab-schedules.toggle-visibility', $entry))
            ->assertOk()
            ->assertJsonPath('success', true);
        $this->assertTrue($entry->fresh()->show_in_schedule);
    }

    public function test_toggle_endpoint_requires_auth_and_permission(): void
    {
        $owner = $this->userWith('manage-lab-schedules');
        $entry = $this->schedule($this->lab(), $owner);

        $this->patchJson(route('lab-schedules.toggle-visibility', $entry))
            ->assertUnauthorized();

        $this->actingAs($this->userWith('manage-laboratories'))
            ->patchJson(route('lab-schedules.toggle-visibility', $entry))
            ->assertForbidden();

        $this->assertTrue($entry->fresh()->show_in_schedule);
    }

    public function test_store_and_update_persist_visibility_flag(): void
    {
        $user = $this->userWith('manage-lab-schedules');
        $lab = $this->lab();

        $payload = [
            'laboratory_id' => $lab->id,
            'day' => 'Monday',
            'start_time' => '13:00',
            'end_time' => '15:00',
            'course_name' => 'Basis Data',
            'study_program' => 'Teknik Informatika',
            'semester' => '3',
            'instructor' => 'Andi',
            'class_group' => 'B',
            'show_in_schedule' => '0',
        ];

        $this->actingAs($user)
            ->postJson(route('lab-schedules.store'), $payload)
            ->assertOk()
            ->assertJsonPath('success', true);

        $created = LabSchedule::where('course_name', 'Basis Data')->firstOrFail();
        $this->assertFalse($created->show_in_schedule);

        $this->actingAs($user)
            ->putJson(route('lab-schedules.update', $created), array_merge($payload, ['show_in_schedule' => '1']))
            ->assertOk()
            ->assertJsonPath('success', true);

        $this->assertTrue($created->fresh()->show_in_schedule);
    }

    public function test_store_without_visibility_field_defaults_to_true(): void
    {
        $user = $this->userWith('manage-lab-schedules');

        $this->actingAs($user)
            ->postJson(route('lab-schedules.store'), [
                'laboratory_id' => $this->lab()->id,
                'day' => 'Tuesday',
                'start_time' => '08:00',
                'end_time' => '10:00',
                'course_name' => 'Algoritma',
                'study_program' => 'Teknik Informatika',
            ])
            ->assertOk()
            ->assertJsonPath('success', true);

        $this->assertTrue(
            LabSchedule::where('course_name', 'Algoritma')->firstOrFail()->show_in_schedule
        );
    }
}
