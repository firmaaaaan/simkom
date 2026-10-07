<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\Computer;
use App\Models\DeviceCheck;
use App\Models\DeviceCheckItem;
use App\Models\Laboratory;
use App\Models\MaintenanceChecklist;
use App\Models\MaintenanceChecklistItem;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KartuKendaliHistoryTabTest extends TestCase
{
    use RefreshDatabase;

    private function seedLab(): array
    {
        $lab = Laboratory::create([
            'name' => 'Lab Komputer 1', 'code' => 'LK1', 'location' => 'Gedung A', 'capacity' => 40,
        ]);

        $computer = Computer::create([
            'code' => 'K1-001', 'laboratory_id' => $lab->id, 'status' => 'Aktif',
        ]);

        return [$lab, $computer];
    }

    private function seedYear(): AcademicYear
    {
        return AcademicYear::create([
            'name' => 'Tahun Ajaran 2025/2026',
            'start_year' => 2025,
            'end_year' => 2026,
            'status' => 'Aktif',
        ]);
    }

    private function seedDeviceCheck(Laboratory $lab, Computer $computer, AcademicYear $year): DeviceCheck
    {
        $check = DeviceCheck::create([
            'laboratory_id' => $lab->id,
            'academic_year_id' => $year->id,
            'check_date' => '2025-09-15',
            'officer_name' => 'Firmansyah',
            'notes' => 'Pengecekan awal semester',
        ]);

        foreach (DeviceCheck::itemKeys() as $itemKey) {
            DeviceCheckItem::create([
                'device_check_id' => $check->id,
                'computer_id' => $computer->id,
                'item_key' => $itemKey,
                'is_checked' => $itemKey === 'mouse',
            ]);
        }

        return $check;
    }

    private function seedMaintenance(Laboratory $lab, Computer $computer, AcademicYear $year): MaintenanceChecklist
    {
        $maintenance = MaintenanceChecklist::create([
            'laboratory_id' => $lab->id,
            'academic_year_id' => $year->id,
            'maintenance_date' => '2025-08-20',
            'inspector_name' => 'Siti Rahma',
            'notes_computer' => 'Debu dibersihkan',
        ]);

        foreach (MaintenanceChecklist::getChecklistItems() as $category => $definition) {
            foreach ($definition['items'] as $number => $question) {
                MaintenanceChecklistItem::create([
                    'maintenance_checklist_id' => $maintenance->id,
                    'computer_id' => $computer->id,
                    'category' => $category,
                    'item_number' => $number + 1,
                    'is_checked' => $category === 'A' && $number === 0,
                ]);
            }
        }

        return $maintenance;
    }

    private function viewer(): User
    {
        $role = Role::firstOrCreate(['name' => 'viewer-kartu'], ['label' => 'Viewer Kartu']);
        $role->permissions()->attach(
            Permission::firstOrCreate(['name' => 'view-computers'], ['label' => 'view-computers'])
        );

        $user = User::create([
            'name' => 'Viewer Kartu',
            'email' => 'viewer-kartu@uji.test',
            'password' => 'rahasia123',
        ]);
        $user->roles()->attach($role);

        return $user;
    }

    public function test_public_kartu_page_shows_history_tabs(): void
    {
        [$lab, $computer] = $this->seedLab();
        $year = $this->seedYear();
        $this->seedDeviceCheck($lab, $computer, $year);
        $this->seedMaintenance($lab, $computer, $year);

        $response = $this->get(route('kartu.show', $computer));

        $response
            ->assertOk()
            ->assertSee('Pengecekan Perangkat')
            ->assertSee('Pemeliharaan')
            // Data tab pengecekan perangkat.
            ->assertSee('15 September 2025')
            ->assertSee('Firmansyah')
            ->assertSee('Kabel Power PC')
            ->assertSee('1 dari 8 item berfungsi')
            ->assertSee('Pengecekan awal semester')
            // Data tab pemeliharaan.
            ->assertSee('Siti Rahma')
            ->assertSee('Apakah kondisi komputer normal sebelum dilakukan pemeliharaan?')
            ->assertSee('Pemeriksaan Komputer')
            ->assertSee('Debu dibersihkan');

        // Label tab pertama adalah "Kartu Kendali" (bukan "Riwayat Pengecekan").
        $this->assertMatchesRegularExpression(
            "/activeTab = 'riwayat'.*?>\s*Kartu Kendali\s*<\/button>/s",
            $response->getContent()
        );
        // Heading panel tetap "Riwayat Pengecekan".
        $response->assertSee('Riwayat Pengecekan');
    }

    public function test_admin_card_page_shows_history_tabs(): void
    {
        [$lab, $computer] = $this->seedLab();
        $year = $this->seedYear();
        $this->seedDeviceCheck($lab, $computer, $year);
        $this->seedMaintenance($lab, $computer, $year);

        $this->actingAs($this->viewer())
            ->get(route('computers.card', $computer))
            ->assertOk()
            ->assertSee('Riwayat Pemeliharaan')
            ->assertSee('Hasil matriks pengecekan perangkat')
            ->assertSee('15 September 2025')
            ->assertSee('1 dari 8 item berfungsi')
            ->assertSee('Siti Rahma')
            ->assertSee('Apakah mouse dan keyboard dapat digunakan dengan baik?');
    }

    public function test_history_tabs_show_empty_state(): void
    {
        [, $computer] = $this->seedLab();

        $this->get(route('kartu.show', $computer))
            ->assertOk()
            ->assertSee('Belum ada riwayat pengecekan perangkat untuk komputer ini')
            ->assertSee('Belum ada riwayat pemeliharaan untuk komputer ini');
    }

    public function test_tab_hydrates_from_query_string(): void
    {
        [, $computer] = $this->seedLab();

        $this->get(route('kartu.show', $computer).'?tab=pemeliharaan')
            ->assertOk()
            ->assertSee("activeTab: 'pemeliharaan'", false);
    }
}
