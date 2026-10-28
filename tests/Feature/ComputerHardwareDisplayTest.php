<?php

namespace Tests\Feature;

use App\Models\Computer;
use App\Models\Hardware;
use App\Models\Laboratory;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ComputerHardwareDisplayTest extends TestCase
{
    use RefreshDatabase;

    private function userWith(string $email, string $permission): User
    {
        $role = Role::firstOrCreate(['name' => 'role-'.md5($email)], ['label' => 'Role Uji']);
        $role->permissions()->syncWithoutDetaching(
            Permission::firstOrCreate(['name' => $permission], ['label' => $permission])->id
        );

        $user = User::create([
            'name' => 'User Uji',
            'email' => $email,
            'password' => 'rahasia123',
        ]);
        $user->roles()->attach($role);

        return $user;
    }

    private function computer(): Computer
    {
        $lab = Laboratory::firstOrCreate(['code' => 'LAB-HW-DISP'], [
            'name' => 'Lab Uji Hardware',
            'location' => 'Gedung A',
            'capacity' => 20,
        ]);

        return Computer::create([
            'code' => 'K-HW-001',
            'laboratory_id' => $lab->id,
            'status' => 'Aktif',
        ]);
    }

    private function hardware(string $name, string $category, ?string $brand = null, ?string $model = null): Hardware
    {
        return Hardware::create([
            'code' => Hardware::generateCode(),
            'name' => $name,
            'category' => $category,
            'brand' => $brand,
            'model' => $model,
        ]);
    }

    public function test_show_displays_all_hardware_details_grouped_by_category(): void
    {
        $user = $this->userWith('hw-show@uji.test', 'view-computers');
        $computer = $this->computer();

        // Urutan pembuatan sengaja tidak sesuai urutan kategori.
        $ram = $this->hardware('8GB DDR4', 'RAM');
        $storage = $this->hardware('512 GB SSD NVMe', 'Storage', 'Kingston', 'A2000');
        $cpu = $this->hardware('Intel Core i5-12400', 'Processor', 'Intel', 'i5-12400');

        $computer->hardware()->sync([$ram->id, $storage->id, $cpu->id]);

        $this->actingAs($user)
            ->get(route('computers.show', $computer))
            ->assertOk()
            ->assertSee('Intel Core i5-12400')
            ->assertSee('i5-12400')
            ->assertSee('512 GB SSD NVMe')
            ->assertSee('Kingston')
            ->assertSee('A2000')
            ->assertSee('8GB DDR4')
            ->assertSeeInOrder(['Processor (CPU)', 'Memory (RAM)', 'Storage (HDD/SSD)']);
    }

    public function test_bulk_assign_displays_all_hardware_details_grouped_by_category(): void
    {
        $user = $this->userWith('hw-bulk@uji.test', 'edit-computers');

        // Urutan pembuatan sengaja tidak sesuai urutan kategori.
        $storage = $this->hardware('1 TB HDD', 'Storage', 'Seagate', 'BarraCuda');
        $ram = $this->hardware('8GB DDR4', 'RAM');
        $cpu = $this->hardware('Intel Core i5-12400', 'Processor', 'Intel', 'i5-12400');

        $this->actingAs($user)
            ->get(route('computers.bulk-assign'))
            ->assertOk()
            ->assertSee('1 TB HDD')
            ->assertSee('Seagate')
            ->assertSee('BarraCuda')
            ->assertSee('8GB DDR4')
            ->assertSee('i5-12400')
            ->assertSeeInOrder(['Processor (CPU)', 'Memory (RAM)', 'Storage (HDD/SSD)'])
            ->assertSee('name="hardware_ids[]"', false);

        $this->assertDatabaseHas('hardware', ['id' => $storage->id]);
        $this->assertDatabaseHas('hardware', ['id' => $cpu->id]);
    }
}
