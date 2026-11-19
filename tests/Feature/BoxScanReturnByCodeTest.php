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

class BoxScanReturnByCodeTest extends TestCase
{
    use RefreshDatabase;

    private function box(string $code = 'BOX-RAM-001'): Box
    {
        return Box::create(['name' => 'Box RAM', 'code' => $code, 'location' => 'Rak A']);
    }

    private function admin(): User
    {
        $role = Role::firstOrCreate(['name' => 'admin'], ['label' => 'Admin']);
        $role->permissions()->attach(
            Permission::firstOrCreate(['name' => 'view-components'], ['label' => 'view-components'])
        );

        $user = User::create([
            'name' => 'Admin',
            'email' => 'admin-scan-return@lab.test',
            'password' => 'rahasia123',
        ]);
        $user->roles()->attach($role);

        return $user;
    }

    private function using(Box $box, array $overrides = []): BoxUsage
    {
        return BoxUsage::create(array_merge([
            'box_id'     => $box->id,
            'user_name'  => 'Budi Santoso',
            'user_nim'   => '2210512001',
            'user_kelas' => 'TI-4A',
            'status'     => 'Using',
            'used_at'    => now()->subHour(),
        ], $overrides));
    }

    public function test_admin_can_return_active_usage_by_box_code(): void
    {
        $box = $this->box();
        $admin = $this->admin();
        $usage = $this->using($box);

        $this->actingAs($admin)
            ->postJson(route('box-scan.return-by-code'), ['box_code' => $box->code])
            ->assertOk()
            ->assertJson(['success' => true, 'count' => 1]);

        $fresh = $usage->fresh();
        $this->assertSame('Returned', $fresh->status);
        $this->assertNotNull($fresh->returned_at);
    }

    public function test_returns_all_active_usages_of_scanned_box_only(): void
    {
        $box = $this->box();
        $other = $this->box('BOX-RAM-002');
        $admin = $this->admin();

        $first = $this->using($box, ['user_name' => 'Sari']);
        $second = $this->using($box, ['user_name' => 'Andi']);
        $untouched = $this->using($other);

        $this->actingAs($admin)
            ->postJson(route('box-scan.return-by-code'), ['box_code' => $box->code])
            ->assertOk()
            ->assertJson(['success' => true, 'count' => 2]);

        $this->assertSame('Returned', $first->fresh()->status);
        $this->assertSame('Returned', $second->fresh()->status);
        $this->assertSame('Using', $untouched->fresh()->status);
        $this->assertNull($untouched->fresh()->returned_at);
    }

    public function test_accepts_full_qr_url_as_box_code(): void
    {
        $box = $this->box();
        $admin = $this->admin();
        $usage = $this->using($box);

        $this->actingAs($admin)
            ->postJson(route('box-scan.return-by-code'), [
                'box_code' => url('/box-scan/' . $box->code),
            ])
            ->assertOk()
            ->assertJson(['success' => true, 'count' => 1]);

        $this->assertSame('Returned', $usage->fresh()->status);
    }

    public function test_box_without_active_usage_returns_nothing_to_return(): void
    {
        $box = $this->box();
        $admin = $this->admin();
        $usage = $this->using($box, [
            'status'      => 'Returned',
            'returned_at' => now()->subMinutes(10),
        ]);

        $this->actingAs($admin)
            ->postJson(route('box-scan.return-by-code'), ['box_code' => $box->code])
            ->assertStatus(422)
            ->assertJson(['success' => false, 'message' => 'Tidak ada yang dikembalikan']);

        $this->assertSame('Returned', $usage->fresh()->status);
        $this->assertSame(1, BoxUsage::count());
    }

    public function test_optional_note_is_saved_for_each_returned_usage(): void
    {
        $box = $this->box();
        $admin = $this->admin();
        $first = $this->using($box, ['user_name' => 'Sari']);
        $second = $this->using($box, ['user_name' => 'Andi']);

        $this->actingAs($admin)
            ->postJson(route('box-scan.return-by-code'), [
                'box_code' => $box->code,
                'note'     => 'Kondisi baik, lengkap',
            ])
            ->assertOk()
            ->assertJson(['success' => true, 'count' => 2]);

        $this->assertSame('Kondisi baik, lengkap', $first->fresh()->returnNote?->note);
        $this->assertSame('Kondisi baik, lengkap', $second->fresh()->returnNote?->note);
        $this->assertSame(2, BoxReturnNote::count());
    }

    public function test_unknown_box_code_returns_404(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)
            ->postJson(route('box-scan.return-by-code'), ['box_code' => 'BOX-TIDAK-ADA'])
            ->assertStatus(404)
            ->assertJson(['success' => false, 'message' => 'Box tidak ditemukan']);

        $this->assertSame(0, BoxUsage::count());
    }

    public function test_box_code_is_required(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)
            ->postJson(route('box-scan.return-by-code'), [])
            ->assertStatus(422)
            ->assertJsonValidationErrors('box_code');
    }

    public function test_non_admin_cannot_return_by_scan(): void
    {
        $box = $this->box();
        $usage = $this->using($box);

        $laboran = User::create([
            'name' => 'Laboran',
            'email' => 'laboran-scan-return@lab.test',
            'password' => 'rahasia123',
        ]);

        $this->actingAs($laboran)
            ->postJson(route('box-scan.return-by-code'), ['box_code' => $box->code])
            ->assertForbidden();

        $this->assertSame('Using', $usage->fresh()->status);
    }

    public function test_history_page_shows_scan_return_button_for_admin(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)
            ->get(route('components.index', ['tab' => 'history']))
            ->assertOk()
            ->assertSee('Scan untuk Kembalikan')
            ->assertSee('scanReturnModal', false);
    }
}
