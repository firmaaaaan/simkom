<?php

namespace App\Http\Controllers;

use App\Exports\ComputerExport;
use App\Models\Computer;
use App\Models\Hardware;
use App\Models\Software;
use App\Models\Laboratory;
use App\Models\ComputerCheck;
use App\Models\Setting;
use App\Models\AcademicYear;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Facades\Excel;

class ComputerController extends Controller
{
    public function index(Request $request)
    {
        $computers = $this->filteredQuery($request)->latest()->paginate(10);
        $publicSpecEnabled = Setting::publicSpecEnabled();

        return view('computers.index', compact('computers', 'publicSpecEnabled'));
    }

    /**
     * Query daftar komputer dengan filter yang sedang aktif — dipakai bersama
     * oleh halaman daftar dan export Excel.
     */
    private function filteredQuery(Request $request)
    {
        $query = Computer::with(['laboratory', 'hardware', 'software']);

        if ($search = $request->search) {
            $query->where('code', 'like', "%{$search}%")
                  ->orWhereHas('laboratory', fn($q) => $q->where('name', 'like', "%{$search}%"));
        }

        return $query;
    }

    public function export(Request $request)
    {
        return Excel::download(
            new ComputerExport($this->filteredQuery($request)->latest()),
            'komputer-' . now()->format('Y-m-d') . '.xlsx'
        );
    }

    public function create()
    {
        $laboratories = Laboratory::orderBy('name')->get();
        $hardware = Hardware::orderBy('name')->get();
        $software = Software::orderBy('name')->get();
        return view('computers.create', compact('laboratories', 'hardware', 'software'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:50|unique:computers,code',
            'laboratory_id' => 'nullable|exists:laboratories,id',
            'status' => 'required|in:Aktif,Tidak Aktif,Maintenance',
            'description' => 'nullable|string',
            'hardware' => 'nullable|array',
            'hardware.*' => 'exists:hardware,id',
            'software' => 'nullable|array',
            'software.*' => 'exists:software,id',
        ]);

        $hardwareIds = $validated['hardware'] ?? [];
        $softwareIds = $validated['software'] ?? [];
        unset($validated['hardware'], $validated['software']);

        $computer = Computer::create($validated);
        $computer->hardware()->sync($hardwareIds);
        $computer->software()->sync($softwareIds);

        return redirect()->route('computers.index')->with('success', 'Komputer berhasil ditambahkan.');
    }

    public function show(Computer $computer)
    {
        $computer->load(['laboratory', 'hardware', 'software']);
        return view('computers.show', compact('computer'));
    }

    public function edit(Computer $computer)
    {
        $computer->load(['hardware', 'software']);
        $laboratories = Laboratory::orderBy('name')->get();
        $hardware = Hardware::orderBy('name')->get();
        $software = Software::orderBy('name')->get();
        $selectedHardware = $computer->hardware->pluck('id')->toArray();
        $selectedSoftware = $computer->software->pluck('id')->toArray();
        return view('computers.edit', compact('computer', 'laboratories', 'hardware', 'software', 'selectedHardware', 'selectedSoftware'));
    }

