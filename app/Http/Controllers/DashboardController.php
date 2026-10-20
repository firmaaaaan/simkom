<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\Computer;
use App\Models\ComputerBorrowing;
use App\Models\ComputerCheck;
use App\Models\Laboratory;
use App\Models\LabUsage;
use App\Models\MaintenanceChecklist;
use App\Models\Notification;
use App\Models\PcMonitor;
use App\Models\Pengadaan;
use App\Models\Ticket;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    private const ACTIVITY_LIMIT = 6;

    private const TICKET_LIMIT = 6;

    private const TICKET_ACTIVE_STATUSES = ['Open', 'In Progress'];

    public function index(Request $request)
    {
        // Bagian dashboard disembunyikan bila user tidak punya izin modulnya.
        $user = auth()->user();
        $can = [
            'computers' => $user->hasAnyPermission(['view-computers', 'view-reports']),
            'tickets' => $user->hasPermission('view-tickets'),
            'borrowings' => $user->hasPermission('view-borrowings'),
            'maintenance' => $user->hasPermission('view-maintenance'),
            'academic_years' => $user->hasPermission('view-academic-years'),
            'lab_usages' => $user->hasPermission('view-lab-usages'),
            'pengadaan' => $user->hasPermission('view-pengadaan'),
            'monitoring' => $user->hasPermission('view-monitoring'),
        ];

        $selectedAcademicYear = $this->selectedAcademicYear($request);

        return view('dashboard', [
            'can' => $can,
            'academicYears' => AcademicYear::orderByDesc('start_year')->get(),
            'selectedAcademicYear' => $selectedAcademicYear,
            'stats' => $this->stats($can, $selectedAcademicYear),
            'chart' => $can['tickets'] ? $this->ticketChart($selectedAcademicYear) : null,
            'ticketAnalysis' => $can['tickets'] ? $this->ticketAnalysis($selectedAcademicYear) : null,
            'labOccupancy' => $can['lab_usages'] ? $this->labOccupancy() : null,
            'pengadaanSummary' => $can['pengadaan'] ? $this->pengadaanSummary($selectedAcademicYear) : null,
            'monitoringSummary' => $can['monitoring'] ? $this->monitoringSummary() : null,
            'quickActions' => $this->quickActions($can),
            'unreadNotifications' => Notification::unreadFor($user)->count(),
            'activities' => $this->activities($can),
            'laboratorySummary' => ($can['computers'] || $can['maintenance']) ? $this->laboratorySummary() : collect(),
            'academicYearAlert' => ($can['academic_years'] || $can['computers'] || $can['tickets'])
                ? $this->academicYearAlert()
                : null,
            'recentTickets' => $can['tickets']
                ? Ticket::with(['computer', 'laboratory'])->latest()->limit(self::TICKET_LIMIT)->get()
                : collect(),
        ]);
    }

    /**
     * Tahun ajaran terpilih dari query ?academic_year=. Tanpa parameter =
     * tahun ajaran aktif (bila ada). Nilai null = "Semua Tahun Ajaran".
     */
    private function selectedAcademicYear(Request $request): ?AcademicYear
    {
        if ($request->query->has('academic_year')) {
            $id = $request->query('academic_year');

            return $id ? AcademicYear::find($id) : null;
        }

        return AcademicYear::current();
    }

    /**
     * Peringatan bila tahun ajaran aktif sudah lewat periodenya, atau belum ada
     * tahun ajaran aktif sama sekali (data baru tidak akan masuk laporan).
     */
    private function academicYearAlert(): ?array
    {
        $active = AcademicYear::active()->orderByDesc('start_year')->get();

        if ($active->isEmpty()) {
            return ['type' => 'missing', 'years' => collect()];
        }

        $stale = $active->reject(fn (AcademicYear $year) => $year->coversDate(now()));

        if ($stale->isEmpty()) {
            return null;
        }

        return ['type' => 'stale', 'years' => $stale];
    }

    /**
     * Pemeliharaan terakhir, pengecekan terakhir, dan distribusi status
     * komputer untuk setiap laboratorium.
     */
    private function laboratorySummary(): Collection
    {
        $laboratories = Laboratory::orderBy('name')->get();

        $checks = DB::table('computer_checks')
            ->join('computers', 'computers.id', '=', 'computer_checks.computer_id')
            ->whereNotNull('computers.laboratory_id')
            ->whereIn('computers.laboratory_id', $laboratories->pluck('id'))
            ->selectRaw('computers.laboratory_id, MAX(computer_checks.created_at) as last_checked_at, COUNT(*) as total_checks')
            ->groupBy('computers.laboratory_id')
            ->get()
            ->keyBy('laboratory_id');

        $maintenance = MaintenanceChecklist::query()
            ->whereIn('laboratory_id', $laboratories->pluck('id'))
            ->orderByDesc('maintenance_date')
            ->orderByDesc('id')
            ->get()
            ->groupBy('laboratory_id')
            ->map(fn (Collection $items) => $items->first());

        $computerCounts = DB::table('computers')
            ->whereIn('laboratory_id', $laboratories->pluck('id'))
            ->selectRaw('laboratory_id, COUNT(*) as total')
            ->groupBy('laboratory_id')
            ->pluck('total', 'laboratory_id');

        $computersByStatus = DB::table('computers')
            ->whereIn('laboratory_id', $laboratories->pluck('id'))
            ->selectRaw('laboratory_id, status, COUNT(*) as total')
            ->groupBy('laboratory_id', 'status')
            ->get()
            ->groupBy('laboratory_id')
            ->map(fn (Collection $rows) => $rows->pluck('total', 'status'));

        return $laboratories->map(fn (Laboratory $laboratory) => [
            'laboratory' => $laboratory,
            'last_checked_at' => ($row = $checks->get($laboratory->id)) ? Carbon::parse($row->last_checked_at) : null,
            'total_checks' => (int) ($row->total_checks ?? 0),
            'total_computers' => (int) ($computerCounts[$laboratory->id] ?? 0),
            'computers_by_status' => $computersByStatus->get($laboratory->id)
                ?? collect(['Aktif' => 0, 'Maintenance' => 0, 'Tidak Aktif' => 0]),
            'last_maintenance' => $maintenance->get($laboratory->id),
        ]);
    }

    /**
     * Angka ringkas untuk kartu statistik. Tiket & pengecekan mengikuti
     * tahun ajaran terpilih bila ada.
     */
    private function stats(array $can, ?AcademicYear $academicYear): array
    {
        $stats = [];

        if ($can['computers']) {
            $computers = Computer::query()
                ->selectRaw('status, COUNT(*) as total')
                ->groupBy('status')
                ->pluck('total', 'status');

            $stats['computers_total'] = (int) $computers->sum();
            $stats['computers_aktif'] = (int) ($computers['Aktif'] ?? 0);
            $stats['computers_maintenance'] = (int) ($computers['Maintenance'] ?? 0);

            $checksQuery = ComputerCheck::query();

            if ($academicYear) {
                $checksQuery->forAcademicYear($academicYear);
                $stats['checks_period'] = (int) $checksQuery->count();
                $stats['checks_period_label'] = 'tahun ajaran '.$academicYear->name;
            } else {
                $checksQuery->whereMonth('created_at', now()->month)
                    ->whereYear('created_at', now()->year);
                $stats['checks_period'] = (int) $checksQuery->count();
                $stats['checks_period_label'] = 'bulan ini';
            }
        }

        if ($can['tickets']) {
            $ticketsQuery = Ticket::query();

            if ($academicYear) {
                $ticketsQuery->where('academic_year_id', $academicYear->id);
            }

            $tickets = (clone $ticketsQuery)
                ->selectRaw('status, COUNT(*) as total')
                ->groupBy('status')
                ->pluck('total', 'status');

            $stats['tickets_open'] = (int) ($tickets['Open'] ?? 0);
            $stats['tickets_in_progress'] = (int) ($tickets['In Progress'] ?? 0);
            $stats['tickets_resolved'] = (int) ($tickets['Resolved'] ?? 0) + (int) ($tickets['Closed'] ?? 0);
            $stats['tickets_total'] = (int) $tickets->sum();
        }

        if ($can['borrowings']) {
            $stats['borrowings_pending'] = ComputerBorrowing::where('status', 'Pending')->count();
        }

        return $stats;
    }

    /**
     * Jumlah tiket per bulan untuk siklus tahun ajaran terpilih
     * (Agu–Jul) atau tahun kalender berjalan bila "Semua".
     */
    private function ticketChart(?AcademicYear $academicYear): array
    {
        if ($academicYear) {
            $months = collect(range(0, 11))->map(function (int $offset) use ($academicYear) {
                $date = $academicYear->periodStart()->copy()->addMonthsNoOverflow($offset);

                return ['month' => (int) $date->month, 'year' => (int) $date->year];
            })->all();

            $query = Ticket::where('academic_year_id', $academicYear->id);
            $scopeLabel = 'tahun ajaran '.$academicYear->name;
        } else {
            $year = (int) now()->year;

            $months = collect(range(1, 12))
                ->map(fn (int $month) => ['month' => $month, 'year' => $year])
                ->all();

            $query = Ticket::whereYear('created_at', $year);
            $scopeLabel = 'tahun '.$year;
        }

        $counts = array_fill(0, 12, 0);

        $query->pluck('created_at')
            ->each(function ($createdAt) use (&$counts) {
                $counts[(int) $createdAt->month - 1]++;
            });

        return [
            'months' => $months,
            'counts' => $counts,
            'max' => max($counts) ?: 1,
            'total' => array_sum($counts),
            'currentMonth' => (int) now()->month,
            'scopeLabel' => $scopeLabel,
        ];
    }

    /**
     * Analisis tiket: donut prioritas (tiket aktif) & bar kategori
     * (semua tiket pada periode terpilih).
     */
    private function ticketAnalysis(?AcademicYear $academicYear): array
    {
        $base = Ticket::query();

        if ($academicYear) {
            $base->where('academic_year_id', $academicYear->id);
        }

        $priorityLabels = ['Rendah', 'Sedang', 'Tinggi', 'Darurat'];
        $priorityColors = ['#3b82f6', '#f59e0b', '#ef4444', '#7f1d1d'];

        $priorityCounts = (clone $base)
            ->whereIn('status', self::TICKET_ACTIVE_STATUSES)
            ->selectRaw('priority, COUNT(*) as total')
            ->groupBy('priority')
            ->pluck('total', 'priority');

        $categoryLabels = ['Komputer', 'Hardware', 'Software', 'Jaringan', 'Listrik/UPS'];
        $categoryColors = ['#22c55e', '#3b82f6', '#a855f7', '#f59e0b', '#64748b'];

        $categoryCounts = (clone $base)
            ->selectRaw('category, COUNT(*) as total')
            ->groupBy('category')
            ->pluck('total', 'category');

        return [
            'priority' => [
                'labels' => $priorityLabels,
                'counts' => array_map(fn (string $label) => (int) ($priorityCounts[$label] ?? 0), $priorityLabels),
                'colors' => $priorityColors,
                'total' => (int) $priorityCounts->sum(),
            ],
            'category' => [
                'labels' => $categoryLabels,
                'counts' => array_map(fn (string $label) => (int) ($categoryCounts[$label] ?? 0), $categoryLabels),
                'colors' => $categoryColors,
                'total' => (int) $categoryCounts->sum(),
            ],
        ];
    }

    /**
     * Okupansi laboratorium hari ini: yang masih di dalam (In) dan yang
     * sudah keluar (Out) dari kunjungan hari ini.
     */
    private function labOccupancy(): array
    {
        return [
            'in_now' => LabUsage::where('status', 'In')->count(),
            'out_today' => LabUsage::where('status', 'Out')
                ->whereDate('checked_in_at', today())
                ->count(),
            'today_total' => LabUsage::whereDate('checked_in_at', today())->count(),
        ];
    }

    /**
     * Ringkasan pengadaan pada periode terpilih: jumlah per status dan
     * total nilai (jumlah × harga) yang sudah Acc.
     */
    private function pengadaanSummary(?AcademicYear $academicYear): array
    {
        $base = Pengadaan::query();

        if ($academicYear) {
            $base->where('academic_year_id', $academicYear->id);
        }

        $byStatus = (clone $base)
            // Alias berbeda dari "total" — accessor Pengadaan::getTotalAttribute
            // (jumlah × harga) akan menimpa kolom pluck bila namanya sama.
            ->selectRaw('status, COUNT(*) as status_count')
            ->groupBy('status')
            ->pluck('status_count', 'status');

        $accValue = (int) (clone $base)
            ->where('status', 'Acc')
            ->selectRaw('COALESCE(SUM(jumlah * harga), 0) as total_value')
            ->value('total_value');

        return [
            'proses' => (int) ($byStatus['Proses'] ?? 0),
            'acc' => (int) ($byStatus['Acc'] ?? 0),
            'ditolak' => (int) ($byStatus['Ditolak'] ?? 0),
            'acc_value' => $accValue,
        ];
    }

    /**
     * Status telemetri PC: online = terlihat dalam STALE_SECONDS terakhir.
     */
    private function monitoringSummary(): array
    {
        $total = PcMonitor::count();
        $offline = PcMonitor::stale()->count();

        return [
            'total' => $total,
            'online' => $total - $offline,
            'offline' => $offline,
        ];
    }

    /**
     * Tombol aksi cepat yang relevan dengan izin user.
     */
    private function quickActions(array $can): array
    {
        $user = auth()->user();
        $actions = [];

        if ($user->hasPermission('create-tickets')) {
            $actions[] = [
                'label' => 'Buat Tiket',
                'url' => route('tickets.create'),
                'color' => 'red',
            ];
        }

        if ($can['borrowings']) {
            $actions[] = [
                'label' => 'Peminjaman Pending',
                'url' => route('borrowings.index', ['status' => 'Pending']),
                'color' => 'amber',
            ];
        }

        if ($user->hasPermission('view-reports')) {
            $actions[] = [
                'label' => 'Kartu Kendali',
                'url' => route('reports.card-control'),
                'color' => 'blue',
            ];
        }

        if ($can['monitoring']) {
            $actions[] = [
                'label' => 'Monitoring PC',
                'url' => route('monitoring-pc.index'),
                'color' => 'green',
            ];
        }

        if ($can['lab_usages']) {
            $actions[] = [
                'label' => 'Penggunaan Lab',
                'url' => route('lab-usages.index'),
                'color' => 'blue',
            ];
        }

        if ($can['pengadaan']) {
            $actions[] = [
                'label' => 'Pengadaan',
                'url' => route('pengadaan.index'),
                'color' => 'amber',
            ];
        }

        return array_values($actions);
    }

    /**
     * Aktivitas terbaru dari tiket, peminjaman, dan pengecekan.
     */
    private function activities(array $can): Collection
    {
        $items = collect();

        if ($can['tickets']) {
            Ticket::with(['computer', 'laboratory'])->latest()->limit(self::ACTIVITY_LIMIT)->get()
                ->each(function (Ticket $ticket) use ($items) {
                    $items->push([
                        'title' => $ticket->title,
                        'meta' => $ticket->computer?->code
                            ? 'Komputer '.$ticket->computer->code
                            : ($ticket->laboratory?->name ?? 'Kendala'),
                        'status' => $ticket->status,
                        'at' => $ticket->created_at,
                        'url' => route('tickets.show', $ticket),
                        'color' => 'red',
                    ]);
                });
        }

        if ($can['borrowings']) {
            ComputerBorrowing::with('computer')->latest()->limit(self::ACTIVITY_LIMIT)->get()
                ->each(function (ComputerBorrowing $borrowing) use ($items) {
                    $items->push([
                        'title' => 'Peminjaman oleh '.$borrowing->borrower_name,
                        'meta' => 'Komputer '.($borrowing->computer?->code ?? '-'),
                        'status' => $borrowing->status,
                        'at' => $borrowing->created_at,
                        'url' => route('borrowings.index'),
                        'color' => 'amber',
                    ]);
                });
        }

        if ($can['computers']) {
            ComputerCheck::with('computer')->latest()->limit(self::ACTIVITY_LIMIT)->get()
                ->each(function (ComputerCheck $check) use ($items) {
                    $items->push([
                        'title' => 'Pengecekan '.($check->computer?->code ?? 'komputer'),
                        'meta' => 'Hasil: '.$check->overall_status,
                        'status' => $check->overall_status,
                        'at' => $check->created_at,
                        'url' => route('computers.card', $check->computer_id),
                        'color' => 'blue',
                    ]);
                });
        }

        return $items->sortByDesc('at')->take(self::ACTIVITY_LIMIT)->values();
    }
}
