<?php

namespace Tests\Feature;

use App\Models\Box;
use App\Models\BoxComponent;
use App\Models\Component;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ComponentBoxColumnTest extends TestCase
{
    use RefreshDatabase;

    protected function manager(): User
    {
        $role = Role::create(['name' => 'role-manage-components-2', 'label' => 'Role Manage Components']);
        $role->permissions()->attach(
            Permission::firstOrCreate(['name' => 'manage-components'], ['label' => 'Kelola Komponen'])
        );

        $user = User::create([
            'name' => 'Admin Komponen',
            'email' => 'komponen-admin@lab.test',
            'password' => 'rahasia123',
        ]);
        $user->roles()->attach($role);

        return $user;
    }

    protected function makeComponent(string $name, string $code): Component
    {
        return Component::create([
            'name' => $name,
            'code' => $code,
            'category' => 'IoT',
            'quantity' => 10,
        ]);
    }

    protected function box(string $code, string $name): Box
    {
        return Box::create([
            'code' => $code,
            'name' => $name,
            'location' => 'Rak A',
        ]);
    }

    protected function putInBox(Component $component, Box $box, int $quantity = 1): void
    {
        BoxComponent::create([
            'box_id' => $box->id,
            'component_id' => $component->id,
            'quantity' => $quantity,
        ]);
    }

    public function test_index_shows_dash_for_component_without_box(): void
    {
        $this->makeComponent('Komponen Bebas', 'KMP-901');

        $response = $this->actingAs($this->manager())->get(route('components.index'));

        $response->assertOk();
        $this->assertMatchesRegularExpression('/data-label="Box">\s*-\s*<\/td>/', $response->getContent());
    }

    public function test_index_shows_box_name_for_single_box(): void
    {
        $component = $this->makeComponent('Komponen Satu Box', 'KMP-902');
        $this->putInBox($component, $this->box('BOX-902', 'Box Jaringan Unik'));

        $response = $this->actingAs($this->manager())->get(route('components.index'));

        $response->assertOk()->assertSee('Box Jaringan Unik');
        $response->assertDontSee('+1 lainnya');
    }

    public function test_index_shows_more_link_for_multiple_boxes(): void
    {
        $component = $this->makeComponent('Komponen Banyak Box', 'KMP-903');
        $this->putInBox($component, $this->box('BOX-903A', 'Box A Unik'));
        $this->putInBox($component, $this->box('BOX-903B', 'Box B Unik'));
        $this->putInBox($component, $this->box('BOX-903C', 'Box C Unik'));

        $response = $this->actingAs($this->manager())->get(route('components.index'));

        $response->assertOk()
            ->assertSee('Box A Unik')
            ->assertSee('+2 lainnya');
        $response->assertSee(route('components.show', $component) . '#box', false);
    }

    public function test_component_detail_page_lists_all_boxes(): void
    {
        $component = $this->makeComponent('Komponen Detail', 'KMP-904');
        $this->putInBox($component, $this->box('BOX-904A', 'Box Detail A'), 2);
        $this->putInBox($component, $this->box('BOX-904B', 'Box Detail B'), 3);

        $response = $this->actingAs($this->manager())->get(route('components.show', $component));

        $response->assertOk()
            ->assertSee('id="box"', false)
            ->assertSee('Box Penyimpanan')
            ->assertSee('2 box')
            ->assertSee('Box Detail A')
            ->assertSee('Box Detail B')
            ->assertSee('2 item')
            ->assertSee('3 item');
    }

    public function test_component_detail_page_shows_dash_without_boxes(): void
    {
        $component = $this->makeComponent('Komponen Tanpa Box', 'KMP-905');

        $response = $this->actingAs($this->manager())->get(route('components.show', $component));

        $response->assertOk()
            ->assertSee('Box Penyimpanan')
            ->assertSee('0 box');
    }
}
