<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\Computer;
use App\Models\ComputerBorrowing;
use App\Models\ComputerCheck;
use App\Models\DeviceCheck;
use App\Models\Laboratory;
use App\Models\LabUsage;
use App\Models\MaintenanceChecklist;
use App\Models\Pengadaan;
use App\Models\Ticket;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
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
    private function collectRows(string $jenis, $labIds, $years, bool $tagYear): Collection
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

    /**
     * Laporan inventaris per laboratorium: jumlah komputer menurut kapasitas
     * RAM, tipe storage, dan jenis processor. Inventaris adalah kondisi saat
     * ini, jadi tidak difilter tahun ajaran.
     */
    public function inventory(Request $request)
    {
        return view('reports.inventory', $this->inventoryData($request));
    }

    public function inventoryPrint(Request $request)
    {
        return view('reports.inventory-print', $this->inventoryData($request));
    }

    private function inventoryData(Request $request): array
    {
        $laboratories = Laboratory::orderBy('name')->get();
        $selectedLabId = (string) $request->input('laboratory_id', 'all');

        $labs = $selectedLabId === 'all'
            ? $laboratories
            : $laboratories->where('id', $selectedLabId)->values();

        $computersByLab = Computer::with('hardware')
            ->whereIn('laboratory_id', $labs->pluck('id'))
            ->orderBy('code')
            ->get()
            ->groupBy('laboratory_id');

        $rows = $labs->map(
            fn (Laboratory $lab) => $this->inventoryRow($lab, $computersByLab->get($lab->id, collect()))
        );

        return [
            'laboratories' => $laboratories,
            'selectedLabId' => $selectedLabId,
            'rows' => $rows,
            'title' => 'Inventaris-'.($rows->count() === 1
                ? $rows->first()['laboratory']->name
                : 'Semua Laboratorium'),
        ];
    }

    /**
     * Rekap satu laboratorium: hitungan RAM / storage / processor untuk tabel
     * ringkasan, plus rincian spesifikasi tiap komputer.
     */
    private function inventoryRow(Laboratory $laboratory, $computers): array
    {
        $ramCounts = [];
        $storageCounts = [];
        $processorCounts = [];
        $details = [];

        foreach ($computers as $computer) {
            $ramNames = $this->hardwareNames($computer->hardware, 'RAM');
            $storageNames = $this->hardwareNames($computer->hardware, 'Storage');
            $processorNames = $this->hardwareNames($computer->hardware, 'Processor')
                ->unique(fn ($name) => mb_strtolower($name))
                ->values();

            $ramTotal = $ramNames->sum(fn ($name) => $this->ramCapacity($name) ?? 0);
            $ramKey = $ramNames->isEmpty() ? 'Tidak tercatat' : ($ramTotal > 0 ? $ramTotal : 'Tidak diketahui');
            $ramLabel = is_int($ramKey) ? $ramKey.' GB' : $ramKey;
            $ramCounts[$ramKey] = ($ramCounts[$ramKey] ?? 0) + 1;

            $storageLabels = $storageNames
                ->map(fn ($name) => $this->storageLabel($name))
                ->unique()
                ->values();

            if ($storageLabels->isEmpty()) {
                $storageLabels = collect(['Tidak tercatat']);
            }

            foreach ($storageLabels as $type) {
                $storageCounts[$type] = ($storageCounts[$type] ?? 0) + 1;
            }

            if ($processorNames->isEmpty()) {
                $processorNames = collect(['Tidak tercatat']);
            }

            foreach ($processorNames as $name) {
                $key = mb_strtolower($name);
                $processorCounts[$key] ??= ['label' => $name, 'count' => 0];
                $processorCounts[$key]['count']++;
            }

            $details[] = [
                'computer' => $computer,
                'ram' => $ramLabel,
                'storage' => $storageLabels->implode(', '),
                'processor' => $processorNames->implode(', '),
            ];
        }

        return [
            'laboratory' => $laboratory,
            'computers_count' => $computers->count(),
            'ram' => $this->ramRecap($ramCounts),
            'storage' => $this->storageRecap($storageCounts),
            'processors' => collect($processorCounts)
                ->sortBy([['count', 'desc'], ['label', 'asc']])
                ->values(),
            'details' => $details,
        ];
    }

    /**
     * Nama komponen untuk satu kategori, dicocokkan tanpa memedulikan besar
     * kecil huruf karena kategori hasil import mempertahankan judul kolom file.
     */
    private function hardwareNames($hardware, string $category)
    {
        return $hardware
            ->filter(fn ($item) => mb_strtolower((string) $item->category) === mb_strtolower($category))
            ->pluck('name')
            ->map(fn ($name) => trim((string) $name))
            ->filter()
            ->values();
    }

    /**
     * Total kapasitas RAM (GB) dari nama komponen bebas: "8GB DDR4" -> 8,
     * "2x8GB" -> 16, "16 GB DDR4 3200" -> 16. Null bila tidak terbaca.
     */
    private function ramCapacity(string $name): ?int
    {
        if (preg_match('/^\s*(\d+)\s*[x×]\s*(\d+)\s*gb/i', $name, $match)) {
            return (int) $match[1] * (int) $match[2];
        }

        if (preg_match('/(\d+)\s*gb/i', $name, $match)) {
            return (int) $match[1];
        }

        return null;
    }

    /**
     * Tipe storage dari nama komponen: SSD (termasuk NVMe), HDD, atau lainnya.
     */
    private function storageType(string $name): string
    {
        $name = mb_strtolower($name);

        if (str_contains($name, 'ssd') || str_contains($name, 'nvme')) {
            return 'SSD';
        }

        if (str_contains($name, 'hdd') || str_contains($name, 'hard disk') || str_contains($name, 'harddisk')) {
            return 'HDD';
        }

        return 'Lainnya';
    }

    /**
     * Kapasitas storage dari nama komponen: "SSD 512GB" -> ["512 GB", 512],
     * "HDD 1TB" -> ["1 TB", 1024], "1.5 TB NVMe" -> ["1.5 TB", 1536].
     * Null bila tidak terbaca.
     */
    private function storageCapacity(string $name): ?array
    {
        if (! preg_match('/(\d+(?:[.,]\d+)?)\s*(tb|gb)\b/i', $name, $match)) {
            return null;
        }

        $value = (float) str_replace(',', '.', $match[1]);
        $isTerabyte = mb_strtolower($match[2]) === 'tb';
        $label = rtrim(rtrim(number_format($value, 2, '.', ''), '0'), '.').($isTerabyte ? ' TB' : ' GB');

        return [
            'label' => $label,
            'gb' => (int) round($value * ($isTerabyte ? 1024 : 1)),
        ];
    }

    /**
     * Label storage untuk rekap & rincian: "SSD 512 GB" bila tipe dan
     * kapasitas terbaca, "SSD" bila kapasitas tidak terbaca, dan "512 GB"
     * bila tipe tidak dikenali.
     */
    private function storageLabel(string $name): string
    {
        $type = $this->storageType($name);
        $capacity = $this->storageCapacity($name);

        if ($capacity === null) {
            return $type;
        }

        return $type === 'Lainnya' ? $capacity['label'] : $type.' '.$capacity['label'];
    }

    /**
     * Rekap RAM: kapasitas urut numerik, "Tidak diketahui"/"Tidak tercatat"
     * di belakang.
     */
    private function ramRecap(array $counts)
    {
        return collect($counts)
            ->map(fn ($count, $key) => [
                'label' => is_int($key) ? $key.' GB' : $key,
                'count' => $count,
                'sort' => is_int($key) ? $key : PHP_INT_MAX,
            ])
            ->sortBy([['sort', 'asc'], ['label', 'asc']])
            ->values()
            ->map(fn ($row) => ['label' => $row['label'], 'count' => $row['count']]);
    }

    /**
     * Rekap storage: dikelompokkan per tipe lalu kapasitas terbesar dulu,
     * sehingga "SSD 512 GB" dan "SSD 1 TB" tampil terpisah.
     */
    private function storageRecap(array $counts)
    {
        return collect($counts)
            ->map(fn ($count, $label) => ['label' => $label, 'count' => $count])
            ->values()
            ->sort(function ($a, $b) {
                $order = $this->storageTypeOrder($a['label']) <=> $this->storageTypeOrder($b['label']);

                if ($order !== 0) {
                    return $order;
                }

                $capacityA = $this->storageCapacity($a['label'])['gb'] ?? 0;
                $capacityB = $this->storageCapacity($b['label'])['gb'] ?? 0;

                return ($capacityB <=> $capacityA) ?: strcmp($a['label'], $b['label']);
            })
            ->values();
    }

    private function storageTypeOrder(string $label): int
    {
        return match (true) {
            str_starts_with($label, 'SSD') => 0,
            str_starts_with($label, 'HDD') => 1,
            str_starts_with($label, 'Tidak') => 3,
            default => 2,
        };
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
