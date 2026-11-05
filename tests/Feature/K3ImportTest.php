<?php

namespace Tests\Feature;

use App\Models\AccidentReport;
use App\Models\AparInspection;
use App\Models\DamageReport;
use App\Models\HseInspection;
use App\Models\IncidentReport;
use App\Models\JobSafetyAnalysis;
use App\Models\K3Document;
use App\Models\K3InspectionReport;
use App\Models\OccupationalDiseaseInvestigation;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Tests\TestCase;

class K3ImportTest extends TestCase
{
    use RefreshDatabase;

    private const TEMPLATE_ROUTES = [
        'incident-reports.template',
        'apar-inspections.template',
        'hse-inspections.template',
        'k3-inspection-reports.template',
        'k3-documents.template',
        'damage-reports.template',
        'occupational-disease-investigations.template',
        'berita-acara-kecelakaan.template',
        'analisis-keselamatan-pekerjaan.template',
    ];

    private function userWith(array $permissions, string $email): User
    {
        $role = Role::firstOrCreate(['name' => 'role-'.md5($email)], ['label' => 'Role Uji']);
        foreach ($permissions as $name) {
            $permission = Permission::firstOrCreate(['name' => $name], ['label' => $name]);
            $role->permissions()->syncWithoutDetaching([$permission->id]);
        }

        $user = User::firstOrCreate(
            ['email' => $email],
            ['name' => 'User Import', 'password' => 'rahasia123']
        );
        $user->roles()->syncWithoutDetaching([$role->id]);

        return $user;
    }

    /** Buat file xlsx dari array baris (baris pertama = heading). */
    private function makeExcel(array $rows, string $sheetName = 'Sheet1', array $sheetRows = []): UploadedFile
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle($sheetName);
        $sheet->fromArray($rows, null, 'A1');

        foreach ($sheetRows as $name => $data) {
            $extra = $spreadsheet->createSheet();
            $extra->setTitle($name);
            $extra->fromArray($data, null, 'A1');
        }

        $path = tempnam(sys_get_temp_dir(), 'k3import').'.xlsx';
        (new Xlsx($spreadsheet))->save($path);

        return new UploadedFile($path, 'import.xlsx', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', null, true);
    }

    public function test_all_template_routes_download_excel(): void
    {
        $user = $this->userWith([], 'template-all@uji.test');

        // Beri seluruh permission import-* untuk semua modul.
        $role = $user->roles->first();
        foreach ([
            'incident-reports', 'apar-inspections', 'hse-inspections', 'k3-inspection-reports',
            'k3-documents', 'damage-reports', 'occupational-disease-investigations',
            'accident-reports', 'jsa',
        ] as $module) {
            $permission = Permission::firstOrCreate(['name' => "import-{$module}"], ['label' => "Import {$module}"]);
            $role->permissions()->syncWithoutDetaching([$permission->id]);
        }

        foreach (self::TEMPLATE_ROUTES as $route) {
            $this->actingAs($user)
                ->get(route($route))
                ->assertOk()
                ->assertDownload();
        }
    }

    public function test_template_contains_contoh_example_row(): void
    {
        $user = $this->userWith(['import-apar-inspections'], 'template-apar@uji.test');

        $response = $this->actingAs($user)->get(route('apar-inspections.template'));
        $path = $response->baseResponse->getFile()->getPathname();
        $rows = IOFactory::load($path)->getActiveSheet()->toArray(null, true, false, false);

        $this->assertSame('Kode', $rows[0][0]);
        $this->assertSame('Contoh', $rows[1][0]);
        // 5 kolom dasar + 7 item x 2 kolom
        $this->assertCount(19, $rows[0]);
    }

