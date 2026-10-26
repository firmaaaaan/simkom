<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\Computer;
use App\Models\ComputerBorrowing;
use App\Models\ComputerCheck;
use App\Models\DeviceCheck;
use App\Models\LabUsage;
use App\Models\Laboratory;
use App\Models\MaintenanceChecklist;
use App\Models\Pengadaan;
use App\Models\Ticket;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use ZipArchive;

class ReportController extends Controller
{
    /**
     * Halaman laporan: filter jenis laporan, laboratorium, dan tahun ajaran
     * (termasuk "Semua"). Hanya tabel jenis terpilih yang ditampilkan.
     */
    public function index(Request $request)
    {
        $jenis = $this->resolveJenis($request);
        $laboratories = Laboratory::orderBy('name')->get();
        $academicYears = AcademicYear::orderByDesc('start_year')->get();

        $selectedYear = $this->resolveYear($request, $academicYears);
        $selectedLab = $this->resolveLab($request, $laboratories);

        $allYears = $request->academic_year_id === 'all';
        $years = $allYears ? $academicYears : ($selectedYear ? collect([$selectedYear]) : collect());

        $labIds = $selectedLab->id === 'all'
            ? $laboratories->pluck('id')
            : collect([$selectedLab->id]);

        $rows = $this->collectRows($jenis, $labIds, $years, $allYears);
        $total = $rows->count();

        return view('reports.laporan', compact(
            'jenis', 'laboratories', 'academicYears', 'selectedYear', 'selectedLab', 'rows', 'total', 'allYears'
        ));
    }

    /**
     * Unduh ZIP: satu file PDF per kombinasi laboratorium & tahun ajaran,
     * layout menyesuaikan jenis laporan terpilih.
     *
     * ZipArchive butuh file nyata (seekable) — dipakai file sementara yang
     * dihapus otomatis setelah dikirim (deleteFileAfterSend).
     */
    public function download(Request $request): BinaryFileResponse
    {
        $jenis = $this->resolveJenis($request);
        $jenisTitle = self::sections()[$jenis];
        $laboratories = Laboratory::orderBy('name')->get();
        $academicYears = AcademicYear::orderByDesc('start_year')->get();

        $selectedYear = $this->resolveYear($request, $academicYears);
        $selectedLab = $this->resolveLab($request, $laboratories);

        $allYears = $request->academic_year_id === 'all';
        $years = $allYears ? $academicYears : ($selectedYear ? collect([$selectedYear]) : collect());

        // Pengadaan tidak terikat laboratorium.
        $isPengadaan = $jenis === 'pengadaan';
        $labs = $isPengadaan
            ? collect([(object) ['id' => null, 'name' => '-', 'code' => null]])
            : ($selectedLab->id === 'all' ? $laboratories : collect([$selectedLab]));

        $filename = 'laporan-'.strtolower(preg_replace('/[^A-Za-z0-9]+/', '-', $jenisTitle)).'-'.now()->format('Y-m-d').'.zip';

        $tmp = tempnam(sys_get_temp_dir(), 'laporan-zip');

        $zip = new ZipArchive;
        $zip->open($tmp, ZipArchive::CREATE | ZipArchive::OVERWRITE);

        foreach ($years as $year) {
            foreach ($labs as $lab) {
                $labIds = $lab->id ? collect([$lab->id]) : collect();
                $rows = $this->rowsFor($jenis, $labIds, $year);

                $pdf = Pdf::loadView('reports.print-file', [
                    'jenis' => $jenis,
                    'jenisTitle' => $jenisTitle,
                    'rows' => $rows,
                    'lab' => $isPengadaan ? null : $lab,
                    'year' => $year,
                ])->setPaper('a4', 'portrait')->output();

                $zip->addFromString($this->zipEntryName($jenisTitle, $isPengadaan ? null : $lab, $year), $pdf);
            }
        }

        $zip->close();

        return response()->download($tmp, $filename, ['Content-Type' => 'application/zip'])
            ->deleteFileAfterSend(true);
    }

    /**
     * Kumpulkan baris preview: gabungan tahun terpilih (satu atau semua),
     * diurutkan terbaru lebih dulu. Saat "Semua TA", setiap baris diberi
     * atribut academic_year_name untuk kolom Tahun Ajaran.
     */
    private function collectRows(string $jenis, $labIds, $years, bool $tagYear): \Illuminate\Support\Collection
    {
        $rows = collect();

        foreach ($years as $year) {
            foreach ($this->rowsFor($jenis, $labIds, $year) as $row) {
                if ($tagYear) {
                    $row->setAttribute('academic_year_name', $year->name);
                }
                $rows->push($row);
            }
        }

        // Kartu kendali: baris adalah komputer (bukan transaksi) — urut kode naik.
        if ($jenis === 'card_control') {
            return $rows->sortBy('code')->values();
        }

        $sortKey = match ($jenis) {
            'lab_usages' => 'checked_in_at',
            'borrowings' => 'borrow_date',
            'device_checks' => 'check_date',
            'tickets' => 'created_at',
            'maintenances' => 'maintenance_date',
            'pengadaan' => 'tanggal',
        };

        return $rows->sortByDesc($sortKey)->values();
    }

