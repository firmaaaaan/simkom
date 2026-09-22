<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\Box;
use App\Models\Component;
use App\Models\Computer;
use App\Models\ComputerBorrowing;
use App\Models\ComputerCheck;
use App\Models\DeviceCheck;
use App\Models\DeviceCheckItem;
use App\Models\Laboratory;
use App\Models\MaintenanceChecklist;
use App\Models\MaintenanceChecklistItem;
use App\Models\Permission;
use App\Models\Role;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\TestResponse;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Tests\TestCase;

class ExcelExportTempTest extends TestCase
{
    use RefreshDatabase;

    private const ROUTES = [
        'computers.export',
        'laboratories.export',
        'users.export',
        'roles.export',
        'academic-years.export',
        'maintenance.export',
        'device-checks.export',
        'tickets.export',
        'borrowings.export',
        'boxes.export',
    ];

    private function admin(): User
    {
        $role = Role::create(['name' => 'admin', 'label' => 'Admin']);

        foreach ([
            'manage-users', 'manage-roles', 'manage-laboratories', 'manage-academic-years',
            'manage-computers', 'manage-maintenance', 'manage-tickets', 'manage-borrowings',
            'manage-components',
        ] as $name) {
            $role->permissions()->attach(Permission::firstOrCreate(['name' => $name], ['label' => $name]));
        }

        $user = User::create(['name' => 'Admin Uji', 'email' => 'admin@e.test', 'password' => 'rahasia123']);
        $user->roles()->attach($role);

        return $user;
    }

    private function seedData(): array
    {
        $lab = Laboratory::create(['name' => 'Lab Komputer 1', 'code' => 'LK1', 'location' => 'Gedung A', 'capacity' => 40]);
        $computer = Computer::create(['code' => 'LK1-001', 'laboratory_id' => $lab->id, 'status' => 'Aktif']);

        $year = AcademicYear::create([
            'name' => 'TA 2026/2027', 'start_year' => 2026, 'end_year' => 2027, 'status' => 'Aktif',
            'start_date' => '2026-08-01', 'end_date' => '2027-06-30',
        ]);

        $maintenance = MaintenanceChecklist::create([
            'laboratory_id' => $lab->id, 'academic_year_id' => $year->id,
            'maintenance_date' => '2026-09-10', 'inspector_name' => 'Firmansyah',
            'notes_computer' => 'Debu dibersihkan',
        ]);
        MaintenanceChecklistItem::insert([
            ['maintenance_checklist_id' => $maintenance->id, 'computer_id' => $computer->id, 'category' => 'A', 'item_number' => 1, 'is_checked' => true, 'created_at' => now(), 'updated_at' => now()],
            ['maintenance_checklist_id' => $maintenance->id, 'computer_id' => $computer->id, 'category' => 'A', 'item_number' => 2, 'is_checked' => false, 'created_at' => now(), 'updated_at' => now()],
        ]);

        $check = DeviceCheck::create([
            'laboratory_id' => $lab->id, 'academic_year_id' => $year->id,
            'check_date' => '2026-09-19', 'officer_name' => 'Firmansyah', 'notes' => 'Mouse diganti',
        ]);
        $rows = [];
        foreach (DeviceCheck::itemKeys() as $index => $key) {
            $rows[] = [
                'device_check_id' => $check->id, 'computer_id' => $computer->id, 'item_key' => $key,
                'is_checked' => $index < 5, 'created_at' => now(), 'updated_at' => now(),
            ];
        }
        DeviceCheckItem::insert($rows);

        Ticket::create([
            'tracking_code' => 'TKT-001', 'laboratory_id' => $lab->id, 'computer_id' => $computer->id,
            'academic_year_id' => $year->id, 'reported_by' => 1, 'reporter_name' => 'Budi',
            'category' => 'Hardware', 'title' => 'Mouse tidak berfungsi', 'description' => 'Klik kiri mati',
            'priority' => 'Tinggi', 'status' => 'Open',
        ]);
        Ticket::create([
            'tracking_code' => 'TKT-002', 'laboratory_id' => $lab->id, 'computer_id' => $computer->id,
            'academic_year_id' => $year->id, 'reported_by' => 1, 'reporter_name' => 'Sari',
            'category' => 'Software', 'title' => 'Aplikasi tidak bisa dibuka', 'description' => 'Error',
            'priority' => 'Sedang', 'status' => 'Resolved',
        ]);

        ComputerBorrowing::create([
            'computer_id' => $computer->id, 'laboratory_id' => $lab->id, 'borrower_name' => 'Budi',
            'borrower_nim' => '12345', 'borrower_prodi' => 'Informatika', 'purpose' => 'Praktikum',
            'borrow_date' => '2026-09-20', 'borrow_time_start' => '08:00', 'borrow_time_end' => '10:00',
            'status' => 'Approved', 'admin_notes' => 'Ambil kunci di ruang lab',
        ]);

        $component = Component::create([
            'name' => 'RAM DDR4 8GB', 'code' => 'C-001', 'category' => 'RAM', 'quantity' => 4, 'status' => 'Tersedia',
        ]);
        $box = Box::create(['name' => 'Box RAM', 'code' => 'BOX-RAM-001', 'location' => 'Rak 1']);
        $box->components()->attach($component->id, ['quantity' => 2]);

        return compact('lab', 'computer', 'year', 'check', 'box');
    }

    /**
     * Baca isi file xlsx dari response unduhan.
     */
    private function rows(TestResponse $response): array
    {
        $path = $response->baseResponse->getFile()->getPathname();

        return IOFactory::load($path)->getActiveSheet()->toArray(null, true, false, false);
    }