    public function test_import_apar_inspections(): void
    {
        $user = $this->userWith(['import-apar-inspections'], 'import-apar@uji.test');

        $rows = [
            ['Kode', 'No. Registrasi', 'Tanggal Inspeksi', 'Lokasi', 'Diperiksa Oleh', 'Kawat Segel', 'Ket. Kawat Segel', 'Pin', 'Ket. Pin', 'Penunjuk Tekanan', 'Ket. Penunjuk Tekanan', 'Kondisi Catridge', 'Ket. Kondisi Catridge', 'Tepung Kimia', 'Ket. Tepung Kimia', 'Kondisi Selang', 'Ket. Kondisi Selang', 'Kondisi Botol', 'Ket. Kondisi Botol'],
            ['Contoh', 'APAR-XX-01', '2026-11-01', 'Lab Contoh', 'Petugas', 'Baik', '', 'Baik', '', 'Baik', '', 'Baik', '', 'Baik', '', 'Baik', '', 'Baik', ''],
            ['', 'APAR-LAB1-01', '2026-11-05', 'Lab Komputer 1', 'Petugas K3', 'Baik', '', 'Baik', '', 'Tidak', 'Tekanan rendah', 'Baik', '', 'Baik', '', 'Baik', '', 'Baik', ''],
            ['', 'APAR-LAB2-01', '2026-11-06', 'Lab Jaringan', 'Petugas K3', 'Baik', '', 'Baik', '', 'Baik', '', 'Baik', '', 'Baik', '', 'Baik', '', 'Baik', ''],
        ];

        $this->actingAs($user)
            ->from(route('apar-inspections.import'))
            ->post(route('apar-inspections.store-import'), [
                'file' => $this->makeExcel($rows),
            ])
            ->assertRedirect(route('apar-inspections.index'))
            ->assertSessionHas('success')
            ->assertSessionMissing('import_error');

        $this->assertDatabaseCount('apar_inspections', 2);

        $inspection = AparInspection::where('nomor_registrasi', 'APAR-LAB1-01')->first();
        $this->assertNotNull($inspection);
        $this->assertStringStartsWith('APAR-', $inspection->kode);
        $this->assertSame('tidak', $inspection->items['pressure_gauge']['status']);
        $this->assertSame('Tekanan rendah', $inspection->items['pressure_gauge']['keterangan']);
        $this->assertSame($user->id, $inspection->created_by);
    }

    public function test_import_incident_reports_computes_risk(): void
    {
        $user = $this->userWith(['import-incident-reports'], 'import-insiden@uji.test');

        $rows = [
            ['Kode', 'Tanggal Kejadian', 'Waktu Kejadian', 'Jenis Kejadian', 'Lokasi', 'Nama Korban', 'Jabatan', 'Asal Institusi', 'Sebab', 'Riwayat', 'Saksi', 'Akibat', 'Likelihood (1-5)', 'Impact (1-5)', 'Pelapor'],
            ['', '2026-11-05', '13:45', 'Nearmiss (Nyaris celaka)', 'Lab Komputer 1', 'Budi Santoso', 'Praktikan', 'UNISA', 'Kabel terkelupas', 'Korban hampir tersengat', 'Siti', 'Tidak ada cedera', 3, 2, 'Koordinator K3'],
        ];

        $this->actingAs($user)
            ->post(route('incident-reports.store-import'), ['file' => $this->makeExcel($rows)])
            ->assertRedirect(route('incident-reports.index'))
            ->assertSessionHas('success');

        $report = IncidentReport::first();
        $this->assertNotNull($report);
        $this->assertSame('Nearmiss', $report->telah_terjadi);
        $this->assertSame('13:45', substr($report->waktu_kejadian, 0, 5));
        $this->assertSame(6, $report->risk_score);
        $this->assertSame('Moderate', $report->risk_level);
    }

    public function test_import_accident_reports(): void
    {
        $user = $this->userWith(['import-accident-reports'], 'import-bak@uji.test');

        $rows = [
            ['Kode', 'Tanggal', 'Nama Pelapor', 'Jabatan Pelapor', 'Nama Korban', 'Alamat Korban', 'Tempat Tanggal Lahir', 'Unit/Status', 'Uraian Kejadian 1', 'Uraian Kejadian 2', 'Uraian Kejadian 3', 'Dampak', 'Saksi I', 'Saksi II'],
            ['', '2026-11-05', 'Koordinator K3', 'Koordinator K3 Laboratorium', 'Budi Santoso', 'Jl. Kaliurang', 'Yogyakarta, 12 Mei 2005', 'Praktikan', 'Tersengat listrik.', 'Komputer mati.', '', 'Luka bakar ringan.', 'Siti', 'Ahmad'],
        ];

        $this->actingAs($user)
            ->post(route('berita-acara-kecelakaan.store-import'), ['file' => $this->makeExcel($rows)])
            ->assertRedirect(route('berita-acara-kecelakaan.index'))
            ->assertSessionHas('success');

        $report = AccidentReport::first();
        $this->assertNotNull($report);
        $this->assertSame(['Tersengat listrik.', 'Komputer mati.'], $report->uraian_kejadian);
    }

