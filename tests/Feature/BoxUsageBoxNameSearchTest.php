<?php

namespace Tests\Feature;

use App\Models\Box;
use App\Models\BoxUsage;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Pencarian pada tab Riwayat Penggunaan Box juga mencocokkan nama & kode box
 * (bukan hanya nama pengguna / NIM).
 */
class BoxUsageBoxNameSearchTest extends TestCase
{
    use RefreshDatabase;

    private function userWith(): User
    {
        $role = Role::firstOrCreate(['name' => 'viewer-history'], ['label' => 'Viewer History']);
        $role->permissions()->attach(
            Permission::firstOrCreate(['name' => 'view-components'], ['label' => 'view-components'])
        );

        $user = User::create([
            'name' => 'Viewer',
            'email' => 'viewer-history@uji.test',
            'password' => 'rahasia123',
        ]);
        $user->roles()->attach($role);

        return $user;
    }

    private function usage(Box $box, string $name, string $nim): BoxUsage
    {
        return BoxUsage::create([
            'box_id' => $box->id,
            'user_name' => $name,
            'user_nim' => $nim,
            'user_kelas' => 'TI-4A',
            'status' => 'Returned',
            'used_at' => now()->subDays(2),
            'returned_at' => now()->subDay(),
            'source' => 'manual',
        ]);
    }

    public function test_search_matches_box_name(): void
    {
        $user = $this->userWith();
        $this->usage(Box::create(['name' => 'Box RAM', 'code' => 'BOX-RAM-001']), 'Alice Match', '2210511');
        $this->usage(Box::create(['name' => 'Box SSD', 'code' => 'BOX-SSD-001']), 'Bob Other', '2210512');

        $this->actingAs($user)
            ->get(route('components.index', ['tab' => 'history', 'usage_search' => 'RAM']))
            ->assertOk()
            ->assertSee('Alice Match')
            ->assertDontSee('Bob Other');
    }

    public function test_search_matches_box_code(): void
    {
        $user = $this->userWith();
        $this->usage(Box::create(['name' => 'Box RAM', 'code' => 'BOX-RAM-001']), 'Alice Match', '2210511');
        $this->usage(Box::create(['name' => 'Box SSD', 'code' => 'BOX-SSD-001']), 'Bob Other', '2210512');

        $this->actingAs($user)
            ->get(route('components.index', ['tab' => 'history', 'usage_search' => 'SSD-001']))
            ->assertOk()
            ->assertSee('Bob Other')
            ->assertDontSee('Alice Match');
    }

    public function test_search_still_matches_user_name_and_nim(): void
    {
        $user = $this->userWith();
        $this->usage(Box::create(['name' => 'Box RAM', 'code' => 'BOX-RAM-001']), 'Alice Match', '2210511');
        $this->usage(Box::create(['name' => 'Box SSD', 'code' => 'BOX-SSD-001']), 'Bob Other', '2210512');

        $this->actingAs($user)
            ->get(route('components.index', ['tab' => 'history', 'usage_search' => '2210512']))
            ->assertOk()
            ->assertSee('Bob Other')
            ->assertDontSee('Alice Match');
    }

    public function test_search_field_label_and_placeholder_mention_box(): void
    {
        $user = $this->userWith();

        $this->actingAs($user)
            ->get(route('components.index', ['tab' => 'history']))
            ->assertOk()
            ->assertSee('Cari Pengguna / Box')
            ->assertSee('Nama pengguna, NIM, atau nama box...');
    }
}
