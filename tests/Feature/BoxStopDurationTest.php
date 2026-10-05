<?php

namespace Tests\Feature;

use App\Models\Box;
use App\Models\BoxUsage;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BoxStopDurationTest extends TestCase
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
            'name' => ucfirst($roleName),
            'email' => $email,
            'password' => 'rahasia123',
        ]);
        $user->roles()->attach($role);

        return $user;
    }

    private function admin(): User
    {
        return $this->userWith('admin', ['view-components', 'create-components', 'edit-components', 'delete-components'], 'admin-stop@lab.test');
    }

    private function usage(Box $box, array $overrides = []): BoxUsage
    {
        return BoxUsage::create(array_merge([
            'box_id' => $box->id,
            'user_name' => 'Budi Santoso',
            'user_nim' => '2210512001',
            'user_kelas' => 'TI-4A',
            'status' => 'Using',
            'used_at' => now()->subHours(2),
        ], $overrides));
    }

    public function test_stop_durations_freezes_all_active_rows_at_once(): void
    {
        $active1 = $this->usage($this->box('BOX-RAM-001'));
        $active2 = $this->usage($this->box('BOX-RAM-002'));
        $returned = $this->usage($this->box('BOX-RAM-003'), [
            'status' => 'Returned',
            'used_at' => now()->subHours(5),
            'returned_at' => now()->subHours(1),
        ]);

        $this->actingAs($this->admin())
            ->post(route('box-usages.stop-durations'))
            ->assertOk()
            ->assertJson(['success' => true, 'count' => 2]);

        foreach ([$active1, $active2] as $row) {
            $fresh = $row->fresh();
            $this->assertSame('Stopped', $fresh->status);
            $this->assertNotNull($fresh->stopped_at);
        }

        $this->assertSame('Returned', $returned->fresh()->status);
        $this->assertNull($returned->fresh()->stopped_at);
    }

    public function test_stop_durations_reports_zero_when_nothing_is_active(): void
    {
        $this->usage($this->box(), [
            'status' => 'Stopped',
            'stopped_at' => now(),
        ]);

        $this->actingAs($this->admin())
            ->post(route('box-usages.stop-durations'))
            ->assertOk()
            ->assertJson(['success' => true, 'count' => 0]);
    }

    public function test_stop_durations_forbidden_for_laboran_and_ordinary_user(): void
    {
        $laboran = $this->userWith('laboran', ['view-components', 'edit-components'], 'laboran-stop@lab.test');
        $this->usage($this->box());

        $this->actingAs($laboran)
            ->post(route('box-usages.stop-durations'))
            ->assertForbidden();

        $outsider = User::create(['name' => 'Orang Luar', 'email' => 'luar-stop@lab.test', 'password' => 'x']);
        $this->actingAs($outsider)
            ->post(route('box-usages.stop-durations'))
            ->assertForbidden();

        $this->assertSame('Using', BoxUsage::first()->status);
    }

    public function test_stopped_row_still_blocks_qr_borrowing(): void
    {
        $box = $this->box();
        $this->usage($box, ['status' => 'Stopped', 'stopped_at' => now()]);

        $this->post(route('box-scan.use', $box->code), [
            'user_name' => 'Sari',
            'user_nim' => '2210512002',
            'user_kelas' => 'TI-4A',
        ])->assertStatus(422);

        $this->assertSame(1, BoxUsage::count());
    }

    public function test_stopped_row_blocks_manual_reuse_of_the_same_box(): void
    {
        $box = $this->box();
        $admin = $this->admin();
        $this->usage($box, ['status' => 'Stopped', 'stopped_at' => now()]);

        $this->actingAs($admin)
            ->post(route('box-usages.store'), [
                'box_id' => $box->id,
                'user_name' => 'Sari',
                'user_nim' => '2210512002',
                'user_kelas' => 'TI-4A',
                'status' => 'Using',
                'used_at' => '2026-10-05 08:00',
            ])
            ->assertSessionHasErrors('box_id');

        $this->assertSame(1, BoxUsage::count());
    }

    public function test_admin_can_return_stopped_row_and_duration_stays_frozen(): void
    {
        $box = $this->box();
        $admin = $this->admin();

        $usage = $this->usage($box, [
            'used_at' => now()->setTime(8, 0),
            'status' => 'Stopped',
            'stopped_at' => now()->setTime(12, 0),
        ]);

        $this->actingAs($admin)
            ->post(route('box-scan.return', $usage->id))
            ->assertOk()
            ->assertJson(['success' => true]);

        $fresh = $usage->fresh();
        $this->assertSame('Returned', $fresh->status);
        $this->assertNotNull($fresh->returned_at);
        $this->assertSame('12:00:00', $fresh->stopped_at->format('H:i:s'));
        $this->assertSame('4 jam 0 menit', $fresh->duration);
    }

    public function test_bulk_multi_return_accepts_stopped_rows(): void
    {
        $box = $this->box();
        $admin = $this->admin();
        $usage = $this->usage($box, ['status' => 'Stopped', 'stopped_at' => now()]);

        $this->actingAs($admin)
            ->post(route('box-scan.multi-return'), ['items' => [['usage_id' => (string) $usage->id]]])
            ->assertOk()
            ->assertJson(['success' => true]);

        $this->assertSame('Returned', $usage->fresh()->status);
    }

    public function test_manual_update_sets_and_clears_stopped_at(): void
    {
        $box = $this->box();
        $admin = $this->admin();
        $usage = $this->usage($box);

        $base = [
            'box_id' => $box->id,
            'user_name' => 'Budi Santoso',
            'user_nim' => '2210512001',
            'user_kelas' => 'TI-4A',
            'used_at' => '2026-10-05 08:00',
        ];

        $this->actingAs($admin)
            ->put(route('box-usages.update', $usage), array_merge($base, ['status' => 'Stopped']))
            ->assertSessionHas('success');

        $fresh = $usage->fresh();
        $this->assertSame('Stopped', $fresh->status);
        $this->assertNotNull($fresh->stopped_at);
        $this->assertNull($fresh->returned_at);

        $this->actingAs($admin)
            ->put(route('box-usages.update', $usage), array_merge($base, ['status' => 'Using']))
            ->assertSessionHas('success');

        $this->assertNull($usage->fresh()->stopped_at);
    }

    public function test_history_page_shows_stop_button_and_stopped_badge(): void
    {
        $box = $this->box();
        $admin = $this->admin();
        $this->usage($box);

        $this->actingAs($admin)
            ->get(route('components.index', ['tab' => 'history']))
            ->assertOk()
            ->assertSee('Hentikan Semua Durasi');

        $this->actingAs($admin)->post(route('box-usages.stop-durations'));

        $this->actingAs($admin)
            ->get(route('components.index', ['tab' => 'history']))
            ->assertOk()
            ->assertSee('Durasi Dihentikan')
            ->assertDontSee('Hentikan Semua Durasi');
    }

    public function test_history_page_can_filter_stopped_rows(): void
    {
        $box = $this->box();
        $admin = $this->admin();
        $this->usage($box, ['user_name' => 'Stopped Person', 'status' => 'Stopped', 'stopped_at' => now()]);
        $this->usage($this->box('BOX-RAM-002'), ['user_name' => 'Returned Person', 'status' => 'Returned', 'returned_at' => now()]);

        $this->actingAs($admin)
            ->get(route('components.index', ['tab' => 'history', 'usage_status' => 'Stopped']))
            ->assertOk()
            ->assertSee('Stopped Person')
            ->assertDontSee('Returned Person');
    }
}
