<?php

namespace App\Http\Controllers;

use App\Exports\ComponentExport;
use App\Imports\ComponentImport;
use App\Models\Box;
use App\Models\BoxUsage;
use App\Models\Component;
use App\Models\ComponentBorrowing;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class ComponentController extends Controller
{
    public function index(Request $request)
    {
        $query = Component::with('boxes');

        if ($search = $request->search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%")
                  ->orWhere('brand', 'like', "%{$search}%");
            });
        }

        if ($category = $request->category) {
            $query->where('category', $category);
        }

        if ($status = $request->status) {
            $query->where('status', $status);
        }

        $components = $query->latest()->paginate(10)->withQueryString();

        $boxes = Box::withCount('boxComponents')
            ->withSum('boxComponents', 'quantity')
            ->orderBy('code')
            ->get();

        $usageQuery = BoxUsage::with(['box', 'returnNote']);

        $usageStatus = $request->input('usage_status');
        $usageSearch = $request->input('usage_search');
        $usageBox = $request->input('usage_box');

        if ($usageStatus && in_array($usageStatus, ['Using', 'Returned'])) {
            $usageQuery->where('status', $usageStatus);
        }

        if ($usageSearch) {
            $usageQuery->where(function ($q) use ($usageSearch) {
                $q->where('user_name', 'like', "%{$usageSearch}%")
                  ->orWhere('user_nim', 'like', "%{$usageSearch}%");
            });
        }

        if ($usageBox) {
            $usageQuery->where('box_id', $usageBox);
        }

        $usages = $usageQuery->latest('used_at')->paginate(15)->withQueryString();

        $usageStats = [
            'total'    => BoxUsage::count(),
            'using'    => BoxUsage::where('status', 'Using')->count(),
            'returned' => BoxUsage::where('status', 'Returned')->count(),
        ];

        $borrowingQuery = ComponentBorrowing::with('component');

        $borrowingStatus = $request->input('borrow_status');
        $borrowingSearch = $request->input('borrow_search');

        if ($borrowingStatus && in_array($borrowingStatus, ['Using', 'Returned'])) {
            $borrowingQuery->where('status', $borrowingStatus);
        }

        if ($borrowingSearch) {
            $borrowingQuery->where(function ($q) use ($borrowingSearch) {
                $q->where('user_name', 'like', "%{$borrowingSearch}%")
                  ->orWhere('user_nim', 'like', "%{$borrowingSearch}%");
            });
        }

        $borrowings = $borrowingQuery->latest('borrowed_at')->paginate(15)->withQueryString();

        $borrowingStats = [
            'total'    => ComponentBorrowing::count(),
            'using'    => ComponentBorrowing::where('status', 'Using')->count(),
            'returned' => ComponentBorrowing::where('status', 'Returned')->count(),
        ];

        return view('components.index', compact('components', 'boxes', 'usages', 'usageStats', 'borrowings', 'borrowingStats'));
    }

    public function create()
    {
        return view('components.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:50|unique:components,code',
            'category' => 'required|in:IoT,Jaringan,Lain-lain',
            'brand' => 'nullable|string|max:255',
            'model' => 'nullable|string|max:255',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'quantity' => 'required|integer|min:0',
            'status' => 'required|in:Tersedia,Digunakan,Rusak,Maintenance',
            'description' => 'nullable|string',
        ]);

        $validated['code'] = $validated['code'] ?: Component::generateCode();

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('components', 'public');
        } else {
            $validated['image'] = null;
        }

        Component::create($validated);

        return redirect()->route('components.index')->with('success', 'Komponen berhasil ditambahkan.');
    }

    public function show(Component $component)
    {
        return view('components.show', compact('component'));
    }

    public function edit(Component $component)
    {
        return view('components.edit', compact('component'));
    }

    public function update(Request $request, Component $component)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:components,code,' . $component->id,
            'category' => 'required|in:IoT,Jaringan,Lain-lain',
            'brand' => 'nullable|string|max:255',
            'model' => 'nullable|string|max:255',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'quantity' => 'required|integer|min:0',
            'status' => 'required|in:Tersedia,Digunakan,Rusak,Maintenance',
            'description' => 'nullable|string',
        ]);

        if ($request->hasFile('image')) {
            if ($component->image) {
                Storage::disk('public')->delete($component->image);
            }
            $validated['image'] = $request->file('image')->store('components', 'public');
        } else {
            unset($validated['image']);
        }

        $component->update($validated);

        return redirect()->route('components.index')->with('success', 'Komponen berhasil diperbarui.');
    }

    public function destroy(Component $component)
    {
        if ($component->image) {
            Storage::disk('public')->delete($component->image);
        }

        $component->delete();

        return redirect()->route('components.index')->with('success', 'Komponen berhasil dihapus.');
    }

    public function bulkDestroy(Request $request)
    {
        $request->validate([
            'ids' => 'required|array|min:1',
            'ids.*' => 'exists:components,id',
        ]);

        $components = Component::whereIn('id', $request->ids)->get();

        foreach ($components as $component) {
            if ($component->image) {
                Storage::disk('public')->delete($component->image);
            }
        }

        Component::whereIn('id', $request->ids)->delete();

        return redirect()->route('components.index')->with('success', count($request->ids) . ' komponen berhasil dihapus.');
    }

    public function export()
    {
        return Excel::download(new ComponentExport, 'komponen.xlsx');
    }

    public function template()
    {
        return Excel::download(new ComponentExport, 'template_komponen.xlsx');
    }

    public function import()
    {
        return view('components.import');
    }

    public function storeImport(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv|max:10240',
        ]);

        $import = new ComponentImport();
        try {
            Excel::import($import, $request->file('file'));
        } catch (\Exception $e) {
            return redirect()->route('components.import')
                ->with('import_error', 'Gagal mengimport: ' . $e->getMessage());
        }

        $message = "Berhasil mengimpor {$import->getImportedCount()} data komponen";
        if ($errors = $import->getErrors()) {
            $message .= '. ' . count($errors) . ' baris gagal: ' . implode('; ', array_slice($errors, 0, 5));
            if (count($errors) > 5) {
                $message .= ' dan ' . (count($errors) - 5) . ' lagi...';
            }
        }

        return redirect()->route('components.index')
            ->with('success', $message)
            ->with('import_errors', $import->getErrors());
    }
}
