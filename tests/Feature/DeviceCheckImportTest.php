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
use Illuminate\Http\UploadedFile;
use Illuminate\Testing\TestResponse;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Tests\TestCase;

class DeviceCheckImportTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return $this->userWith('Admin Import', 'admin-import@uji.test', ['view-maintenance', 'edit-maintenance', 'import-maintenance']);
    }

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

        foreach (['K1-001', 'K1-002', 'K1-003'] as $code) {
            Computer::create(['code' => $code, 'laboratory_id' => $lab->id, 'status' => 'Aktif']);
        }

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

    /**
     * Susun file xlsx dari grid seluruh baris (termasuk blok judul di atas
     * header) untuk diunggah saat import.
     */
    private function fileFromGrid(array $grid, string $name = 'pengecekan-perangkat.xlsx'): UploadedFile
    {
        $spreadsheet = new Spreadsheet;
        $spreadsheet->getActiveSheet()->fromArray($grid, null, 'A1');

        $path = tempnam(sys_get_temp_dir(), 'cek').'.xlsx';
        (new Xlsx($spreadsheet))->save($path);
        $spreadsheet->disconnectWorksheets();

        return new UploadedFile(
            $path,
            $name,
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            null,
            true
        );
    }

    /**
     * Baca isi file xlsx dari response unduhan.
     */
    private function rows(TestResponse $response): array
    {
        $path = $response->baseResponse->getFile()->getPathname();

        return IOFactory::load($path)->getActiveSheet()->toArray(null, true, false, false);
    }

    /**
     * Peta sel "kode.item_key" => tercentang untuk sebuah pengecekan.
     */
    private function matrix(DeviceCheck $check): array
    {
        $map = [];

        foreach ($check->items()->with('computer')->get() as $item) {
            $map[$item->computer->code.'.'.$item->item_key] = (bool) $item->is_checked;
        }

        return $map;
    }

    private function importPayload(Laboratory $lab, AcademicYear $year, array $grid): array
    {
        return [
            'file' => $this->fileFromGrid($grid),
            'laboratory_id' => $lab->id,
            'academic_year_id' => $year->id,
            'check_date' => '2025-09-15',
            'officer_name' => 'Firmansyah',
            'notes' => 'Impor matriks',
        ];
    }

    public function test_import_creates_device_check_with_matrix_values(): void
    {
        $admin = $this->admin();
        $lab = $this->seedLab();
        $year = $this->seedYear();

        // Blok judul di atas header dilewati; header 1 baris (format template).
        $grid = [
            ['Nama Lab', 'Lab Komputer 1'],
            ['Periode', '2025/2026'],
            ['No', 'Kode Komputer', 'Mouse', 'Keyboard', 'HDMI', 'Kabel Power PC', 'Kabel Power CPU', 'Kabel Power UPS', 'UPS', 'Jaringan'],
            [1, 'K1-001', '✓', '✓', '', 'x', '✓', '✓', '', '✓'],
            [2, 'K1-002', '', 'v', '', '', '', '', '', ''],
        ];

        $this->actingAs($admin)
            ->post(route('device-checks.store-import'), $this->importPayload($lab, $year, $grid))
            ->assertRedirect(route('device-checks.import'))
            ->assertSessionHas('success', fn ($message) => str_contains($message, 'pengecekan baru')
                && str_contains($message, 'Tahun Ajaran 2025/2026'));

        $this->assertSame(1, DeviceCheck::count());

        $check = DeviceCheck::first();
        $this->assertSame($lab->id, $check->laboratory_id);
        $this->assertSame($year->id, $check->academic_year_id);
        $this->assertSame('2025-09-15', $check->check_date->format('Y-m-d'));
        $this->assertSame('Firmansyah', $check->officer_name);
        $this->assertSame('Impor matriks', $check->notes);

        // Seluruh komputer lab × seluruh item ditulis, termasuk yang tak di file.
        $this->assertSame(3 * count(DeviceCheck::itemKeys()), DeviceCheckItem::count());

        $matrix = $this->matrix($check);
        $this->assertTrue($matrix['K1-001.mouse']);
        $this->assertTrue($matrix['K1-001.keyboard']);
        $this->assertFalse($matrix['K1-001.hdmi']);
        $this->assertTrue($matrix['K1-001.power_pc']);
        $this->assertTrue($matrix['K1-001.power_cpu']);
        $this->assertTrue($matrix['K1-001.power_ups']);
        $this->assertFalse($matrix['K1-001.ups']);
        $this->assertTrue($matrix['K1-001.jaringan']);

        $this->assertFalse($matrix['K1-002.mouse']);
        $this->assertTrue($matrix['K1-002.keyboard']);
        $this->assertFalse($matrix['K1-002.jaringan']);

        $this->assertFalse($matrix['K1-003.mouse']);
        $this->assertFalse($matrix['K1-003.jaringan']);
    }

    public function test_import_supports_two_row_group_header(): void
    {
        $admin = $this->admin();
        $lab = $this->seedLab();
        $year = $this->seedYear();

        // Meniru salinan matriks cetak: grup "Kabel Power" di-merge di atas
        // sub-kolom PC/CPU/UPS (baris judul kedua).
        $grid = [
            ['No', 'Kode Komputer', 'Mouse', 'Keyboard', 'HDMI', 'Kabel Power', '', '', 'UPS', 'Jaringan'],
            ['', '', '', '', '', 'PC', 'CPU', 'UPS', '', ''],
            [1, 'K1-001', '✓', '', '✓', '✓', '', '✓', '', '✓'],
        ];

        $this->actingAs($admin)
            ->post(route('device-checks.store-import'), $this->importPayload($lab, $year, $grid))
            ->assertRedirect(route('device-checks.import'))
            ->assertSessionHas('success', fn ($message) => str_contains($message, 'pengecekan baru'));

        $matrix = $this->matrix(DeviceCheck::first());
        $this->assertTrue($matrix['K1-001.mouse']);
        $this->assertTrue($matrix['K1-001.hdmi']);
        $this->assertTrue($matrix['K1-001.power_pc']);
        $this->assertFalse($matrix['K1-001.power_cpu']);
        $this->assertTrue($matrix['K1-001.power_ups']);
        $this->assertFalse($matrix['K1-001.ups']);
        $this->assertTrue($matrix['K1-001.jaringan']);
    }

    public function test_import_updates_instead_of_duplicating_for_same_context(): void
    {
        $admin = $this->admin();
        $lab = $this->seedLab();
        $year = $this->seedYear();

        $header = ['No', 'Kode Komputer', 'Mouse', 'Keyboard', 'HDMI', 'Kabel Power PC', 'Kabel Power CPU', 'Kabel Power UPS', 'UPS', 'Jaringan'];

        $first = [$header, [1, 'K1-001', '✓', '✓', '✓', '✓', '✓', '✓', '✓', '✓']];
        $this->actingAs($admin)
            ->post(route('device-checks.store-import'), $this->importPayload($lab, $year, $first))
            ->assertSessionHas('success', fn ($message) => str_contains($message, 'pengecekan baru'));

        $second = [$header, [1, 'K1-001', '', '', '', '', '', '', '', '']];
        $this->actingAs($admin)
            ->post(route('device-checks.store-import'), $this->importPayload($lab, $year, $second))
            ->assertRedirect(route('device-checks.import'))
            ->assertSessionHas('success', fn ($message) => str_contains($message, '1 diperbarui'));

        // Tidak digandakan, seluruh matriks ditimpa (bukan hanya baris file).
        $this->assertSame(1, DeviceCheck::count());
        $this->assertSame(3 * count(DeviceCheck::itemKeys()), DeviceCheckItem::count());

        $matrix = $this->matrix(DeviceCheck::first());
        $this->assertFalse($matrix['K1-001.mouse']);
        $this->assertFalse($matrix['K1-001.jaringan']);
    }

    public function test_import_reports_bad_rows_without_aborting_the_file(): void
    {
        $admin = $this->admin();
        $lab = $this->seedLab();
        $year = $this->seedYear();

        $grid = [
            ['No', 'Kode Komputer', 'Mouse', 'Keyboard', 'HDMI', 'Kabel Power PC', 'Kabel Power CPU', 'Kabel Power UPS', 'UPS', 'Jaringan'],
            [1, 'K1-001', '✓', '', '', '', '', '', '', ''],
            [2, 'K9-999', '✓', '', '', '', '', '', '', ''],
            [3, '', '✓', '', '', '', '', '', '', ''],
            [4, 'K1-002', '', '✓', '', '', '', '', '', ''],
        ];

        $this->actingAs($admin)
            ->post(route('device-checks.store-import'), $this->importPayload($lab, $year, $grid))
            ->assertRedirect(route('device-checks.import'))
            ->assertSessionHas('import_errors', function (array $errors) {
                $this->assertCount(2, $errors);
                $this->assertStringContainsString('K9-999', implode(' ', $errors));
                $this->assertStringContainsString('tidak ditemukan di lab ini', implode(' ', $errors));
                $this->assertStringContainsString('kode komputer kosong', implode(' ', $errors));

                return true;
            })
            ->assertSessionHas('success', fn ($message) => str_contains($message, 'pengecekan baru')
                && str_contains($message, '2 baris gagal'));

        // Baris valid tetap tersimpan meski ada baris gagal.
        $this->assertSame(1, DeviceCheck::count());
        $matrix = $this->matrix(DeviceCheck::first());
        $this->assertTrue($matrix['K1-001.mouse']);
        $this->assertTrue($matrix['K1-002.keyboard']);
    }

    public function test_template_headers_match_item_columns_and_reupload_is_noop(): void
    {
        $admin = $this->admin();
        $this->seedLab();
        $year = $this->seedYear();

        $response = $this->actingAs($admin)->get(route('device-checks.template'));
        $response->assertOk();

        $rows = $this->rows($response);
        $this->assertSame(
            ['No', 'Kode Komputer', 'Mouse', 'Keyboard', 'HDMI', 'Kabel Power PC', 'Kabel Power CPU', 'Kabel Power UPS', 'UPS', 'Jaringan'],
            array_values(array_filter($rows[0], fn ($cell) => $cell !== null))
        );

        // Unggah ulang template apa adanya tidak membuat data (baris "Contoh").
        $lab = Laboratory::firstOrFail();
        $this->actingAs($admin)
            ->post(route('device-checks.store-import'), [
                'file' => $this->fileFromGrid($rows),
                'laboratory_id' => $lab->id,
                'academic_year_id' => $year->id,
                'check_date' => '2025-09-15',
            ])
            ->assertRedirect(route('device-checks.import'))
            ->assertSessionHas('success', fn ($message) => str_contains($message, 'Tidak ada pengecekan'));

        $this->assertSame(0, DeviceCheck::count());
    }

    public function test_import_page_and_index_link_render_for_authorized_user(): void
    {
        $admin = $this->admin();
        $this->seedLab();
        $this->seedYear();

        $this->actingAs($admin)
            ->get(route('device-checks.import'))
            ->assertOk()
            ->assertSee('Import Pengecekan Perangkat dari Excel')
            ->assertSee('Download Template')
            ->assertSee(route('device-checks.template'), false)
            ->assertSee('Pilih Laboratorium')
            ->assertSee('Pilih Tahun Ajaran');

        $this->actingAs($admin)
            ->get(route('device-checks.index'))
            ->assertOk()
            ->assertSee(route('device-checks.import'), false);
    }

    public function test_import_requires_import_maintenance_and_template_requires_export_or_import(): void
    {
        $this->seedLab();
        $year = $this->seedYear();

        $viewer = $this->userWith('Viewer', 'viewer-maint@uji.test', ['view-maintenance']);
        $editor = $this->userWith('Editor', 'editor-maint@uji.test', ['edit-maintenance', 'import-maintenance']);

        $this->actingAs($viewer)->get(route('device-checks.import'))->assertForbidden();
        $this->actingAs($viewer)->get(route('device-checks.template'))->assertForbidden();
        $this->actingAs($viewer)
            ->post(route('device-checks.store-import'), [
                'file' => $this->fileFromGrid([['No', 'Kode Komputer']]),
                'laboratory_id' => Laboratory::firstOrFail()->id,
                'academic_year_id' => $year->id,
                'check_date' => '2025-09-15',
            ])
            ->assertForbidden();

        $this->actingAs($editor)->get(route('device-checks.import'))->assertOk();
    }
}
