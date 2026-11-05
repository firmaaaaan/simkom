<?php

namespace App\Http\Controllers;

use App\Exports\AparInspectionTemplate;
use App\Imports\AparInspectionImport;
use App\Models\AparInspection;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class AparInspectionController extends Controller
{
    private function filteredQuery(Request $request)
    {
        return AparInspection::query()
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

        // Dihitung di PHP agar kompatibel dengan MySQL maupun SQLite.
        $tahunOptions = AparInspection::query()
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

        return view('apar-inspections.index', compact('inspections', 'tahunOptions'));
    }

    public function create()
    {
        return view('apar-inspections.create', [
            'defaultDiperiksaOleh' => auth()->user()->name,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $this->validateInspection($request);
        $validated['items'] = AparInspection::normalizeItems($request->input('items', []));
        $validated['created_by'] = auth()->id();

        AparInspection::create($validated);

        if ($request->input('action') === 'save_another') {
            return redirect()->route('apar-inspections.create')
                ->with('success', 'Lembar periksa APAR berhasil disimpan. Silakan isi form untuk unit berikutnya.');
        }

        return redirect()->route('apar-inspections.index')
            ->with('success', 'Lembar periksa APAR berhasil disimpan.');
    }

    public function show(AparInspection $aparInspection)
    {
        $aparInspection->load('creator');

        return view('apar-inspections.show', compact('aparInspection'));
    }

    public function edit(AparInspection $aparInspection)
    {
        return view('apar-inspections.edit', compact('aparInspection'));
    }

    public function update(Request $request, AparInspection $aparInspection)
    {
        $validated = $this->validateInspection($request);
        $validated['items'] = AparInspection::normalizeItems($request->input('items', []));

        $aparInspection->update($validated);

        return redirect()->route('apar-inspections.index')
            ->with('success', 'Lembar periksa APAR berhasil diperbarui.');
    }

    public function destroy(AparInspection $aparInspection)
    {
        $aparInspection->delete();

        return redirect()->route('apar-inspections.index')
            ->with('success', 'Lembar periksa APAR berhasil dihapus.');
    }

    public function bulkDestroy(Request $request)
    {
        $request->validate([
            'ids' => 'required|array|min:1',
            'ids.*' => 'exists:apar_inspections,id',
        ]);

        AparInspection::whereIn('id', $request->ids)->delete();

        return redirect()->route('apar-inspections.index')
            ->with('success', count($request->ids).' lembar periksa APAR berhasil dihapus.');
    }

    public function print(AparInspection $aparInspection)
    {
        return view('apar-inspections.print', compact('aparInspection'));
    }

    public function template()
    {
        return Excel::download(new AparInspectionTemplate, 'template_lembar_periksa_apar.xlsx');
    }

    public function import()
    {
        return view('apar-inspections.import');
    }

    public function storeImport(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls|max:10240',
        ]);

        $import = new AparInspectionImport();
        try {
            Excel::import($import, $request->file('file'));
        } catch (\Exception $e) {
            return redirect()->route('apar-inspections.import')
                ->with('import_error', 'Gagal mengimport: '.$e->getMessage());
        }

        $message = "Berhasil mengimpor {$import->getImportedCount()} lembar periksa APAR";
        if ($errors = $import->getErrors()) {
            $message .= '. '.count($errors).' baris gagal: '.implode('; ', array_slice($errors, 0, 5));
            if (count($errors) > 5) {
                $message .= ' dan '.(count($errors) - 5).' lagi...';
            }
        }

        return redirect()->route('apar-inspections.index')
            ->with('success', $message)
            ->with('import_errors', $import->getErrors());
    }

    private function validateInspection(Request $request): array
    {
        $rules = [
            'nomor_registrasi' => 'required|string|max:50',
            'tanggal_inspeksi' => 'required|date',
            'lokasi' => 'required|string|max:255',
            'diperiksa_oleh' => 'required|string|max:255',
            'items' => 'required|array',
        ];

        // Setiap item wajib dinilai Baik/Tidak agar lembar periksa tidak bolong.
        foreach (array_keys(AparInspection::ITEMS) as $key) {
            $rules["items.{$key}.status"] = 'required|in:baik,tidak';
            $rules["items.{$key}.keterangan"] = 'nullable|string|max:255';
        }

        return $request->validate($rules, [
            'nomor_registrasi.required' => 'Nomor registrasi APAR wajib diisi.',
            'tanggal_inspeksi.required' => 'Hari/tanggal pemeriksaan wajib diisi.',
            'lokasi.required' => 'Lokasi APAR wajib diisi.',
            'diperiksa_oleh.required' => 'Nama pemeriksa wajib diisi.',
            'items.*.status.required' => 'Setiap item pemeriksaan harus dipilih Baik atau Tidak.',
            'items.*.status.in' => 'Pilihan item pemeriksaan tidak valid.',
        ]);
    }
}
