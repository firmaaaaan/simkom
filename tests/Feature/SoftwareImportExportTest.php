<?php

namespace Tests\Feature;

use App\Models\Computer;
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

class SoftwareImportExportTest extends TestCase
{
    use RefreshDatabase;

    private const SOFTWARE_HEADINGS = [
        'Kode', 'Nama', 'Versi', 'Kategori', 'Jenis Lisensi',
        'Penanggung Jawab', 'Email PJ', 'Status', 'Keterangan',
        'Penyedia', 'Link Download',
    ];

    private const LICENSE_HEADINGS = [
        'Kode Software', 'Label', 'Product Key', 'PC',
        'Tanggal Beli', 'Tanggal Expired', 'Catatan', 'Status',
    ];

    private function admin(bool $withKeyPermission = true): User
    {
        $role = Role::firstOrCreate(['name' => 'role-'.($withKeyPermission ? 'key' : 'nokey')], ['label' => 'Role Uji']);

        $permissions = ['view-software', 'create-software', 'edit-software', 'export-software', 'import-software'];

        if ($withKeyPermission) {
            $permissions[] = 'view-license-key';
        }

        foreach ($permissions as $name) {
            $role->permissions()->syncWithoutDetaching([
                Permission::firstOrCreate(['name' => $name], ['label' => $name])->id,
            ]);
        }

        $user = User::create([
            'name' => 'Admin Uji',
            'email' => 'admin-'.($withKeyPermission ? 'key' : 'nokey').'@software.test',
            'password' => 'rahasia123',
        ]);
        $user->roles()->syncWithoutDetaching([$role->id]);

        return $user;
    }

    private function seedSoftware(): array
    {
        $computer = Computer::create(['code' => 'PC-01', 'status' => 'Aktif']);

        $software = Software::create([
            'code' => 'SW-001',
            'name' => 'Microsoft Office 2021',
            'version' => '2021',
            'category' => 'Office Suite',
            'license_type' => 'Volume License',
            'provider' => 'PT Distribusi Software Nusantara',
            'download_link' => 'https://contoh.ac.id/download/office',
            'pic_name' => 'Budi',
            'pic_email' => 'budi@kampus.ac.id',
            'status' => 'Aktif',
        ]);

        $software->licenses()->create([
            'label' => 'Lisensi 1',
            'license_key' => 'AAAA-BBBB-CCCC',
            'assigned_computer_id' => $computer->id,
            'purchase_date' => '2025-01-01',
            'expiry_date' => '2027-01-01',
        ]);

        return compact('computer', 'software');
    }

    private function download(TestResponse $response): array
    {
        $path = $response->baseResponse->getFile()->getPathname();
        $spreadsheet = IOFactory::load($path);

        $software = $spreadsheet->getSheetByName('Software')?->toArray(null, true, false, false);
        $licenses = $spreadsheet->getSheetByName('Lisensi')?->toArray(null, true, false, false);

        $spreadsheet->disconnectWorksheets();

        return ['software' => $software, 'licenses' => $licenses];
    }

    private function uploadableWorkbook(array $softwareRows = [], array $licenseRows = []): UploadedFile
    {
        $spreadsheet = new Spreadsheet();

        $softwareSheet = $spreadsheet->getActiveSheet();
        $softwareSheet->setTitle('Software');
        $softwareSheet->fromArray(array_merge([self::SOFTWARE_HEADINGS], $softwareRows), null, 'A1');

        $licenseSheet = $spreadsheet->createSheet();
        $licenseSheet->setTitle('Lisensi');
        $licenseSheet->fromArray(array_merge([self::LICENSE_HEADINGS], $licenseRows), null, 'A1');

        $path = tempnam(sys_get_temp_dir(), 'software') . '.xlsx';
        (new Xlsx($spreadsheet))->save($path);
        $spreadsheet->disconnectWorksheets();

        return new UploadedFile(
            $path,
            'software.xlsx',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            null,
            true
        );
    }

