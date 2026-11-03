<?php

namespace App\Http\Controllers;

use App\Models\IncidentReport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class IncidentReportController extends Controller
{
    private const IMAGE_DIR = 'incident-images';

    private const MAX_IMAGES = 5;

    private function filteredQuery(Request $request)
    {
        return IncidentReport::query()
            ->search($request->string('search')->toString())
            ->when($request->filled('bulan'), fn ($q) => $q->whereMonth('tanggal_kejadian', $request->integer('bulan')))
            ->when($request->filled('tahun'), fn ($q) => $q->whereYear('tanggal_kejadian', $request->integer('tahun')));
    }

    public function index(Request $request)
    {
        $reports = $this->filteredQuery($request)
            ->latest('tanggal_kejadian')
            ->latest('waktu_kejadian')
            ->paginate(10)
            ->withQueryString();

        // Dihitung di PHP agar kompatibel dengan MySQL maupun SQLite.
        $tahunOptions = IncidentReport::query()
            ->whereNotNull('tanggal_kejadian')
            ->pluck('tanggal_kejadian')
            ->map(fn ($tanggal) => (int) substr((string) $tanggal, 0, 4))
            ->unique()
            ->sortDesc()
            ->values()
            ->all();

        if (! in_array((int) date('Y'), $tahunOptions, true)) {
            array_unshift($tahunOptions, (int) date('Y'));
        }

        return view('incident-reports.index', compact('reports', 'tahunOptions'));
    }

    public function create()
    {
        return view('incident-reports.create', [
            'defaultPelapor' => auth()->user()->name,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $this->validateReport($request);

        $validated['created_by'] = auth()->id();
        $validated['images'] = $this->storeImages($request);

        IncidentReport::create($validated);

        if ($request->input('action') === 'save_another') {
            return redirect()->route('incident-reports.create')
                ->with('success', 'Laporan insiden berhasil dibuat. Silakan isi form untuk data berikutnya.');
        }

        return redirect()->route('incident-reports.index')->with('success', 'Laporan insiden berhasil dibuat.');
    }

    /** Form publik (diakses lewat scan QR, tanpa login). */
    public function publicCreate()
    {
        return view('incident-reports.public-create');
    }

    public function publicStore(Request $request)
    {
        $validated = $this->validateReport($request);

        $validated['created_by'] = null;
        $validated['images'] = $this->storeImages($request);

        $report = IncidentReport::create($validated);

        return redirect()->route('incident-reports.public')
            ->with('success', 'Laporan insiden berhasil dikirim. Kode laporan: '.$report->kode);
    }

    public function show(IncidentReport $incidentReport)
    {
        $incidentReport->load('creator');

        return view('incident-reports.show', compact('incidentReport'));
    }

    public function edit(IncidentReport $incidentReport)
    {
        return view('incident-reports.edit', compact('incidentReport'));
    }

    public function update(Request $request, IncidentReport $incidentReport)
    {
        $validated = $this->validateReport($request);
        unset($validated['remove_images']);

        $images = $incidentReport->images ?? [];

        // Hapus gambar yang dicentang untuk dibuang.
        foreach ((array) $request->input('remove_images', []) as $path) {
            if (($key = array_search($path, $images, true)) !== false) {
                Storage::disk('public')->delete($images[$key]);
                unset($images[$key]);
            }
        }

        $images = array_values($images);

        if (count($images) + count($request->file('images', [])) > self::MAX_IMAGES) {
            return back()
                ->withInput()
                ->withErrors(['images' => 'Total bukti dokumentasi maksimal '.self::MAX_IMAGES.' file.']);
        }

        $validated['images'] = array_merge($images, $this->storeImages($request));

        $incidentReport->update($validated);

        return redirect()->route('incident-reports.index')->with('success', 'Laporan insiden berhasil diperbarui.');
    }

    public function destroy(IncidentReport $incidentReport)
    {
        $this->deleteImages($incidentReport);
        $incidentReport->delete();

        return redirect()->route('incident-reports.index')->with('success', 'Laporan insiden berhasil dihapus.');
    }

    public function bulkDestroy(Request $request)
    {
        $request->validate([
            'ids' => 'required|array|min:1',
            'ids.*' => 'exists:incident_reports,id',
        ]);

        $reports = IncidentReport::whereIn('id', $request->ids)->get();
        foreach ($reports as $report) {
            $this->deleteImages($report);
            $report->delete();
        }

        return redirect()->route('incident-reports.index')
            ->with('success', count($request->ids).' laporan insiden berhasil dihapus.');
    }

    private function validateReport(Request $request): array
    {
        return $request->validate([
            'tanggal_kejadian' => 'required|date',
            'waktu_kejadian' => 'required|date_format:H:i',
            'telah_terjadi' => 'required|in:'.implode(',', array_keys(IncidentReport::JENIS_KEJADIAN)),
            'lokasi' => 'required|string|max:255',
            'nama_korban' => 'required|string|max:255',
            'jabatan' => 'nullable|string|max:255',
            'asal_institusi' => 'nullable|string|max:255',
            'sebab' => 'nullable|string',
            'riwayat' => 'nullable|string',
            'saksi' => 'nullable|string',
            'akibat' => 'nullable|string',
            'likelihood' => 'required|integer|in:1,2,3,4,5',
            'impact' => 'required|integer|in:1,2,3,4,5',
            'pelapor' => 'required|string|max:255',
            'images' => 'nullable|array|max:'.self::MAX_IMAGES,
            'images.*' => 'nullable|file|mimes:jpg,jpeg,png,webp|max:5120',
            'remove_images' => 'nullable|array',
        ], [
            'telah_terjadi.required' => 'Pilih jenis kejadian pada field "Telah terjadi".',
            'telah_terjadi.in' => 'Jenis kejadian tidak valid.',
            'likelihood.required' => 'Pilih Kemungkinan (Likelihood) pada penilaian risiko.',
            'likelihood.in' => 'Nilai Kemungkinan tidak valid.',
            'impact.required' => 'Pilih Dampak (Impact) pada penilaian risiko.',
            'impact.in' => 'Nilai Dampak tidak valid.',
            'images.*.mimes' => 'Bukti dokumentasi harus berupa gambar (JPG, PNG, atau WEBP).',
            'images.*.max' => 'Ukuran setiap bukti dokumentasi maksimal 5 MB.',
        ]);
    }

    private function storeImages(Request $request): ?array
    {
        if (! $request->hasFile('images')) {
            return null;
        }

        $paths = [];
        foreach ($request->file('images') as $file) {
            $paths[] = $file->store(self::IMAGE_DIR, 'public');
        }

        return $paths;
    }

    private function deleteImages(IncidentReport $report): void
    {
        foreach ($report->images ?? [] as $path) {
            Storage::disk('public')->delete($path);
        }
    }
}
