<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\Computer;
use App\Models\ComputerBorrowing;
use App\Models\ComputerCheck;
use App\Models\Laboratory;
use App\Models\MaintenanceChecklist;
use App\Models\Ticket;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    private const ACTIVITY_LIMIT = 6;

    private const TICKET_LIMIT = 6;

    public function index(Request $request)
    {
        // Bagian dashboard disembunyikan bila user tidak punya izin modulnya.
        $can = [
            'computers' => auth()->user()->hasAnyPermission(['manage-computers', 'view-reports']),
            'tickets' => auth()->user()->hasPermission('manage-tickets'),
            'borrowings' => auth()->user()->hasPermission('manage-borrowings'),
            'maintenance' => auth()->user()->hasPermission('manage-maintenance'),
            'academic_years' => auth()->user()->hasPermission('manage-academic-years'),
        ];

        return view('dashboard', [
            'can' => $can,
            'stats' => $this->stats($can),
            'chart' => $can['tickets'] ? $this->ticketChart($request) : null,
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
     * Pemeliharaan terakhir & pengecekan terakhir untuk setiap laboratorium.
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

        $checkedComputers = DB::table('computers')
            ->whereIn('laboratory_id', $laboratories->pluck('id'))
            ->selectRaw('laboratory_id, COUNT(*) as total')
            ->groupBy('laboratory_id')
            ->pluck('total', 'laboratory_id');

        return $laboratories->map(fn (Laboratory $laboratory) => [
            'laboratory' => $laboratory,
            'last_checked_at' => ($row = $checks->get($laboratory->id)) ? Carbon::parse($row->last_checked_at) : null,
            'total_checks' => (int) ($row->total_checks ?? 0),
            'total_computers' => (int) ($checkedComputers[$laboratory->id] ?? 0),
            'last_maintenance' => $maintenance->get($laboratory->id),
        ]);
    }

    /**
     * Angka ringkas untuk kartu statistik.
     */
    private function stats(array $can): array
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

            $stats['checks_this_month'] = ComputerCheck::whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->count();
        }

        if ($can['tickets']) {
            $tickets = Ticket::query()
                ->selectRaw('status, COUNT(*) as total')
                ->groupBy('status')
                ->pluck('total', 'status');

            $stats['tickets_open'] = (int) ($tickets['Open'] ?? 0);
            $stats['tickets_in_progress'] = (int) ($tickets['In Progress'] ?? 0);
            $stats['tickets_resolved'] = (int) ($tickets['Resolved'] ?? 0) + (int) ($tickets['Closed'] ?? 0);
        }

        if ($can['borrowings']) {
            $stats['borrowings_pending'] = ComputerBorrowing::where('status', 'Pending')->count();
        }

        return $stats;
    }

    /**
     * Jumlah tiket per bulan untuk tahun yang dipilih.
     */
    private function ticketChart(Request $request): array
    {
        $years = Ticket::availableYears();
        $selectedYear = (int) $request->integer('year');

        if (! in_array($selectedYear, $years, true)) {
            $selectedYear = $years[0];
        }

        $counts = array_fill(1, 12, 0);

        Ticket::whereYear('created_at', $selectedYear)
            ->pluck('created_at')
            ->each(function ($createdAt) use (&$counts) {
                $counts[(int) $createdAt->month]++;
            });

        return [
            'years' => $years,
            'selectedYear' => $selectedYear,
            'counts' => $counts,
            'max' => max($counts) ?: 1,
            'total' => array_sum($counts),
            'currentMonth' => $selectedYear === (int) now()->year ? (int) now()->month : null,
        ];
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
                            ? 'Komputer ' . $ticket->computer->code
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
                        'title' => 'Peminjaman oleh ' . $borrowing->borrower_name,
                        'meta' => 'Komputer ' . ($borrowing->computer?->code ?? '-'),
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
                        'title' => 'Pengecekan ' . ($check->computer?->code ?? 'komputer'),
                        'meta' => 'Hasil: ' . $check->overall_status,
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