    public function test_export_contains_software_and_license_sheets_with_plaintext_key(): void
    {
        $this->seedSoftware();

        $response = $this->actingAs($this->admin())->get(route('software.export'));

        $response->assertOk();
        $response->assertDownload('software.xlsx');

        $sheets = $this->download($response);

        $this->assertNotNull($sheets['software']);
        $this->assertNotNull($sheets['licenses']);
        $this->assertSame(self::SOFTWARE_HEADINGS, $sheets['software'][0]);
        $this->assertSame(self::LICENSE_HEADINGS, $sheets['licenses'][0]);

        $softwareRow = $sheets['software'][1];
        $this->assertSame('SW-001', $softwareRow[0]);
        $this->assertSame('PT Distribusi Software Nusantara', $softwareRow[9]);
        $this->assertSame('https://contoh.ac.id/download/office', $softwareRow[10]);

        $licenseRow = $sheets['licenses'][1];
        $this->assertSame('SW-001', $licenseRow[0]);
        $this->assertSame('AAAA-BBBB-CCCC', $licenseRow[2]);
        $this->assertSame('PC-01', $licenseRow[3]);
        $this->assertSame('2025-01-01', $licenseRow[4]);
    }

    public function test_export_masks_key_without_license_key_permission(): void
    {
        $this->seedSoftware();

        $response = $this->actingAs($this->admin(false))->get(route('software.export'));

        $response->assertOk();

        $licenseRow = $this->download($response)['licenses'][1];

        $this->assertStringContainsString('•', (string) $licenseRow[2]);
        $this->assertNotSame('AAAA-BBBB-CCCC', $licenseRow[2]);
    }

    public function test_template_contains_only_headings(): void
    {
        $response = $this->actingAs($this->admin())->get(route('software.template'));

        $response->assertOk();
        $response->assertDownload('template_software.xlsx');

        $sheets = $this->download($response);

        $this->assertSame([self::SOFTWARE_HEADINGS], $sheets['software']);
        $this->assertSame([self::LICENSE_HEADINGS], $sheets['licenses']);
    }

    public function test_import_creates_software_and_licenses(): void
    {
        $computer = Computer::create(['code' => 'PC-01', 'status' => 'Aktif']);

        $file = $this->uploadableWorkbook(
            [
                ['SW-100', 'AutoCAD', '2024', 'Design', 'Subscription', 'Sari', 'sari@kampus.ac.id', 'Aktif', 'Lisensi pendidikan', 'PT Media Kreatif', 'https://contoh.ac.id/download/autocad'],
            ],
            [
                ['SW-100', 'Lisensi 1', 'KEY1-KEY1-KEY1', 'PC-01', '2025-02-01', '2027-02-01', 'Catatan', 'Aktif'],
                ['SW-100', 'Lisensi 2', 'KEY2-KEY2-KEY2', '', '', '', '', ''],
            ]
        );

        $response = $this->actingAs($this->admin())->post(route('software.store-import'), ['file' => $file]);

        $response->assertRedirect(route('software.index'));

        $software = Software::where('code', 'SW-100')->firstOrFail();
        $this->assertSame('AutoCAD', $software->name);
        $this->assertSame('Sari', $software->pic_name);
        $this->assertSame('sari@kampus.ac.id', $software->pic_email);
        $this->assertSame('PT Media Kreatif', $software->provider);
        $this->assertSame('https://contoh.ac.id/download/autocad', $software->download_link);
        $this->assertCount(2, $software->licenses);
        $this->assertDatabaseHas('software_licenses', ['software_id' => $software->id, 'license_key' => 'KEY1-KEY1-KEY1']);
        $this->assertDatabaseHas('computer_software', ['computer_id' => $computer->id, 'software_id' => $software->id]);
    }