    public function test_each_export_route_downloads_a_usable_excel_file(): void
    {
        $admin = $this->admin();
        $this->seedData();

        foreach (self::ROUTES as $name) {
            $response = $this->actingAs($admin)->get(route($name));

            $response->assertOk();
            $response->assertDownload();
            $this->assertStringContainsString('spreadsheetml', $response->headers->get('content-type'), $name);

            $rows = $this->rows($response);
            $this->assertNotEmpty($rows[0], "Baris judul kosong pada {$name}");
            $this->assertGreaterThanOrEqual(2, count($rows), "Tidak ada data pada {$name}");

            // Setiap baris data punya jumlah kolom sama dengan judulnya.
            foreach ($rows as $row) {
                $this->assertCount(count($rows[0]), $row, "Jumlah kolom tidak konsisten pada {$name}");
            }
        }
    }

    public function test_exports_follow_the_active_filters(): void
    {
        $admin = $this->admin();
        $this->seedData();

        $tickets = $this->rows($this->actingAs($admin)->get(route('tickets.export', ['status' => 'Open'])));
        $this->assertCount(2, $tickets); // judul + 1 tiket Open
        $this->assertSame('Mouse tidak berfungsi', $tickets[1][1]);

        $resolved = $this->rows($this->actingAs($admin)->get(route('tickets.export', ['status' => 'Resolved'])));
        $this->assertCount(2, $resolved);
        $this->assertSame('Aplikasi tidak bisa dibuka', $resolved[1][1]);

        $all = $this->rows($this->actingAs($admin)->get(route('tickets.export')));
        $this->assertCount(3, $all);

        // Pencarian komputer & user.
        $computers = $this->rows($this->actingAs($admin)->get(route('computers.export', ['search' => 'LK1-001'])));
        $this->assertCount(2, $computers);

        $none = $this->rows($this->actingAs($admin)->get(route('computers.export', ['search' => 'TIDAK-ADA'])));
        $this->assertCount(1, $none);

        $users = $this->rows($this->actingAs($admin)->get(route('users.export', ['search' => 'admin@e.test'])));
        $this->assertCount(2, $users);
    }

    public function test_export_content_is_complete_and_safe(): void
    {
        $admin = $this->admin();
        $data = $this->seedData();

        // Pengguna: tanpa password.
        $users = $this->rows($this->actingAs($admin)->get(route('users.export')));
        $this->assertSame(['Nama', 'Email', 'Role', 'Jumlah Role', 'Terdaftar'], $users[0]);
        $this->assertSame('Admin Uji', $users[1][0]);
        $this->assertSame('Admin', $users[1][2]);
        $this->assertStringNotContainsString('rahasia123', json_encode($users));
        $this->assertStringNotContainsString('$2y$', json_encode($users));

        // Lab: jumlah komputer ikut.
        $labs = $this->rows($this->actingAs($admin)->get(route('laboratories.export')));
        $this->assertSame('Lab Komputer 1', $labs[1][1]);
        $this->assertSame(1, $labs[1][5]);

        // Komputer: lab + status.
        $computers = $this->rows($this->actingAs($admin)->get(route('computers.export')));
        $this->assertSame('LK1-001', $computers[1][0]);
        $this->assertSame('Lab Komputer 1', $computers[1][1]);

        // Pemeliharaan: 1 dari 2 item dicentang.
        $maintenance = $this->rows($this->actingAs($admin)->get(route('maintenance.export')));
        $this->assertSame(1, $maintenance[1][4]);
        $this->assertSame(2, $maintenance[1][5]);
        $this->assertSame('Debu dibersihkan', $maintenance[1][6]);

        // Pengecekan perangkat: 5 dari 7 sel berfungsi.
        $checks = $this->rows($this->actingAs($admin)->get(route('device-checks.export')));
        $this->assertSame(1, $checks[1][4]);   // jumlah komputer
        $this->assertSame(5, $checks[1][5]);   // berfungsi
        $this->assertSame(2, $checks[1][6]);   // perlu perhatian
        $this->assertSame(7, $checks[1][7]);   // total sel
        $this->assertSame('71.4%', $checks[1][8]);

        // Peminjaman: catatan admin ikut terekspor.
        $borrowings = $this->rows($this->actingAs($admin)->get(route('borrowings.export')));
        $this->assertSame('Budi', $borrowings[1][1]);
        $this->assertSame('Approved', $borrowings[1][10]);
        $this->assertSame('Ambil kunci di ruang lab', $borrowings[1][11]);

        // Box: isi komponen + total unit.
        $boxes = $this->rows($this->actingAs($admin)->get(route('boxes.export')));
        $this->assertSame('BOX-RAM-001', $boxes[1][0]);
        $this->assertSame(1, $boxes[1][3]);
        $this->assertSame(2, $boxes[1][4]);
        $this->assertSame('RAM DDR4 8GB (2 unit)', $boxes[1][5]);

        // Role & tahun ajaran.
        $roles = $this->rows($this->actingAs($admin)->get(route('roles.export')));
        $this->assertSame('admin', $roles[1][0]);
        $this->assertStringContainsString('manage-tickets', implode(',', $roles[1]));

        $years = $this->rows($this->actingAs($admin)->get(route('academic-years.export')));
        $this->assertSame('TA 2026/2027', $years[1][0]);
        $this->assertSame('01 Aug 2026 - 30 Jun 2027', $years[1][3]);
    }

    public function test_every_export_requires_its_permission(): void
    {
        $outsider = User::create(['name' => 'Orang Luar', 'email' => 'luar@e.test', 'password' => 'x']);

        $this->seedData();

        foreach (self::ROUTES as $name) {
            $this->actingAs($outsider)->get(route($name))->assertForbidden();
        }
    }
}
