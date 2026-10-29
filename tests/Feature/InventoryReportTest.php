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

class InventoryReportTest extends TestCase
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

    private function laboratory(string $name, string $code): Laboratory
    {
        return Laboratory::create([
            'name' => $name,
            'code' => $code,
            'location' => 'Gedung A',
            'capacity' => 40,
        ]);
    }

    /**
     * Buat komputer beserta komponen hardware: [kategori => nama].
     */
    private function computer(Laboratory $laboratory, string $code, array $specs): Computer
    {
        $computer = Computer::create([
            'code' => $code,
            'laboratory_id' => $laboratory->id,
            'status' => 'Aktif',
        ]);

        $ids = [];

        foreach ($specs as $category => $name) {
            $hardware = Hardware::create([
                'code' => Hardware::generateCode(),
                'name' => $name,
                'category' => $category,
            ]);
            $ids[] = $hardware->id;
        }

        $computer->hardware()->sync($ids);

        return $computer;
    }

    public function test_report_counts_ram_storage_and_processor_per_laboratory(): void
    {
        $admin = $this->userWith('inventory@uji.test', ['view-reports']);
        $lab = $this->laboratory('Lab Komputer 1', 'LK1');

        $this->computer($lab, 'K1-001', ['RAM' => '8GB DDR4', 'Storage' => 'SSD 512GB', 'Processor' => 'Intel Core i5-12400']);
        $this->computer($lab, 'K1-002', ['RAM' => '2x8GB DDR4', 'Storage' => 'HDD 1TB', 'Processor' => 'Intel Core i5-12400']);
        $this->computer($lab, 'K1-003', ['RAM' => '4GB DDR3', 'Storage' => 'SSD NVMe 256GB', 'Processor' => 'AMD Ryzen 5 5600G']);
        $this->computer($lab, 'K1-004', ['RAM' => '16GB DDR5', 'Storage' => '1.5 TB NVMe', 'Processor' => 'Intel Core i5-12400']);

        $this->actingAs($admin)
            ->get(route('reports.inventory', ['laboratory_id' => $lab->id]))
            ->assertOk()
            ->assertSee('Laporan Inventaris Laboratorium')
            ->assertSee('8 GB')
            ->assertSee('16 GB')
            ->assertSee('4 GB')
            ->assertSee('SSD 512 GB')
            ->assertSee('SSD 256 GB')
            ->assertSee('SSD 1.5 TB')
            ->assertSee('HDD 1 TB')
            ->assertSee('Intel Core i5-12400')
            ->assertSee('AMD Ryzen 5 5600G')
            ->assertSee('K1-001')
            ->assertSee('K1-003');
    }

    public function test_report_filters_by_laboratory(): void
    {
        $admin = $this->userWith('inventory-filter@uji.test', ['view-reports']);
        $lab1 = $this->laboratory('Lab Komputer 1', 'LK1');
        $lab2 = $this->laboratory('Lab Komputer 2', 'LK2');

        $this->computer($lab1, 'K1-001', ['RAM' => '8GB DDR4', 'Storage' => 'SSD 512GB', 'Processor' => 'Intel Core i5-12400']);
        $this->computer($lab2, 'K2-001', ['RAM' => '16GB DDR5', 'Storage' => 'HDD 512GB', 'Processor' => 'Intel Core i7-12700']);

        $this->actingAs($admin)
            ->get(route('reports.inventory', ['laboratory_id' => $lab1->id]))
            ->assertOk()
            ->assertSee('K1-001')
            ->assertDontSee('K2-001')
            ->assertDontSee('Intel Core i7-12700');

        $this->actingAs($admin)
            ->get(route('reports.inventory', ['laboratory_id' => 'all']))
            ->assertOk()
            ->assertSee('K1-001')
            ->assertSee('K2-001');
    }

    public function test_report_labels_combined_storage_names_by_their_own_types(): void
    {
        $admin = $this->userWith('inventory-combined@uji.test', ['view-reports']);
        $lab = $this->laboratory('Lab Komputer 1', 'LK1');

        $this->computer($lab, 'K1-001', ['RAM' => '8GB DDR4', 'Storage' => '1 TB HDD, 512 GB SSD', 'Processor' => 'Intel Core i5-12400']);
        $this->computer($lab, 'K1-002', ['RAM' => '8GB DDR4', 'Storage' => 'SSD 256GB; HDD 1TB', 'Processor' => 'Intel Core i5-12400']);
        $this->computer($lab, 'K1-003', ['RAM' => '8GB DDR4', 'Storage' => '1TB HDD 128GB SSD', 'Processor' => 'Intel Core i5-12400']);

        $this->actingAs($admin)
            ->get(route('reports.inventory', ['laboratory_id' => $lab->id]))
            ->assertOk()
            ->assertSee('SSD 512 GB')
            ->assertSee('SSD 256 GB')
            ->assertSee('SSD 128 GB')
            ->assertSee('HDD 1 TB')
            ->assertSeeInOrder(['SSD 512 GB', 'HDD 1 TB'])
            ->assertDontSee('SSD 1 TB');
    }

    public function test_report_marks_missing_specs_as_not_recorded(): void
    {
        $admin = $this->userWith('inventory-empty@uji.test', ['view-reports']);
        $lab = $this->laboratory('Lab Komputer 1', 'LK1');

        $this->computer($lab, 'K1-001', []);

        $this->actingAs($admin)
            ->get(route('reports.inventory', ['laboratory_id' => $lab->id]))
            ->assertOk()
            ->assertSee('Tidak tercatat');
    }

    public function test_report_includes_monitor_category_in_recap_and_details(): void
    {
        $user = $this->userWith('inventory-monitor-hw@uji.test', ['view-reports', 'print-reports']);
        $lab = $this->laboratory('Lab Komputer 1', 'LK1');

        $specs = ['RAM' => '8GB DDR4', 'Storage' => 'SSD 512GB', 'Processor' => 'Intel Core i5-12400'];

        $this->computer($lab, 'K1-001', $specs + ['Monitor' => 'LG 24MP400']);
        $this->computer($lab, 'K1-002', $specs + ['Monitor' => 'LG 24MP400']);
        $this->computer($lab, 'K1-003', $specs + ['Monitor' => 'Samsung Odyssey G5']);
        $this->computer($lab, 'K1-004', $specs);

        $this->actingAs($user)
            ->get(route('reports.inventory', ['laboratory_id' => $lab->id]))
            ->assertOk()
            ->assertSee('Monitor')
            ->assertSee('LG 24MP400')
            ->assertSee('Samsung Odyssey G5')
            // Rekap monitor urut jumlah terbanyak: LG (2) sebelum Samsung (1).
            ->assertSeeInOrder(['LG 24MP400', 'Samsung Odyssey G5']);

        $this->actingAs($user)
            ->get(route('reports.inventory-print', ['laboratory_id' => $lab->id]))
            ->assertOk()
            ->assertSee('Monitor')
            ->assertSee('LG 24MP400')
            ->assertSee('Tidak tercatat');
    }

    public function test_report_requires_report_permission_and_print_requires_print_permission(): void
    {
        $viewer = $this->userWith('inventory-viewer@uji.test', ['view-computers']);
        $reporter = $this->userWith('inventory-reporter@uji.test', ['view-reports']);
        $printer = $this->userWith('inventory-printer@uji.test', ['print-reports']);

        $this->actingAs($viewer)->get(route('reports.inventory'))->assertForbidden();
        $this->actingAs($reporter)->get(route('reports.inventory'))->assertOk();
        $this->actingAs($reporter)->get(route('reports.inventory-print'))->assertForbidden();
        $this->actingAs($printer)
            ->get(route('reports.inventory-print'))
            ->assertOk()
            ->assertSee('<title>Inventaris-Semua Laboratorium</title>', false);
    }

    public function test_print_hides_specification_details_when_details_is_zero(): void
    {
        $user = $this->userWith('inventory-no-details@uji.test', ['print-reports']);
        $lab = $this->laboratory('Lab Komputer 1', 'LK1');
        $this->computer($lab, 'K1-001', ['RAM' => '8GB DDR4', 'Storage' => 'SSD 512GB', 'Processor' => 'Intel Core i5-12400']);

        $this->actingAs($user)
            ->get(route('reports.inventory-print', ['laboratory_id' => $lab->id, 'details' => '0']))
            ->assertOk()
            ->assertDontSee('Rincian Spesifikasi per Komputer')
            ->assertDontSee('K1-001');

        $this->actingAs($user)
            ->get(route('reports.inventory-print', ['laboratory_id' => $lab->id, 'details' => '1']))
            ->assertOk()
            ->assertSee('Rincian Spesifikasi per Komputer')
            ->assertSee('K1-001');
    }

    public function test_print_shows_specification_details_by_default(): void
    {
        $user = $this->userWith('inventory-default-details@uji.test', ['print-reports']);
        $lab = $this->laboratory('Lab Komputer 1', 'LK1');
        $this->computer($lab, 'K1-001', ['RAM' => '8GB DDR4', 'Storage' => 'SSD 512GB', 'Processor' => 'Intel Core i5-12400']);

        $this->actingAs($user)
            ->get(route('reports.inventory-print', ['laboratory_id' => $lab->id]))
            ->assertOk()
            ->assertSee('Rincian Spesifikasi per Komputer')
            ->assertSee('K1-001')
            ->assertSee('kop/cropped-logo-unisa-crop.png', false);
    }

    public function test_list_page_shows_details_checkbox(): void
    {
        $user = $this->userWith('inventory-checkbox@uji.test', ['view-reports', 'print-reports']);
        $lab = $this->laboratory('Lab Komputer 1', 'LK1');

        $this->actingAs($user)
            ->get(route('reports.inventory', ['laboratory_id' => $lab->id]))
            ->assertOk()
            ->assertSee('Sertakan rincian spesifikasi')
            ->assertSee('name="details"', false);
    }
}
