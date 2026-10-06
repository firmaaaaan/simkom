<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\Computer;
use App\Models\ComputerCheck;
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

class ComputerCheckImportTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        $role = Role::firstOrCreate(['name' => 'admin'], ['label' => 'Admin']);

        foreach (['view-computers', 'edit-computers'] as $name) {
            $role->permissions()->attach(
                Permission::firstOrCreate(['name' => $name], ['label' => $name])
            );
        }

        $user = User::create([
            'name' => 'Admin Kartu Kendali',
            'email' => 'admin-kartu@uji.test',
            'password' => 'rahasia123',
        ]);
        $user->roles()->attach($role);

        return $user;
    }

    private function seedComputers(): void
    {
        $lab = Laboratory::create([
            'name' => 'Lab Komputer 1', 'code' => 'LK1', 'location' => 'Gedung A', 'capacity' => 40,
        ]);

        foreach (['K1-001', 'K1-002', 'K1-003'] as $code) {
            Computer::create(['code' => $code, 'laboratory_id' => $lab->id, 'status' => 'Aktif']);
        }
    }

    /**
     * Susun file xlsx dari grid seluruh baris (termasuk blok judul di atas
     * header) untuk diunggah saat import.
     */
    private function fileFromGrid(array $grid): UploadedFile
    {
        $spreadsheet = new Spreadsheet();
        $spreadsheet->getActiveSheet()->fromArray($grid, null, 'A1');

        $path = tempnam(sys_get_temp_dir(), 'kartu') . '.xlsx';
        (new Xlsx($spreadsheet))->save($path);
        $spreadsheet->disconnectWorksheets();

        return new UploadedFile(
            $path,
            'kartu-kendali.xlsx',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            null,
            true
        );
    }

    private function import(array $grid, string $tanggal): TestResponse
    {
        return $this->post(route('computers.store-check-import'), [
            'file' => $this->fileFromGrid($grid),
            'tanggal' => $tanggal,
        ]);
    }

    /**
     * Baca isi file xlsx dari response unduhan.
     */
    private function rows(TestResponse $response): array
    {
        $path = $response->baseResponse->getFile()->getPathname();

        return IOFactory::load($path)->getActiveSheet()->toArray(null, true, false, false);
    }

    public function test_import_parses_preamble_and_two_row_header_with_backdating(): void
    {
        $admin = $this->admin();
        $this->seedComputers();

        // Meniru file asli: blok judul (Nama Lab, Periode), header "Fungsi"
        // yang di-merge di atas sub-judul "Baik"/"Tidak".
        $grid = [
            ['Nama Lab', 'SB.1.02 Lab. Komputer 1'],
            ['Periode', 'Gasal 2022-2023'],
            ['No', 'Kode Komputer', 'Fungsi', '', 'Keterangan', 'PJ'],
            ['', '', 'Baik', 'Tidak', '', ''],
            [1, 'K1-001', '✓', '', 'Kondisi baik', ''],
            [2, 'K1-002', '', 'v', 'Layar retak', ''],
        ];

        $this->actingAs($admin)
            ->post(route('computers.store-check-import'), [
                'file' => $this->fileFromGrid($grid),
                'tanggal' => '2022-09-15',
            ])
            ->assertRedirect(route('computers.check-import'))
            ->assertSessionHas('success', fn ($message) => str_contains($message, 'Tahun Ajaran 2022/2023')
                && str_contains($message, 'dibuat otomatis'));

        $this->assertSame(2, ComputerCheck::count());

        // "Baik" → status Baik, tanggal historis dipakai sebagai created_at.
        $first = ComputerCheck::whereRelation('computer', 'code', 'K1-001')->first();
        $this->assertSame('Baik', $first->overall_status);
        $this->assertSame('Kondisi baik', $first->notes);
        $this->assertSame('2022-09-15 08:00', $first->created_at->format('Y-m-d H:i'));

        // "Tidak" → status Perlu Perbaikan.
        $second = ComputerCheck::whereRelation('computer', 'code', 'K1-002')->first();
        $this->assertSame('Perlu Perbaikan', $second->overall_status);
        $this->assertSame('Layar retak', $second->notes);

        // Tahun ajaran dibuat otomatis dari tanggal dan ditautkan ke semua baris.
        $this->assertSame(1, AcademicYear::count());
        $year = AcademicYear::first();
        $this->assertSame('Tahun Ajaran 2022/2023', $year->name);
        $this->assertSame('Non Aktif', $year->status);
        $this->assertSame($year->id, $first->academic_year_id);
        $this->assertSame($year->id, $second->academic_year_id);
    }

    public function test_import_matches_pj_to_user_and_reports_unmatched(): void
    {
        $admin = $this->admin();
        $this->seedComputers();
        $firmansyah = User::create(['name' => 'Firmansyah', 'email' => 'firman@uji.test', 'password' => 'x']);

        $grid = [
            ['Kode Komputer', 'Baik', 'Tidak', 'Keterangan', 'PJ'],
            ['K1-001', '✓', '', '', 'Firmansyah'],
            ['K1-002', '', '✓', '', 'Tidak Ada'],
        ];

        $response = $this->actingAs($admin)
            ->post(route('computers.store-check-import'), [
                'file' => $this->fileFromGrid($grid),
                'tanggal' => '2023-02-10',
            ])
            ->assertRedirect(route('computers.check-import'));

        $response->assertSessionHas('success', fn ($message) => str_contains($message, 'Tidak Ada')
            && str_contains($message, 'System'));

        $matched = ComputerCheck::whereRelation('computer', 'code', 'K1-001')->first();
        $this->assertSame($firmansyah->id, $matched->checked_by);

        $unmatched = ComputerCheck::whereRelation('computer', 'code', 'K1-002')->first();
        $this->assertNull($unmatched->checked_by);
    }

    public function test_import_reports_bad_rows_without_aborting_the_file(): void
    {
        $admin = $this->admin();
        $this->seedComputers();

        $grid = [
            ['Kode Komputer', 'Baik', 'Tidak'],
            ['SB-999', '✓', ''],
            ['K1-001', '', ''],
            ['K1-002', '✓', '✓'],
            ['K1-003', true, ''],
        ];

        $response = $this->actingAs($admin)
            ->post(route('computers.store-check-import'), [
                'file' => $this->fileFromGrid($grid),
                'tanggal' => '2022-09-15',
            ])
            ->assertRedirect(route('computers.check-import'));

        $response->assertSessionHas('import_errors', function ($errors) {
            return count($errors) === 3
                && str_contains($errors[0], 'SB-999')
                && str_contains($errors[0], 'tidak ditemukan')
                && str_contains($errors[1], 'tidak dicentang')
                && str_contains($errors[2], 'sama-sama terisi');
        });

        // Baris valid (dengan sel checkbox boolean TRUE) tetap diproses.
        $this->assertSame(1, ComputerCheck::count());
        $check = ComputerCheck::whereRelation('computer', 'code', 'K1-003')->first();
        $this->assertSame('Baik', $check->overall_status);
    }

    public function test_import_updates_instead_of_duplicating_for_same_date(): void
    {
        $admin = $this->admin();
        $this->seedComputers();

        $grid = [
            ['Kode Komputer', 'Baik', 'Tidak', 'Keterangan'],
            ['K1-001', '✓', '', 'Pertama kali'],
        ];
        $this->actingAs($admin)
            ->post(route('computers.store-check-import'), [
                'file' => $this->fileFromGrid($grid),
                'tanggal' => '2022-10-01',
            ])
            ->assertSessionHas('success', fn ($message) => str_contains($message, '1 pengecekan baru'));

        $this->assertSame(1, ComputerCheck::count());

        $grid[1] = ['K1-001', '', '✓', 'Diperbarui'];
        $this->actingAs($admin)
            ->post(route('computers.store-check-import'), [
                'file' => $this->fileFromGrid($grid),
                'tanggal' => '2022-10-01',
            ])
            ->assertSessionHas('success', fn ($message) => str_contains($message, '1 diperbarui'));

        $this->assertSame(1, ComputerCheck::count());
        $check = ComputerCheck::first();
        $this->assertSame('Perlu Perbaikan', $check->overall_status);
        $this->assertSame('Diperbarui', $check->notes);
    }

    public function test_import_page_template_and_permissions(): void
    {
        $admin = $this->admin();
        $this->seedComputers();

        $this->actingAs($admin)
            ->get(route('computers.check-import'))
            ->assertOk()
            ->assertSee('Import Kartu Kendali dari Excel')
            ->assertSee(route('computers.check-template'));

        // Menu import kartu kendali tampil di halaman daftar komputer.
        $this->actingAs($admin)
            ->get(route('computers.index'))
            ->assertOk()
            ->assertSee('Import Kartu Kendali');

        $rows = $this->rows($this->actingAs($admin)->get(route('computers.check-template')));
        $this->assertSame(['No', 'Kode Komputer', 'Baik', 'Tidak', 'Keterangan', 'PJ'], $rows[0]);
        $this->assertSame('Contoh', $rows[1][1]);

        // Template yang diunggah apa adanya tidak membuat data pengecekan.
        $download = $this->actingAs($admin)->get(route('computers.check-template'));
        $upload = new UploadedFile(
            $download->baseResponse->getFile()->getPathname(),
            'template-kartu-kendali.xlsx',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            null,
            true
        );
        $this->actingAs($admin)
            ->post(route('computers.store-check-import'), [
                'file' => $upload,
                'tanggal' => '2022-09-15',
            ])
            ->assertRedirect(route('computers.check-import'));
        $this->assertSame(0, ComputerCheck::count());
    }

    public function test_check_import_requires_edit_computers_and_template_requires_view(): void
    {
        $outsider = User::create(['name' => 'Orang Luar', 'email' => 'luar-kartu@e.test', 'password' => 'x']);

        $this->actingAs($outsider)->get(route('computers.check-import'))->assertForbidden();
        $this->actingAs($outsider)
            ->post(route('computers.store-check-import'), [
                'file' => $this->fileFromGrid([['Kode Komputer'], ['K1-001']]),
                'tanggal' => '2022-09-15',
            ])
            ->assertForbidden();
        $this->actingAs($outsider)->get(route('computers.check-template'))->assertForbidden();
    }

    public function test_imported_checks_appear_on_card_and_follow_year_filter(): void
    {
        $admin = $this->admin();
        $this->seedComputers();

        $grid = [
            ['Kode Komputer', 'Baik', 'Tidak', 'Keterangan'],
            ['K1-001', '✓', '', 'Semua fungsi normal'],
        ];
        $this->actingAs($admin)
            ->post(route('computers.store-check-import'), [
                'file' => $this->fileFromGrid($grid),
                'tanggal' => '2022-09-15',
            ])
            ->assertSessionHas('success');

        $computer = Computer::where('code', 'K1-001')->first();

        $this->actingAs($admin)
            ->get(route('computers.card', $computer))
            ->assertOk()
            ->assertSee('Semua fungsi normal')
            ->assertSee('15 Sep 2022 08:00')
            ->assertSee('Tahun Ajaran 2022/2023')
            ->assertSee('oleh System');

        // Filter tahun mengikuti created_at hasil backdating.
        $this->actingAs($admin)
            ->get(route('computers.card', ['computer' => $computer->id, 'year' => 2022]))
            ->assertOk()
            ->assertSee('Semua fungsi normal');

        $this->actingAs($admin)
            ->get(route('computers.card', ['computer' => $computer->id, 'year' => 2021]))
            ->assertOk()
            ->assertDontSee('Semua fungsi normal');
    }
}
