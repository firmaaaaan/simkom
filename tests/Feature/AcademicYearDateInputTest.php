<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Tests\TestCase;

/**
 * Form tahun ajaran tidak lagi meminta tanggal mulai/selesai — periode selalu
 * diturunkan otomatis dari tahun (1 Agustus sampai 30 Juni).
 */
class AcademicYearDateInputTest extends TestCase
{
    use RefreshDatabase;

    private function userWith(string ...$permissions): User
    {
        $role = Role::firstOrCreate(['name' => 'admin-ta'], ['label' => 'Admin TA']);

        foreach ($permissions as $name) {
            $role->permissions()->attach(
                Permission::firstOrCreate(['name' => $name], ['label' => $name])
            );
        }

        $user = User::create([
            'name' => 'Admin Tahun Ajaran',
            'email' => 'admin-ta@uji.test',
            'password' => 'rahasia123',
        ]);
        $user->roles()->attach($role);

        return $user;
    }

    public function test_create_and_edit_forms_do_not_ask_for_dates(): void
    {
        $user = $this->userWith('view-academic-years', 'create-academic-years', 'edit-academic-years');
        $year = AcademicYear::create([
            'name' => 'TA 2026/2027', 'start_year' => 2026, 'end_year' => 2027, 'status' => 'Non Aktif',
        ]);

        $this->actingAs($user)
            ->get(route('academic-years.create'))
            ->assertOk()
            ->assertSee('Tahun Mulai')
            ->assertDontSee('Tanggal Mulai')
            ->assertDontSee('Tanggal Selesai');

        $this->actingAs($user)
            ->get(route('academic-years.edit', $year))
            ->assertOk()
            ->assertSee('Tahun Mulai')
            ->assertDontSee('Tanggal Mulai')
            ->assertDontSee('Tanggal Selesai');
    }

    public function test_store_creates_year_without_dates_and_derives_period(): void
    {
        $user = $this->userWith('create-academic-years');

        $this->actingAs($user)->post(route('academic-years.store'), [
            'name' => '2030/2031',
            'start_year' => 2030,
            'end_year' => 2031,
            'status' => 'Non Aktif',
        ])->assertRedirect(route('academic-years.index'));

        $year = AcademicYear::first();
        $this->assertNull($year->start_date);
        $this->assertNull($year->end_date);
        $this->assertSame('01 Aug 2030 - 30 Jun 2031', $year->periodLabel());
    }

    public function test_store_ignores_posted_dates(): void
    {
        $user = $this->userWith('create-academic-years');

        $this->actingAs($user)->post(route('academic-years.store'), [
            'name' => '2031/2032',
            'start_year' => 2031,
            'end_year' => 2032,
            'status' => 'Non Aktif',
            'start_date' => '2031-08-15',
            'end_date' => '2032-06-15',
        ])->assertRedirect(route('academic-years.index'));

        $year = AcademicYear::first();
        $this->assertNull($year->start_date);
        $this->assertNull($year->end_date);
        $this->assertSame('01 Aug 2031 - 30 Jun 2032', $year->periodLabel());
    }

    public function test_update_keeps_existing_dates_untouched(): void
    {
        $user = $this->userWith('edit-academic-years');
        $year = AcademicYear::create([
            'name' => 'TA 2026/2027', 'start_year' => 2026, 'end_year' => 2027, 'status' => 'Aktif',
            'start_date' => '2026-08-15', 'end_date' => '2027-06-30',
        ]);

        $this->actingAs($user)->put(route('academic-years.update', $year), [
            'name' => 'TA 2026/2027 Baru',
            'start_year' => 2026,
            'end_year' => 2027,
            'status' => 'Non Aktif',
        ])->assertRedirect(route('academic-years.index'));

        $year->refresh();
        $this->assertSame('TA 2026/2027 Baru', $year->name);
        $this->assertSame('2026-08-15', $year->start_date->format('Y-m-d'));
        $this->assertSame('2027-06-30', $year->end_date->format('Y-m-d'));
    }

    public function test_show_page_hides_date_fields(): void
    {
        $user = $this->userWith('view-academic-years');
        $year = AcademicYear::create([
            'name' => 'TA 2026/2027', 'start_year' => 2026, 'end_year' => 2027, 'status' => 'Aktif',
        ]);

        $this->actingAs($user)
            ->get(route('academic-years.show', $year))
            ->assertOk()
            ->assertSee('TA 2026/2027')
            ->assertDontSee('Tanggal Mulai')
            ->assertDontSee('Tanggal Selesai');
    }

    public function test_export_drops_date_columns(): void
    {
        $user = $this->userWith('view-academic-years');
        AcademicYear::create([
            'name' => 'TA 2026/2027', 'start_year' => 2026, 'end_year' => 2027, 'status' => 'Non Aktif',
        ]);

        $download = $this->actingAs($user)->get(route('academic-years.export'));
        $download->assertOk();

        $path = $download->baseResponse->getFile()->getPathname();
        $rows = IOFactory::load($path)->getActiveSheet()->toArray(null, true, false, false);

        $this->assertSame(
            ['Nama Tahun Ajaran', 'Tahun Mulai', 'Tahun Selesai', 'Periode', 'Status'],
            $rows[0]
        );
        // Periode tetap berada di index 3 (tetangga ExcelExportTempTest).
        $this->assertSame('01 Aug 2026 - 30 Jun 2027', $rows[1][3]);
        $this->assertStringNotContainsString('Tanggal Mulai', implode(',', $rows[0]));
    }
}