    public function update(Request $request, Computer $computer)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:50|unique:computers,code,' . $computer->id,
            'laboratory_id' => 'nullable|exists:laboratories,id',
            'status' => 'required|in:Aktif,Tidak Aktif,Maintenance',
            'description' => 'nullable|string',
            'hardware' => 'nullable|array',
            'hardware.*' => 'exists:hardware,id',
            'software' => 'nullable|array',
            'software.*' => 'exists:software,id',
        ]);

        $hardwareIds = $validated['hardware'] ?? [];
        $softwareIds = $validated['software'] ?? [];
        unset($validated['hardware'], $validated['software']);

        $computer->update($validated);
        $computer->hardware()->sync($hardwareIds);
        $computer->software()->sync($softwareIds);

        return redirect()->route('computers.index')->with('success', 'Komputer berhasil diperbarui.');
    }

    public function destroy(Computer $computer)
    {
        $computer->hardware()->detach();
        $computer->software()->detach();
        $computer->delete();

        return redirect()->route('computers.index')->with('success', 'Komputer berhasil dihapus.');
    }

    public function bulkDestroy(Request $request)
    {
        $request->validate([
            'ids' => 'required|array|min:1',
            'ids.*' => 'exists:computers,id',
        ]);

        $computers = Computer::whereIn('id', $request->ids)->get();
        foreach ($computers as $computer) {
            $computer->hardware()->detach();
            $computer->software()->detach();
            $computer->delete();
        }

        return redirect()->route('computers.index')->with('success', count($request->ids) . ' komputer berhasil dihapus.');
    }

    public function generate()
    {
        $laboratories = Laboratory::orderBy('name')->get();
        return view('computers.generate', compact('laboratories'));
    }

    public function storeGenerate(Request $request)
    {
        $validated = $request->validate([
            'laboratory_id' => 'required|exists:laboratories,id',
            'prefix' => 'required|string|max:10',
            'start_number' => 'required|integer|min:1',
            'count' => 'required|integer|min:1|max:500',
            'status' => 'required|in:Aktif,Tidak Aktif,Maintenance',
        ]);

        $prefix = strtoupper($validated['prefix']);
        $start = $validated['start_number'];
        $count = $validated['count'];
        $labId = $validated['laboratory_id'];
        $status = $validated['status'];

        $created = 0;
        $skipped = 0;

        for ($i = 0; $i < $count; $i++) {
            $num = $start + $i;
            $code = $prefix . '-' . str_pad($num, 3, '0', STR_PAD_LEFT);

            if (Computer::where('code', $code)->exists()) {
                $skipped++;
                continue;
            }

            Computer::create([
                'code' => $code,
                'laboratory_id' => $labId,
                'status' => $status,
            ]);
            $created++;
        }

        $message = "{$created} komputer berhasil dibuat.";
        if ($skipped > 0) {
            $message .= " {$skipped} dilewati (kode sudah ada).";
        }

        return redirect()->route('computers.index')->with('success', $message);
    }

    public function card(Request $request, Computer $computer)
    {
        // Route ini sengaja tidak lagi di dalam middleware auth/permission supaya QR lama
        // pada stiker tidak mendarat di halaman login. Tamu dan user tanpa izin
        // dialihkan ke halaman kartu kendali publik (riwayat pengecekan saja).
        if (! auth()->check() || ! auth()->user()->hasAnyPermission(['manage-computers'])) {
            return redirect()->route('kartu.show', array_filter([
                'computer' => $computer,
                'month' => $request->month,
                'year' => $request->year,
            ]));
        }

        $computer->load(['laboratory', 'hardware', 'software']);

        $month = $request->integer('month') ?: null;
        $year = $request->integer('year') ?: null;

        $checks = ComputerCheck::where('computer_id', $computer->id)
            ->forPeriod($month, $year)
            ->with(['checkedBy', 'academicYear', 'hardwareChecks.checkable'])
            ->latest()
            ->get();

        $years = ComputerCheck::availableYears($computer);
        $currentYear = AcademicYear::current();

        return view('computers.card', compact('computer', 'checks', 'years', 'month', 'year', 'currentYear'));
    }

    public function cardPrint(Computer $computer)
    {
        $computer->load(['laboratory', 'hardware', 'software']);
        $checks = ComputerCheck::where('computer_id', $computer->id)
            ->with(['checkedBy', 'hardwareChecks.checkable'])
            ->latest()
            ->get();

        $latestCheck = $checks->first();

        return view('computers.card-print', compact('computer', 'checks', 'latestCheck'));
    }

    public function storeCheck(Request $request, Computer $computer)
    {
        $validated = $request->validate([
            'hardware_status' => 'nullable|array',
            'hardware_status.*' => 'required|string',
            'hardware_notes' => 'nullable|array',
            'hardware_notes.*' => 'nullable|string',
            'software_status' => 'nullable|array',
            'software_status.*' => 'required|string',
            'software_notes' => 'nullable|array',
            'software_notes.*' => 'nullable|string',
            'overall_status' => 'required|in:Baik,Perlu Perbaikan,Kritis',
            'notes' => 'nullable|string',
        ]);

        $check = ComputerCheck::create([
            'computer_id' => $computer->id,
            'academic_year_id' => AcademicYear::current()?->id,
            'overall_status' => $validated['overall_status'],
            'notes' => $validated['notes'] ?? null,
            'checked_by' => auth()->id(),
        ]);

        $hardwareItems = $computer->hardware;
        foreach ($validated['hardware_status'] ?? [] as $index => $status) {
            if (isset($hardwareItems[$index])) {
                $check->hardwareChecks()->create([
                    'checkable_type' => Hardware::class,
                    'checkable_id' => $hardwareItems[$index]->id,
                    'status' => $status,
                    'notes' => $validated['hardware_notes'][$index] ?? null,
                ]);
            }
        }

        $softwareItems = $computer->software;
        foreach ($validated['software_status'] ?? [] as $index => $status) {
            if (isset($softwareItems[$index])) {
                $check->hardwareChecks()->create([
                    'checkable_type' => Software::class,
                    'checkable_id' => $softwareItems[$index]->id,
                    'status' => $status,
                    'notes' => $validated['software_notes'][$index] ?? null,
                ]);
            }
        }

        return redirect()->route('computers.card', $computer)->with('success', 'Pengecekan berhasil disimpan.');
    }

    public function qrStiker(Request $request)
    {
        $laboratories = Laboratory::orderBy('name')->get();
        $computers = collect();
        $selectedLab = null;

        if ($labId = $request->laboratory_id) {
            if ($labId === 'all') {
                $computers = Computer::with('laboratory')->orderBy('code')->get();
                $selectedLab = (object) ['id' => 'all', 'name' => 'Semua Laboratorium'];
            } else {
                $selectedLab = Laboratory::find($labId);
                $computers = Computer::with('laboratory')
                    ->where('laboratory_id', $labId)
                    ->orderBy('code')
                    ->get();
            }
        }

        return view('computers.qr-stiker', compact('laboratories', 'computers', 'selectedLab'));
    }

    public function praktikumLabels(Request $request)
    {
        $laboratories = Laboratory::orderBy('name')->get();
        $computers = collect();
        $selectedLab = null;

        $labId = $request->laboratory_id;

        if ($labId === 'all') {
            $selectedLab = (object) ['id' => 'all', 'name' => 'Semua Laboratorium'];
            $computers = Computer::with('laboratory')
                ->orderBy('laboratory_id')
                ->orderBy('code')
                ->get()
                ->groupBy('laboratory_id')
                ->flatMap(function ($group) {
                    return $group->values()->map(function ($computer, $index) {
                        $computer->nomor_meja = $index + 1;
                        return $computer;
                    });
                })
                ->values();
        } elseif ($labId) {
            $selectedLab = Laboratory::find($labId);
            $computers = Computer::where('laboratory_id', $labId)
                ->orderBy('code')
                ->get()
                ->map(function ($computer, $index) {
                    $computer->nomor_meja = $index + 1;
                    return $computer;
                });
        } elseif ($laboratories->isNotEmpty()) {
            $selectedLab = $laboratories->first();
            $computers = Computer::where('laboratory_id', $selectedLab->id)
                ->orderBy('code')
                ->get()
                ->map(function ($computer, $index) {
                    $computer->nomor_meja = $index + 1;
                    return $computer;
                });
        }

        return view('computers.praktikum-labels', compact('laboratories', 'computers', 'selectedLab'));
    }

    private function getReportData(Request $request)
    {
        $laboratories = Laboratory::orderBy('name')->get();

        // Semua tahun ajaran ditampilkan (tidak hanya yang berstatus Aktif),
        // supaya periode yang mencakup tanggal pengecekan tetap bisa dipilih.
        $academicYears = AcademicYear::orderBy('start_year', 'desc')->get();

        $computers = collect();
        $selectedLab = null;
        $selectedYear = null;
        $summary = ['total' => 0, 'baik' => 0, 'tidak_baik' => 0, 'belum_dicek' => 0];

        $labId = $request->laboratory_id;
        $yearId = $request->academic_year_id;

        if ($yearId) {
            $selectedYear = AcademicYear::find($yearId);
        }

        if ($selectedYear) {
            if ($labId === 'all') {
                $selectedLab = (object) ['id' => 'all', 'name' => 'Semua Laboratorium'];
                $computers = Computer::with('laboratory')
                    ->orderBy('laboratory_id')
                    ->orderBy('code')
                    ->get();
            } elseif ($labId) {
                $selectedLab = Laboratory::find($labId);

                if ($selectedLab) {
                    $computers = Computer::where('laboratory_id', $labId)->orderBy('code')->get();
                }
            }

            $latestChecks = $this->latestChecksPerComputer($computers->pluck('id'), $selectedYear);

            $computers->each(function ($computer) use ($latestChecks) {
                $computer->latestCheck = $latestChecks->get($computer->id);
                $computer->is_baik = $computer->latestCheck?->overall_status === 'Baik';
            });

            $summary = [
                'total' => $computers->count(),
                'baik' => $computers->filter(fn ($c) => $c->is_baik)->count(),
                'tidak_baik' => $computers->filter(fn ($c) => $c->latestCheck && ! $c->is_baik)->count(),
                'belum_dicek' => $computers->filter(fn ($c) => ! $c->latestCheck)->count(),
            ];
        }

        return compact('laboratories', 'academicYears', 'computers', 'selectedLab', 'selectedYear', 'summary');
    }

    /**
     * Ambil pengecekan terakhir tiap komputer dalam periode tahun ajaran
     * dengan satu query (menghindari N+1: satu query per komputer).
     */
    private function latestChecksPerComputer(Collection $computerIds, AcademicYear $academicYear): Collection
    {
        if ($computerIds->isEmpty()) {
            return collect();
        }

        return ComputerCheck::whereIn('computer_id', $computerIds)
            ->forAcademicYear($academicYear)
            ->with('checkedBy')
            ->get()
            ->groupBy('computer_id')
            ->map(fn ($checks) => $checks->sortByDesc('created_at')->first());
    }

    public function reportCardControl(Request $request)
    {
        $data = $this->getReportData($request);
        return view('computers.report-card-control', $data);
    }

    public function reportCardControlPrint(Request $request)
    {
        $data = $this->getReportData($request);
        return view('computers.report-card-control-print', $data);
    }
}
