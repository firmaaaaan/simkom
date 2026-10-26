<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\Computer;
use App\Models\ComputerBorrowing;
use App\Models\ComputerCheck;
use App\Models\DeviceCheck;
use App\Models\LabUsage;
use App\Models\Laboratory;
use App\Models\MaintenanceChecklist;
use App\Models\Pengadaan;
use App\Models\Permission;
use App\Models\Role;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use ZipArchive;

/**
 * Laporan per jenis (dropdown), lab, dan tahun ajaran (termasuk "Semua").
 * Hasil cetak berupa ZIP berisi file HTML terpisah per kombinasi lab &
 * tahun ajaran dengan layout khusus jenis laporan.
 * Nama tahun ajaran saja yang tampil — periodLabel tidak boleh muncul.
 */
class LaporanTest extends TestCase
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

    private function seedYear(string $name, int $start, int $end, string $status = 'Non Aktif'): AcademicYear
    {
        return AcademicYear::create([
            'name' => $name, 'start_year' => $start, 'end_year' => $end, 'status' => $status,
        ]);
    }

    private function seedLab(string $name = 'Lab Komputer 1', string $code = 'LK1'): Laboratory
    {
        $lab = Laboratory::create([
            'name' => $name, 'code' => $code, 'location' => 'Gedung A', 'capacity' => 40,
        ]);

        Computer::create(['code' => $code.'-001', 'laboratory_id' => $lab->id, 'status' => 'Aktif']);

        return $lab;
    }

    private function seedTransactions(Laboratory $lab, AcademicYear $year): void
    {
        $computer = Computer::where('laboratory_id', $lab->id)->firstOrFail();

        LabUsage::create([
            'laboratory_id' => $lab->id, 'user_name' => 'Budi Mahasiswa', 'user_prodi' => 'Informatika',
            'purpose' => 'Praktikum', 'day' => 'Senin', 'status' => 'In',
            'checked_in_at' => $year->periodStart()->copy()->addMonth()->setTime(8, 0),
        ]);
        // Di luar periode tahun ajaran — tidak boleh tampil.
        LabUsage::create([
            'laboratory_id' => $lab->id, 'user_name' => 'Sari Luar Periode', 'user_prodi' => 'Informatika',
            'purpose' => 'Penelitian', 'day' => 'Senin', 'status' => 'In',
            'checked_in_at' => $year->periodStart()->copy()->subMonth()->setTime(8, 0),
        ]);

        ComputerBorrowing::create([
            'computer_id' => $computer->id, 'laboratory_id' => $lab->id, 'borrower_name' => 'Andi',
            'borrower_nim' => '12345', 'borrower_prodi' => 'Informatika', 'purpose' => 'Praktikum',
            'borrow_date' => $year->periodStart()->copy()->addMonth()->toDateString(),
            'borrow_time_start' => '08:00', 'borrow_time_end' => '10:00', 'status' => 'Approved',
        ]);

        DeviceCheck::create([
            'laboratory_id' => $lab->id, 'academic_year_id' => $year->id,
            'check_date' => $year->periodStart()->copy()->addMonth()->toDateString(), 'officer_name' => 'Firmansyah',
        ]);

        Ticket::create([
            'laboratory_id' => $lab->id, 'computer_id' => $computer->id, 'academic_year_id' => $year->id,
            'reporter_name' => 'Budi', 'category' => 'Hardware', 'title' => 'Mouse tidak berfungsi',
            'description' => 'Klik kiri mati', 'priority' => 'Tinggi', 'status' => 'Open',
        ]);

        MaintenanceChecklist::create([
            'laboratory_id' => $lab->id, 'academic_year_id' => $year->id,
            'maintenance_date' => $year->periodStart()->copy()->addMonth()->toDateString(), 'inspector_name' => 'Firmansyah',
        ]);

        Pengadaan::create([
            'nama_barang' => 'Monitor 24 inch', 'kategori' => 'Hardware', 'jumlah' => 5,
            'harga' => 1500000, 'tanggal' => $year->periodStart()->copy()->toDateString(),
            'status' => 'Acc', 'academic_year_id' => $year->id,
        ]);
    }

    private function extractZip($response): array
    {
        $path = $response->baseResponse->getFile()->getPathname();

        $zip = new ZipArchive;
        $this->assertTrue($zip->open($path));

        $files = [];
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $stat = $zip->statIndex($i);
            $files[$stat['name']] = $zip->getFromIndex($i);
        }

        $zip->close();

        return $files;
    }

    public function test_preview_hanya_menampilkan_jenis_terpilih(): void
    {
        $user = $this->userWith('Viewer', 'viewer@uji.test', ['view-reports']);
        $lab = $this->seedLab();
        $year = $this->seedYear('Tahun Ajaran 2025/2026', 2025, 2026, 'Aktif');
        $this->seedTransactions($lab, $year);

        $this->actingAs($user)->get(route('reports.laporan', [
            'jenis' => 'borrowings', 'laboratory_id' => $lab->id, 'academic_year_id' => $year->id,
        ]))
            ->assertOk()
            ->assertSee('Peminjaman Komputer')
            ->assertSee('Andi')
            ->assertDontSee('Budi Mahasiswa')
            ->assertDontSee($year->periodLabel());
    }

    public function test_preview_default_jenis_lab_usages_dan_ta_aktif(): void
    {
        $user = $this->userWith('Viewer Default', 'viewer-default@uji.test', ['view-reports']);
        $lab = $this->seedLab();
        $year = $this->seedYear('Tahun Ajaran 2025/2026', 2025, 2026, 'Aktif');
        $this->seedTransactions($lab, $year);

        $this->actingAs($user)->get(route('reports.laporan'))
            ->assertOk()
            ->assertSee('Penggunaan Lab')
            ->assertSee('Budi Mahasiswa')
            ->assertDontSee('Sari Luar Periode');
    }

    public function test_semua_tahun_ajaran_menampilkan_kolom_tahun(): void
    {
        $user = $this->userWith('Viewer Semua TA', 'viewer-semua-ta@uji.test', ['view-reports']);
        $lab = $this->seedLab();
        $yearA = $this->seedYear('Tahun Ajaran 2025/2026', 2025, 2026, 'Aktif');
        $yearB = $this->seedYear('Tahun Ajaran 2024/2025', 2024, 2025);
        $this->seedTransactions($lab, $yearA);
        $this->seedTransactions($lab, $yearB);

        $this->actingAs($user)->get(route('reports.laporan', [
            'jenis' => 'device_checks', 'laboratory_id' => $lab->id, 'academic_year_id' => 'all',
        ]))
            ->assertOk()
            ->assertSee('Tahun Ajaran')
            ->assertSee($yearA->name)
            ->assertSee($yearB->name)
            ->assertDontSee($yearA->periodLabel())
            ->assertDontSee($yearB->periodLabel());
    }

    public function test_download_zip_isi_file_per_kombinasi_lab_dan_tahun(): void
    {
        $user = $this->userWith('Pengunduh', 'pengunduh@uji.test', ['view-reports', 'print-reports']);
        $labA = $this->seedLab('Lab Komputer 1', 'LK1');
        $labB = $this->seedLab('Lab Komputer 2', 'LK2');
        $yearA = $this->seedYear('Tahun Ajaran 2025/2026', 2025, 2026, 'Aktif');
        $yearB = $this->seedYear('Tahun Ajaran 2024/2025', 2024, 2025);
        $this->seedTransactions($labA, $yearA);
        $this->seedTransactions($labA, $yearB);
        $this->seedTransactions($labB, $yearA);
        $this->seedTransactions($labB, $yearB);

        $response = $this->actingAs($user)->get(route('reports.laporan-download', [
            'jenis' => 'lab_usages', 'laboratory_id' => 'all', 'academic_year_id' => 'all',
        ]));

        $response->assertOk();
        $response->assertDownload(); // Content-Disposition attachment

        $files = $this->extractZip($response);

        // 2 lab × 2 tahun ajaran = 4 file.
        $this->assertCount(4, $files);

        $entryNames = implode("\n", array_keys($files));
        foreach ([$yearA, $yearB] as $year) {
            // Nama file disanitasi: "Tahun Ajaran 2025/2026" → "Tahun-Ajaran-2025-2026".
            $this->assertStringContainsString(str_replace([' ', '/'], ['-', '-'], $year->name), $entryNames);
        }
        $this->assertStringContainsString('Lab-Komputer-1', $entryNames);
        $this->assertStringContainsString('Lab-Komputer-2', $entryNames);

        $content = implode("\n", $files);
        $this->assertStringContainsString('Budi Mahasiswa', $content);
        $this->assertStringNotContainsString('Sari Luar Periode', $content);
        $this->assertStringNotContainsString($yearA->periodLabel(), $content);
        $this->assertStringNotContainsString($yearB->periodLabel(), $content);
    }

    public function test_download_zip_pengadaan_tanpa_lab_satu_file_per_tahun(): void
    {
        $user = $this->userWith('Pengunduh Pengadaan', 'pengunduh-pengadaan@uji.test', ['view-reports', 'print-reports']);
        $lab = $this->seedLab();
        $yearA = $this->seedYear('Tahun Ajaran 2025/2026', 2025, 2026, 'Aktif');
        $yearB = $this->seedYear('Tahun Ajaran 2024/2025', 2024, 2025);
        $this->seedTransactions($lab, $yearA);
        $this->seedTransactions($lab, $yearB);

        $response = $this->actingAs($user)->get(route('reports.laporan-download', [
            'jenis' => 'pengadaan', 'laboratory_id' => 'all', 'academic_year_id' => 'all',
        ]));

        $response->assertOk();

        $files = $this->extractZip($response);

        // Pengadaan tidak terikat lab: 1 file per tahun ajaran.
        $this->assertCount(2, $files);
        $this->assertStringContainsString('Monitor 24 inch', implode("\n", $files));
        $this->assertStringNotContainsString('Lab Komputer 1', implode("\n", array_keys($files)));
    }

    public function test_download_zip_lab_tunggal_satu_tahun(): void
    {
        $user = $this->userWith('Pengunduh Tunggal', 'pengunduh-tunggal@uji.test', ['view-reports', 'print-reports']);
        $lab = $this->seedLab();
        $year = $this->seedYear('Tahun Ajaran 2025/2026', 2025, 2026, 'Aktif');
        $this->seedTransactions($lab, $year);

        $response = $this->actingAs($user)->get(route('reports.laporan-download', [
            'jenis' => 'tickets', 'laboratory_id' => $lab->id, 'academic_year_id' => $year->id,
        ]));

        $response->assertOk();

        $files = $this->extractZip($response);

        $this->assertCount(1, $files);
        $content = implode("\n", $files);
        $this->assertStringContainsString('Mouse tidak berfungsi', $content);
        $this->assertStringContainsString($year->name, $content);
        $this->assertStringNotContainsString($year->periodLabel(), $content);
    }

    public function test_download_zip_kartu_kendali_menampilkan_baris_komputer(): void
    {
        $user = $this->userWith('PJ Lab', 'pj-lab@uji.test', ['view-reports', 'print-reports']);
        $lab = $this->seedLab();
        $year = $this->seedYear('Tahun Ajaran 2025/2026', 2025, 2026, 'Aktif');
        $computer = Computer::where('laboratory_id', $lab->id)->firstOrFail();

        ComputerCheck::create([
            'computer_id' => $computer->id, 'academic_year_id' => $year->id,
            'overall_status' => 'Baik', 'notes' => 'Semua komponen normal', 'checked_by' => $user->id,
        ]);

        $response = $this->actingAs($user)->get(route('reports.laporan-download', [
            'jenis' => 'card_control', 'laboratory_id' => $lab->id, 'academic_year_id' => $year->id,
        ]));

        $response->assertOk();

        $files = $this->extractZip($response);

        $this->assertCount(1, $files);
        $content = implode("\n", $files);
        $this->assertStringContainsString($computer->code, $content);
        $this->assertStringContainsString('Semua komponen normal', $content);
        $this->assertStringContainsString('checkbox checked', $content);
        $this->assertStringContainsString($year->name, $content);
    }

    public function test_akses_tanpa_izin_ditolak(): void
    {
        $user = $this->userWith('Tanpa Izin', 'tanpa-izin@uji.test', []);

        $this->actingAs($user)->get(route('reports.laporan'))->assertForbidden();
        $this->actingAs($user)->get(route('reports.laporan-download'))->assertForbidden();
    }

    public function test_download_ditolak_tanpa_izin_print(): void
    {
        $user = $this->userWith('Viewer Saja', 'viewer-saja@uji.test', ['view-reports']);

        $this->actingAs($user)->get(route('reports.laporan'))->assertOk();
        $this->actingAs($user)->get(route('reports.laporan-download'))->assertForbidden();
    }
}
