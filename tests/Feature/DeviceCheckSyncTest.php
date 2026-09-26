<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\Computer;
use App\Models\DeviceCheck;
use App\Models\Laboratory;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeviceCheckSyncTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        $role = Role::create(['name' => 'admin', 'label' => 'Admin']);
        $role->permissions()->attach(
            Permission::firstOrCreate(['name' => 'manage-maintenance'], ['label' => 'Kelola Maintenance'])
        );

        $user = User::create(['name' => 'Admin', 'email' => 'admin@device.test', 'password' => 'rahasia123']);
        $user->roles()->attach($role);

        return $user;
    }

    private function seedLab(): array
    {
        $lab = Laboratory::create(['name' => 'Lab Uji', 'code' => 'LU1', 'location' => 'Gedung A', 'capacity' => 40]);
        $computer = Computer::create(['code' => 'LU1-001', 'laboratory_id' => $lab->id, 'status' => 'Aktif']);
        $year = AcademicYear::create([
            'name' => 'TA 2026/2027', 'start_year' => 2026, 'end_year' => 2027, 'status' => 'Aktif',
            'start_date' => '2026-08-01', 'end_date' => '2027-06-30',
        ]);

        return [$lab, $computer, $year];
    }

    public function test_storing_device_check_persists_matrix_items_with_ids(): void
    {
        [$lab, $computer, $year] = $this->seedLab();

        $items = [];
        foreach (DeviceCheck::itemKeys() as $index => $key) {
            $items[$key] = [$computer->id => $index < 3];
        }

        $this->actingAs($this->admin())->post(route('device-checks.store'), [
            'laboratory_id' => $lab->id,
            'academic_year_id' => $year->id,
            'check_date' => '2026-09-20',
            'officer_name' => 'Firmansyah',
            'items' => $items,
        ])->assertRedirect();

        $check = DeviceCheck::first();
        $this->assertNotNull($check);
        $this->assertCount(count(DeviceCheck::itemKeys()), $check->items);

        foreach ($check->items as $item) {
            $this->assertNotNull($item->id, 'Bulk inserted item must receive a UUID.');
        }

        $this->assertSame(3, $check->items()->where('is_checked', true)->count());
    }

    public function test_updating_device_check_replaces_matrix_items(): void
    {
        [$lab, $computer, $year] = $this->seedLab();

        $check = DeviceCheck::create([
            'laboratory_id' => $lab->id, 'academic_year_id' => $year->id,
            'check_date' => '2026-09-20', 'officer_name' => 'Firmansyah',
        ]);

        $items = [];
        foreach (DeviceCheck::itemKeys() as $key) {
            $items[$key] = [$computer->id => true];
        }

        $this->actingAs($this->admin())->put(route('device-checks.update', $check), [
            'laboratory_id' => $lab->id,
            'academic_year_id' => $year->id,
            'check_date' => '2026-09-21',
            'officer_name' => 'Firmansyah',
            'items' => $items,
        ])->assertRedirect(route('device-checks.show', $check));

        $this->assertSame(count(DeviceCheck::itemKeys()), $check->fresh()->items()->count());
        $this->assertTrue($check->fresh()->items()->where('is_checked', true)->count() > 0);
    }
}
