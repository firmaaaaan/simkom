<?php

namespace Tests\Feature;

use App\Models\Computer;
use App\Models\Permission;
use App\Models\Role;
use App\Models\Software;
use App\Models\SoftwareLicense;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SoftwareLicenseTest extends TestCase
{
    use RefreshDatabase;

    private function userWith(array $permissions, string $email = 'uji@software.test'): User
    {
        $role = Role::firstOrCreate(['name' => 'role-'.md5($email)], ['label' => 'Role Uji']);

        foreach ($permissions as $name) {
            $role->permissions()->syncWithoutDetaching([
                Permission::firstOrCreate(['name' => $name], ['label' => $name])->id,
            ]);
        }

        $user = User::create(['name' => 'User Uji', 'email' => $email, 'password' => 'rahasia123']);
        $user->roles()->syncWithoutDetaching([$role->id]);

        return $user;
    }

    private function admin(): User
    {
        return $this->userWith([
            'view-software', 'create-software', 'edit-software', 'delete-software', 'view-license-key',
        ]);
    }

    private function computer(string $code = 'PC-01'): Computer
    {
        return Computer::create(['code' => $code, 'status' => 'Aktif']);
    }

    private function payload(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Microsoft Office 2021',
            'category' => 'Office Suite',
            'status' => 'Aktif',
            'license_type' => 'Volume License',
        ], $overrides);
    }

    public function test_create_software_with_multiple_licenses_and_pic(): void
    {
        $pc1 = $this->computer('PC-01');
        $pc2 = $this->computer('PC-02');

        $response = $this->actingAs($this->admin())->post(route('software.store'), $this->payload([
            'pic_name' => 'Budi Santoso',
            'pic_email' => 'budi@kampus.ac.id',
            'licenses' => [
                ['label' => 'Lisensi 1', 'license_key' => 'AAAA-BBBB-CCCC', 'assigned_computer_id' => $pc1->id, 'purchase_date' => '2025-01-01', 'expiry_date' => '2027-01-01'],
                ['label' => 'Lisensi 2', 'license_key' => 'DDDD-EEEE-FFFF', 'assigned_computer_id' => $pc2->id],
                ['label' => 'Lisensi 3', 'license_key' => 'GGGG-HHHH-IIII'],
            ],
        ]));

        $response->assertRedirect(route('software.index'));

        $software = Software::where('name', 'Microsoft Office 2021')->firstOrFail();
        $this->assertSame('Budi Santoso', $software->pic_name);
        $this->assertSame('budi@kampus.ac.id', $software->pic_email);
        $this->assertCount(3, $software->licenses);

        $this->assertDatabaseHas('computer_software', ['computer_id' => $pc1->id, 'software_id' => $software->id]);
        $this->assertDatabaseHas('computer_software', ['computer_id' => $pc2->id, 'software_id' => $software->id]);
    }

    public function test_create_software_without_licenses_is_allowed(): void
    {
        $response = $this->actingAs($this->admin())->post(route('software.store'), $this->payload([
            'name' => 'Software Tanpa Lisensi',
        ]));

        $response->assertRedirect(route('software.index'));

        $software = Software::where('name', 'Software Tanpa Lisensi')->firstOrFail();
        $this->assertCount(0, $software->licenses);
        $this->assertNull($software->pic_name);
    }

    public function test_expiry_date_before_purchase_date_is_rejected(): void
    {
        $response = $this->actingAs($this->admin())->post(route('software.store'), $this->payload([
            'licenses' => [
                ['license_key' => 'AAAA-BBBB-CCCC', 'purchase_date' => '2026-05-01', 'expiry_date' => '2026-01-01'],
            ],
        ]));

        $response->assertSessionHasErrors('licenses.0.expiry_date');
        $this->assertDatabaseCount('software_licenses', 0);
    }

    public function test_same_computer_cannot_get_two_licenses_for_same_software(): void
    {
        $pc = $this->computer('PC-01');

        $response = $this->actingAs($this->admin())->post(route('software.store'), $this->payload([
            'licenses' => [
                ['license_key' => 'AAAA', 'assigned_computer_id' => $pc->id],
                ['license_key' => 'BBBB', 'assigned_computer_id' => $pc->id],
            ],
        ]));

        $response->assertSessionHasErrors('licenses.1.assigned_computer_id');
    }

    public function test_update_syncs_licenses_and_computer_pivot(): void
    {
        $pc = $this->computer('PC-01');

        $user = $this->admin();
        $this->actingAs($user)->post(route('software.store'), $this->payload([
            'licenses' => [
                ['license_key' => 'AAAA-BBBB-CCCC', 'assigned_computer_id' => $pc->id],
            ],
        ]));

        $software = Software::firstOrFail();
        $license = $software->licenses()->firstOrFail();

        $response = $this->actingAs($user)->put(route('software.update', $software), $this->payload([
            'name' => 'Microsoft Office 2021 Updated',
            'licenses' => [
                ['id' => $license->id, 'label' => 'Lisensi Utama', 'license_key' => 'ZZZZ-YYYY-XXXX', 'assigned_computer_id' => $pc->id],
                ['label' => 'Lisensi Baru', 'license_key' => 'NEW1-NEW2-NEW3'],
            ],
        ]));

        $response->assertRedirect(route('software.index'));

        $software->refresh();
        $this->assertSame('Microsoft Office 2021 Updated', $software->name);
        $this->assertCount(2, $software->licenses);
        $this->assertDatabaseHas('software_licenses', ['id' => $license->id, 'license_key' => 'ZZZZ-YYYY-XXXX']);
        $this->assertDatabaseHas('computer_software', ['computer_id' => $pc->id, 'software_id' => $software->id]);
    }

    public function test_removing_license_detaches_computer_when_no_license_left(): void
    {
        $pc = $this->computer('PC-01');

        $user = $this->admin();
        $this->actingAs($user)->post(route('software.store'), $this->payload([
            'licenses' => [
                ['license_key' => 'AAAA-BBBB-CCCC', 'assigned_computer_id' => $pc->id],
            ],
        ]));

        $software = Software::firstOrFail();
        $this->assertDatabaseHas('computer_software', ['computer_id' => $pc->id, 'software_id' => $software->id]);

        $this->actingAs($user)->put(route('software.update', $software), $this->payload([
            'licenses' => [],
        ]));

        $this->assertDatabaseMissing('computer_software', ['computer_id' => $pc->id, 'software_id' => $software->id]);
        $this->assertCount(0, $software->fresh()->licenses);
    }

    public function test_submitting_empty_license_form_removes_all_licenses(): void
    {
        $user = $this->admin();
        $this->actingAs($user)->post(route('software.store'), $this->payload([
            'licenses' => [
                ['license_key' => 'AAAA-BBBB-CCCC'],
            ],
        ]));

        $software = Software::firstOrFail();

        $this->actingAs($user)->put(route('software.update', $software), $this->payload([
            'licenses_submitted' => '1',
        ]));

        $this->assertCount(0, $software->fresh()->licenses);
    }

    public function test_software_pages_render(): void
    {
        $user = $this->admin();

        $this->actingAs($user)->get(route('software.create'))->assertOk();

        $this->actingAs($user)->post(route('software.store'), $this->payload([
            'licenses' => [
                ['license_key' => 'AAAA-BBBB-CCCC', 'expiry_date' => '2030-01-01'],
            ],
        ]));

        $software = Software::firstOrFail();

        $this->actingAs($user)->get(route('software.show', $software))->assertOk();
        $this->actingAs($user)->get(route('software.edit', $software))->assertOk();
        $this->actingAs($user)->get(route('software.index'))->assertOk();
    }

    public function test_update_without_licenses_key_keeps_existing_licenses(): void
    {
        $user = $this->admin();
        $this->actingAs($user)->post(route('software.store'), $this->payload([
            'licenses' => [
                ['license_key' => 'AAAA-BBBB-CCCC'],
            ],
        ]));

        $software = Software::firstOrFail();

        $this->actingAs($user)->put(route('software.update', $software), $this->payload([
            'name' => 'Nama Baru',
        ]));

        $this->assertCount(1, $software->fresh()->licenses);
    }

    public function test_reveal_license_key_requires_permission(): void
    {
        $user = $this->admin();
        $this->actingAs($user)->post(route('software.store'), $this->payload([
            'licenses' => [
                ['license_key' => 'AAAA-BBBB-CCCC'],
            ],
        ]));

        $software = Software::firstOrFail();
        $license = $software->licenses()->firstOrFail();

        $this->actingAs($user)
            ->getJson(route('software.license-key', [$software, $license]))
            ->assertOk()
            ->assertJson(['key' => 'AAAA-BBBB-CCCC']);

        $withoutPermission = $this->userWith(['view-software'], 'tanpa-izin@software.test');

        $this->actingAs($withoutPermission)
            ->getJson(route('software.license-key', [$software, $license]))
            ->assertForbidden();
    }

    public function test_deleting_software_cascades_licenses(): void
    {
        $user = $this->admin();
        $this->actingAs($user)->post(route('software.store'), $this->payload([
            'licenses' => [
                ['license_key' => 'AAAA-BBBB-CCCC'],
            ],
        ]));

        $software = Software::firstOrFail();
        $this->assertDatabaseCount('software_licenses', 1);

        $this->actingAs($user)->delete(route('software.destroy', $software));

        $this->assertDatabaseCount('software_licenses', 0);
        $this->assertDatabaseCount('software', 0);
    }

    public function test_masked_key_hides_middle_characters(): void
    {
        $license = new SoftwareLicense(['license_key' => 'ABCD-EFGH-IJKL']);

        $this->assertSame('ABCD••••••IJKL', $license->maskedKey());
    }
}
