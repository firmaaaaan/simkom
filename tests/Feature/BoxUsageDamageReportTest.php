<?php

namespace Tests\Feature;

use App\Models\Box;
use App\Models\BoxComponent;
use App\Models\BoxUsage;
use App\Models\BoxUsageDamage;
use App\Models\Component;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Laporan kerusakan komponen dalam pemakaian box (admin/laboran):
 * maksimal satu kali per komponen untuk pengguna (NIM) yang sama —
 * termasuk lintas sesi pemakaian. Komponen otomatis berstatus "Rusak".
 */
class BoxUsageDamageReportTest extends TestCase
{
    use RefreshDatabase;

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
        return $this->userWith('admin-damage', ['view-components', 'edit-components'], 'admin-damage@uji.test');
    }

    private function laboran(): User
    {
        return $this->userWith('laboran-damage', ['view-components', 'edit-components'], 'laboran-damage@uji.test');
    }

    private function viewer(): User
    {
        return $this->userWith('viewer-damage', ['view-components'], 'viewer-damage@uji.test');
    }

    /** @return array{0: Box, 1: Component[]} */
    private function boxWithComponents(int $count = 2): array
    {
        $suffix = str_replace('.', '', uniqid());
        $box = Box::create(['name' => 'Box RAM', 'code' => "BOX-{$suffix}", 'location' => 'Rak A']);

        $components = [];
        for ($i = 1; $i <= $count; $i++) {
            $component = Component::create([
                'name' => "Komponen {$suffix}-{$i}",
                'code' => "CMP-{$suffix}-{$i}",
                'category' => 'IoT',
                'status' => 'Tersedia',
            ]);
            BoxComponent::create(['box_id' => $box->id, 'component_id' => $component->id, 'quantity' => 1]);
            $components[] = $component;
        }

        return [$box, $components];
    }

    private function usage(Box $box, string $nim = '2210511', string $name = 'Budi Santoso'): BoxUsage
    {
        return BoxUsage::create([
            'box_id' => $box->id,
            'user_name' => $name,
            'user_nim' => $nim,
            'user_kelas' => 'TI-4A',
            'status' => 'Using',
            'used_at' => now()->subHour(),
            'source' => 'manual',
        ]);
    }

    private function postDamage(User $user, BoxUsage $usage, array $componentIds, array $extra = [])
    {
        return $this->actingAs($user)
            ->from(route('components.index', ['tab' => 'history']))
            ->post(route('box-usages.store-damage', $usage), array_merge([
                'component_ids' => $componentIds,
            ], $extra));
    }

    public function test_admin_can_report_damage_and_components_become_rusak(): void
    {
        $admin = $this->admin();
        [$box, $components] = $this->boxWithComponents(2);
        $usage = $this->usage($box);

        $this->postDamage($admin, $usage, [$components[0]->id, $components[1]->id], ['note' => 'Resistor terbakar'])
            ->assertRedirect(route('components.index', ['tab' => 'history']))
            ->assertSessionHas('success');

        $this->assertSame('Rusak', $components[0]->fresh()->status);
        $this->assertSame('Rusak', $components[1]->fresh()->status);
        $this->assertSame(2, BoxUsageDamage::count());

        $damage = BoxUsageDamage::first();
        $this->assertSame($usage->id, $damage->box_usage_id);
        $this->assertSame($admin->id, $damage->reported_by);
        $this->assertSame('Resistor terbakar', $damage->note);
    }

    public function test_same_component_cannot_be_reported_twice_in_same_usage(): void
    {
        $admin = $this->admin();
        [$box, $components] = $this->boxWithComponents();
        $usage = $this->usage($box);

        $this->postDamage($admin, $usage, [$components[0]->id])->assertSessionHas('success');

        $this->postDamage($admin, $usage, [$components[0]->id])
            ->assertSessionHasErrors('component_ids');

        $this->assertSame(1, BoxUsageDamage::count());
    }

    public function test_same_nim_cannot_report_same_component_in_another_session(): void
    {
        $admin = $this->admin();
        [$box, $components] = $this->boxWithComponents();

        $usageA = $this->usage($box, '2210511', 'Budi Santoso');
        $usageB = $this->usage($box, '2210511', 'Budi Santoso');
        $usageC = $this->usage($box, '2210999', 'Sari Other');

        $this->postDamage($admin, $usageA, [$components[0]->id])->assertSessionHas('success');

        // NIM sama, sesi berikutnya → ditolak.
        $this->postDamage($admin, $usageB, [$components[0]->id])
            ->assertSessionHasErrors('component_ids');
        $this->assertSame(1, BoxUsageDamage::count());

        // Pengguna lain boleh melaporkan komponen yang sama.
        $this->postDamage($admin, $usageC, [$components[0]->id])->assertSessionHas('success');
        $this->assertSame(2, BoxUsageDamage::count());
    }

    public function test_component_outside_the_box_is_rejected(): void
    {
        $admin = $this->admin();
        [$box] = $this->boxWithComponents();
        [, $otherComponents] = $this->boxWithComponents(1);
        $usage = $this->usage($box);

        $this->postDamage($admin, $usage, [$otherComponents[0]->id])
            ->assertSessionHasErrors('component_ids');

        $this->assertSame(0, BoxUsageDamage::count());
        $this->assertSame('Tersedia', $otherComponents[0]->fresh()->status);
    }

    public function test_laboran_can_report_damage(): void
    {
        $laboran = $this->laboran();
        [$box, $components] = $this->boxWithComponents();
        $usage = $this->usage($box);

        $this->postDamage($laboran, $usage, [$components[0]->id])
            ->assertSessionHas('success');

        $this->assertSame(1, BoxUsageDamage::count());
        $this->assertSame('Rusak', $components[0]->fresh()->status);
    }

    public function test_user_without_edit_components_permission_cannot_report(): void
    {
        $viewer = $this->viewer();
        [$box, $components] = $this->boxWithComponents();
        $usage = $this->usage($box);

        $this->postDamage($viewer, $usage, [$components[0]->id])->assertForbidden();

        $this->assertSame(0, BoxUsageDamage::count());
        $this->assertSame('Tersedia', $components[0]->fresh()->status);
    }

    public function test_guest_is_redirected_to_login(): void
    {
        [$box, $components] = $this->boxWithComponents();
        $usage = $this->usage($box);

        $this->post(route('box-usages.store-damage', $usage), ['component_ids' => [$components[0]->id]])
            ->assertRedirect(route('login'));

        $this->assertSame(0, BoxUsageDamage::count());
    }

    public function test_history_page_shows_damage_column_and_button_only_for_permitted_user(): void
    {
        $admin = $this->admin();
        $viewer = $this->viewer();
        [$box, $components] = $this->boxWithComponents();
        $usage = $this->usage($box);

        $this->postDamage($admin, $usage, [$components[0]->id], ['note' => 'Pin putus']);

        $this->actingAs($admin)
            ->get(route('components.index', ['tab' => 'history']))
            ->assertOk()
            ->assertSee('Kerusakan')
            ->assertSee('data-components=', false)
            ->assertSee('Lapor Kerusakan')
            ->assertSee('Pin putus');

        $this->actingAs($viewer)
            ->get(route('components.index', ['tab' => 'history']))
            ->assertOk()
            ->assertSee('Kerusakan')
            ->assertDontSee('data-components=', false);
    }
}
