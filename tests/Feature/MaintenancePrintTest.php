<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\Computer;
use App\Models\Laboratory;
use App\Models\MaintenanceChecklist;
use App\Models\MaintenanceChecklistItem;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MaintenancePrintTest extends TestCase
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

    private function laboratory(string $name, string $code, array $computerCodes): Laboratory
    {
        $laboratory = Laboratory::create([
            'name' => $name,
            'code' => $code,
            'location' => 'Gedung A',
            'capacity' => 40,
        ]);

        foreach ($computerCodes as $computerCode) {
            Computer::create([
                'code' => $computerCode,
                'laboratory_id' => $laboratory->id,
                'status' => 'Aktif',
            ]);
        }

        return $laboratory;
    }

    private function academicYear(string $name, int $startYear, int $endYear, string $status = 'Aktif'): AcademicYear
    {
        return AcademicYear::create([
            'name' => $name,
            'start_year' => $startYear,
            'end_year' => $endYear,
            'status' => $status,
        ]);
    }

    /**
     * Buat satu pemeliharaan lengkap matriks item untuk tiap komputer lab.
     * $checked berisi kunci "A.1.K1-001" untuk sel yang tercentang.
     */
    private function maintenanceChecklist(
        Laboratory $laboratory,
        AcademicYear $academicYear,
        string $date,
        string $inspector,
        array $checked = [],
    ): MaintenanceChecklist {
        $checklist = MaintenanceChecklist::create([
            'laboratory_id' => $laboratory->id,
            'academic_year_id' => $academicYear->id,
            'maintenance_date' => $date,
            'inspector_name' => $inspector,
        ]);

        $computers = Computer::where('laboratory_id', $laboratory->id)->get();
        $definitions = MaintenanceChecklist::getChecklistItems();

        foreach ($computers as $computer) {
            foreach ($definitions as $category => $definition) {
                foreach (array_keys($definition['items']) as $index) {
                    $itemNumber = $index + 1;
                    $key = "{$category}.{$itemNumber}.{$computer->code}";

                    MaintenanceChecklistItem::create([
                        'maintenance_checklist_id' => $checklist->id,
                        'computer_id' => $computer->id,
                        'category' => $category,
                        'item_number' => $itemNumber,
                        'is_checked' => in_array($key, $checked, true),
                    ]);
                }
            }
        }

        return $checklist;
    }

    public function test_index_filters_by_laboratory_and_academic_year(): void
    {
        $admin = $this->userWith('filter-maint@uji.test', ['view-maintenance', 'print-maintenance']);
        $lab1 = $this->laboratory('Lab Komputer 1', 'LK1', ['K1-001']);
        $lab2 = $this->laboratory('Lab Komputer 2', 'LK2', ['K2-001']);
        $year1 = $this->academicYear('Tahun Ajaran 2025/2026', 2025, 2026);
        $year2 = $this->academicYear('Tahun Ajaran 2024/2025', 2024, 2025, 'Non Aktif');

        $this->maintenanceChecklist($lab1, $year1, '2025-08-20', 'Petugas Satu');
        $this->maintenanceChecklist($lab2, $year1, '2025-08-21', 'Petugas Dua');
        $this->maintenanceChecklist($lab1, $year2, '2024-08-21', 'Petugas Tiga');

        $this->actingAs($admin)
            ->get(route('maintenance.index', ['laboratory_id' => $lab1->id, 'academic_year_id' => $year1->id]))
            ->assertOk()
            ->assertSee('Semua Laboratorium')
            ->assertSee('Semua Tahun Ajaran')
            ->assertSee(route('maintenance.print-all'), false)
            ->assertSee('Petugas Satu')
            ->assertDontSee('Petugas Dua')
            ->assertDontSee('Petugas Tiga');

        $this->actingAs($admin)
            ->get(route('maintenance.index', ['laboratory_id' => 'all', 'academic_year_id' => 'all']))
            ->assertOk()
            ->assertSee('Petugas Satu')
            ->assertSee('Petugas Dua')
            ->assertSee('Petugas Tiga');
    }

    public function test_print_all_renders_one_page_per_filtered_maintenance(): void
    {
        $printer = $this->userWith('print-maint@uji.test', ['print-maintenance']);
        $lab1 = $this->laboratory('Lab Komputer 1', 'LK1', ['K1-001', 'K1-002']);
        $lab2 = $this->laboratory('Lab Komputer 2', 'LK2', ['K2-001']);
        $year1 = $this->academicYear('Tahun Ajaran 2025/2026', 2025, 2026);
        $year2 = $this->academicYear('Tahun Ajaran 2024/2025', 2024, 2025, 'Non Aktif');

        $this->maintenanceChecklist($lab1, $year1, '2025-08-20', 'Petugas Satu', ['A.1.K1-001']);
        $this->maintenanceChecklist($lab1, $year1, '2025-09-10', 'Petugas Empat');
        $this->maintenanceChecklist($lab2, $year1, '2025-08-21', 'Petugas Dua');
        $this->maintenanceChecklist($lab1, $year2, '2024-08-21', 'Petugas Tiga');

        $response = $this->actingAs($printer)
            ->get(route('maintenance.print-all', ['laboratory_id' => $lab1->id, 'academic_year_id' => $year1->id]))
            ->assertOk()
            ->assertSee('<title>Pemeliharaan-Lab Komputer 1-Tahun Ajaran 2025-2026</title>', false)
            ->assertSee('KONTROL PEMELIHARAAN LABORATORIUM KOMPUTER')
            ->assertSee('Lab Komputer 1')
            ->assertSee('Tahun Ajaran 2025/2026')
            ->assertSee('20 Agustus 2025')
            ->assertSee('10 September 2025')
            ->assertSee('K1-001')
            ->assertSee('K1-002')
            ->assertSee('Petugas Satu')
            ->assertSee('Petugas Empat')
            ->assertSee('Apakah kondisi komputer normal sebelum dilakukan pemeliharaan?')
            ->assertSee('checkbox checked', false)
            ->assertDontSee('Lab Komputer 2')
            ->assertDontSee('Petugas Dua')
            ->assertDontSee('Petugas Tiga');

        // Satu halaman cetak per data pemeliharaan pada filter.
        $this->assertSame(2, substr_count($response->getContent(), 'class="sheet"'));

        $this->actingAs($printer)
            ->get(route('maintenance.print-all', ['laboratory_id' => $lab2->id, 'academic_year_id' => $year2->id]))
            ->assertOk()
            ->assertSee('<title>Pemeliharaan-Lab Komputer 2-Tahun Ajaran 2024-2025</title>', false)
            ->assertSee('Tidak ada data pemeliharaan pada filter ini.');
    }

    public function test_print_all_requires_print_maintenance_permission(): void
    {
        $viewer = $this->userWith('viewer-print-maint@uji.test', ['view-maintenance']);
        $printer = $this->userWith('printer-print-maint@uji.test', ['print-maintenance']);

        $this->actingAs($viewer)->get(route('maintenance.print-all'))->assertForbidden();
        $this->actingAs($printer)
            ->get(route('maintenance.print-all'))
            ->assertOk()
            ->assertSee('<title>Pemeliharaan-Semua Laboratorium-Semua Tahun Ajaran</title>', false);

        $this->actingAs($viewer)
            ->get(route('maintenance.index'))
            ->assertOk()
            ->assertDontSee(route('maintenance.print-all'), false);
    }

    public function test_single_print_still_renders_its_own_page(): void
    {
        $printer = $this->userWith('single-print-maint@uji.test', ['print-maintenance']);
        $lab = $this->laboratory('Lab Komputer 1', 'LK1', ['K1-001']);
        $year = $this->academicYear('Tahun Ajaran 2025/2026', 2025, 2026);

        $checklist = $this->maintenanceChecklist($lab, $year, '2025-08-20', 'Petugas Satu');

        $response = $this->actingAs($printer)
            ->get(route('maintenance.print', $checklist))
            ->assertOk()
            ->assertSee('<title>Pemeliharaan-Lab Komputer 1-Tahun Ajaran 2025-2026</title>', false)
            ->assertSee('Lab Komputer 1')
            ->assertSee('Tahun Ajaran 2025/2026')
            ->assertSee('Petugas Satu');

        $this->assertSame(1, substr_count($response->getContent(), 'class="sheet"'));
    }
}
