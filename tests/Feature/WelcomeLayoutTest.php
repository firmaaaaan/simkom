<?php

namespace Tests\Feature;

use App\Models\LabLayout;
use App\Models\Laboratory;
use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WelcomeLayoutTest extends TestCase
{
    use RefreshDatabase;

    public function test_welcome_loads_without_layout(): void
    {
        Laboratory::create([
            'name' => 'Lab Test',
            'code' => 'LT1',
            'location' => 'Gedung A',
            'capacity' => 40,
        ]);

        $this->get('/')
            ->assertOk()
            ->assertSee('Denah Komputer')
            ->assertDontSee('Published');
    }

    public function test_welcome_renders_published_layout(): void
    {
        $lab = Laboratory::create([
            'name' => 'Lab Layout',
            'code' => 'LL1',
            'location' => 'Gedung A',
            'capacity' => 40,
        ]);

        $layout = LabLayout::create([
            'laboratory_id' => $lab->id,
            'name' => 'Layout Published',
            'grid_cols' => 12,
            'grid_rows' => 8,
            'cell_size' => 100,
            'background_color' => '#ffffff',
            'is_published' => true,
            'is_draft' => false,
            'layout_data' => [
                ['type' => 'computer', 'id' => 'c1', 'grid_x' => 2, 'grid_y' => 1, 'rotation' => 0, 'label' => 'PC-01'],
                ['type' => 'door', 'id' => 'd1', 'grid_x' => 0, 'grid_y' => 0, 'rotation' => 0, 'label' => 'Pintu'],
            ],
        ]);

        $response = $this->get('/?laboratory_id=' . $lab->id);

        $response->assertOk()
            ->assertSee('Layout Published')
            ->assertSee('Published')
            ->assertSee('PC-01')
            ->assertSee('Pintu');
    }

    public function test_welcome_does_not_render_draft_layout(): void
    {
        $lab = Laboratory::create([
            'name' => 'Lab Draft',
            'code' => 'LD1',
            'location' => 'Gedung A',
            'capacity' => 40,
        ]);

        LabLayout::create([
            'laboratory_id' => $lab->id,
            'name' => 'Layout Draft',
            'grid_cols' => 12,
            'grid_rows' => 8,
            'cell_size' => 100,
            'background_color' => '#ffffff',
            'is_draft' => true,
            'is_published' => false,
            'layout_data' => [
                ['type' => 'computer', 'id' => 'c1', 'grid_x' => 2, 'grid_y' => 1, 'rotation' => 0, 'label' => 'PC-Draft'],
            ],
        ]);

        $response = $this->get('/?laboratory_id=' . $lab->id);

        $response->assertOk()
            ->assertDontSee('Layout Draft')
            ->assertDontSee('PC-Draft');
    }

    public function test_welcome_uses_first_lab_when_no_param(): void
    {
        $lab1 = Laboratory::create([
            'name' => 'Lab First',
            'code' => 'LF1',
            'location' => 'Gedung A',
            'capacity' => 40,
        ]);

        $lab2 = Laboratory::create([
            'name' => 'Lab Second',
            'code' => 'LS1',
            'location' => 'Gedung B',
            'capacity' => 30,
        ]);

        LabLayout::create([
            'laboratory_id' => $lab1->id,
            'name' => 'First Lab Layout',
            'grid_cols' => 12,
            'grid_rows' => 8,
            'cell_size' => 100,
            'background_color' => '#ffffff',
            'is_published' => true,
            'is_draft' => false,
        ]);

        LabLayout::create([
            'laboratory_id' => $lab2->id,
            'name' => 'Second Lab Layout',
            'grid_cols' => 12,
            'grid_rows' => 8,
            'cell_size' => 100,
            'background_color' => '#ffffff',
            'is_published' => true,
            'is_draft' => false,
        ]);

        $response = $this->get('/');

        $response->assertOk()
            ->assertSee('First Lab Layout')
            ->assertDontSee('Second Lab Layout');
    }

    public function test_welcome_falls_back_to_computer_grid_when_no_layout(): void
    {
        $lab = Laboratory::create([
            'name' => 'Lab No Layout',
            'code' => 'LNL',
            'location' => 'Gedung A',
            'capacity' => 40,
        ]);

        // Create some computers
        \App\Models\Computer::create([
            'code' => 'PC-001',
            'name' => 'PC Test',
            'category' => 'PC',
            'laboratory_id' => $lab->id,
            'quantity' => 1,
            'status' => 'Aktif',
        ]);

        $response = $this->get('/?laboratory_id=' . $lab->id);

        $response->assertOk()
            ->assertSee('PC-001');
    }
}