    public function test_import_syncs_existing_licenses(): void
    {
        $seeded = $this->seedSoftware();
        $software = $seeded['software'];

        $software->licenses()->create(['label' => 'Lisensi 2', 'license_key' => 'OLD-OLD-OLD']);

        $file = $this->uploadableWorkbook(
            [
                ['SW-001', 'Microsoft Office 2021', '2021', 'Office Suite', 'Volume License', 'Budi', 'budi@kampus.ac.id', 'Aktif', '', '', ''],
            ],
            [
                ['SW-001', 'Lisensi 1', 'NEW-AAA-BBB', 'PC-01', '2025-01-01', '2027-01-01', '', ''],
                ['SW-001', 'Lisensi 3', 'NEW-CCC-DDD', '', '', '', '', ''],
            ]
        );

        $this->actingAs($this->admin())->post(route('software.store-import'), ['file' => $file]);

        $software->refresh();

        $this->assertCount(2, $software->licenses);
        $this->assertDatabaseHas('software_licenses', ['label' => 'Lisensi 1', 'license_key' => 'NEW-AAA-BBB']);
        $this->assertDatabaseHas('software_licenses', ['label' => 'Lisensi 3', 'license_key' => 'NEW-CCC-DDD']);
        $this->assertDatabaseMissing('software_licenses', ['license_key' => 'OLD-OLD-OLD']);
    }

    public function test_import_reports_unknown_computer_but_keeps_software(): void
    {
        $file = $this->uploadableWorkbook(
            [
                ['SW-200', 'Matlab', '2024', 'Utilities', 'Subscription', '', '', 'Aktif', '', '', ''],
            ],
            [
                ['SW-200', 'Lisensi 1', 'KEY-KEY-KEY', 'PC-TIDAK-ADA', '', '', '', ''],
            ]
        );

        $admin = $this->admin();
        $response = $this->actingAs($admin)->post(route('software.store-import'), ['file' => $file]);

        $response->assertRedirect(route('software.index'));
        $response->assertSessionHas('import_errors');

        $this->actingAs($admin)
            ->get(route('software.index'))
            ->assertOk()
            ->assertSee('baris saat import bermasalah', false)
            ->assertSee('PC-TIDAK-ADA', false)
            ->assertSee('tidak ditemukan', false);

        $this->assertDatabaseHas('software', ['code' => 'SW-200']);
        $this->assertDatabaseCount('software_licenses', 0);
    }

    public function test_masked_key_does_not_overwrite_existing_key(): void
    {
        $seeded = $this->seedSoftware();

        $file = $this->uploadableWorkbook(
            [
                ['SW-001', 'Microsoft Office 2021', '2021', 'Office Suite', 'Volume License', 'Budi', 'budi@kampus.ac.id', 'Aktif', '', '', ''],
            ],
            [
                ['SW-001', 'Lisensi 1', 'AAAA••••CCCC', 'PC-01', '', '', '', ''],
            ]
        );

        $this->actingAs($this->admin())->post(route('software.store-import'), ['file' => $file]);

        $this->assertDatabaseHas('software_licenses', [
            'software_id' => $seeded['software']->id,
            'label' => 'Lisensi 1',
            'license_key' => 'AAAA-BBBB-CCCC',
        ]);
    }

    public function test_round_trip_export_then_import_keeps_license_data(): void
    {
        $this->seedSoftware();
        $admin = $this->admin();

        $export = $this->actingAs($admin)->get(route('software.export'));
        $export->assertOk();

        $path = $export->baseResponse->getFile()->getPathname();
        $copy = tempnam(sys_get_temp_dir(), 'roundtrip') . '.xlsx';
        copy($path, $copy);

        $file = new UploadedFile(
            $copy,
            'software.xlsx',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            null,
            true
        );

        $this->actingAs($admin)->post(route('software.store-import'), ['file' => $file]);

        $this->assertDatabaseHas('software_licenses', [
            'label' => 'Lisensi 1',
            'license_key' => 'AAAA-BBBB-CCCC',
            'assigned_computer_id' => Computer::where('code', 'PC-01')->value('id'),
        ]);
        $this->assertDatabaseCount('software', 1);
        $this->assertDatabaseCount('software_licenses', 1);
    }
}
