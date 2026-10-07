<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\Computer;
use App\Models\DeviceCheck;
use App\Models\DeviceCheckItem;
use App\Models\Laboratory;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Tampilan laporan (layar & cetak) hanya menampilkan nama tahun ajaran —
 * rentang tanggal periode (periodLabel, mis. "01 Aug 2025 - 30 Jun 2026")
 * tidak boleh muncul lagi.
 */
class ReportPeriodWithoutDatesTest extends TestCase
{
    use RefreshDatabase;

    private function userWith(string $name, string $email, array $permissions): User
    {
        $role = Role::firstOrCreate(['name' => 'role-'.md5($email)], ['label' => 'Role '.$name]);

        foreach ($permissions as $permission) {
            $role->permissions()->attach(
                Permission::firstOrCreate(['name' => $permission], ['label' => $permission])
            );
        }

        $user = User::create([
            'name' => $name,
            'email' => $email,
            'password' => 'rahasia123',
        ]);
        $user->roles()->attach($role);

        return $user;
    }

    private function seedLab(): Laboratory
    {
        $lab = Laboratory::create([
            'name' => 'Lab Komputer 1', 'code' => 'LK1', 'location' => 'Gedung A', 'capacity' => 40,
        ]);

        Computer::create(['code' => 'K1-001', 'laboratory_id' => $lab->id, 'status' => 'Aktif']);

        return $lab;
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

    private function seedCheck(Laboratory $lab, AcademicYear $year): DeviceCheck
    {
        $check = DeviceCheck::create([
            'laboratory_id' => $lab->id,
            'academic_year_id' => $year->id,
            'check_date' => '2025-09-15',
            'officer_name' => 'Firmansyah',
        ]);

        $computer = Computer::where('laboratory_id', $lab->id)->firstOrFail();

        foreach (DeviceCheck::itemKeys() as $itemKey) {
            DeviceCheckItem::create([
                'device_check_id' => $check->id,
                'computer_id' => $computer->id,
                'item_key' => $itemKey,
                'is_checked' => true,
            ]);
        }

        return $check;
    }

    public function test_kartu_kendali_report_screen_and_print_omit_period_dates(): void
    {
        $user = $this->userWith('Viewer Laporan', 'viewer-laporan@uji.test', ['view-reports']);
        $lab = $this->seedLab();
        $year = $this->seedYear();
        $query = ['laboratory_id' => $lab->id, 'academic_year_id' => $year->id];

        $this->actingAs($user)
            ->get(route('reports.card-control', $query))
            ->assertOk()
            ->assertSee($year->name)
            ->assertSee('Periode')
            ->assertDontSee($year->periodLabel());

        $this->actingAs($user)
            ->get(route('reports.card-control-print', $query))
            ->assertOk()
            ->assertSee($year->name)
            ->assertDontSee($year->periodLabel());
    }

    public function test_device_check_report_screen_and_print_omit_period_dates(): void
    {
        $user = $this->userWith('Viewer Pengecekan', 'viewer-pengecekan@uji.test', ['view-maintenance']);
        $lab = $this->seedLab();
        $year = $this->seedYear();
        $query = ['laboratory_id' => $lab->id, 'academic_year_id' => $year->id];

        $this->actingAs($user)
            ->get(route('device-checks.report', $query))
            ->assertOk()
            ->assertSee($year->name)
            ->assertDontSee($year->periodLabel());

        $this->actingAs($user)
            ->get(route('device-checks.report-print', $query))
            ->assertOk()
            ->assertSee($year->name)
            ->assertDontSee($year->periodLabel());
    }

    public function test_device_check_print_omit_period_dates(): void
    {
        $user = $this->userWith('Viewer Cetak', 'viewer-cetak@uji.test', ['view-maintenance']);
        $lab = $this->seedLab();
        $year = $this->seedYear();
        $check = $this->seedCheck($lab, $year);

        $this->actingAs($user)
            ->get(route('device-checks.print', $check))
            ->assertOk()
            ->assertSee($year->name)
            ->assertDontSee($year->periodLabel());
    }
}
