<?php

namespace Tests\Feature;

use App\Models\LabUsage;
use App\Models\Laboratory;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LabUsageManualInputTest extends TestCase
{
    use RefreshDatabase;

    private function lab(): Laboratory
    {
        return Laboratory::create([
            'name' => 'Lab Komputer 1', 'code' => 'LK1', 'location' => 'Gedung A', 'capacity' => 40,
        ]);
    }

    private function userWith(string $roleName, array $permissions): User
    {
        $role = Role::firstOrCreate(['name' => $roleName], ['label' => ucfirst($roleName)]);
        foreach ($permissions as $permission) {
            $role->permissions()->attach(
                Permission::firstOrCreate(['name' => $permission], ['label' => $permission])
            );
        }

        $user = User::create([
            'name'     => ucfirst($roleName),
            'email'    => "{$roleName}@lab.test",
            'password' => 'rahasia123',
        ]);
        $user->roles()->attach($role);

        return $user;
    }

    private function admin(): User
    {
        return $this->userWith('admin', ['manage-lab-usages']);
    }

    private function payload(Laboratory $lab, array $overrides = []): array
    {
        return array_merge([
            'laboratory_id' => $lab->id,
            'user_name'     => 'Budi Santoso',
            'user_prodi'    => 'Teknik Informatika',
            'purpose'       => 'Praktikum Jaringan',
            'status'        => 'In',
            'checked_in_at' => '2026-09-30 08:00',
        ], $overrides);
    }

    public function test_admin_can_store_manual_check_in(): void
    {
        $lab = $this->lab();
        $admin = $this->admin();

        $this->actingAs($admin)
            ->from(route('lab-usages.index'))
            ->post(route('lab-usages.store'), $this->payload($lab))
            ->assertRedirect(route('lab-usages.index'))
            ->assertSessionHas('success');

        $usage = LabUsage::first();
        $this->assertNotNull($usage);
        $this->assertSame('In', $usage->status);
        $this->assertSame('manual', $usage->source);
        $this->assertSame($admin->id, $usage->created_by);
        $this->assertNull($usage->validated_at);
        $this->assertSame('Rabu', $usage->day);
        $this->assertSame('2026-09-30 08:00:00', $usage->checked_in_at->format('Y-m-d H:i:s'));
    }

    public function test_admin_can_store_manual_usage_that_already_ended(): void
    {
        $lab = $this->lab();
        $admin = $this->admin();

        $this->actingAs($admin)
            ->post(route('lab-usages.store'), $this->payload($lab, [
                'status'       => 'Out',
                'validated_at' => '2026-09-30 11:00',
                'exit_note'    => 'Selesai praktikum',
            ]))
            ->assertSessionHas('success');

        $usage = LabUsage::first();
        $this->assertSame('Out', $usage->status);
        $this->assertSame('2026-09-30 11:00:00', $usage->validated_at->format('Y-m-d H:i:s'));
        $this->assertSame($admin->id, $usage->validated_by);
        $this->assertSame('Selesai praktikum', $usage->exit_note);
        $this->assertSame($admin->id, $usage->created_by);
    }

    public function test_manual_input_does_not_create_notification(): void
    {
        $lab = $this->lab();
        $admin = $this->admin();

        $this->actingAs($admin)->post(route('lab-usages.store'), $this->payload($lab));

        $this->assertDatabaseCount('notifications', 0);
    }

    public function test_qr_check_in_still_creates_notification(): void
    {
        $lab = $this->lab();

        $this->post(route('lab-scan.check-in', $lab->code), [
            'user_name'  => 'Budi Santoso',
            'user_prodi' => 'Teknik Informatika',
            'purpose'    => 'Praktikum Jaringan',
        ]);

        $this->assertDatabaseCount('notifications', 1);
        $this->assertSame('qr', LabUsage::first()->source);
    }

    public function test_store_requires_core_fields(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)
            ->from(route('lab-usages.index'))
            ->post(route('lab-usages.store'), [])
            ->assertRedirect(route('lab-usages.index'))
            ->assertSessionHasErrors([
                'laboratory_id', 'user_name', 'user_prodi', 'purpose', 'status', 'checked_in_at',
            ]);

        $this->assertSame(0, LabUsage::count());
    }

    public function test_store_requires_checkout_time_when_status_out(): void
    {
        $lab = $this->lab();
        $admin = $this->admin();

        $this->actingAs($admin)
            ->from(route('lab-usages.index'))
            ->post(route('lab-usages.store'), $this->payload($lab, ['status' => 'Out']))
            ->assertSessionHasErrors(['validated_at']);

        $this->assertSame(0, LabUsage::count());
    }

    public function test_checkout_cannot_be_before_check_in(): void
    {
        $lab = $this->lab();
        $admin = $this->admin();

        $this->actingAs($admin)
            ->post(route('lab-usages.store'), $this->payload($lab, [
                'status'       => 'Out',
                'validated_at' => '2026-09-30 07:00',
            ]))
            ->assertSessionHasErrors(['validated_at']);

        $this->assertSame(0, LabUsage::count());
    }

    public function test_store_rejects_person_already_inside_same_lab(): void
    {
        $lab = $this->lab();
        $admin = $this->admin();

        $this->actingAs($admin)->post(route('lab-usages.store'), $this->payload($lab));
        $this->actingAs($admin)
            ->post(route('lab-usages.store'), $this->payload($lab, ['checked_in_at' => '2026-09-30 09:00']))
            ->assertSessionHasErrors('user_name');

        $this->assertSame(1, LabUsage::count());
    }

    public function test_duplicate_rule_ignores_people_already_checked_out(): void
    {
        $lab = $this->lab();
        $admin = $this->admin();

        $this->actingAs($admin)->post(route('lab-usages.store'), $this->payload($lab, [
            'status'       => 'Out',
            'validated_at' => '2026-09-30 10:00',
        ]));
        $this->actingAs($admin)
            ->post(route('lab-usages.store'), $this->payload($lab, ['checked_in_at' => '2026-09-30 13:00']))
            ->assertSessionMissing('errors');

        $this->assertSame(2, LabUsage::count());
    }

    public function test_laboran_with_permission_cannot_store_update_or_delete(): void
    {
        $lab = $this->lab();
        $laboran = $this->userWith('laboran', ['manage-lab-usages']);

        $usage = LabUsage::create([
            'laboratory_id' => $lab->id,
            'user_name'     => 'Sari',
            'user_prodi'    => 'Informatika',
            'purpose'       => 'Tugas',
            'day'           => 'Senin',
            'status'        => 'In',
            'checked_in_at' => now(),
            'source'        => 'manual',
        ]);

        $this->actingAs($laboran)->post(route('lab-usages.store'), $this->payload($lab))->assertForbidden();
        $this->actingAs($laboran)->put(route('lab-usages.update', $usage), $this->payload($lab))->assertForbidden();
        $this->actingAs($laboran)->delete(route('lab-usages.destroy', $usage))->assertForbidden();

        $this->assertSame(1, LabUsage::count());
        $this->assertNotNull($usage->fresh());
    }

    public function test_user_without_permission_cannot_store(): void
    {
        $lab = $this->lab();
        $outsider = User::create(['name' => 'Orang Luar', 'email' => 'luar@lab.test', 'password' => 'x']);

        $this->actingAs($outsider)
            ->post(route('lab-usages.store'), $this->payload($lab))
            ->assertForbidden();

        $this->assertSame(0, LabUsage::count());
    }

    public function test_admin_can_update_manual_usage(): void
    {
        $lab = $this->lab();
        $otherLab = Laboratory::create(['name' => 'Lab Komputer 2', 'code' => 'LK2', 'location' => 'Gedung B', 'capacity' => 40]);
        $admin = $this->admin();

        $usage = LabUsage::create([
            'laboratory_id' => $lab->id,
            'user_name'     => 'Sari',
            'user_prodi'    => 'Informatika',
            'purpose'       => 'Tugas',
            'day'           => 'Senin',
            'status'        => 'In',
            'checked_in_at' => now()->subHour(),
            'source'        => 'manual',
            'created_by'    => $admin->id,
        ]);

        $this->actingAs($admin)
            ->put(route('lab-usages.update', $usage), [
                'laboratory_id' => $otherLab->id,
                'user_name'     => 'Sari Wijaya',
                'user_prodi'    => 'Sistem Informasi',
                'purpose'       => 'Praktikum Basis Data',
                'status'        => 'Out',
                'checked_in_at' => '2026-09-30 08:00',
                'validated_at'  => '2026-09-30 10:30',
                'exit_note'     => 'Selesai',
            ])
            ->assertSessionHas('success');

        $fresh = $usage->fresh();
        $this->assertSame('Sari Wijaya', $fresh->user_name);
        $this->assertSame('Sistem Informasi', $fresh->user_prodi);
        $this->assertSame($otherLab->id, $fresh->laboratory_id);
        $this->assertSame('Out', $fresh->status);
        $this->assertSame('Rabu', $fresh->day);
        $this->assertSame($admin->id, $fresh->validated_by);
        $this->assertSame($admin->id, $fresh->created_by);
        $this->assertSame('manual', $fresh->source);
        $this->assertSame(
            $usage->created_at->format('Y-m-d H:i:s'),
            $fresh->created_at->format('Y-m-d H:i:s')
        );
    }

    public function test_update_rejects_duplicate_active_person(): void
    {
        $lab = $this->lab();
        $admin = $this->admin();

        $this->actingAs($admin)->post(route('lab-usages.store'), $this->payload($lab, [
            'user_name' => 'Budi Santoso',
        ]));

        $other = LabUsage::create([
            'laboratory_id' => $lab->id,
            'user_name'     => 'Budi Santoso',
            'user_prodi'    => 'Teknik Informatika',
            'purpose'       => 'Tugas',
            'day'           => 'Senin',
            'status'        => 'In',
            'checked_in_at' => now()->subMinutes(30),
            'source'        => 'manual',
        ]);

        $this->actingAs($admin)
            ->put(route('lab-usages.update', $other), $this->payload($lab))
            ->assertSessionHasErrors('user_name');

        $this->assertSame('Tugas', $other->fresh()->purpose);
    }

    public function test_admin_can_destroy_usage(): void
    {
        $lab = $this->lab();
        $admin = $this->admin();

        $usage = LabUsage::create([
            'laboratory_id' => $lab->id,
            'user_name'     => 'Sari',
            'user_prodi'    => 'Informatika',
            'purpose'       => 'Tugas',
            'day'           => 'Senin',
            'status'        => 'In',
            'checked_in_at' => now(),
            'source'        => 'manual',
            'created_by'    => $admin->id,
        ]);

        $this->actingAs($admin)
            ->delete(route('lab-usages.destroy', $usage))
            ->assertSessionHas('success');

        $this->assertNull($usage->fresh());
        $this->assertSame(0, LabUsage::count());
    }

    public function test_index_shows_manual_badge_and_input_button_for_admin(): void
    {
        $lab = $this->lab();
        $admin = $this->admin();

        $this->actingAs($admin)->post(route('lab-usages.store'), $this->payload($lab));

        $this->actingAs($admin)
            ->get(route('lab-usages.index'))
            ->assertOk()
            ->assertSee('Input Manual')
            ->assertSee('Manual');
    }
}
