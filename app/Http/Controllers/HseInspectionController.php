<?php

namespace App\Http\Controllers;

use App\Models\HseInspection;
use Illuminate\Http\Request;

class HseInspectionController extends Controller
{
    private function filteredQuery(Request $request)
    {
        return HseInspection::query()
            ->search($request->string('search')->toString())
            ->when($request->filled('bulan'), fn ($q) => $q->whereMonth('tanggal_inspeksi', $request->integer('bulan')))
            ->when($request->filled('tahun'), fn ($q) => $q->whereYear('tanggal_inspeksi', $request->integer('tahun')));
    }

    public function index(Request $request)
    {
        $inspections = $this->filteredQuery($request)
            ->latest('tanggal_inspeksi')
            ->latest('created_at')
            ->paginate(10)
            ->withQueryString();

        $tahunOptions = HseInspection::query()
            ->whereNotNull('tanggal_inspeksi')
            ->pluck('tanggal_inspeksi')
            ->map(fn ($tanggal) => (int) substr((string) $tanggal, 0, 4))
            ->unique()
            ->sortDesc()
            ->values()
            ->all();

        if (! in_array((int) date('Y'), $tahunOptions, true)) {
            array_unshift($tahunOptions, (int) date('Y'));
        }

        return view('hse-inspections.index', compact('inspections', 'tahunOptions'));
    }

    public function create()
    {
        return view('hse-inspections.create', [
            'defaultDiperiksaOleh' => auth()->user()->name,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $this->validateInspection($request);
        $validated['findings'] = HseInspection::normalizeFindings($request->input('findings', []));
        $validated['created_by'] = auth()->id();

        HseInspection::create($validated);

        if ($request->input('action') === 'save_another') {
            return redirect()->route('hse-inspections.create')
                ->with('success', 'Inspeksi HSE berhasil disimpan. Silakan isi form untuk lokasi berikutnya.');
        }

        return redirect()->route('hse-inspections.index')
            ->with('success', 'Inspeksi HSE berhasil disimpan.');
    }

    public function show(HseInspection $hseInspection)
    {
        $hseInspection->load('creator');

        return view('hse-inspections.show', compact('hseInspection'));
    }

    public function edit(HseInspection $hseInspection)
    {
        return view('hse-inspections.edit', compact('hseInspection'));
    }

    public function update(Request $request, HseInspection $hseInspection)
    {
        $validated = $this->validateInspection($request);
        $validated['findings'] = HseInspection::normalizeFindings($request->input('findings', []));

        $hseInspection->update($validated);

        return redirect()->route('hse-inspections.index')
            ->with('success', 'Inspeksi HSE berhasil diperbarui.');
    }

    public function destroy(HseInspection $hseInspection)
    {
        $hseInspection->delete();

        return redirect()->route('hse-inspections.index')
            ->with('success', 'Inspeksi HSE berhasil dihapus.');
    }

    public function bulkDestroy(Request $request)
    {
        $request->validate([
            'ids' => 'required|array|min:1',
            'ids.*' => 'exists:hse_inspections,id',
        ]);

        HseInspection::whereIn('id', $request->ids)->delete();

        return redirect()->route('hse-inspections.index')
            ->with('success', count($request->ids).' inspeksi HSE berhasil dihapus.');
    }

    public function print(HseInspection $hseInspection)
    {
        return view('hse-inspections.print', compact('hseInspection'));
    }

    private function validateInspection(Request $request): array
    {
        return $request->validate([
            'lokasi' => 'required|string|max:255',
            'diperiksa_oleh' => 'required|string|max:255',
            'tanggal_inspeksi' => 'required|date',
            'findings' => 'nullable|array|max:20',
            'findings.*.lokasi' => 'nullable|string|max:255',
            'findings.*.temuan' => 'nullable|string|max:2000',
            'findings.*.unsafe_condition' => 'nullable|boolean',
            'findings.*.unsafe_action' => 'nullable|boolean',
            'findings.*.tanggal_kejadian' => 'nullable|date',
            'findings.*.tindakan_perbaikan' => 'nullable|string|max:2000',
            'findings.*.tanggal_perbaikan' => 'nullable|date',
            'findings.*.pic' => 'nullable|string|max:255',
        ], [
            'lokasi.required' => 'Lokasi inspeksi wajib diisi.',
            'diperiksa_oleh.required' => 'Nama pemeriksa wajib diisi.',
            'tanggal_inspeksi.required' => 'Tanggal inspeksi wajib diisi.',
            'findings.max' => 'Maksimal 20 temuan dalam satu inspeksi.',
            'findings.*.tanggal_kejadian.date' => 'Tanggal kejadian tidak valid.',
            'findings.*.tanggal_perbaikan.date' => 'Tanggal perbaikan tidak valid.',
        ]);
    }
}