    /**
     * Query baris satu jenis laporan untuk lab & tahun ajaran tertentu.
     * Jumlah query tetap per kombinasi (tidak N+1).
     *
     * lab_usages & computer_borrowings tidak punya academic_year_id —
     * disaring via rentang tanggal periode (helper internal, tidak
     * ditampilkan ke user).
     */
    private function rowsFor(string $jenis, $labIds, AcademicYear $year)
    {
        $period = [$year->periodStart(), $year->periodEnd()];

        return match ($jenis) {
            'card_control' => $this->computersWithLatestCheck($labIds, $year),
            'lab_usages' => LabUsage::with('laboratory')
                ->whereIn('laboratory_id', $labIds)
                ->whereBetween('checked_in_at', $period)
                ->orderByDesc('checked_in_at')
                ->get(),
            'borrowings' => ComputerBorrowing::with('laboratory', 'computer')
                ->whereIn('laboratory_id', $labIds)
                ->whereBetween('borrow_date', [$period[0]->toDateString(), $period[1]->toDateString()])
                ->orderByDesc('borrow_date')
                ->get(),
            'device_checks' => DeviceCheck::with('laboratory')
                ->where('academic_year_id', $year->id)
                ->whereIn('laboratory_id', $labIds)
                ->orderByDesc('check_date')
                ->get(),
            'tickets' => Ticket::with('laboratory', 'computer')
                ->where('academic_year_id', $year->id)
                ->whereIn('laboratory_id', $labIds)
                ->orderByDesc('created_at')
                ->get(),
            'maintenances' => MaintenanceChecklist::with('laboratory')
                ->where('academic_year_id', $year->id)
                ->whereIn('laboratory_id', $labIds)
                ->orderByDesc('maintenance_date')
                ->get(),
            'pengadaan' => Pengadaan::where('academic_year_id', $year->id)
                ->orderByDesc('tanggal')
                ->get(),
        };
    }

    /**
     * Komputer pada lab terpilih + pengecekan terakhir (kartu kendali) dalam
     * tahun ajaran. Satu query cek untuk semua komputer (hindari N+1).
     * Komputer tanpa cek tetap tampil (belum dicek).
     */
    private function computersWithLatestCheck($labIds, AcademicYear $year)
    {
        $computers = Computer::with('laboratory')
            ->whereIn('laboratory_id', $labIds)
            ->orderBy('code')
            ->get();

        if ($computers->isEmpty()) {
            return $computers;
        }

        $latestChecks = ComputerCheck::whereIn('computer_id', $computers->pluck('id'))
            ->forAcademicYear($year)
            ->with('checkedBy')
            ->get()
            ->groupBy('computer_id')
            ->map(fn ($checks) => $checks->sortByDesc('created_at')->first());

        $computers->each(function (Computer $computer) use ($latestChecks) {
            $computer->latestCheck = $latestChecks->get($computer->id);
            $computer->is_baik = $computer->latestCheck?->overall_status === 'Baik';
        });

        return $computers;
    }

    private function resolveJenis(Request $request): string
    {
        $jenis = $request->input('jenis');

        return array_key_exists($jenis, self::sections()) ? $jenis : 'lab_usages';
    }

    private function resolveYear(Request $request, $academicYears): ?AcademicYear
    {
        if ($request->academic_year_id === 'all') {
            return null;
        }

        return $request->academic_year_id
            ? AcademicYear::find($request->academic_year_id)
            : AcademicYear::current();
    }

    private function resolveLab(Request $request, $laboratories)
    {
        $labId = $request->input('laboratory_id', 'all');

        if ($labId === 'all') {
            return (object) ['id' => 'all', 'name' => 'Semua Laboratorium'];
        }

        return $laboratories->firstWhere('id', $labId)
            ?? (object) ['id' => $labId, 'name' => 'Laboratorium Tidak Ditemukan'];
    }

    private function zipEntryName(string $jenisTitle, $lab, AcademicYear $year): string
    {
        $parts = [$jenisTitle];

        if ($lab) {
            $parts[] = trim($lab->name.($lab->code ? ' '.$lab->code : ''));
        }

        $parts[] = $year->name;

        $name = preg_replace('/[^A-Za-z0-9]+/', '-', implode('_', $parts));

        return trim(preg_replace('/-+/', '-', $name), '-').'.pdf';
    }

    /**
     * Daftar jenis laporan: key => judul. Urutan menentukan urutan dropdown.
     */
    private static function sections(): array
    {
        return [
            'lab_usages' => 'Penggunaan Lab',
            'borrowings' => 'Peminjaman Komputer',
            'device_checks' => 'Pengecekan Perangkat',
            'card_control' => 'Kartu Kendali',
            'tickets' => 'Kendala Praktikum',
            'maintenances' => 'Pemeliharaan',
            'pengadaan' => 'Pengadaan',
        ];
    }
}
