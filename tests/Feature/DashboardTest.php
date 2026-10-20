<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\Computer;
use App\Models\Laboratory;
use App\Models\LabUsage;
use App\Models\Notification;
use App\Models\PcMonitor;
use App\Models\Pengadaan;
use App\Models\Permission;
use App\Models\Role;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    private function userWith(string ...$permissions): User
    {
        $role = Role::firstOrCreate(['name' => 'role-'.md5(implode(',', $permissions))], ['label' => 'Role Dashboard Uji']);

        foreach ($permissions as $name) {
            $permission = Permission::firstOrCreate(['name' => $name], ['label' => $name]);
            $role->permissions()->syncWithoutDetaching($permission->id);
        }

        $user = User::create([
            'name' => 'User Dashboard',
            'email' => 'dashboard+'.uniqid().'@uji.test',
            'password' => 'rahasia123',
        ]);
        $user->roles()->attach($role);

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        return $user;
    }

    private function fullPermissions(): array
    {
        return [
            'view-computers', 'view-reports', 'view-tickets', 'create-tickets',
            'view-borrowings', 'view-maintenance', 'view-academic-years',
            'view-lab-usages', 'view-pengadaan', 'view-monitoring',
        ];
    }

    private function laboratory(string $suffix = 'A'): Laboratory
    {
        return Laboratory::create([
            'name' => 'Lab '.$suffix,
            'code' => 'LAB-'.$suffix,
            'location' => 'Gedung 1',
            'capacity' => 20,
        ]);
    }

    private function academicYear(array $overrides = []): AcademicYear
    {
        return AcademicYear::create(array_merge([
            'name' => 'Tahun Ajaran 2026/2027',
            'start_year' => 2026,
            'end_year' => 2027,
            'status' => 'Aktif',
        ], $overrides));
    }

    private function ticket(Laboratory $lab, AcademicYear $year, array $overrides = []): Ticket
    {
        return Ticket::create(array_merge([
            'laboratory_id' => $lab->id,
            'academic_year_id' => $year->id,
            'category' => 'Hardware',
            'title' => 'Monitor blank',
            'description' => 'Layar monitor tidak menyala',
            'priority' => 'Sedang',
            'status' => 'Open',
        ], $overrides));
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get(route('dashboard'))->assertRedirect(route('login'));
    }

    public function test_user_without_permissions_gets_ok_with_sections_hidden(): void
    {
        $user = $this->userWith();

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertDontSee('Total Komputer')
            ->assertDontSee('Kendala Aktif')
            ->assertDontSee('Statistik Kendala')
            ->assertDontSee('PC Online')
            ->assertDontSee('Pengadaan Diproses');
    }

    public function test_full_permissions_show_informative_sections(): void
    {
        $lab = $this->laboratory();
        $year = $this->academicYear();

        Computer::create(['code' => 'PC-01', 'laboratory_id' => $lab->id, 'status' => 'Aktif']);
        Computer::create(['code' => 'PC-02', 'laboratory_id' => $lab->id, 'status' => 'Maintenance']);

        $this->ticket($lab, $year, ['priority' => 'Darurat', 'status' => 'Open', 'title' => 'Jaringan mati total']);
        $this->ticket($lab, $year, ['priority' => 'Sedang', 'status' => 'In Progress', 'category' => 'Software', 'title' => 'Aplikasi error']);
        $this->ticket($lab, $year, ['priority' => 'Rendah', 'status' => 'Resolved', 'category' => 'Komputer', 'title' => 'Keyboard error']);

        LabUsage::create([
            'laboratory_id' => $lab->id,
            'user_name' => 'Budi',
            'user_prodi' => 'TI',
            'purpose' => 'Praktikum',
            'day' => LabUsage::dayLabel(today()),
            'status' => 'In',
            'checked_in_at' => now(),
        ]);
        LabUsage::create([
            'laboratory_id' => $lab->id,
            'user_name' => 'Siti',
            'user_prodi' => 'SI',
            'purpose' => 'Riset',
            'day' => LabUsage::dayLabel(today()),
            'status' => 'Out',
            'checked_in_at' => now()->subHours(2),
            'validated_at' => now()->subHour(),
        ]);

        Pengadaan::create([
            'nama_barang' => 'Monitor 24"',
            'kategori' => 'Hardware',
            'jumlah' => 2,
            'harga' => 7500000,
            'tanggal' => today()->toDateString(),
            'status' => 'Acc',
            'academic_year_id' => $year->id,
        ]);
        Pengadaan::create([
            'nama_barang' => 'Keyboard Mekanik',
            'kategori' => 'Hardware',
            'jumlah' => 5,
            'harga' => 500000,
            'tanggal' => today()->toDateString(),
            'status' => 'Proses',
            'academic_year_id' => $year->id,
        ]);

        PcMonitor::create([
            'hostname' => 'LAB-PC-01',
            'ip_address' => '192.168.1.10',
            'status' => PcMonitor::STATUS_ONLINE,
            'last_seen_at' => now(),
        ]);
        PcMonitor::create([
            'hostname' => 'LAB-PC-02',
            'ip_address' => '192.168.1.11',
            'status' => PcMonitor::STATUS_OFFLINE,
            'last_seen_at' => now()->subMinutes(10),
        ]);

        $user = $this->userWith(...$this->fullPermissions());

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Total Komputer')
            ->assertSee('Kendala Aktif')
            ->assertSee('PC Online')
            ->assertSee('Di Lab Hari Ini')
            ->assertSee('Pengadaan Diproses')
            ->assertSee('Rp 15.000.000')
            ->assertSee('Aksi Cepat')
            ->assertSee('Buat Tiket')
            ->assertSee('Monitoring PC')
            ->assertSee('Status Komputer')
            ->assertSee('Prioritas Kendala Aktif')
            ->assertSee('Kategori Kendala')
            ->assertSee('Statistik Kendala')
            ->assertViewHas('monitoringSummary', fn (array $m) => $m['online'] === 1 && $m['offline'] === 1 && $m['total'] === 2)
            ->assertViewHas('labOccupancy', fn (array $o) => $o['in_now'] === 1 && $o['out_today'] === 1 && $o['today_total'] === 2)
            ->assertViewHas('pengadaanSummary', fn (array $p) => $p['proses'] === 1 && $p['acc'] === 1 && $p['acc_value'] === 15000000)
            ->assertViewHas('laboratorySummary', function ($summary) use ($lab) {
                $row = $summary->first(fn (array $r) => $r['laboratory']->id === $lab->id);

                return $row
                    && (int) $row['computers_by_status']->get('Aktif', 0) === 1
                    && (int) $row['computers_by_status']->get('Maintenance', 0) === 1;
            });
    }

    public function test_sections_hidden_without_module_permissions(): void
    {
        $user = $this->userWith('view-tickets');

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Kendala Aktif')
            ->assertSee('Statistik Kendala')
            ->assertSee('Prioritas Kendala Aktif')
            ->assertDontSee('Total Komputer')
            ->assertDontSee('PC Online')
            ->assertDontSee('Di Lab Hari Ini')
            ->assertDontSee('Pengadaan Diproses')
            ->assertDontSee('Status Komputer');
    }

    public function test_academic_year_filter_limits_ticket_stats(): void
    {
        $lab = $this->laboratory();
        $currentYear = $this->academicYear();
        $pastYear = $this->academicYear([
            'name' => 'Tahun Ajaran 2025/2026',
            'start_year' => 2025,
            'end_year' => 2026,
            'status' => 'Non Aktif',
        ]);

        $this->ticket($lab, $currentYear);
        $this->ticket($lab, $currentYear, ['title' => 'Tiket kedua']);
        $this->ticket($lab, $pastYear, ['title' => 'Tiket lama']);

        $user = $this->userWith(...$this->fullPermissions());

        // Default: tahun ajaran aktif (2026/2027) dipilih otomatis.
        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertViewHas('selectedAcademicYear', fn (AcademicYear $year) => $year->is($currentYear))
            ->assertViewHas('chart', fn (array $chart) => $chart['total'] === 2)
            ->assertSee('2 tiket tercatat pada tahun ajaran '.$currentYear->name);

        // Filter manual ke tahun ajaran lama.
        $this->actingAs($user)
            ->get(route('dashboard', ['academic_year' => $pastYear->id]))
            ->assertOk()
            ->assertViewHas('selectedAcademicYear', fn (AcademicYear $year) => $year->is($pastYear))
            ->assertViewHas('chart', fn (array $chart) => $chart['total'] === 1);

        // "Semua Tahun Ajaran" = tahun kalender berjalan (3 tiket dibuat sekarang).
        $this->actingAs($user)
            ->get(route('dashboard', ['academic_year' => '']))
            ->assertOk()
            ->assertViewHas('selectedAcademicYear', fn ($year) => $year === null)
            ->assertViewHas('chart', fn (array $chart) => $chart['total'] === 3);
    }

    public function test_ticket_analysis_counts_priorities_and_categories(): void
    {
        $lab = $this->laboratory();
        $year = $this->academicYear();

        $this->ticket($lab, $year, ['priority' => 'Darurat', 'status' => 'Open']);
        $this->ticket($lab, $year, ['priority' => 'Tinggi', 'status' => 'Open', 'category' => 'Jaringan']);
        $this->ticket($lab, $year, ['priority' => 'Rendah', 'status' => 'Resolved', 'category' => 'Software']);

        $user = $this->userWith('view-tickets');

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertViewHas('ticketAnalysis', function (array $analysis) {
                $priority = $analysis['priority'];
                $category = $analysis['category'];

                return $priority['total'] === 2
                    && $priority['counts'][array_search('Darurat', $priority['labels'], true)] === 1
                    && $priority['counts'][array_search('Tinggi', $priority['labels'], true)] === 1
                    && $category['total'] === 3
                    && $category['counts'][array_search('Jaringan', $category['labels'], true)] === 1
                    && $category['counts'][array_search('Software', $category['labels'], true)] === 1;
            });
    }

    public function test_quick_actions_follow_permissions(): void
    {
        $monitor = $this->userWith('view-monitoring');

        $this->actingAs($monitor)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Aksi Cepat')
            ->assertSee('Monitoring PC')
            ->assertDontSee('Buat Tiket')
            ->assertDontSee('Peminjaman Pending');

        $creator = $this->userWith('create-tickets', 'view-borrowings');

        $this->actingAs($creator)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Buat Tiket')
            ->assertSee('Peminjaman Pending')
            ->assertDontSee('Monitoring PC');
    }

    public function test_unread_notification_count_is_passed_to_view(): void
    {
        Notification::create([
            'title' => 'Notifikasi Uji',
            'message' => 'Pesan untuk dashboard',
            'type' => 'ticket',
            'url' => '/dashboard',
        ]);

        $user = $this->userWith('view-tickets');

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertViewHas('unreadNotifications', fn (int $count) => $count >= 1)
            ->assertSee('notifikasi belum dibaca');
    }

    public function test_chart_shows_empty_state_without_tickets(): void
    {
        $this->academicYear();
        $user = $this->userWith('view-tickets');

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Belum ada tiket pada tahun ajaran')
            ->assertSee('Tidak ada kendala aktif pada periode ini');
    }
}
