<?php

namespace App\Http\Controllers;

use App\Exports\DeviceCheckExport;
use App\Models\AcademicYear;
use App\Models\Computer;
use App\Models\DeviceCheck;
use App\Models\DeviceCheckItem;
use App\Models\Laboratory;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class DeviceCheckController extends Controller
{
    /**
     * Query daftar pengecekan — dipakai halaman daftar & export Excel.
     */
    private function listQuery()
    {
        return DeviceCheck::with(['laboratory', 'academicYear'])
            ->withCount(['items as computers_count' => fn ($query) => $query->select(DB::raw('count(distinct computer_id)'))])
            ->withCount('items')
            ->latest('check_date')
            ->latest('id');
    }

    public function index()
    {
        $checks = $this->listQuery()->paginate(10);

        return view('device-checks.index', compact('checks'));
    }

    public function export()
    {
        return Excel::download(
            new DeviceCheckExport($this->listQuery()),
            'pengecekan-perangkat-' . now()->format('Y-m-d') . '.xlsx'
        );
    }

    public function create(Request $request)
    {
        return view('device-checks.create', $this->formData(new DeviceCheck([
            'laboratory_id' => $request->laboratory_id,
            'academic_year_id' => $request->academic_year_id,
            'check_date' => now()->toDateString(),
        ])));
    }

    public function store(Request $request)
    {
        $validated = $this->validatePayload($request);

        $check = DeviceCheck::create(Arr::only($validated, [
            'laboratory_id',
            'academic_year_id',
            'check_date',
            'officer_name',
            'notes',
        ]));

        $this->syncItems($check, $validated['items'] ?? []);

        return redirect()
            ->route('device-checks.show', $check)
            ->with('success', 'Data pengecekan perangkat berhasil disimpan.');
    }

    public function show(DeviceCheck $deviceCheck)
    {
        return view('device-checks.show', $this->formData($deviceCheck));
    }

    public function edit(DeviceCheck $deviceCheck)
    {
        return view('device-checks.edit', $this->formData($deviceCheck));
    }

    public function update(Request $request, DeviceCheck $deviceCheck)
    {
        $validated = $this->validatePayload($request);

        $deviceCheck->update(Arr::only($validated, [
            'laboratory_id',
            'academic_year_id',
            'check_date',
            'officer_name',
            'notes',
        ]));

        $this->syncItems($deviceCheck, $validated['items'] ?? []);

        return redirect()
            ->route('device-checks.show', $deviceCheck)
            ->with('success', 'Data pengecekan perangkat berhasil diperbarui.');
    }

    public function destroy(DeviceCheck $deviceCheck)
    {
        $deviceCheck->items()->delete();
        $deviceCheck->delete();

        return redirect()
            ->route('device-checks.index')
            ->with('success', 'Data pengecekan perangkat berhasil dihapus.');
    }

    public function print(DeviceCheck $deviceCheck)
    {
        return view('device-checks.print', $this->formData($deviceCheck));
    }

    /**
     * Laporan pengecekan per tahun ajaran: rekap setiap pengecekan pada tahun
     * terpilih + matriksnya. Baris rekap bisa dipilih lewat checkbox
     * (?checks[]=id) sehingga admin bebas menentukan apa yang dicetak.
     */
    public function report(Request $request)
    {
        return view('device-checks.report', $this->reportData($request));
    }

    public function reportPrint(Request $request)
    {
        return view('device-checks.report-print', $this->reportData($request));
    }

    /**
     * Data yang dibutuhkan form & halaman detail: daftar lab, tahun ajaran,
     * komputer pada lab terpilih, serta peta centang per komputer.
     */
    private function formData(DeviceCheck $check): array
    {
        $laboratories = Laboratory::orderBy('name')->get();
        $academicYears = AcademicYear::orderByDesc('start_year')->get();

        $computers = $check->laboratory_id
            ? Computer::where('laboratory_id', $check->laboratory_id)->orderBy('code')->get()
            : collect();

        $checked = $check->exists
            ? $check->items()->get()
                ->mapWithKeys(fn (DeviceCheckItem $item) => [$item->computer_id . '.' . $item->item_key => $item->is_checked])
                ->all()
            : [];

        return [
            'check' => $check,
            'laboratories' => $laboratories,
            'academicYears' => $academicYears,
            'computers' => $computers,
            'checked' => $checked,
        ];
    }

    /**
     * Data laporan per tahun ajaran. Jumlah query tetap (tidak tumbuh
     * seiring jumlah lab/pengecekan).
     */
    private function reportData(Request $request): array
    {
        $laboratories = Laboratory::orderBy('name')->get();
        $academicYears = AcademicYear::orderByDesc('start_year')->get();

        $selectedYear = $request->academic_year_id ? AcademicYear::find($request->academic_year_id) : null;
        $selectedLab = null;
        $rows = collect();
        $selectedRows = collect();
        $summary = [
            'labs' => 0,
            'checked_labs' => 0,
            'computers' => 0,
            'covered_computers' => 0,
            'checks' => 0,
            'cells' => 0,
            'ok' => 0,
            'problems' => 0,
        ];

        if ($selectedYear) {
            $labId = $request->laboratory_id;
            $filtered = $labId && $labId !== 'all';

            // Satu query lab dipakai ulang untuk dropdown dan isi laporan.
            $labs = $filtered
                ? $laboratories->where('id', (int) $labId)->values()
                : $laboratories;

            $selectedLab = $filtered
                ? $labs->first()
                : (object) ['id' => 'all', 'name' => 'Semua Laboratorium'];

            $labIds = $labs->pluck('id');
            $itemCount = DeviceCheck::itemColumnCount();

            $computersByLab = Computer::whereIn('laboratory_id', $labIds)
                ->orderBy('code')
                ->get()
                ->groupBy('laboratory_id');

            $checksByLab = DeviceCheck::with('items')
                ->where('academic_year_id', $selectedYear->id)
                ->whereIn('laboratory_id', $labIds)
                ->orderByDesc('check_date')
                ->orderByDesc('id')
                ->get()
                ->groupBy('laboratory_id');

            // Tanpa parameter checks[] semua baris dianggap terpilih.
            $requested = collect($request->input('checks', []))
                ->filter(fn ($id) => is_numeric($id))
                ->map(fn ($id) => (int) $id);
            $hasSelection = $requested->isNotEmpty();

            foreach ($labs as $laboratory) {
                $computers = $computersByLab->get($laboratory->id, collect());
                $checks = $checksByLab->get($laboratory->id, collect());

                if ($checks->isEmpty()) {
                    // Lab yang belum pernah dicek tetap tampil sebagai penanda.
                    $rows->push([
                        'laboratory' => $laboratory,
                        'check' => null,
                        'computers' => $computers,
                        'checked' => [],
                        'cells' => 0,
                        'ok' => 0,
                        'problems' => 0,
                        'selectable' => false,
                        'selected' => false,
                    ]);

                    continue;
                }

                foreach ($checks as $check) {
                    $checked = $check->items
                        ->mapWithKeys(fn (DeviceCheckItem $item) => [
                            $item->computer_id . '.' . $item->item_key => $item->is_checked,
                        ])->all();

                    $cells = $computers->count() * $itemCount;
                    $ok = count(array_filter($checked));

                    $rows->push([
                        'laboratory' => $laboratory,
                        'check' => $check,
                        'computers' => $computers,
                        'checked' => $checked,
                        'cells' => $cells,
                        'ok' => $ok,
                        'problems' => $cells - $ok,
                        'selectable' => true,
                        'selected' => ! $hasSelection || $requested->contains($check->id),
                    ]);
                }
            }

            $selectedRows = $hasSelection ? $rows->where('selected') : $rows;
            $covered = $selectedRows->whereNotNull('check');
            $coveredLabIds = $covered->pluck('laboratory.id')->unique();

            $summary = [
                'labs' => $labs->count(),
                'checked_labs' => $coveredLabIds->count(),
                // Total komputer pada lab yang sedang difilter (semua lab, terlepas dari sudah dicek atau belum).
                'computers' => $labs->sum(fn (Laboratory $lab) => $computersByLab->get($lab->id, collect())->count()),
                'covered_computers' => $coveredLabIds->sum(fn ($id) => $computersByLab->get($id, collect())->count()),
                'checks' => $covered->count(),
                'cells' => $covered->sum('cells'),
                'ok' => $covered->sum('ok'),
                'problems' => $covered->sum('problems'),
            ];
        }

        return compact('laboratories', 'academicYears', 'selectedYear', 'selectedLab', 'rows', 'selectedRows', 'summary');
    }

    private function validatePayload(Request $request): array
    {
        return $request->validate([
            'laboratory_id' => 'required|exists:laboratories,id',
            'academic_year_id' => 'required|exists:academic_years,id',
            'check_date' => 'required|date',
            'officer_name' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
            'items' => 'nullable|array',
            'items.*' => 'array',
        ]);
    }

    /**
     * Tulis ulang seluruh sel matriks: satu baris database per komputer × item,
     * termasuk sel yang tidak dicentang (is_checked = false).
     */
    private function syncItems(DeviceCheck $check, array $items): void
    {
        $check->items()->delete();

        $computerIds = Computer::where('laboratory_id', $check->laboratory_id)->pluck('id');
        $itemKeys = DeviceCheck::itemKeys();
        $now = now();
        $rows = [];

        foreach ($computerIds as $computerId) {
            foreach ($itemKeys as $itemKey) {
                $rows[] = [
                    'device_check_id' => $check->id,
                    'computer_id' => $computerId,
                    'item_key' => $itemKey,
                    'is_checked' => ! empty($items[$itemKey][$computerId]),
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }

        foreach (array_chunk($rows, 500) as $chunk) {
            DeviceCheckItem::insert($chunk);
        }
    }
}
