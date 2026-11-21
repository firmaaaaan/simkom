<?php

namespace Tests\Feature;

use App\Models\Alat;
use App\Models\Laboratory;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AlatPindahTest extends TestCase
{
    use RefreshDatabase;

    private function userWith(array $permissions): User
    {
        $role = Role::firstOrCreate(['name' => 'role-pindah-'.md5(implode(',', $permissions))], ['label' => 'Role Pindah']);
        foreach ($permissions as $p) {
            $role->permissions()->syncWithoutDetaching([
                Permission::firstOrCreate(['name' => $p], ['label' => $p])->id,
            ]);
        }

        $user = User::firstOrCreate(
            ['email' => 'pindah-'.md5(implode(',', $permissions)).'@uji.test'],
            ['name' => 'Uji Pindah', 'password' => 'rahasia123']
        );
        $user->roles()->syncWithoutDetaching($role->id);

        return $user;
    }

    private function seedAlat(): array
    {
        $labA = Laboratory::create(['name' => 'Lab A', 'code' => 'LA', 'location' => 'Gedung A', 'capacity' => 20]);
        $labB = Laboratory::create(['name' => 'Lab B', 'code' => 'LB', 'location' => 'Gedung B', 'capacity' => 20]);

        $alat = Alat::create([
            'laboratory_id' => $labA->id,
            'name' => 'Mikroskop',
            'code' => 'INV-PINDAH-01',
            'merk_type' => 'Olympus',
            'location' => 'Meja 1',
            'status' => 'Baik',
        ]);

        return [$labA, $labB, $alat];
    }

    public function test_pindah_updates_laboratory_and_keeps_code_and_id(): void
    {
        [$labA, $labB, $alat] = $this->seedAlat();
        $originalId = $alat->id;
        $originalCode = $alat->code;
        $originalLocation = $alat->location;
        $originalScanUrl = route('alat.scan', $alat);

        $this->actingAs($this->userWith(['edit-alat']))
            ->post(route('alat.pindah', $alat), [
                'laboratory_id' => $labB->id,
            ])
            ->assertRedirect(route('alat.show', $alat))
            ->assertSessionHas('success');

        $alat->refresh();

        $this->assertSame($labB->id, $alat->laboratory_id);
        // Lokasi (default dari SIMPTT Inventaris) tidak berubah.
        $this->assertSame($originalLocation, $alat->location);
        $this->assertSame($originalId, $alat->id);
        $this->assertSame($originalCode, $alat->code);
        $this->assertSame($originalScanUrl, route('alat.scan', $alat));
    }

    public function test_pindah_requires_edit_permission(): void
    {
        [$labA, $labB, $alat] = $this->seedAlat();

        $this->actingAs($this->userWith(['view-alat']))
            ->post(route('alat.pindah', $alat), [
                'laboratory_id' => $labB->id,
            ])
            ->assertForbidden();

        $this->assertSame($labA->id, $alat->fresh()->laboratory_id);
    }

    public function test_pindah_requires_laboratory(): void
    {
        [$labA, $labB, $alat] = $this->seedAlat();

        $this->actingAs($this->userWith(['edit-alat']))
            ->post(route('alat.pindah', $alat), [])
            ->assertRedirect()
            ->assertSessionHasErrors('laboratory_id');

        $this->assertSame($labA->id, $alat->fresh()->laboratory_id);
    }

    public function test_pindah_rejects_unknown_laboratory(): void
    {
        [$labA, $labB, $alat] = $this->seedAlat();

        $this->actingAs($this->userWith(['edit-alat']))
            ->post(route('alat.pindah', $alat), [
                'laboratory_id' => 999999,
            ])
            ->assertRedirect()
            ->assertSessionHasErrors('laboratory_id');

        $this->assertSame($labA->id, $alat->fresh()->laboratory_id);
    }

    public function test_pindah_accepts_edit_permission_alias(): void
    {
        [$labA, $labB, $alat] = $this->seedAlat();

        $this->actingAs($this->userWith(['edit-kartu-alat']))
            ->post(route('alat.pindah', $alat), [
                'laboratory_id' => $labB->id,
            ])
            ->assertRedirect(route('alat.show', $alat));
    }
}