    public function test_import_jsa_multi_sheet(): void
    {
        $user = $this->userWith(['import-jsa'], 'import-jsa@uji.test');

        $jsaRows = [
            ['Kode', 'Nama Pekerja', 'Nama Pekerjaan', 'Tanggal Pekerjaan', 'Tempat', 'APD (dipisah koma)', 'APD Lain'],
            ['Contoh', 'Contoh Pekerja', 'Pekerjaan Contoh', '2026-11-01', 'Lab Contoh', 'Helm', ''],
            ['JSA-UJI01', 'Andi Pratama', 'Ganti Thermal Paste', '2026-11-05', 'Lab Komputer 1', 'Helm, Sarung Tangan, APD Misterius', 'Masker'],
        ];
        $stepRows = [
            ['Kode JSA', 'Urutan Langkah Pekerjaan', 'Identifikasi Bahaya', 'Pengendalian', 'Penanggung Jawab'],
            ['Contoh', 'Langkah contoh', 'Bahaya contoh', 'Pengendalian contoh', 'PJ contoh'],
            ['JSA-UJI01', 'Matikan PC.', 'Sengatan listrik.', 'Pastikan mati total.', 'Andi'],
            ['JSA-UJI01', 'Bongkar casing.', 'Iritasi debu.', 'Pakai masker.', 'Andi'],
        ];

        $this->actingAs($user)
            ->post(route('analisis-keselamatan-pekerjaan.store-import'), [
                'file' => $this->makeExcel($jsaRows, 'JSA', ['Langkah Pekerjaan' => $stepRows]),
            ])
            ->assertRedirect(route('analisis-keselamatan-pekerjaan.index'))
            ->assertSessionHas('success')
            ->assertSessionHas('import_errors');

        $this->assertDatabaseCount('job_safety_analyses', 1);

        $jsa = JobSafetyAnalysis::where('kode', 'JSA-UJI01')->first();
        $this->assertNotNull($jsa);
        $this->assertSame(['helm' => true, 'sarung_tangan' => true], $jsa->apd);
        $this->assertSame('Masker', $jsa->apd_lain);
        $this->assertCount(2, $jsa->steps);
        $this->assertSame('Matikan PC.', $jsa->steps[0]['langkah']);
    }

    public function test_import_hse_inspections_multi_sheet(): void
    {
        $user = $this->userWith(['import-hse-inspections'], 'import-hse@uji.test');

        $hseRows = [
            ['Kode', 'Lokasi', 'Diperiksa Oleh', 'Tanggal Inspeksi'],
            ['HSE-UJI01', 'Lab Jaringan', 'Petugas K3', '2026-11-05'],
        ];
        $findingRows = [
            ['Kode HSE', 'Lokasi', 'Temuan', 'Unsafe Condition (Ya/Tidak)', 'Unsafe Action (Ya/Tidak)', 'Tanggal Kejadian', 'Tindakan Perbaikan', 'Tanggal Perbaikan', 'PIC'],
            ['HSE-UJI01', 'Lab Jaringan', 'Kabel tidak ber-ground.', 'Ya', 'Tidak', '2026-11-05', 'Ganti kabel.', '2026-11-12', 'Andi'],
        ];

        $this->actingAs($user)
            ->post(route('hse-inspections.store-import'), [
                'file' => $this->makeExcel($hseRows, 'HSE', ['Temuan' => $findingRows]),
            ])
            ->assertRedirect(route('hse-inspections.index'))
            ->assertSessionHas('success');

        $inspection = HseInspection::where('kode', 'HSE-UJI01')->first();
        $this->assertNotNull($inspection);
        $this->assertCount(1, $inspection->findings);
        $this->assertTrue($inspection->findings[0]['unsafe_condition']);
        $this->assertFalse($inspection->findings[0]['unsafe_action']);
    }

