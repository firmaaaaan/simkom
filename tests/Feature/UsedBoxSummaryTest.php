<?php

namespace Tests\Feature;

use App\Models\Box;
use App\Models\BoxComponent;
use App\Models\BoxUsage;
use App\Models\Component;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class UsedBoxSummaryTest extends TestCase
{
    use RefreshDatabase;

    private function box(string $code, string $name = 'IOT'): Box
    {
        return Box::create(['name' => $name, 'code' => $code, 'location' => 'Rak A']);
    }

    private function usage(Box $box, array $overrides = []): BoxUsage
    {
        return BoxUsage::create(array_merge([
            'box_id' => $box->id,
            'user_name' => 'Budi Santoso',
            'user_nim' => '2210512001',
            'user_kelas' => 'TI-4A',
            'status' => 'Using',
            'used_at' => now()->subDay(),
            'source' => 'qr',
        ], $overrides));
    }

    private function userWith(array $permissions, string $email): User
    {
        $role = Role::firstOrCreate(['name' => 'operator-'.md5($email)], ['label' => 'Operator']);
        foreach ($permissions as $permission) {
            $role->permissions()->attach(
                Permission::firstOrCreate(['name' => $permission], ['label' => $permission])
            );
        }

        $user = User::create([
            'name' => 'Operator',
            'email' => $email,
            'password' => 'rahasia123',
        ]);
        $user->roles()->attach($role);

        return $user;
    }

    private function makeComponent(int $stock = 50): Component
    {
        return Component::create([
            'name' => 'ESP32',
            'code' => 'CMP-ESP32',
            'category' => 'IoT',
            'quantity' => $stock,
            'status' => 'Tersedia',
        ]);
    }

    public function test_used_boxes_lists_each_box_once_despite_multiple_usages(): void
    {
        $box = $this->box('BOX-IOT-001');
        $this->usage($box, ['status' => 'Returned', 'used_at' => now()->subDays(5)]);
        $this->usage($box, ['status' => 'Returned', 'used_at' => now()->subDays(3)]);
        $this->usage($box, ['status' => 'Using', 'used_at' => now()->subHour()]);

        $admin = $this->userWith(['view-components', 'export-components'], 'dedup@lab.test');

        $this->actingAs($admin)
            ->get(route('components.index', ['tab' => 'used-boxes']))
            ->assertOk()
            ->assertSee('Box Dipakai')
            ->assertSee('BOX-IOT-001')
            ->assertViewHas('usedBoxes', function ($usedBoxes) use ($box) {
                return $usedBoxes->count() === 1
                    && $usedBoxes->first()->id === $box->id
                    && $usedBoxes->first()->usage_count === 3;
            })
            ->assertViewHas('usedBoxStats', function ($stats) {
                return $stats['total'] === 1 && $stats['without_components'] === 1;
            });
    }

    public function test_boxes_without_usage_are_excluded_and_max_date_wins(): void
    {
        $used = $this->box('BOX-USED-001');
        $idle = $this->box('BOX-IDLE-001');

        $latest = now()->subDays(10);
        $this->usage($used, ['used_at' => $latest]);
        $this->usage($used, ['status' => 'Returned', 'used_at' => '2026-01-15 08:00']);

        $admin = $this->userWith(['view-components'], 'filter@lab.test');

        $this->actingAs($admin)
            ->get(route('components.index', ['tab' => 'used-boxes']))
            ->assertOk()
            ->assertSee('BOX-USED-001')
            ->assertViewHas('usedBoxes', function ($usedBoxes) use ($used, $idle, $latest) {
                return $usedBoxes->count() === 1
                    && $usedBoxes->first()->id === $used->id
                    && ! $usedBoxes->contains('id', $idle->id)
                    && Carbon::parse($usedBoxes->first()->last_used_at)->format('Y-m-d H:i:s') === $latest->format('Y-m-d H:i:s');
            });
    }

    public function test_summary_shows_component_columns_and_no_component_badge(): void
    {
        $empty = $this->box('BOX-EMPTY-001');
        $filled = $this->box('BOX-FILLED-001');
        $this->usage($empty);
        $this->usage($filled);

        $component = $this->makeComponent(30);
        BoxComponent::create(['box_id' => $filled->id, 'component_id' => $component->id, 'quantity' => 7]);

        $admin = $this->userWith(['view-components', 'create-components'], 'columns@lab.test');

        $this->actingAs($admin)
            ->get(route('components.index', ['tab' => 'used-boxes']))
            ->assertOk()
            ->assertSee('Belum ada komponen')
            ->assertSee('Jumlah Pemakaian')
            ->assertSee('Terakhir Dipakai')
            ->assertViewHas('usedBoxes', function ($usedBoxes) {
                $byCode = $usedBoxes->keyBy('code');

                return $byCode['BOX-EMPTY-001']->components_count === 0
                    && $byCode['BOX-FILLED-001']->components_count === 1
                    && (int) $byCode['BOX-FILLED-001']->total_quantity === 7;
            });
    }

    public function test_bulk_add_accepts_box_ids(): void
    {
        $boxA = $this->box('BOX-IOT-010');
        $boxB = $this->box('BOX-IOT-011');
        $this->usage($boxA);
        $this->usage($boxB);

        $component = $this->makeComponent(50);
        $admin = $this->userWith(['view-components', 'create-components'], 'bulk-ids@lab.test');

        $this->actingAs($admin)
            ->postJson(route('boxes.bulk-add-component'), [
                'component_id' => $component->id,
                'quantity' => 3,
                'box_ids' => [$boxA->id, $boxB->id],
            ])
            ->assertOk()
            ->assertJson(['success' => true]);

        $this->assertSame(2, BoxComponent::count());
        $this->assertSame(44, $component->fresh()->quantity);
        $this->assertSame(3, $boxA->fresh()->boxComponents()->sum('quantity'));
        $this->assertSame(3, $boxB->fresh()->boxComponents()->sum('quantity'));
    }

    public function test_bulk_add_requires_names_or_ids(): void
    {
        $component = $this->makeComponent(50);
        $admin = $this->userWith(['view-components', 'create-components'], 'bulk-missing@lab.test');

        $this->actingAs($admin)
            ->postJson(route('boxes.bulk-add-component'), [
                'component_id' => $component->id,
                'quantity' => 2,
            ])
            ->assertJsonValidationErrors(['box_names']);

        $this->assertSame(0, BoxComponent::count());
        $this->assertSame(50, $component->fresh()->quantity);
    }

    public function test_bulk_add_still_accepts_box_names(): void
    {
        $this->box('BOX-IOT-020', 'Jaringan');
        $this->box('BOX-IOT-021', 'Jaringan');
        $component = $this->makeComponent(50);
        $admin = $this->userWith(['view-components', 'create-components'], 'bulk-names@lab.test');

        $this->actingAs($admin)
            ->postJson(route('boxes.bulk-add-component'), [
                'component_id' => $component->id,
                'quantity' => 2,
                'box_names' => ['Jaringan'],
            ])
            ->assertOk()
            ->assertJson(['success' => true]);

        $this->assertSame(2, BoxComponent::count());
        $this->assertSame(46, $component->fresh()->quantity);
    }

    public function test_export_used_boxes_requires_permission(): void
    {
        $box = $this->box('BOX-IOT-030');
        $this->usage($box);

        $viewer = $this->userWith(['view-components'], 'viewer-export@lab.test');
        $this->actingAs($viewer)
            ->get(route('boxes.export-used'))
            ->assertForbidden();

        $exporter = $this->userWith(['view-components', 'export-components'], 'exporter@lab.test');
        $this->actingAs($exporter)
            ->get(route('boxes.export-used'))
            ->assertOk();
    }

    public function test_add_buttons_hidden_without_create_permission(): void
    {
        $box = $this->box('BOX-IOT-040');
        $this->usage($box);

        $viewer = $this->userWith(['view-components', 'export-components'], 'viewer-btn@lab.test');

        $this->actingAs($viewer)
            ->get(route('components.index', ['tab' => 'used-boxes']))
            ->assertOk()
            ->assertSee('BOX-IOT-040')
            ->assertDontSee('Tambah Komponen (Bulk)')
            ->assertDontSee('Tambah komponen ke box ini');

        $admin = $this->userWith(['view-components', 'create-components'], 'admin-btn@lab.test');

        $this->actingAs($admin)
            ->get(route('components.index', ['tab' => 'used-boxes']))
            ->assertOk()
            ->assertSee('Tambah Komponen (Bulk)')
            ->assertSee('js-used-quick-add');
    }
}
