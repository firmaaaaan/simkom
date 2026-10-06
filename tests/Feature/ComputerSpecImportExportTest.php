<?php

namespace Tests\Feature;

use App\Models\Computer;
use App\Models\Hardware;
use App\Models\Laboratory;
use App\Models\Permission;
use App\Models\Role;
use App\Models\Software;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Testing\TestResponse;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Tests\TestCase;

class ComputerSpecImportExportTest extends TestCase
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
            'name' => 'Admin Spesifikasi',
            'email' => 'admin-spek@uji.test',
            'password' => 'rahasia123',
        ]);
        $user->roles()->attach($role);

        return $user;
    }

    private function seedData(): array
    {
        $lab = Laboratory::create([
            'name' => 'Lab Komputer 1', 'code' => 'LK1', 'location' => 'Gedung A', 'capacity' => 40,
        ]);
        $computer = Computer::create(['code' => 'K1-001', 'laboratory_id' => $lab->id, 'status' => 'Aktif']);
        $emptyComputer = Computer::create(['code' => 'K1-003', 'laboratory_id' => $lab->id, 'status' => 'Aktif']);

        $processor = Hardware::create(['code' => 'HW-001', 'name' => 'Intel Core i5-12400', 'category' => 'Processor']);
        $ram = Hardware::create(['code' => 'HW-002', 'name' => '8GB DDR4', 'category' => 'RAM']);
        $computer->hardware()->attach([$processor->id, $ram->id]);

        $software = Software::create(['code' => 'SW-001', 'name' => 'AutoCAD', 'category' => 'Design', 'status' => 'Aktif']);
        $computer->software()->attach($software->id);

        return compact('lab', 'computer', 'emptyComputer', 'processor', 'ram', 'software');
    }

    /**
     * Susun file xlsx dari judul + baris data untuk diunggah saat import.
     */
    private function uploadableFile(array $headings, array $rows): UploadedFile
    {
        $spreadsheet = new Spreadsheet();
        $spreadsheet->getActiveSheet()->fromArray(array_merge([$headings], $rows), null, 'A1');

        $path = tempnam(sys_get_temp_dir(), 'spek') . '.xlsx';
        (new Xlsx($spreadsheet))->save($path);
        $spreadsheet->disconnectWorksheets();

        return new UploadedFile(
            $path,
            'spesifikasi.xlsx',
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
     * Struktur judul kolom export/template spesifikasi: identitas komputer,
     * seluruh kategori form hardware, kategori ekstra dari data, Keterangan.
     */
    private function expectedHeadings(array $extraCategories = []): array
    {
        return array_merge(
            ['Kode Komputer', 'Laboratorium'],
            Hardware::CATEGORIES,
            $extraCategories,
            ['Keterangan'],
        );
    }

    public function test_spec_export_shows_one_row_per_computer_with_category_columns(): void
    {
        $admin = $this->admin();
        $this->seedData();

        $rows = $this->rows($this->actingAs($admin)->get(route('computers.spec-export')));

        // Seluruh 14 kategori form ikut tampil, walaupun belum ada datanya.
        $this->assertSame($this->expectedHeadings(), $rows[0]);
        $this->assertCount(2 + count(Hardware::CATEGORIES) + 1, $rows[0]);

        $byCode = collect($rows)->slice(1)->keyBy(0);
        $this->assertCount(2, $byCode);
        $this->assertSame('Lab Komputer 1', $byCode['K1-001'][1]);
        $this->assertSame('Intel Core i5-12400', $byCode['K1-001'][2]);
        $this->assertSame('8GB DDR4', $byCode['K1-001'][3]);
        // Komputer tanpa spesifikasi tetap tampil dengan sel kosong.
        $this->assertSame('K1-003', $byCode['K1-003'][0]);
        $this->assertEmpty($byCode['K1-003'][2]);
    }

    public function test_spec_export_appends_categories_outside_the_form_list(): void
    {
        $admin = $this->admin();
        $data = $this->seedData();
        $speaker = Hardware::create(['code' => 'HW-009', 'name' => 'Logitech Z120', 'category' => 'Speaker']);
        $data['computer']->hardware()->attach($speaker->id);

        $rows = $this->rows($this->actingAs($admin)->get(route('computers.spec-export')));

        // Kategori data yang tidak ada di form tetap diekspor (setelah daftar
        // form, sebelum Keterangan) agar round-trip tidak kehilangan data.
        $this->assertSame($this->expectedHeadings(['Speaker']), $rows[0]);

        $speakerColumn = array_search('Speaker', $rows[0], true);
        $byCode = collect($rows)->slice(1)->keyBy(0);
        $this->assertSame('Logitech Z120', $byCode['K1-001'][$speakerColumn]);
    }

    public function test_spec_template_lists_every_form_category(): void
    {
        $admin = $this->admin();

        $rows = $this->rows($this->actingAs($admin)->get(route('computers.spec-template')));

        $this->assertSame($this->expectedHeadings(), $rows[0]);
        $this->assertSame('Contoh', $rows[1][0]);
        $this->assertNotEmpty($rows[1][array_search('Processor', $rows[0], true)]);
    }

    public function test_spec_export_joins_multiple_parts_and_follows_filters(): void
    {
        $admin = $this->admin();
        $data = $this->seedData();
        $monitor = Hardware::create(['code' => 'HW-003', 'name' => 'LG 24 Inch', 'category' => 'Monitor']);
        $monitor2 = Hardware::create(['code' => 'HW-004', 'name' => 'Samsung 22 Inch', 'category' => 'Monitor']);
        $data['computer']->hardware()->attach([$monitor->id, $monitor2->id]);

        $rows = $this->rows($this->actingAs($admin)->get(route('computers.spec-export')));
        $this->assertSame($this->expectedHeadings(), $rows[0]);

        $monitorColumn = array_search('Monitor', $rows[0], true);
        $this->assertSame(
            'LG 24 Inch; Samsung 22 Inch',
            collect($rows)->slice(1)->keyBy(0)['K1-001'][$monitorColumn]
        );

        $filtered = $this->rows($this->actingAs($admin)->get(route('computers.spec-export', ['search' => 'K1-001'])));
        $this->assertCount(2, $filtered);

        $none = $this->rows($this->actingAs($admin)->get(route('computers.spec-export', ['search' => 'TIDAK-ADA'])));
        $this->assertCount(1, $none);
    }

    public function test_spec_export_and_template_require_view_computers(): void
    {
        $outsider = User::create(['name' => 'Orang Luar', 'email' => 'luar-spek@e.test', 'password' => 'x']);
        $this->seedData();

        $this->actingAs($outsider)->get(route('computers.spec-export'))->assertForbidden();
        $this->actingAs($outsider)->get(route('computers.spec-template'))->assertForbidden();
    }

    public function test_spec_import_replaces_hardware_and_creates_missing_records(): void
    {
        $admin = $this->admin();
        $data = $this->seedData();

        $file = $this->uploadableFile(
            ['Kode Komputer', 'Laboratorium', 'Processor', 'RAM', 'Storage'],
            [
                ['K1-001', 'Lab Komputer 1', 'Intel Core i5-12400', '16GB DDR4', 'SSD 512GB; HDD 1TB'],
                ['K1-002', 'Lab Tidak Ada', 'Ryzen 5 5600', '', ''],
            ]
        );

        $this->actingAs($admin)
            ->post(route('computers.store-spec-import'), ['file' => $file])
            ->assertRedirect(route('computers.index'))
            ->assertSessionHas('success');

        $data['computer']->refresh();
        $names = $data['computer']->hardware->pluck('name');

        // Processor lama dipakai ulang, RAM lama diganti, storage baru dibuat.
        $this->assertEqualsCanonicalizing(
            ['Intel Core i5-12400', '16GB DDR4', 'SSD 512GB', 'HDD 1TB'],
            $names->all()
        );
        // 2 hardware awal + 4 hardware baru (RAM, 2 storage, processor komputer kedua).
        $this->assertSame(6, Hardware::count());

        // Komputer baru dibuat dengan status aktif, laboratorium tidak dipaksa.
        $created = Computer::where('code', 'K1-002')->first();
        $this->assertNotNull($created);
        $this->assertSame('Aktif', $created->status);
        $this->assertNull($created->laboratory_id);
        $this->assertEqualsCanonicalizing(
            ['Ryzen 5 5600'],
            $created->hardware->pluck('name')->all()
        );

        // Software komputer tidak disentuh oleh import.
        $this->assertEqualsCanonicalizing(
            ['AutoCAD'],
            $data['computer']->refresh()->software->pluck('name')->all()
        );
    }

    public function test_spec_import_keeps_specs_when_row_is_completely_empty(): void
    {
        $admin = $this->admin();
        $data = $this->seedData();

        $file = $this->uploadableFile(
            ['Kode Komputer', 'Processor', 'RAM'],
            [['K1-001', '', '']]
        );

        $this->actingAs($admin)->post(route('computers.store-spec-import'), ['file' => $file]);

        $this->assertEqualsCanonicalizing(
            ['Intel Core i5-12400', '8GB DDR4'],
            $data['computer']->refresh()->hardware->pluck('name')->all()
        );
    }

    public function test_spec_import_reuses_hardware_case_insensitively(): void
    {
        $admin = $this->admin();
        $this->seedData();

        $file = $this->uploadableFile(
            ['Kode Komputer', 'processor', 'ram'],
            [['K1-001', 'intel core i5-12400', '8GB DDR4']]
        );

        $this->actingAs($admin)->post(route('computers.store-spec-import'), ['file' => $file]);

        $this->assertSame(1, Hardware::where('category', 'Processor')->count());
        $this->assertSame(1, Hardware::where('category', 'RAM')->count());
        $this->assertSame(2, Hardware::count());
    }

    public function test_spec_import_reports_bad_rows_without_aborting_the_file(): void
    {
        $admin = $this->admin();
        $this->seedData();

        $file = $this->uploadableFile(
            ['Kode Komputer', 'Processor'],
            [
                ['', 'Tanpa Kode'],
                ['K1-001', 'Intel Core i5-12400'],
            ]
        );

        $response = $this->actingAs($admin)
            ->post(route('computers.store-spec-import'), ['file' => $file]);

        $response->assertRedirect(route('computers.index'));
        $response->assertSessionHas('import_errors', function ($errors) {
            return count($errors) === 1
                && str_contains($errors[0], 'kode komputer kosong');
        });

        // Baris valid tetap diproses.
        $this->assertSame(
            ['Intel Core i5-12400'],
            Computer::where('code', 'K1-001')->first()->hardware->pluck('name')->all()
        );
    }

    public function test_spec_import_skips_the_template_sample_row(): void
    {
        $admin = $this->admin();

        $file = $this->uploadableFile(
            ['Kode Komputer', 'Laboratorium', 'Processor', 'RAM', 'Storage', 'Monitor'],
            [['Contoh', 'Lab Komputer 1', 'Intel Core i5-12400', '8GB DDR4', 'SSD 512GB', '24 Inch']]
        );

        $this->actingAs($admin)->post(route('computers.store-spec-import'), ['file' => $file]);

        $this->assertNull(Computer::where('code', 'Contoh')->first());
        $this->assertSame(0, Hardware::count());
    }

    public function test_spec_import_page_renders_for_authorized_user(): void
    {
        $admin = $this->admin();
        $this->seedData();

        $this->actingAs($admin)
            ->get(route('computers.spec-import'))
            ->assertOk()
            ->assertSee('Import Spesifikasi dari Excel')
            ->assertSee(route('computers.spec-template'));

        // Menu export & import spesifikasi tampil di halaman daftar komputer.
        $this->actingAs($admin)
            ->get(route('computers.index'))
            ->assertOk()
            ->assertSee('Export Spesifikasi')
            ->assertSee('Import Spesifikasi');
    }

    public function test_spec_import_requires_edit_computers(): void
    {
        $outsider = User::create(['name' => 'Orang Luar 2', 'email' => 'luar-spek2@e.test', 'password' => 'x']);
        $this->seedData();

        $this->actingAs($outsider)->get(route('computers.spec-import'))->assertForbidden();
        $this->actingAs($outsider)
            ->post(route('computers.store-spec-import'), ['file' => $this->uploadableFile(['Kode Komputer'], [['K1-001']])])
            ->assertForbidden();
    }

    public function test_export_then_import_keeps_the_data_unchanged(): void
    {
        $admin = $this->admin();
        $data = $this->seedData();
        $monitor = Hardware::create(['code' => 'HW-003', 'name' => 'LG 24 Inch', 'category' => 'Monitor']);
        $data['computer']->hardware()->attach($monitor->id);

        $before = $data['computer']->refresh()->hardware->pluck('name')->sort()->values()->all();

        $download = $this->actingAs($admin)->get(route('computers.spec-export'));
        $download->assertOk()->assertDownload();

        $path = $download->baseResponse->getFile()->getPathname();
        $upload = new UploadedFile(
            $path,
            'spesifikasi.xlsx',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            null,
            true
        );

        $this->actingAs($admin)
            ->post(route('computers.store-spec-import'), ['file' => $upload])
            ->assertRedirect(route('computers.index'));

        $after = $data['computer']->refresh()->hardware->pluck('name')->sort()->values()->all();

        $this->assertSame($before, $after);
        $this->assertSame(3, Hardware::count());
    }
}
