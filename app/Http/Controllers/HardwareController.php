<?php

namespace App\Http\Controllers;

use App\Exports\HardwareExport;
use App\Imports\HardwareImport;
use App\Models\Hardware;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class HardwareController extends Controller
{
    public function index()
    {
        $hardware = Hardware::latest()->paginate(10);
        return view('hardware.index', compact('hardware'));
    }

    public function create()
    {
        return view('hardware.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:hardware,code',
            'brand' => 'nullable|string|max:255',
            'model' => 'nullable|string|max:255',
            'category' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        Hardware::create($validated);

        return redirect()->route('hardware.index')->with('success', 'Hardware berhasil ditambahkan.');
    }

    public function show(Hardware $hardware)
    {
        return view('hardware.show', compact('hardware'));
    }

    public function edit(Hardware $hardware)
    {
        return view('hardware.edit', compact('hardware'));
    }

    public function update(Request $request, Hardware $hardware)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:hardware,code,' . $hardware->id,
            'brand' => 'nullable|string|max:255',
            'model' => 'nullable|string|max:255',
            'category' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $hardware->update($validated);

        return redirect()->route('hardware.index')->with('success', 'Hardware berhasil diperbarui.');
    }

    public function destroy(Hardware $hardware)
    {
        $hardware->delete();

        return redirect()->route('hardware.index')->with('success', 'Hardware berhasil dihapus.');
    }

    public function bulkDestroy(Request $request)
    {
        $request->validate([
            'ids' => 'required|array|min:1',
            'ids.*' => 'exists:hardware,id',
        ]);

        Hardware::whereIn('id', $request->ids)->delete();

        return redirect()->route('hardware.index')->with('success', count($request->ids) . ' hardware berhasil dihapus.');
    }

    public function export()
    {
        return Excel::download(new HardwareExport, 'hardware.xlsx');
    }

    public function template()
    {
        return Excel::download(new HardwareExport, 'template_hardware.xlsx');
    }

    public function import()
    {
        return view('hardware.import');
    }

    public function storeImport(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv|max:10240',
        ]);

        Excel::import(new HardwareImport, $request->file('file'));

        return redirect()->route('hardware.index')->with('success', 'Data hardware berhasil diimport.');
    }
}