    public function test_import_k3_inspection_report_multi_sheet(): void
    {
        $user = $this->userWith(['import-k3-inspection-reports'], 'import-k3insp@uji.test');

        $laporanRows = [
            ['Kode', 'Hari', 'Tanggal BA', 'Bulan BA', 'Tahun BA', 'Bulan Inspeksi', 'Lokasi', 'Pemeriksa Nama', 'Pemeriksa Jabatan'],
            ['K3INSP-UJI1', 'Rabu', '2026-11-05', 'November', '2026', 'November', 'Lab Komputer 1', 'Pemeriksa Uji', 'Koordinator'],
        ];
        $checklistRows = [
            ['Kode Laporan', 'No Item', 'Hasil (Ya/Tidak/NA)', 'Catatan'],
            ['K3INSP-UJI1', 1, 'Ya', 'Terkait poster'],
            ['K3INSP-UJI1', 2, 'NA', ''],
        ];
        $temuanRows = [
            ['Kode Laporan', 'Temuan', 'Dokumentasi', 'Saran Perbaikan', 'Pelaksana', 'Target', 'Catatan'],
            ['K3INSP-UJI1', 'APAR kadaluarsa.', 'Foto APAR', 'Isi ulang.', 'Petugas K3', '2026-12-01', ''],
        ];

        $this->actingAs($user)
            ->post(route('k3-inspection-reports.store-import'), [
                'file' => $this->makeExcel($laporanRows, 'Laporan', [
                    'Checklist' => $checklistRows,
                    'Temuan' => $temuanRows,
                ]),
            ])
            ->assertRedirect(route('k3-inspection-reports.index'))
            ->assertSessionHas('success');

        $report = K3InspectionReport::where('kode', 'K3INSP-UJI1')->first();
        $this->assertNotNull($report);
        $this->assertCount(33, $report->checklist_items);
        $ya = collect($report->checklist_items)->firstWhere('item_no', 1);
        $na = collect($report->checklist_items)->firstWhere('item_no', 2);
        $this->assertTrue($ya['ya']);
        $this->assertSame('Terkait poster', $ya['catatan']);
        $this->assertTrue($na['na']);
        $this->assertCount(1, $report->findings);
    }

    public function test_import_damage_reports_multi_sheet(): void
    {
        $user = $this->userWith(['import-damage-reports'], 'import-krk@uji.test');

        $laporanRows = [
            ['Kode', 'Nama Pelapor', 'Ruangan', 'Telepon', 'Tanggal Insiden', 'Waktu Kejadian', 'Tanggal Dilaporkan', 'Waktu Dilaporkan', 'Lokasi Spesifik', 'Jenis Kejadian', 'Deskripsi Kejadian', 'Perkiraan Biaya', 'Penanggung Jawab', 'Dilaporkan ke Polisi (Ya/Tidak)', 'Berkas Polisi', 'Petugas Polisi', 'Unit Kepolisian', 'Telepon Polisi'],
            ['KRK-UJI01', 'Siti', 'Lab Komputer 2', '08123', '2026-11-05', '14:30', '2026-11-05', '16:00', 'Ruang server', 'Kerusakan', 'Monitor pecah.', 'Rp 1.5jt', 'Andi', 'Tidak', '', '', '', ''],
        ];
        $peralatanRows = [
            ['Kode Laporan', 'Nama Peralatan', 'Merk', 'No Inventaris', 'Jumlah', 'Keterangan'],
            ['KRK-UJI01', 'Monitor LED', 'LG', 'INV-1', '1', 'Pecah'],
        ];

        $this->actingAs($user)
            ->post(route('damage-reports.store-import'), [
                'file' => $this->makeExcel($laporanRows, 'Laporan', ['Peralatan' => $peralatanRows]),
            ])
            ->assertRedirect(route('damage-reports.index'))
            ->assertSessionHas('success');

        $report = DamageReport::where('kode', 'KRK-UJI01')->first();
        $this->assertNotNull($report);
        $this->assertSame('kerusakan', $report->jenis_kejadian);
        $this->assertFalse($report->dilaporkan_ke_polisi);
        $this->assertCount(1, $report->peralatan);
        $this->assertSame('Monitor LED', $report->peralatan[0]['nama']);
    }

