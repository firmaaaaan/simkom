<?php

namespace App\Http\Controllers;

use App\Exports\MaintenanceExport;
use App\Exports\MaintenanceTemplate;
use App\Imports\MaintenanceImport;
use App\Models\AcademicYear;
use App\Models\Computer;
use App\Models\Laboratory;
use App\Models\MaintenanceChecklist;
use App\Models\MaintenanceChecklistItem;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class MaintenanceController extends Controller
{
    /**
     * Query daftar pemeliharaan — dipakai halaman daftar, cetak, & export Excel.
     * Filter laboratorium & tahun ajaran: "all"/kosong berarti semua.
     */
    private function listQuery(Request $request)
    {
        return MaintenanceChecklist::with(['laboratory', 'academicYear'])
            ->when(
                $request->filled('laboratory_id') && $request->input('laboratory_id') !== 'all',
                fn ($query) => $query->where('laboratory_id', $request->input('laboratory_id'))
            )
            ->when(
                $request->filled('academic_year_id') && $request->input('academic_year_id') !== 'all',
                fn ($query) => $query->where('academic_year_id', $request->input('academic_year_id'))
            )
            ->latest('maintenance_date')
            ->latest('id');
    }

    public function index(Request $request)
    {
        $checklists = $this->listQuery($request)->paginate(10)->withQueryString();

        return view('maintenance.index', [
            'checklists' => $checklists,
            'laboratories' => Laboratory::orderBy('name')->get(),
            'academicYears' => AcademicYear::orderByDesc('start_year')->get(),
            'selectedLaboratoryId' => $request->input('laboratory_id', 'all'),
            'selectedAcademicYearId' => $request->input('academic_year_id', 'all'),
        ]);
    }

    public function export(Request $request)
    {
        return Excel::download(
            new MaintenanceExport($this->listQuery($request)),
            'pemeliharaan-'.now()->format('Y-m-d').'.xlsx'
        );
    }

    public function template()
    {
        return Excel::download(new MaintenanceTemplate, 'template-pemeliharaan.xlsx');
    }

    public function import()
    {
        return view('maintenance.import', [
            'laboratories' => Laboratory::orderBy('name')->get(),
            'academicYears' => AcademicYear::orderByDesc('start_year')->get(),
            'inspectorName' => auth()->user()?->name,
        ]);
    }

    /**
     * Import pemeliharaan dari Excel. Konteks pemeliharaan (lab, tahun
     * ajaran, tanggal, pemeriksa, catatan) diisi di form karena file hanya
     * berisi matriks centang komputer × pertanyaan.
     */
    public function storeImport(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv|max:10240',
            'laboratory_id' => 'required|exists:laboratories,id',
            'academic_year_id' => 'required|exists:academic_years,id',
            'maintenance_date' => 'required|date',
            'inspector_name' => 'nullable|string|max:255',
            'notes_computer' => 'nullable|string',
            'notes_mouse_keyboard' => 'nullable|string',
            'notes_ups' => 'nullable|string',
            'notes_monitor' => 'nullable|string',
        ]);

        $import = new MaintenanceImport(
            $request->input('laboratory_id'),
            $request->input('academic_year_id'),
            $request->input('maintenance_date'),
            $request->input('inspector_name'),
            [
                'notes_computer' => $request->input('notes_computer'),
                'notes_mouse_keyboard' => $request->input('notes_mouse_keyboard'),
                'notes_ups' => $request->input('notes_ups'),
                'notes_monitor' => $request->input('notes_monitor'),
            ],
        );

        try {
            Excel::import($import, $request->file('file'));
        } catch (\Exception $e) {
            return redirect()->route('maintenance.import')
                ->with('import_error', 'Gagal mengimport: '.$e->getMessage());
        }

        $summary = [];
        if ($import->getCreatedCount() > 0) {
            $summary[] = $import->getCreatedCount().' pemeliharaan baru';
        }
        if ($import->getUpdatedCount() > 0) {
            $summary[] = $import->getUpdatedCount().' diperbarui';
        }
        $message = $summary
            ? 'Berhasil mengimpor '.implode(' dan ', $summary)
            : 'Tidak ada pemeliharaan yang diimpor';
        if ($year = $import->getAcademicYear()) {
            $message .= " pada {$year->name}";
        }
        $message .= '.';

        if ($errors = $import->getErrors()) {
            $message .= ' '.count($errors).' baris gagal: '.implode('; ', array_slice($errors, 0, 5));
            if (count($errors) > 5) {
                $message .= ' dan '.(count($errors) - 5).' lagi...';
            }
        }

        return redirect()->route('maintenance.import')
            ->with('success', $message)
            ->with('import_errors', $import->getErrors());
    }

    public function create(Request $request)
    {
        $laboratories = Laboratory::orderBy('name')->get();
        $academicYears = AcademicYear::active()->orderByDesc('start_year')->get();
        $computers = collect();
        $selectedLab = $request->laboratory_id;
        $selectedYear = $request->academic_year_id;

        if ($selectedLab) {
            $computers = Computer::where('laboratory_id', $selectedLab)
                ->orderBy('code')
                ->get();
        }

        $checklistItems = MaintenanceChecklist::getChecklistItems();

        return view('maintenance.create', compact('laboratories', 'academicYears', 'computers', 'selectedLab', 'selectedYear', 'checklistItems'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'laboratory_id' => 'required|exists:laboratories,id',
            'academic_year_id' => 'required|exists:academic_years,id',
            'maintenance_date' => 'required|date',
            'inspector_name' => 'nullable|string|max:255',
            'notes_computer' => 'nullable|string',
            'notes_mouse_keyboard' => 'nullable|string',
            'notes_ups' => 'nullable|string',
            'notes_monitor' => 'nullable|string',
        ]);

        $checklist = MaintenanceChecklist::create([
            'laboratory_id' => $request->laboratory_id,
            'academic_year_id' => $request->academic_year_id,
            'maintenance_date' => $request->maintenance_date,
            'inspector_name' => $request->inspector_name,
            'notes_computer' => $request->notes_computer,
            'notes_mouse_keyboard' => $request->notes_mouse_keyboard,
            'notes_ups' => $request->notes_ups,
            'notes_monitor' => $request->notes_monitor,
        ]);

        $computers = Computer::where('laboratory_id', $request->laboratory_id)->get();
        $allItems = MaintenanceChecklist::getChecklistItems();

        foreach ($computers as $computer) {
            foreach ($allItems as $category => $categoryData) {
                foreach ($categoryData['items'] as $index => $question) {
                    $itemNumber = $index + 1;
                    $key = "{$category}_{$itemNumber}_{$computer->id}";
                    $isChecked = $request->has($key);

                    MaintenanceChecklistItem::create([
                        'maintenance_checklist_id' => $checklist->id,
                        'computer_id' => $computer->id,
                        'category' => $category,
                        'item_number' => $itemNumber,
                        'is_checked' => $isChecked,
                    ]);
                }
            }
        }

        return redirect()->route('maintenance.show', $checklist)->with('success', 'Data pemeliharaan berhasil disimpan.');
    }

    public function show(MaintenanceChecklist $maintenance)
    {
        $maintenance->load(['laboratory', 'academicYear']);

        $computers = Computer::where('laboratory_id', $maintenance->laboratory_id)
            ->orderBy('code')
            ->get();

        $items = MaintenanceChecklistItem::where('maintenance_checklist_id', $maintenance->id)
            ->get()
            ->keyBy(fn ($item) => "{$item->category}_{$item->item_number}_{$item->computer_id}");

        $checklistItems = MaintenanceChecklist::getChecklistItems();

        return view('maintenance.show', compact('maintenance', 'computers', 'items', 'checklistItems'));
    }

    public function edit(MaintenanceChecklist $maintenance)
    {
        $maintenance->load(['laboratory', 'academicYear']);

        $laboratories = Laboratory::orderBy('name')->get();
        $academicYears = AcademicYear::active()->orderByDesc('start_year')->get();
        $computers = Computer::where('laboratory_id', $maintenance->laboratory_id)
            ->orderBy('code')
            ->get();

        $items = MaintenanceChecklistItem::where('maintenance_checklist_id', $maintenance->id)
            ->get()
            ->keyBy(fn ($item) => "{$item->category}_{$item->item_number}_{$item->computer_id}");

        $checklistItems = MaintenanceChecklist::getChecklistItems();

        return view('maintenance.edit', compact('maintenance', 'laboratories', 'academicYears', 'computers', 'items', 'checklistItems'));
    }

    public function saveComputer(Request $request, MaintenanceChecklist $maintenance, Computer $computer)
    {
        // Validasi: pastikan computer milik lab yang sama
        if ($computer->laboratory_id !== $maintenance->laboratory_id) {
            return response()->json(['success' => false, 'message' => 'Komputer tidak sesuai laboratorium'], 400);
        }

        $allItems = MaintenanceChecklist::getChecklistItems();
        $savedCount = 0;
        $totalItems = 0;

        foreach ($allItems as $category => $categoryData) {
            foreach ($categoryData['items'] as $index => $question) {
                $itemNumber = $index + 1;
                $key = "{$category}_{$itemNumber}_{$computer->id}";
                $isChecked = $request->boolean($key);
                $totalItems++;

                MaintenanceChecklistItem::updateOrCreate(
                    [
                        'maintenance_checklist_id' => $maintenance->id,
                        'computer_id' => $computer->id,
                        'category' => $category,
                        'item_number' => $itemNumber,
                    ],
                    [
                        'is_checked' => $isChecked,
                        'checked_by' => auth()->user()?->name ?? $request->input('inspector_name'),
                        'saved_at' => now(),
                    ]
                );
                $savedCount++;
            }
        }

        return response()->json([
            'success' => true,
            'message' => "Data komputer {$computer->code} berhasil disimpan",
            'saved_count' => $savedCount,
            'total_items' => $totalItems,
            'saved_at' => now()->format('d/m/Y H:i:s'),
        ]);
    }

    public function update(Request $request, MaintenanceChecklist $maintenance)
    {
        $request->validate([
            'laboratory_id' => 'required|exists:laboratories,id',
            'academic_year_id' => 'required|exists:academic_years,id',
            'maintenance_date' => 'required|date',
            'inspector_name' => 'nullable|string|max:255',
            'notes_computer' => 'nullable|string',
            'notes_mouse_keyboard' => 'nullable|string',
            'notes_ups' => 'nullable|string',
            'notes_monitor' => 'nullable|string',
        ]);

        $maintenance->update([
            'laboratory_id' => $request->laboratory_id,
            'academic_year_id' => $request->academic_year_id,
            'maintenance_date' => $request->maintenance_date,
            'inspector_name' => $request->inspector_name,
            'notes_computer' => $request->notes_computer,
            'notes_mouse_keyboard' => $request->notes_mouse_keyboard,
            'notes_ups' => $request->notes_ups,
            'notes_monitor' => $request->notes_monitor,
        ]);

        $maintenance->items()->delete();

        $computers = Computer::where('laboratory_id', $request->laboratory_id)->get();
        $allItems = MaintenanceChecklist::getChecklistItems();

        foreach ($computers as $computer) {
            foreach ($allItems as $category => $categoryData) {
                foreach ($categoryData['items'] as $index => $question) {
                    $itemNumber = $index + 1;
                    $key = "{$category}_{$itemNumber}_{$computer->id}";
                    $isChecked = $request->has($key);

                    MaintenanceChecklistItem::create([
                        'maintenance_checklist_id' => $maintenance->id,
                        'computer_id' => $computer->id,
                        'category' => $category,
                        'item_number' => $itemNumber,
                        'is_checked' => $isChecked,
                    ]);
                }
            }
        }

        return redirect()->route('maintenance.show', $maintenance)->with('success', 'Data pemeliharaan berhasil diperbarui.');
    }

    public function destroy(MaintenanceChecklist $maintenance)
    {
        $maintenance->items()->delete();
        $maintenance->delete();

        return redirect()->route('maintenance.index')->with('success', 'Data pemeliharaan berhasil dihapus.');
    }

    public function print(MaintenanceChecklist $maintenance)
    {
        $maintenance->load(['laboratory', 'academicYear']);

        return view('maintenance.print', [
            'sheets' => $this->printSheets(collect([$maintenance])),
            'checklistItems' => MaintenanceChecklist::getChecklistItems(),
            'title' => $this->printTitle($maintenance->laboratory, $maintenance->academicYear),
        ]);
    }

    /**
     * Cetak semua pemeliharaan sesuai filter halaman daftar: satu halaman per
     * data, jadi tidak perlu membuka detail satu per satu.
     */
    public function printAll(Request $request)
    {
        $checklists = $this->listQuery($request)->get();

        $laboratoryId = $request->input('laboratory_id');
        $academicYearId = $request->input('academic_year_id');

        return view('maintenance.print', [
            'sheets' => $this->printSheets($checklists),
            'checklistItems' => MaintenanceChecklist::getChecklistItems(),
            'title' => $this->printTitle(
                $laboratoryId && $laboratoryId !== 'all' ? Laboratory::find($laboratoryId) : null,
                $academicYearId && $academicYearId !== 'all' ? AcademicYear::find($academicYearId) : null,
            ),
        ]);
    }

    /**
     * Judul dokumen cetak sekaligus nama file saat disimpan sebagai PDF,
     * mis. "Pemeliharaan-Lab. Komputer 2-Tahun Ajaran 2025-2026".
     */
    private function printTitle(?Laboratory $laboratory, ?AcademicYear $academicYear): string
    {
        $title = 'Pemeliharaan-'.($laboratory?->name ?? 'Semua Laboratorium')
            .'-'.($academicYear?->name ?? 'Semua Tahun Ajaran');

        return str_replace(['/', '\\'], '-', $title);
    }

    /**
     * Satu halaman cetak per pemeliharaan: komputer lab terkait + peta centang
     * item. Query tetap (tidak tumbuh seiring jumlah data yang dicetak).
     */
    private function printSheets($checklists)
    {
        $computersByLab = Computer::whereIn('laboratory_id', $checklists->pluck('laboratory_id')->unique())
            ->orderBy('code')
            ->get()
            ->groupBy('laboratory_id');

        $itemsByChecklist = MaintenanceChecklistItem::whereIn('maintenance_checklist_id', $checklists->pluck('id'))
            ->get()
            ->groupBy('maintenance_checklist_id');

        return $checklists->map(fn (MaintenanceChecklist $checklist) => [
            'maintenance' => $checklist,
            'computers' => $computersByLab->get($checklist->laboratory_id, collect()),
            'items' => $itemsByChecklist->get($checklist->id, collect())
                ->keyBy(fn (MaintenanceChecklistItem $item) => "{$item->category}_{$item->item_number}_{$item->computer_id}"),
        ])->values();
    }
}
