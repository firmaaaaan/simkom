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
            'code' => 'nullable|string|max:50|unique:software,code',
            'version' => 'nullable|string|max:50',
            'license_type' => 'nullable|string|max:100',
            'category' => 'required|string|max:255',
            'status' => 'required|in:Aktif,Expired,Trial,Non Aktif',
            'description' => 'nullable|string',
        ]);

        $validated['code'] = $validated['code'] ?: Software::generateCode();

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

        $import = new SoftwareImport();
        try {
            Excel::import($import, $request->file('file'));
        } catch (\Exception $e) {
            return redirect()->route('software.import')
                ->with('import_error', 'Gagal mengimport: ' . $e->getMessage());
        }

        $message = "Berhasil mengimpor {$import->getImportedCount()} data software";
        if ($errors = $import->getErrors()) {
            $message .= '. ' . count($errors) . ' baris gagal: ' . implode('; ', array_slice($errors, 0, 5));
            if (count($errors) > 5) {
                $message .= ' dan ' . (count($errors) - 5) . ' lagi...';
            }
        }

        return redirect()->route('software.index')
            ->with('success', $message)
            ->with('import_errors', $import->getErrors());
    }
}
