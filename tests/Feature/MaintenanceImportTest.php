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
use Illuminate\Http\UploadedFile;
use Illuminate\Testing\TestResponse;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Tests\TestCase;

class MaintenanceImportTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return $this->userWith('Admin Import', 'admin-maint-import@uji.test', ['view-maintenance', 'edit-maintenance']);
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
     * Teks pertanyaan checklist, urut A.1 … D.1.
     */
    private function questions(): array
    {
        $questions = [];

        foreach (MaintenanceChecklist::getChecklistItems() as $definition) {
            foreach ($definition['items'] as $question) {
                $questions[] = $question;
            }
        }

        return $questions;
    }

    private function header(): array
    {
        return array_merge(['No', 'Kode Komputer'], $this->questions());
    }

    /**
     * Susun file xlsx dari grid seluruh baris (termasuk blok judul di atas
     * header) untuk diunggah saat import.
     */
    private function fileFromGrid(array $grid, string $name = 'pemeliharaan.xlsx'): UploadedFile
    {
        $spreadsheet = new Spreadsheet;
        $spreadsheet->getActiveSheet()->fromArray($grid, null, 'A1');

        $path = tempnam(sys_get_temp_dir(), 'pem').'.xlsx';
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
     * Peta "kode.KATEGORI.nomor" => tercentang untuk sebuah pemeliharaan.
     */
    private function matrix(MaintenanceChecklist $checklist): array
    {
        $map = [];

        foreach ($checklist->items()->with('computer')->get() as $item) {
            $map[$item->computer->code.'.'.$item->category.'.'.$item->item_number] = (bool) $item->is_checked;
        }

        return $map;
    }

    private function importPayload(Laboratory $lab, AcademicYear $year, array $grid): array
    {
        return [
            'file' => $this->fileFromGrid($grid),
            'laboratory_id' => $lab->id,
            'academic_year_id' => $year->id,
            'maintenance_date' => '2025-08-20',
            'inspector_name' => 'Siti Rahma',
            'notes_computer' => 'Impor matriks',
        ];
    }

    public function test_import_creates_checklist_with_matrix_values(): void
    {
        $admin = $this->admin();
        $lab = $this->seedLab();
        $year = $this->seedYear();

        // Blok judul di atas header dilewati; header 1 baris (format template).
        $grid = [
            ['Nama Lab', 'Lab Komputer 1'],
            ['Periode', '2025/2026'],
            $this->header(),
            [1, 'K1-001', '✓', '✓', '', '', '', '', '', '✓', '', 'x', '✓'],
            [2, 'K1-002', '', 'v', '', '', '', '', '', '', '', '', ''],
        ];

        $this->actingAs($admin)
            ->post(route('maintenance.store-import'), $this->importPayload($lab, $year, $grid))
            ->assertRedirect(route('maintenance.import'))
            ->assertSessionHas('success', fn ($message) => str_contains($message, 'pemeliharaan baru')
                && str_contains($message, 'Tahun Ajaran 2025/2026'));

        $this->assertSame(1, MaintenanceChecklist::count());

        $checklist = MaintenanceChecklist::first();
        $this->assertSame($lab->id, $checklist->laboratory_id);
        $this->assertSame($year->id, $checklist->academic_year_id);
        $this->assertSame('2025-08-20', $checklist->maintenance_date);
        $this->assertSame('Siti Rahma', $checklist->inspector_name);
        $this->assertSame('Impor matriks', $checklist->notes_computer);

        // Seluruh komputer lab × seluruh pertanyaan ditulis, termasuk yang tak di file.
        $this->assertSame(3 * count($this->questions()), MaintenanceChecklistItem::count());

        $matrix = $this->matrix($checklist);
        $this->assertTrue($matrix['K1-001.A.1']);
        $this->assertTrue($matrix['K1-001.A.2']);
        $this->assertFalse($matrix['K1-001.A.3']);
        $this->assertFalse($matrix['K1-001.A.4']);
        $this->assertFalse($matrix['K1-001.A.5']);
        $this->assertFalse($matrix['K1-001.A.6']);
        $this->assertFalse($matrix['K1-001.A.7']);
        $this->assertTrue($matrix['K1-001.B.1']);
        $this->assertFalse($matrix['K1-001.B.2']);
        $this->assertTrue($matrix['K1-001.C.1']);
        $this->assertTrue($matrix['K1-001.D.1']);

        $this->assertFalse($matrix['K1-002.A.1']);
        $this->assertTrue($matrix['K1-002.A.2']);
        $this->assertFalse($matrix['K1-002.D.1']);

        $this->assertFalse($matrix['K1-003.A.1']);
        $this->assertFalse($matrix['K1-003.D.1']);
    }

    public function test_import_supports_two_row_group_header(): void
    {
        $admin = $this->admin();
        $lab = $this->seedLab();
        $year = $this->seedYear();

        // Meniru matriks dengan baris grup kategori di atas sub-kolom
        // pertanyaan (baris judul kedua).
        $grid = [
            array_merge(['No', 'Kode Komputer', 'A. Pemeriksaan Komputer'], array_fill(0, 6, ''), ['B. Pemeriksaan Mouse dan Keyboard', '', 'C. Pemeriksaan UPS', 'D. Pemeriksaan Monitor']),
            array_merge(['', ''], $this->questions()),
            array_merge([1, 'K1-001'], array_map(fn ($index) => $index % 2 === 0 ? '✓' : '', range(0, 10))),
        ];

        $this->actingAs($admin)
            ->post(route('maintenance.store-import'), $this->importPayload($lab, $year, $grid))
            ->assertRedirect(route('maintenance.import'))
            ->assertSessionHas('success', fn ($message) => str_contains($message, 'pemeliharaan baru'));

        $matrix = $this->matrix(MaintenanceChecklist::first());
        $this->assertTrue($matrix['K1-001.A.1']);
        $this->assertFalse($matrix['K1-001.A.2']);
        $this->assertTrue($matrix['K1-001.A.3']);
        $this->assertFalse($matrix['K1-001.A.4']);
        $this->assertTrue($matrix['K1-001.A.5']);
        $this->assertFalse($matrix['K1-001.A.6']);
        $this->assertTrue($matrix['K1-001.A.7']);
        $this->assertFalse($matrix['K1-001.B.1']);
        $this->assertTrue($matrix['K1-001.B.2']);
        $this->assertFalse($matrix['K1-001.C.1']);
        $this->assertTrue($matrix['K1-001.D.1']);
    }

    public function test_import_updates_instead_of_duplicating_for_same_context(): void
    {
        $admin = $this->admin();
        $lab = $this->seedLab();
        $year = $this->seedYear();

        $header = $this->header();

        $first = [$header, array_merge([1, 'K1-001'], array_fill(0, 11, '✓'))];
        $this->actingAs($admin)
            ->post(route('maintenance.store-import'), $this->importPayload($lab, $year, $first))
            ->assertSessionHas('success', fn ($message) => str_contains($message, 'pemeliharaan baru'));

        $second = [$header, array_merge([1, 'K1-001'], array_fill(0, 11, ''))];
        $this->actingAs($admin)
            ->post(route('maintenance.store-import'), $this->importPayload($lab, $year, $second))
            ->assertRedirect(route('maintenance.import'))
            ->assertSessionHas('success', fn ($message) => str_contains($message, '1 diperbarui'));

        // Tidak digandakan, seluruh matriks ditimpa (bukan hanya baris file).
        $this->assertSame(1, MaintenanceChecklist::count());
        $this->assertSame(3 * count($this->questions()), MaintenanceChecklistItem::count());

        $matrix = $this->matrix(MaintenanceChecklist::first());
        $this->assertFalse($matrix['K1-001.A.1']);
        $this->assertFalse($matrix['K1-001.D.1']);
    }

    public function test_import_reports_bad_rows_without_aborting_the_file(): void
    {
        $admin = $this->admin();
        $lab = $this->seedLab();
        $year = $this->seedYear();

        $header = $this->header();
        $unchecked = array_fill(0, 11, '');

        $grid = [
            $header,
            array_merge([1, 'K1-001'], array_replace($unchecked, [0 => '✓'])),
            array_merge([2, 'K9-999'], array_replace($unchecked, [0 => '✓'])),
            array_merge([3, ''], array_replace($unchecked, [0 => '✓'])),
            array_merge([4, 'K1-002'], array_replace($unchecked, [7 => '✓'])),
        ];

        $this->actingAs($admin)
            ->post(route('maintenance.store-import'), $this->importPayload($lab, $year, $grid))
            ->assertRedirect(route('maintenance.import'))
            ->assertSessionHas('import_errors', function (array $errors) {
                $this->assertCount(2, $errors);
                $this->assertStringContainsString('K9-999', implode(' ', $errors));
                $this->assertStringContainsString('tidak ditemukan di lab ini', implode(' ', $errors));
                $this->assertStringContainsString('kode komputer kosong', implode(' ', $errors));

                return true;
            })
            ->assertSessionHas('success', fn ($message) => str_contains($message, 'pemeliharaan baru')
                && str_contains($message, '2 baris gagal'));

        // Baris valid tetap tersimpan meski ada baris gagal.
        $this->assertSame(1, MaintenanceChecklist::count());
        $matrix = $this->matrix(MaintenanceChecklist::first());
        $this->assertTrue($matrix['K1-001.A.1']);
        $this->assertTrue($matrix['K1-002.B.1']);
    }

    public function test_template_headers_match_checklist_and_reupload_is_noop(): void
    {
        $admin = $this->admin();
        $this->seedLab();
        $year = $this->seedYear();

        $response = $this->actingAs($admin)->get(route('maintenance.template'));
        $response->assertOk();

        $rows = $this->rows($response);
        $this->assertSame(
            $this->header(),
            array_values(array_filter($rows[0], fn ($cell) => $cell !== null))
        );

        // Unggah ulang template apa adanya tidak membuat data (baris "Contoh").
        $lab = Laboratory::firstOrFail();
        $this->actingAs($admin)
            ->post(route('maintenance.store-import'), [
                'file' => $this->fileFromGrid($rows),
                'laboratory_id' => $lab->id,
                'academic_year_id' => $year->id,
                'maintenance_date' => '2025-08-20',
            ])
            ->assertRedirect(route('maintenance.import'))
            ->assertSessionHas('success', fn ($message) => str_contains($message, 'Tidak ada pemeliharaan'));

        $this->assertSame(0, MaintenanceChecklist::count());
    }

    public function test_import_page_and_index_link_render_for_authorized_user(): void
    {
        $admin = $this->admin();
        $this->seedLab();
        $this->seedYear();

        $this->actingAs($admin)
            ->get(route('maintenance.import'))
            ->assertOk()
            ->assertSee('Import Pemeliharaan dari Excel')
            ->assertSee('Download Template')
            ->assertSee(route('maintenance.template'), false)
            ->assertSee('Pilih Laboratorium')
            ->assertSee('Pilih Tahun Ajaran')
            ->assertSee('Tanggal Pemeliharaan')
            ->assertSee('Nama Pemeriksa');

        $this->actingAs($admin)
            ->get(route('maintenance.index'))
            ->assertOk()
            ->assertSee(route('maintenance.import'), false);
    }

    public function test_import_requires_edit_maintenance_and_template_requires_view(): void
    {
        $this->seedLab();
        $year = $this->seedYear();

        $viewer = $this->userWith('Viewer', 'viewer-maint-import@uji.test', ['view-maintenance']);
        $editor = $this->userWith('Editor', 'editor-maint-import@uji.test', ['edit-maintenance']);

        $this->actingAs($viewer)->get(route('maintenance.import'))->assertForbidden();
        $this->actingAs($viewer)->get(route('maintenance.template'))->assertOk();
        $this->actingAs($viewer)
            ->post(route('maintenance.store-import'), [
                'file' => $this->fileFromGrid([['No', 'Kode Komputer']]),
                'laboratory_id' => Laboratory::firstOrFail()->id,
                'academic_year_id' => $year->id,
                'maintenance_date' => '2025-08-20',
            ])
            ->assertForbidden();

        $this->actingAs($editor)->get(route('maintenance.import'))->assertOk();
    }
}
