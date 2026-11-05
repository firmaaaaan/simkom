<?php

namespace App\Http\Controllers;

use App\Exports\K3InspectionReportTemplate;
use App\Imports\K3ImportContext;
use App\Imports\K3InspectionReportImport;
use App\Models\K3InspectionReport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class K3InspectionReportController extends Controller
{
    private function filteredQuery(Request $request)
    {
        return K3InspectionReport::query()
            ->search($request->string('search')->toString())
            ->when($request->filled('bulan'), fn ($q) => $q->where('bulan_inspeksi', 'like', '%'.$request->string('bulan')->toString().'%'))
            ->when($request->filled('tahun'), fn ($q) => $q->where('tahun_ba', $request->string('tahun')->toString()));
    }

    public function index(Request $request)
    {
        $reports = $this->filteredQuery($request)
            ->latest('tanggal_ba')
            ->latest('created_at')
            ->paginate(10)
            ->withQueryString();

        $tahunOptions = K3InspectionReport::query()
            ->whereNotNull('tahun_ba')
            ->pluck('tahun_ba')
            ->unique()
            ->sortDesc()
            ->values()
            ->all();

        if (! in_array((string) date('Y'), $tahunOptions, true)) {
            array_unshift($tahunOptions, (string) date('Y'));
        }

        return view('k3-inspection-reports.index', compact('reports', 'tahunOptions'));
    }

    public function create()
    {
        return view('k3-inspection-reports.create', [
            'pemeriksaNama' => K3InspectionReport::PEMERIKSA_NAMA,
            'pemeriksaJabatan' => K3InspectionReport::PEMERIKSA_JABATAN,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $this->validateReport($request);
        $validated['checklist_items'] = K3InspectionReport::normalizeChecklist($request->input('checklist', []));
        $validated['findings'] = K3InspectionReport::normalizeFindings($request->input('findings', []));
        $validated['created_by'] = auth()->id();

        K3InspectionReport::create($validated);

        if ($request->input('action') === 'save_another') {
            return redirect()->route('k3-inspection-reports.create')
                ->with('success', 'Laporan Inspeksi K3 berhasil disimpan. Silakan buat laporan berikutnya.');
        }

        return redirect()->route('k3-inspection-reports.index')
            ->with('success', 'Laporan Inspeksi K3 berhasil disimpan.');
    }

    public function show(K3InspectionReport $k3InspectionReport)
    {
        $k3InspectionReport->load('creator');

        return view('k3-inspection-reports.show', compact('k3InspectionReport'));
    }

    public function edit(K3InspectionReport $k3InspectionReport)
    {
        return view('k3-inspection-reports.edit', compact('k3InspectionReport'));
    }

    public function update(Request $request, K3InspectionReport $k3InspectionReport)
    {
        $validated = $this->validateReport($request);
        $validated['checklist_items'] = K3InspectionReport::normalizeChecklist($request->input('checklist', []));
        $validated['findings'] = K3InspectionReport::normalizeFindings($request->input('findings', []));

        $k3InspectionReport->update($validated);

        return redirect()->route('k3-inspection-reports.index')
            ->with('success', 'Laporan Inspeksi K3 berhasil diperbarui.');
    }

    public function destroy(K3InspectionReport $k3InspectionReport)
    {
        $k3InspectionReport->delete();

        return redirect()->route('k3-inspection-reports.index')
            ->with('success', 'Laporan Inspeksi K3 berhasil dihapus.');
    }

    public function bulkDestroy(Request $request)
    {
        $request->validate([
            'ids' => 'required|array|min:1',
            'ids.*' => 'exists:k3_inspection_reports,id',
        ]);

        K3InspectionReport::whereIn('id', $request->ids)->delete();

        return redirect()->route('k3-inspection-reports.index')
            ->with('success', count($request->ids).' laporan inspeksi K3 berhasil dihapus.');
    }

    public function print(K3InspectionReport $k3InspectionReport)
    {
        return view('k3-inspection-reports.print', compact('k3InspectionReport'));
    }

    public function template()
    {
        return Excel::download(new K3InspectionReportTemplate, 'template_laporan_inspeksi_k3.xlsx');
    }

    public function import()
    {
        return view('k3-inspection-reports.import');
    }

    public function storeImport(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls|max:10240',
        ]);

        $import = new K3InspectionReportImport(new K3ImportContext());
        try {
            Excel::import($import, $request->file('file'));
        } catch (\Exception $e) {
            return redirect()->route('k3-inspection-reports.import')
                ->with('import_error', 'Gagal mengimport: '.$e->getMessage());
        }

        $message = "Berhasil mengimpor {$import->getImportedCount()} laporan inspeksi K3";
        if ($errors = $import->getErrors()) {
            $message .= '. '.count($errors).' baris gagal: '.implode('; ', array_slice($errors, 0, 5));
            if (count($errors) > 5) {
                $message .= ' dan '.(count($errors) - 5).' lagi...';
            }
        }

        return redirect()->route('k3-inspection-reports.index')
            ->with('success', $message)
            ->with('import_errors', $import->getErrors());
    }

    private function validateReport(Request $request): array
    {
        return $request->validate([
            'hari' => 'required|string|max:50',
            'tanggal_ba' => 'required|date',
            'bulan_ba' => 'required|string|max:50',
            'tahun_ba' => 'required|string|max:10',
            'bulan_inspeksi' => 'required|string|max:50',
            'lokasi' => 'required|string|max:255',
            'pemeriksa_nama' => 'required|string|max:255',
            'pemeriksa_jabatan' => 'required|string|max:255',
            'checklist' => 'nullable|array',
            'findings' => 'nullable|array|max:30',
            'findings.*.temuan' => 'nullable|string|max:2000',
            'findings.*.dokumentasi' => 'nullable|string|max:255',
            'findings.*.saran_perbaikan' => 'nullable|string|max:2000',
            'findings.*.pelaksana' => 'nullable|string|max:255',
            'findings.*.target' => 'nullable|string|max:255',
            'findings.*.catatan' => 'nullable|string|max:2000',
        ], [
            'hari.required' => 'Hari pada berita acara wajib diisi.',
            'tanggal_ba.required' => 'Tanggal berita acara wajib diisi.',
            'bulan_ba.required' => 'Bulan berita acara wajib diisi.',
            'tahun_ba.required' => 'Tahun berita acara wajib diisi.',
            'bulan_inspeksi.required' => 'Bulan inspeksi wajib diisi.',
            'lokasi.required' => 'Lokasi inspeksi wajib diisi.',
            'pemeriksa_nama.required' => 'Nama pemeriksa wajib diisi.',
            'pemeriksa_jabatan.required' => 'Jabatan pemeriksa wajib diisi.',
        ]);
    }
}
