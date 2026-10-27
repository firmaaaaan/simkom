<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\Computer;
use App\Models\DeviceCheck;
use App\Models\DeviceCheckItem;
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
        foreach (['view-maintenance', 'create-maintenance', 'edit-maintenance', 'delete-maintenance'] as $name) {
            $role->permissions()->attach(
                Permission::firstOrCreate(['name' => $name], ['label' => $name])
            );
        }

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
        ]);

        return [$lab, $computer, $year];
    }

    public function test_storing_device_check_persists_matrix_items_with_ids(): void
    {
        [$lab, $computer, $year] = $this->seedLab();

        $items = [];
        foreach (DeviceCheck::itemKeys() as $index => $key) {
            $items[$key] = [
                $computer->id => $index < 3
                    ? DeviceCheckItem::STATUS_BERFUNGSI
                    : ($index < 6 ? DeviceCheckItem::STATUS_RUSAK : DeviceCheckItem::STATUS_TIDAK_ADA),
            ];
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

        $this->assertSame(3, $check->items()->where('status', DeviceCheckItem::STATUS_BERFUNGSI)->count());
        $this->assertSame(3, $check->items()->where('status', DeviceCheckItem::STATUS_RUSAK)->count());
        $this->assertSame(2, $check->items()->where('status', DeviceCheckItem::STATUS_TIDAK_ADA)->count());
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
            $items[$key] = [$computer->id => DeviceCheckItem::STATUS_BERFUNGSI];
        }

        $this->actingAs($this->admin())->put(route('device-checks.update', $check), [
            'laboratory_id' => $lab->id,
            'academic_year_id' => $year->id,
            'check_date' => '2026-09-21',
            'officer_name' => 'Firmansyah',
            'items' => $items,
        ])->assertRedirect(route('device-checks.show', $check));

        $this->assertSame(count(DeviceCheck::itemKeys()), $check->fresh()->items()->count());
        $this->assertSame(
            count(DeviceCheck::itemKeys()),
            $check->fresh()->items()->where('status', DeviceCheckItem::STATUS_BERFUNGSI)->count()
        );
    }

    public function test_store_rejects_unknown_status_value(): void
    {
        [$lab, $computer, $year] = $this->seedLab();

        $this->actingAs($this->admin())->post(route('device-checks.store'), [
            'laboratory_id' => $lab->id,
            'academic_year_id' => $year->id,
            'check_date' => '2026-09-20',
            'items' => ['mouse' => [$computer->id => 'hilang-total']],
        ])->assertSessionHasErrors('items.mouse.'.$computer->id);

        $this->assertSame(0, DeviceCheck::count());
    }

    public function test_show_distinguishes_rusak_and_tidak_ada_counts(): void
    {
        [$lab, $computer, $year] = $this->seedLab();
        $admin = $this->admin();

        $items = [];
        foreach (DeviceCheck::itemKeys() as $index => $key) {
            // 4 berfungsi, 2 rusak, 2 tidak ada (8 item).
            $status = match (true) {
                $index < 4 => DeviceCheckItem::STATUS_BERFUNGSI,
                $index < 6 => DeviceCheckItem::STATUS_RUSAK,
                default => DeviceCheckItem::STATUS_TIDAK_ADA,
            };
            $items[$key] = [$computer->id => $status];
        }

        $this->actingAs($admin)->post(route('device-checks.store'), [
            'laboratory_id' => $lab->id,
            'academic_year_id' => $year->id,
            'check_date' => '2026-09-20',
            'items' => $items,
        ])->assertRedirect();

        $check = DeviceCheck::first();

        $this->actingAs($admin)
            ->get(route('device-checks.show', $check))
            ->assertOk()
            ->assertSee('Berfungsi: <strong>4</strong>', false)
            ->assertSee('Rusak: <strong>2</strong>', false)
            ->assertSee('Tidak Ada: <strong>2</strong>', false);
    }

    public function test_create_and_edit_pages_render_three_status_matrix(): void
    {
        [$lab, $computer, $year] = $this->seedLab();
        $admin = $this->admin();

        $this->actingAs($admin)
            ->get(route('device-checks.create', [
                'laboratory_id' => $lab->id,
                'academic_year_id' => $year->id,
            ]))
            ->assertOk()
            ->assertSee('Tandai semua', false)
            ->assertSee('status-radio', false)
            ->assertSee('name="items[mouse]['.$computer->id.']"', false)
            ->assertSee('Tidak Ada', false)
            ->assertDontSee('bermasalah atau tidak ada', false); // legenda lama sudah diganti

        $items = [];
        foreach (DeviceCheck::itemKeys() as $key) {
            $items[$key] = [$computer->id => DeviceCheckItem::STATUS_BERFUNGSI];
        }
        $items['mouse'][$computer->id] = DeviceCheckItem::STATUS_RUSAK;

        $this->actingAs($admin)->post(route('device-checks.store'), [
            'laboratory_id' => $lab->id,
            'academic_year_id' => $year->id,
            'check_date' => '2026-09-20',
            'items' => $items,
        ])->assertRedirect();

        $check = DeviceCheck::first();

        $this->actingAs($admin)
            ->get(route('device-checks.edit', $check))
            ->assertOk()
            ->assertSee('value="rusak" checked', false)
            ->assertSee('value="berfungsi" checked', false);
    }
}
