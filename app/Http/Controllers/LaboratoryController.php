<?php

namespace App\Http\Controllers;

use App\Exports\LaboratoryExport;
use App\Models\Laboratory;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class LaboratoryController extends Controller
{
    /**
     * Query daftar laboratorium dengan filter yang sedang aktif — dipakai
     * bersama oleh halaman daftar dan export Excel.
     */
    private function filteredQuery(Request $request)
    {
        $query = Laboratory::query();

        if ($search = $request->search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%")
                  ->orWhere('location', 'like', "%{$search}%");
            });
        }

        return $query;
    }

    public function index(Request $request)
    {
        $laboratories = $this->filteredQuery($request)->latest()->paginate(10)->withQueryString();
        return view('laboratories.index', compact('laboratories'));
    }

    public function export(Request $request)
    {
        return Excel::download(
            new LaboratoryExport($this->filteredQuery($request)->latest()),
            'laboratorium-' . now()->format('Y-m-d') . '.xlsx'
        );
    }

    public function create()
    {
        return view('laboratories.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:laboratories,code',
            'location' => 'required|string|max:255',
            'capacity' => 'required|integer|min:0',
            'status' => 'required|in:Aktif,Tidak Aktif,Maintenance',
            'show_in_schedule' => 'nullable|boolean',
            'description' => 'nullable|string',
        ]);

        $validated['show_in_schedule'] = $request->boolean('show_in_schedule');

        Laboratory::create($validated);

        return redirect()->route('laboratories.index')->with('success', 'Laboratorium berhasil ditambahkan.');
    }

    public function show(Laboratory $laboratory)
    {
        return view('laboratories.show', compact('laboratory'));
    }

    public function edit(Laboratory $laboratory)
    {
        return view('laboratories.edit', compact('laboratory'));
    }

    public function update(Request $request, Laboratory $laboratory)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:laboratories,code,' . $laboratory->id,
            'location' => 'required|string|max:255',
            'capacity' => 'required|integer|min:0',
            'status' => 'required|in:Aktif,Tidak Aktif,Maintenance',
            'show_in_schedule' => 'nullable|boolean',
            'description' => 'nullable|string',
        ]);

        $validated['show_in_schedule'] = $request->boolean('show_in_schedule');

        $laboratory->update($validated);

        return redirect()->route('laboratories.index')->with('success', 'Laboratorium berhasil diperbarui.');
    }

    public function destroy(Laboratory $laboratory)
    {
        $laboratory->delete();

        return redirect()->route('laboratories.index')->with('success', 'Laboratorium berhasil dihapus.');
    }

    public function bulkDestroy(Request $request)
    {
        $request->validate([
            'ids' => 'required|array|min:1',
            'ids.*' => 'exists:laboratories,id',
        ]);

        Laboratory::whereIn('id', $request->ids)->delete();

        return redirect()->route('laboratories.index')->with('success', count($request->ids) . ' laboratorium berhasil dihapus.');
    }
}