    public function test_import_k3_documents_without_file(): void
    {
        $user = $this->userWith(['import-k3-documents'], 'import-dok@uji.test');

        $rows = [
            ['Kode', 'Judul', 'Kategori', 'Nomor Dokumen', 'Tanggal Berlaku', 'Tanggal Revisi', 'Keterangan'],
            ['DOK-K3-UJI1', 'SOP APAR', 'sop', 'SOP/K3/001', '2026-11-05', '', 'SOP pemadaman'],
        ];

        $this->actingAs($user)
            ->post(route('k3-documents.store-import'), ['file' => $this->makeExcel($rows)])
            ->assertRedirect(route('k3-documents.index'))
            ->assertSessionHas('success');

        $document = K3Document::where('kode', 'DOK-K3-UJI1')->first();
        $this->assertNotNull($document);
        $this->assertNull($document->file_path);
    }

    public function test_import_occupational_disease_investigations(): void
    {
        $user = $this->userWith(['import-occupational-disease-investigations'], 'import-pak@uji.test');

        $rows = [
            ['Kode', 'Nama', 'NIP', 'Bagian', 'Jabatan', 'Jenis Kelamin', 'Lama Bekerja', 'Keluhan', 'Tanggal Pemeriksaan', 'Diperiksa Oleh'],
            ['PAK-UJI01', 'Andi Pratama', '191234', 'UPT Lab', 'Laboran', 'Laki-laki', '5 tahun', 'Nyeri punggung', '2026-11-05', 'Dr. Dhesi'],
        ];

        $this->actingAs($user)
            ->post(route('occupational-disease-investigations.store-import'), ['file' => $this->makeExcel($rows)])
            ->assertRedirect(route('occupational-disease-investigations.index'))
            ->assertSessionHas('success');

        $investigation = OccupationalDiseaseInvestigation::where('kode', 'PAK-UJI01')->first();
        $this->assertNotNull($investigation);
        $this->assertSame('Andi Pratama', $investigation->nama);
    }

    public function test_import_reports_row_errors_but_keeps_valid_rows(): void
    {
        $user = $this->userWith(['import-accident-reports'], 'import-error@uji.test');

        $rows = [
            ['Kode', 'Tanggal', 'Nama Pelapor', 'Jabatan Pelapor', 'Nama Korban', 'Alamat Korban', 'Tempat Tanggal Lahir', 'Unit/Status', 'Uraian Kejadian 1', 'Dampak', 'Saksi I', 'Saksi II'],
            ['', '2026-11-05', 'Koordinator', 'K3', 'Budi', 'Jl. Kaliurang', 'Yogyakarta, 2005', 'Praktikan', 'Tersengat listrik.', 'Luka ringan.', 'Siti', 'Ahmad'],
            ['', 'bukan-tanggal', 'Koordinator', 'K3', 'Andi', 'Jl. Gejayan', 'Solo, 2004', 'Peneliti', 'Terpeleset.', 'Terkilir.', 'Dewi', 'Rudi'],
        ];

        $this->actingAs($user)
            ->post(route('berita-acara-kecelakaan.store-import'), ['file' => $this->makeExcel($rows)])
            ->assertRedirect(route('berita-acara-kecelakaan.index'))
            ->assertSessionHas('success')
            ->assertSessionHas('import_errors');

        $this->assertDatabaseCount('accident_reports', 1);
    }

    public function test_import_pages_and_routes_are_forbidden_without_permission(): void
    {
        $user = $this->userWith(['view-apar-inspections'], 'tanpa-import@uji.test');

        $this->actingAs($user)->get(route('apar-inspections.import'))->assertForbidden();
        $this->actingAs($user)->get(route('apar-inspections.template'))->assertForbidden();

        $file = UploadedFile::fake()->create('import.xlsx', 10, 'xlsx');
        $this->actingAs($user)->post(route('apar-inspections.store-import'), ['file' => $file])->assertForbidden();
    }

    public function test_import_rejects_non_excel_file(): void
    {
        $user = $this->userWith(['import-apar-inspections'], 'import-invalid@uji.test');

        $this->actingAs($user)
            ->post(route('apar-inspections.store-import'), [
                'file' => UploadedFile::fake()->create('data.pdf', 10, 'application/pdf'),
            ])
            ->assertSessionHasErrors('file');
    }
}
