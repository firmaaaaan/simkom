<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\Laboratory;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class CreateSaveAndAnotherTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Semua form create admin: [route store, route create, route index, permission].
     *
     * @return array<string, array{0: string, 1: string, 2: string, 3: list<string>}>
     */
    public static function createForms(): array
    {
        return [
            'academic-years' => ['academic-years.store', 'academic-years.create', 'academic-years.index', ['manage-academic-years']],
            'components' => ['components.store', 'components.create', 'components.index', ['manage-components']],
            'computers' => ['computers.store', 'computers.create', 'computers.index', ['manage-computers']],
            'hardware' => ['hardware.store', 'hardware.create', 'hardware.index', ['manage-hardware']],
            'laboratories' => ['laboratories.store', 'laboratories.create', 'laboratories.index', ['manage-laboratories']],
            'roles' => ['roles.store', 'roles.create', 'roles.index', ['manage-roles']],
            'software' => ['software.store', 'software.create', 'software.index', ['manage-software']],
            'tickets' => ['tickets.store', 'tickets.create', 'tickets.index', ['manage-tickets']],
            'users' => ['users.store', 'users.create', 'users.index', ['manage-users']],
        ];
    }

    private function userWith(array $permissions): User
    {
        $role = Role::firstOrCreate(['name' => 'admin-uji'], ['label' => 'Admin Uji']);

        foreach ($permissions as $name) {
            $permission = Permission::firstOrCreate(['name' => $name], ['label' => $name]);
            $role->permissions()->syncWithoutDetaching([$permission->id]);
        }

        $user = User::firstOrCreate(
            ['email' => 'admin-uji@create.test'],
            ['name' => 'Admin Uji', 'password' => 'rahasia123']
        );
        $user->roles()->syncWithoutDetaching([$role->id]);

        return $user;
    }

    /**
     * Payload valid untuk tiap form, termasuk prasyarat data (lab, tahun ajaran, role).
     *
     * @return array<string, mixed>
     */
    private function payload(string $storeRoute): array
    {
        return match ($storeRoute) {
            'academic-years.store' => [
                'name' => '2030/2031 Save Another',
                'start_year' => 2030,
                'end_year' => 2031,
                'status' => 'Non Aktif',
            ],
            'components.store' => [
                'name' => 'Sensor Save Another',
                'category' => 'IoT',
                'quantity' => 2,
                'status' => 'Tersedia',
            ],
            'computers.store' => [
                'code' => 'PC-SA-001',
                'status' => 'Aktif',
            ],
            'hardware.store' => [
                'name' => 'Processor Save Another',
                'category' => 'Processor',
            ],
            'laboratories.store' => [
                'name' => 'Lab Save Another',
                'code' => 'LAB-SA',
                'location' => 'Gedung SA',
                'capacity' => 20,
                'status' => 'Aktif',
            ],
            'roles.store' => [
                'name' => 'role-save-another',
                'label' => 'Role Save Another',
            ],
            'software.store' => [
                'name' => 'Software Save Another',
                'category' => 'Sistem Operasi',
                'status' => 'Aktif',
            ],
            'tickets.store' => [
                'laboratory_id' => Laboratory::create([
                    'name' => 'Lab Tiket SA', 'code' => 'LAB-TSA', 'location' => 'Gedung TSA', 'capacity' => 10,
                ])->id,
                'academic_year_id' => AcademicYear::create([
                    'name' => '2031/2032 Tiket SA', 'start_year' => 2031, 'end_year' => 2032, 'status' => 'Aktif',
                ])->id,
                'category' => 'Komputer',
                'title' => 'Tiket Save Another',
                'description' => 'Deskripsi tiket save another',
                'priority' => 'Rendah',
            ],
            'users.store' => [
                'name' => 'User Save Another',
                'email' => 'save-another@user.test',
                'password' => 'password123',
                'password_confirmation' => 'password123',
                'role_id' => Role::firstOrCreate(['name' => 'admin'], ['label' => 'Admin'])->id,
            ],
            default => [],
        };
    }

    private function tableFor(string $storeRoute): string
    {
        return match ($storeRoute) {
            'academic-years.store' => 'academic_years',
            'components.store' => 'components',
            'computers.store' => 'computers',
            'hardware.store' => 'hardware',
            'laboratories.store' => 'laboratories',
            'roles.store' => 'roles',
            'software.store' => 'software',
            'tickets.store' => 'tickets',
            'users.store' => 'users',
        };
    }

    /**
     * Tombol "Simpan & Buat Ulang" mengembalikan user ke form create yang kosong.
     */
    #[DataProvider('createForms')]
    public function test_save_another_redirects_back_to_create_form(
        string $storeRoute,
        string $createRoute,
        string $indexRoute,
        array $permissions
    ): void {
        $user = $this->userWith($permissions);
        $table = $this->tableFor($storeRoute);
        $before = (int) \DB::table($table)->count();

        $this->actingAs($user)
            ->post(route($storeRoute), $this->payload($storeRoute) + ['action' => 'save_another'])
            ->assertRedirect(route($createRoute))
            ->assertSessionHas('success');

        $this->assertSame($before + 1, (int) \DB::table($table)->count());
    }

    /**
     * Tombol "Simpan" biasa tetap mengarah ke halaman index (perilaku lama).
     */
    #[DataProvider('createForms')]
    public function test_default_submit_still_redirects_to_index(
        string $storeRoute,
        string $createRoute,
        string $indexRoute,
        array $permissions
    ): void {
        $user = $this->userWith($permissions);
        $table = $this->tableFor($storeRoute);
        $before = (int) \DB::table($table)->count();

        $this->actingAs($user)
            ->post(route($storeRoute), $this->payload($storeRoute))
            ->assertRedirect(route($indexRoute))
            ->assertSessionHas('success');

        $this->assertSame($before + 1, (int) \DB::table($table)->count());
    }

    public function test_form_is_cleared_and_success_message_shown_after_save_another(): void
    {
        $user = $this->userWith(['manage-hardware']);

        $this->actingAs($user)
            ->post(route('hardware.store'), [
                'name' => 'Keyboard Khusus Uji Save Another',
                'category' => 'Keyboard',
                'action' => 'save_another',
            ])
            ->assertRedirect(route('hardware.create'));

        $this->actingAs($user)
            ->get(route('hardware.create'))
            ->assertOk()
            ->assertSee('Silakan isi form untuk data berikutnya.')
            // Form kosong: nilai yang baru disimpan tidak muncul lagi (old input tidak di-flashing).
            ->assertDontSee('Keyboard Khusus Uji Save Another');
    }

    public function test_validation_error_still_returns_with_old_input(): void
    {
        $user = $this->userWith(['manage-hardware']);

        $this->actingAs($user)
            ->from(route('hardware.create'))
            ->post(route('hardware.store'), [
                'name' => '',
                'category' => 'Processor',
                'action' => 'save_another',
            ])
            ->assertRedirect(route('hardware.create'))
            ->assertSessionHasErrors('name')
            ->assertSessionHasInput('category', 'Processor');

        $this->assertSame(0, (int) \DB::table('hardware')->count());
    }
}
