<?php

namespace Tests\Feature;

use App\Models\LabUsage;
use App\Models\Laboratory;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LabUsageTest extends TestCase
{
    use RefreshDatabase;

    private function lab(): Laboratory
    {
        return Laboratory::create([
            'name' => 'Lab Komputer 1', 'code' => 'LK1', 'location' => 'Gedung A', 'capacity' => 40,
        ]);
    }

    private function admin(): User
    {
        $role = Role::create(['name' => 'admin', 'label' => 'Admin']);
        $role->permissions()->attach(
            Permission::firstOrCreate(['name' => 'manage-lab-usages'], ['label' => 'Kelola Penggunaan Lab'])
        );

        $user = User::create(['name' => 'Admin', 'email' => 'admin@lab.test', 'password' => 'rahasia123']);
        $user->roles()->attach($role);

        return $user;
    }

    public function test_scan_page_is_public_and_shows_readonly_day_time(): void
    {
        $lab = $this->lab();

        $this->get(route('lab-scan.scan', $lab->code))
            ->assertOk()
            ->assertSee('Isi Data Diri')
            ->assertSee('Hari & waktu terisi otomatis dari sistem.', false);
    }

    public function test_check_in_creates_usage_with_server_day(): void
    {
        $lab = $this->lab();

        $this->post(route('lab-scan.check-in', $lab->code), [
            'user_name'  => 'Budi Santoso',
            'user_prodi' => 'Teknik Informatika',
            'purpose'    => 'Praktikum Jaringan',
        ])->assertRedirect(route('lab-scan.scan', $lab->code));

        $usage = LabUsage::first();
        $this->assertNotNull($usage);
        $this->assertSame('In', $usage->status);
        $this->assertSame('Budi Santoso', $usage->user_name);
        $this->assertSame('Teknik Informatika', $usage->user_prodi);
        $this->assertSame('Praktikum Jaringan', $usage->purpose);
        $this->assertSame($lab->id, $usage->laboratory_id);
        $this->assertNotEmpty($usage->day);
        $this->assertNotNull($usage->checked_in_at);
    }

    public function test_check_in_creates_notification_for_admin_bell(): void
    {
        $lab = $this->lab();

        $this->post(route('lab-scan.check-in', $lab->code), [
            'user_name'  => 'Budi Santoso',
            'user_prodi' => 'Teknik Informatika',
            'purpose'    => 'Praktikum Jaringan',
        ]);

        $this->assertDatabaseCount('notifications', 1);
        $this->assertDatabaseHas('notifications', [
            'title'   => 'Check-in Penggunaan Lab',
            'type'    => 'lab_usage',
            'url'     => route('lab-usages.index', ['status' => 'In']),
        ]);

        $notification = \App\Models\Notification::first();
        $this->assertStringContainsString('Budi Santoso', $notification->message);
        $this->assertStringContainsString($lab->name, $notification->message);
        $this->assertNull($notification->read_at);
    }

    public function test_duplicate_check_in_does_not_create_extra_notification(): void
    {
        $lab = $this->lab();

        $payload = [
            'user_name'  => 'Budi Santoso',
            'user_prodi' => 'Teknik Informatika',
            'purpose'    => 'Praktikum',
        ];

        $this->post(route('lab-scan.check-in', $lab->code), $payload);
        $this->post(route('lab-scan.check-in', $lab->code), $payload);

        $this->assertDatabaseCount('notifications', 1);
    }

    public function test_check_in_requires_form_fields(): void
    {
        $lab = $this->lab();

        $this->from(route('lab-scan.scan', $lab->code))
            ->post(route('lab-scan.check-in', $lab->code), [])
            ->assertRedirect(route('lab-scan.scan', $lab->code))
            ->assertSessionHasErrors(['user_name', 'user_prodi', 'purpose']);

        $this->assertSame(0, LabUsage::count());
    }

    public function test_duplicate_check_in_is_rejected_per_lab(): void
    {
        $lab = $this->lab();

        $payload = [
            'user_name'  => 'Budi Santoso',
            'user_prodi' => 'Teknik Informatika',
            'purpose'    => 'Praktikum',
        ];

        $this->post(route('lab-scan.check-in', $lab->code), $payload);
        $this->post(route('lab-scan.check-in', $lab->code), $payload)
            ->assertSessionHasErrors('duplicate');

        $this->assertSame(1, LabUsage::count());
    }

    public function test_duplicate_is_per_lab_only(): void
    {
        $lab1 = $this->lab();
        $lab2 = Laboratory::create(['name' => 'Lab Komputer 2', 'code' => 'LK2', 'location' => 'Gedung B', 'capacity' => 40]);

        $payload = [
            'user_name'  => 'Budi Santoso',
            'user_prodi' => 'Teknik Informatika',
            'purpose'    => 'Praktikum',
        ];

        $this->post(route('lab-scan.check-in', $lab1->code), $payload);
        $this->post(route('lab-scan.check-in', $lab2->code), $payload)
            ->assertSessionMissing('errors');

        $this->assertSame(2, LabUsage::count());
    }

    public function test_same_person_can_check_in_again_after_validation(): void
    {
        $lab = $this->lab();

        $payload = [
            'user_name'  => 'Budi Santoso',
            'user_prodi' => 'Teknik Informatika',
            'purpose'    => 'Praktikum',
        ];

        $this->post(route('lab-scan.check-in', $lab->code), $payload);
        $usage = LabUsage::first();

        $admin = $this->admin();
        $this->actingAs($admin)
            ->post(route('lab-usages.validate-out', $usage), ['exit_note' => 'Selesai'])
            ->assertRedirect();

        $this->post(route('lab-scan.check-in', $lab->code), $payload)
            ->assertSessionMissing('errors');

        $this->assertSame(2, LabUsage::count());
        $this->assertSame('Out', $usage->fresh()->status);
        $this->assertSame($admin->id, $usage->fresh()->validated_by);
    }

    public function test_index_requires_permission(): void
    {
        $outsider = User::create(['name' => 'Orang Luar', 'email' => 'luar@lab.test', 'password' => 'x']);

        $this->actingAs($outsider)->get(route('lab-usages.index'))->assertForbidden();
    }

    public function test_admin_can_view_index_and_qr_stiker(): void
    {
        $admin = $this->admin();
        $this->lab();

        $this->actingAs($admin)->get(route('lab-usages.index'))->assertOk();
        $this->actingAs($admin)->get(route('lab-usages.qr-stiker'))->assertOk();
    }

    public function test_cannot_validate_out_twice(): void
    {
        $lab = $this->lab();
        $usage = LabUsage::create([
            'laboratory_id' => $lab->id,
            'user_name'     => 'Sari',
            'user_prodi'    => 'Informatika',
            'purpose'       => 'Tugas',
            'day'           => 'Senin',
            'status'        => 'Out',
            'checked_in_at' => now(),
            'validated_at'  => now(),
        ]);

        $admin = $this->admin();
        $this->actingAs($admin)
            ->post(route('lab-usages.validate-out', $usage))
            ->assertSessionHasErrors('usage');
    }
}
