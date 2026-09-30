<?php

namespace Tests\Feature;

use App\Models\Box;
use App\Models\BoxReturnNote;
use App\Models\BoxUsage;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BoxUsageManualInputTest extends TestCase
{
    use RefreshDatabase;

    private function box(string $code = 'BOX-RAM-001'): Box
    {
        return Box::create(['name' => 'Box RAM', 'code' => $code, 'location' => 'Rak A']);
    }

    private function userWith(string $roleName, array $permissions, string $email): User
    {
        $role = Role::firstOrCreate(['name' => $roleName], ['label' => ucfirst($roleName)]);
        foreach ($permissions as $permission) {
            $role->permissions()->attach(
                Permission::firstOrCreate(['name' => $permission], ['label' => $permission])
            );
        }

        $user = User::create([
            'name'     => ucfirst($roleName),
            'email'    => $email,
            'password' => 'rahasia123',
        ]);
        $user->roles()->attach($role);

        return $user;
    }

    private function admin(): User
    {
        return $this->userWith('admin', ['manage-components'], 'admin-box@lab.test');
    }

    private function payload(Box $box, array $overrides = []): array
    {
        return array_merge([
            'box_id'     => $box->id,
            'user_name'  => 'Budi Santoso',
            'user_nim'   => '2210512001',
            'user_kelas' => 'TI-4A',
            'status'     => 'Using',
            'used_at'    => '2026-09-29 08:00',
        ], $overrides);
    }

    public function test_admin_can_store_manual_borrowing(): void
    {
        $box = $this->box();
        $admin = $this->admin();

        $this->actingAs($admin)
            ->from(route('components.index', ['tab' => 'history']))
            ->post(route('box-usages.store'), $this->payload($box))
            ->assertRedirect(route('components.index', ['tab' => 'history']))
            ->assertSessionHas('success');

        $usage = BoxUsage::first();
        $this->assertNotNull($usage);
        $this->assertSame('Using', $usage->status);
        $this->assertSame('manual', $usage->source);
        $this->assertSame($admin->id, $usage->created_by);
        $this->assertNull($usage->returned_at);
        $this->assertSame('2026-09-29 08:00:00', $usage->used_at->format('Y-m-d H:i:s'));
        $this->assertSame('Budi Santoso', $usage->user_name);
        $this->assertNull($usage->returnNote);
    }

    public function test_admin_can_store_manual_borrowing_already_returned(): void
    {
        $box = $this->box();
        $admin = $this->admin();

        $this->actingAs($admin)
            ->post(route('box-usages.store'), $this->payload($box, [
                'status'      => 'Returned',
                'returned_at' => '2026-09-29 15:00',
                'note'        => 'Kondisi baik',
            ]))
            ->assertSessionHas('success');

        $usage = BoxUsage::first();
        $this->assertSame('Returned', $usage->status);
        $this->assertSame('2026-09-29 15:00:00', $usage->returned_at->format('Y-m-d H:i:s'));
        $this->assertSame('Kondisi baik', $usage->returnNote?->note);
        $this->assertSame($admin->id, $usage->created_by);
    }

    public function test_store_requires_core_fields(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)
            ->from(route('components.index', ['tab' => 'history']))
            ->post(route('box-usages.store'), [])
            ->assertRedirect(route('components.index', ['tab' => 'history']))
            ->assertSessionHasErrors(['box_id', 'user_name', 'user_nim', 'status', 'used_at']);

        $this->assertSame(0, BoxUsage::count());
    }

    public function test_store_requires_return_time_when_status_returned(): void
    {
        $box = $this->box();
        $admin = $this->admin();

        $this->actingAs($admin)
            ->post(route('box-usages.store'), $this->payload($box, ['status' => 'Returned']))
            ->assertSessionHasErrors(['returned_at']);

        $this->assertSame(0, BoxUsage::count());
    }

    public function test_return_time_cannot_be_before_borrow_time(): void
    {
        $box = $this->box();
        $admin = $this->admin();

        $this->actingAs($admin)
            ->post(route('box-usages.store'), $this->payload($box, [
                'status'      => 'Returned',
                'returned_at' => '2026-09-29 07:00',
            ]))
            ->assertSessionHasErrors(['returned_at']);

        $this->assertSame(0, BoxUsage::count());
    }

    public function test_store_rejects_box_already_in_use(): void
    {
        $box = $this->box();
        $admin = $this->admin();

        BoxUsage::create([
            'box_id'     => $box->id,
            'user_name'  => 'Sari',
            'user_nim'   => '2210512002',
            'user_kelas' => 'TI-4A',
            'status'     => 'Using',
            'used_at'    => now(),
        ]);

        $this->actingAs($admin)
            ->post(route('box-usages.store'), $this->payload($box))
            ->assertSessionHasErrors('box_id');

        $this->assertSame(1, BoxUsage::count());
    }

    public function test_returned_record_is_allowed_while_box_is_currently_out(): void
    {
        $box = $this->box();
        $admin = $this->admin();

        // Box sedang dipinjam orang lain lewat scan QR.
        BoxUsage::create([
            'box_id'     => $box->id,
            'user_name'  => 'Sari',
            'user_nim'   => '2210512002',
            'user_kelas' => 'TI-4A',
            'status'     => 'Using',
            'used_at'    => now(),
        ]);

        $this->actingAs($admin)
            ->post(route('box-usages.store'), $this->payload($box, [
                'status'      => 'Returned',
                'returned_at' => '2026-09-28 16:00',
                'used_at'     => '2026-09-28 08:00',
            ]))
            ->assertSessionHas('success');

        $this->assertSame(2, BoxUsage::count());
    }

    public function test_laboran_with_permission_cannot_store_update_or_delete(): void
    {
        $box = $this->box();
        $laboran = $this->userWith('laboran', ['manage-components'], 'laboran-box@lab.test');

        $usage = BoxUsage::create([
            'box_id'     => $box->id,
            'user_name'  => 'Sari',
            'user_nim'   => '2210512002',
            'user_kelas' => 'TI-4A',
            'status'     => 'Using',
            'used_at'    => now(),
            'source'     => 'manual',
        ]);

        $this->actingAs($laboran)->post(route('box-usages.store'), $this->payload($box))->assertForbidden();
        $this->actingAs($laboran)->put(route('box-usages.update', $usage), $this->payload($box))->assertForbidden();
        $this->actingAs($laboran)->delete(route('box-usages.destroy', $usage))->assertForbidden();

        $this->assertSame(1, BoxUsage::count());
        $this->assertNotNull($usage->fresh());
    }

    public function test_user_without_permission_cannot_store(): void
    {
        $box = $this->box();
        $outsider = User::create(['name' => 'Orang Luar', 'email' => 'luar-box@lab.test', 'password' => 'x']);

        $this->actingAs($outsider)
            ->post(route('box-usages.store'), $this->payload($box))
            ->assertForbidden();

        $this->assertSame(0, BoxUsage::count());
    }

    public function test_admin_can_update_usage(): void
    {
        $box = $this->box();
        $otherBox = $this->box('BOX-RAM-002');
        $admin = $this->admin();

        $usage = BoxUsage::create([
            'box_id'     => $box->id,
            'user_name'  => 'Sari',
            'user_nim'   => '2210512002',
            'user_kelas' => 'TI-4A',
            'status'     => 'Using',
            'used_at'    => now()->subHour(),
            'source'     => 'manual',
            'created_by' => $admin->id,
        ]);

        $this->actingAs($admin)
            ->put(route('box-usages.update', $usage), [
                'box_id'      => $otherBox->id,
                'user_name'   => 'Sari Wijaya',
                'user_nim'    => '2210512003',
                'user_kelas'  => 'TI-4B',
                'status'      => 'Returned',
                'used_at'     => '2026-09-29 08:00',
                'returned_at' => '2026-09-29 12:00',
                'note'        => 'Lengkap',
            ])
            ->assertSessionHas('success');

        $fresh = $usage->fresh();
        $this->assertSame('Sari Wijaya', $fresh->user_name);
        $this->assertSame('2210512003', $fresh->user_nim);
        $this->assertSame('TI-4B', $fresh->user_kelas);
        $this->assertSame($otherBox->id, $fresh->box_id);
        $this->assertSame('Returned', $fresh->status);
        $this->assertSame('Lengkap', $fresh->returnNote?->note);
        $this->assertSame('manual', $fresh->source);
        $this->assertSame($admin->id, $fresh->created_by);
        $this->assertSame(
            $usage->created_at->format('Y-m-d H:i:s'),
            $fresh->created_at->format('Y-m-d H:i:s')
        );
    }

    public function test_update_clears_return_note_when_status_back_to_using(): void
    {
        $box = $this->box();
        $admin = $this->admin();

        $usage = BoxUsage::create([
            'box_id'      => $box->id,
            'user_name'   => 'Sari',
            'user_nim'    => '2210512002',
            'user_kelas'  => 'TI-4A',
            'status'      => 'Returned',
            'used_at'     => now()->subHours(3),
            'returned_at' => now()->subHour(),
            'source'      => 'manual',
            'created_by'  => $admin->id,
        ]);
        BoxReturnNote::create(['box_usage_id' => $usage->id, 'note' => 'Lengkap']);

        $this->actingAs($admin)
            ->put(route('box-usages.update', $usage), $this->payload($box))
            ->assertSessionHas('success');

        $fresh = $usage->fresh();
        $this->assertSame('Using', $fresh->status);
        $this->assertNull($fresh->returned_at);
        $this->assertNull($fresh->returnNote);
        $this->assertSame(0, BoxReturnNote::count());
    }

    public function test_admin_can_destroy_usage_and_its_note(): void
    {
        $box = $this->box();
        $admin = $this->admin();

        $usage = BoxUsage::create([
            'box_id'      => $box->id,
            'user_name'   => 'Sari',
            'user_nim'    => '2210512002',
            'user_kelas'  => 'TI-4A',
            'status'      => 'Returned',
            'used_at'     => now()->subHours(3),
            'returned_at' => now()->subHour(),
            'source'      => 'manual',
            'created_by'  => $admin->id,
        ]);
        BoxReturnNote::create(['box_usage_id' => $usage->id, 'note' => 'Lengkap']);

        $this->actingAs($admin)
            ->delete(route('box-usages.destroy', $usage))
            ->assertSessionHas('success');

        $this->assertNull($usage->fresh());
        $this->assertSame(0, BoxUsage::count());
        $this->assertSame(0, BoxReturnNote::count());
    }

    public function test_index_history_shows_input_button_and_manual_badge(): void
    {
        $box = $this->box();
        $admin = $this->admin();

        $this->actingAs($admin)->post(route('box-usages.store'), $this->payload($box));

        $this->actingAs($admin)
            ->get(route('components.index', ['tab' => 'history']))
            ->assertOk()
            ->assertSee('Input Manual')
            ->assertSee('Manual');
    }
}
