<?php

namespace Tests\Feature;

use App\Models\Computer;
use App\Models\Laboratory;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ComputerBarcodeTest extends TestCase
{
    use RefreshDatabase;

    private function userWith(string $email, array $permissions): User
    {
        $role = Role::firstOrCreate(['name' => 'role-'.md5($email)], ['label' => 'Role Uji']);

        foreach ($permissions as $permission) {
            $role->permissions()->attach(
                Permission::firstOrCreate(['name' => $permission], ['label' => $permission])
            );
        }

        $user = User::create([
            'name' => 'User Uji',
            'email' => $email,
            'password' => 'rahasia123',
        ]);
        $user->roles()->attach($role);

        return $user;
    }

    private function laboratory(): Laboratory
    {
        return Laboratory::create([
            'name' => 'Lab Uji Barcode',
            'code' => 'LBC',
            'location' => 'Gedung A',
            'capacity' => 20,
        ]);
    }

    private function computer(Laboratory $laboratory, string $code): Computer
    {
        return Computer::create([
            'code' => $code,
            'laboratory_id' => $laboratory->id,
            'status' => 'Aktif',
        ]);
    }

    public function test_barcode_can_be_saved_manually_and_shown_on_edit_and_detail(): void
    {
        $user = $this->userWith('barcode-editor@uji.test', ['view-computers', 'edit-computers']);
        $lab = $this->laboratory();
        $computer = $this->computer($lab, 'K1-001');

        $this->actingAs($user)
            ->put(route('computers.update', $computer), [
                'code' => 'K1-001',
                'laboratory_id' => $lab->id,
                'status' => 'Aktif',
                'barcode' => 'INV-PC-00123',
            ])
            ->assertRedirect(route('computers.index'));

        $this->assertSame('INV-PC-00123', $computer->fresh()->barcode);

        // Form edit terisi dan halaman detail menampilkannya.
        $this->actingAs($user)
            ->get(route('computers.edit', $computer))
            ->assertOk()
            ->assertSee('value="INV-PC-00123"', false)
            ->assertSee('Barcode Inventaris');

        $this->actingAs($user)
            ->get(route('computers.show', $computer))
            ->assertOk()
            ->assertSee('Barcode Inventaris')
            ->assertSee('INV-PC-00123');
    }

    public function test_duplicate_barcode_is_rejected(): void
    {
        $user = $this->userWith('barcode-dupe@uji.test', ['view-computers', 'edit-computers']);
        $lab = $this->laboratory();
        $computerA = $this->computer($lab, 'K1-001');
        $computerB = $this->computer($lab, 'K1-002');

        $computerA->update(['barcode' => 'INV-PC-00123']);

        $this->actingAs($user)
            ->from(route('computers.edit', $computerB))
            ->put(route('computers.update', $computerB), [
                'code' => 'K1-002',
                'laboratory_id' => $lab->id,
                'status' => 'Aktif',
                'barcode' => 'INV-PC-00123',
            ])
            ->assertRedirect(route('computers.edit', $computerB))
            ->assertSessionHasErrors('barcode');

        $this->assertNull($computerB->fresh()->barcode);
        $this->assertSame('INV-PC-00123', $computerA->fresh()->barcode);
    }

    public function test_barcode_can_be_left_empty_or_cleared(): void
    {
        $user = $this->userWith('barcode-empty@uji.test', ['view-computers', 'edit-computers']);
        $lab = $this->laboratory();
        $computer = $this->computer($lab, 'K1-001');
        $computer->update(['barcode' => 'INV-PC-00123']);

        // Permintaan tanpa field barcode tidak menyentuh barcode lama.
        $this->actingAs($user)
            ->put(route('computers.update', $computer), [
                'code' => 'K1-001',
                'laboratory_id' => $lab->id,
                'status' => 'Aktif',
            ])
            ->assertRedirect(route('computers.index'));

        $this->assertSame('INV-PC-00123', $computer->fresh()->barcode);

        // Mengosongkan kolom juga diperbolehkan (barcode lama dilepas).
        $this->actingAs($user)
            ->put(route('computers.update', $computer), [
                'code' => 'K1-001',
                'laboratory_id' => $lab->id,
                'status' => 'Aktif',
                'barcode' => '',
            ])
            ->assertRedirect(route('computers.index'));

        $this->assertNull($computer->fresh()->barcode);

        // Detail tetap tampil tanpa barcode.
        $this->actingAs($user)
            ->get(route('computers.show', $computer))
            ->assertOk()
            ->assertSee('Barcode Inventaris');
    }

    public function test_barcode_requires_edit_permission(): void
    {
        $viewer = $this->userWith('barcode-viewer@uji.test', ['view-computers']);
        $lab = $this->laboratory();
        $computer = $this->computer($lab, 'K1-001');

        $this->actingAs($viewer)
            ->put(route('computers.update', $computer), [
                'code' => 'K1-001',
                'status' => 'Aktif',
                'barcode' => 'INV-PC-00123',
            ])
            ->assertForbidden();

        $this->assertNull($computer->fresh()->barcode);
    }
}
