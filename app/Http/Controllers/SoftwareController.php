<?php

namespace App\Http\Controllers;

use App\Exports\SoftwareExport;
use App\Imports\SoftwareImport;
use App\Models\Software;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class SoftwareController extends Controller
{
    public function index()
    {
        $software = Software::latest()->paginate(10);
        return view('software.index', compact('software'));
    }

    public function create()
    {
        return view('software.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:software,code',
            'version' => 'nullable|string|max:50',
            'license_type' => 'nullable|string|max:100',
            'category' => 'required|string|max:255',
            'status' => 'required|in:Aktif,Expired,Trial,Non Aktif',
            'description' => 'nullable|string',
        ]);

        Software::create($validated);

        return redirect()->route('software.index')->with('success', 'Software berhasil ditambahkan.');
    }

    public function show(Software $software)
    {
        return view('software.show', compact('software'));
    }

    public function edit(Software $software)
    {
        return view('software.edit', compact('software'));
    }

    public function update(Request $request, Software $software)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:software,code,' . $software->id,
            'version' => 'nullable|string|max:50',
            'license_type' => 'nullable|string|max:100',
            'category' => 'required|string|max:255',
            'status' => 'required|in:Aktif,Expired,Trial,Non Aktif',
            'description' => 'nullable|string',
        ]);

        $software->update($validated);

        return redirect()->route('software.index')->with('success', 'Software berhasil diperbarui.');
    }

    public function destroy(Software $software)
    {
        $software->delete();

        return redirect()->route('software.index')->with('success', 'Software berhasil dihapus.');
    }

    public function bulkDestroy(Request $request)
    {
        $request->validate([
            'ids' => 'required|array|min:1',
            'ids.*' => 'exists:software,id',
        ]);

        Software::whereIn('id', $request->ids)->delete();

        return redirect()->route('software.index')->with('success', count($request->ids) . ' software berhasil dihapus.');
    }

    public function export()
    {
        return Excel::download(new SoftwareExport, 'software.xlsx');
    }

    public function template()
    {
        return Excel::download(new SoftwareExport, 'template_software.xlsx');
    }

    public function import()
    {
        return view('software.import');
    }

    public function storeImport(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv|max:10240',
        ]);

        Excel::import(new SoftwareImport, $request->file('file'));

        return redirect()->route('software.index')->with('success', 'Data software berhasil diimport.');
    }
}
