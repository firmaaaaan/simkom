<?php

namespace App\Http\Controllers;

use App\Exports\MaintenanceExport;
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
     * Query daftar pemeliharaan — dipakai halaman daftar & export Excel.
     */
    private function listQuery()
    {
        return MaintenanceChecklist::with(['laboratory', 'academicYear'])
            ->latest('maintenance_date')
            ->latest('id');
    }

    public function index()
    {
        $checklists = $this->listQuery()->paginate(10);

        return view('maintenance.index', compact('checklists'));
    }

    public function export()
    {
        return Excel::download(
            new MaintenanceExport($this->listQuery()),
            'pemeliharaan-' . now()->format('Y-m-d') . '.xlsx'
        );
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
            ->keyBy(fn($item) => "{$item->category}_{$item->item_number}_{$item->computer_id}");

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
            ->keyBy(fn($item) => "{$item->category}_{$item->item_number}_{$item->computer_id}");

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

        $computers = Computer::where('laboratory_id', $maintenance->laboratory_id)
            ->orderBy('code')
            ->get();

        $items = MaintenanceChecklistItem::where('maintenance_checklist_id', $maintenance->id)
            ->get()
            ->keyBy(fn($item) => "{$item->category}_{$item->item_number}_{$item->computer_id}");

        $checklistItems = MaintenanceChecklist::getChecklistItems();

        return view('maintenance.print', compact('maintenance', 'computers', 'items', 'checklistItems'));
    }
}
