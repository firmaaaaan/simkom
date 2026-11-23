<?php

namespace App\Http\Controllers;

use App\Exports\K3InspectionReportTemplate;
use App\Imports\K3ImportContext;
use App\Imports\K3InspectionReportImport;
use App\Models\K3InspectionReport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Facades\Excel;

class K3InspectionReportController extends Controller
{
    private const EVIDENCE_DIR = 'k3-inspection-evidence';

    private const MAX_EVIDENCE_PER_FINDING = 3;
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
        $validated['findings'] = K3InspectionReport::normalizeFindings($this->attachDokumentasiFiles($request));
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
        $validated['findings'] = K3InspectionReport::normalizeFindings($this->attachDokumentasiFiles($request, $k3InspectionReport));

        $k3InspectionReport->update($validated);

        return redirect()->route('k3-inspection-reports.index')
            ->with('success', 'Laporan Inspeksi K3 berhasil diperbarui.');
    }

    public function destroy(K3InspectionReport $k3InspectionReport)
    {
        $this->deleteEvidence($k3InspectionReport);
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

        $reports = K3InspectionReport::whereIn('id', $request->ids)->get();
        foreach ($reports as $report) {
            $this->deleteEvidence($report);
            $report->delete();
        }

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
            'findings.*.dokumentasi' => 'nullable|array|max:'.self::MAX_EVIDENCE_PER_FINDING,
            'findings.*.dokumentasi.*' => 'file|mimes:jpg,jpeg,png,webp,pdf,doc,docx,xls,xlsx|max:5120',
            'findings.*.dokumentasi_existing' => 'nullable|array|max:'.self::MAX_EVIDENCE_PER_FINDING,
            'findings.*.dokumentasi_existing.*' => 'string|max:255',
            'findings.*.dokumentasi_text' => 'nullable|string|max:255',
            'findings.*.saran_perbaikan' => 'nullable|string|max:2000',
            'findings.*.pelaksana' => 'nullable|string|max:255',
            'findings.*.target' => 'nullable|string|max:255',
            'findings.*.catatan' => 'nullable|string|max:2000',
            'remove_dokumentasi' => 'nullable|array',
            'remove_dokumentasi.*' => 'string|max:255',
        ], [
            'hari.required' => 'Hari pada berita acara wajib diisi.',
            'tanggal_ba.required' => 'Tanggal berita acara wajib diisi.',
            'bulan_ba.required' => 'Bulan berita acara wajib diisi.',
            'tahun_ba.required' => 'Tahun berita acara wajib diisi.',
            'bulan_inspeksi.required' => 'Bulan inspeksi wajib diisi.',
            'lokasi.required' => 'Lokasi inspeksi wajib diisi.',
            'pemeriksa_nama.required' => 'Nama pemeriksa wajib diisi.',
            'pemeriksa_jabatan.required' => 'Jabatan pemeriksa wajib diisi.',
            'findings.*.dokumentasi.max' => 'Maksimal '.self::MAX_EVIDENCE_PER_FINDING.' file dokumentasi per temuan.',
            'findings.*.dokumentasi.*.mimes' => 'Dokumentasi harus berupa gambar (JPG/PNG/WEBP) atau dokumen (PDF/Word/Excel).',
            'findings.*.dokumentasi.*.max' => 'Ukuran setiap file dokumentasi maksimal 5 MB.',
        ]);
    }

    /**
     * Simpan file dokumentasi temuan ke disk public dan kembalikan
     * baris findings siap dinormalisasi (dokumentasi = array path | teks legacy).
     */
    private function attachDokumentasiFiles(Request $request, ?K3InspectionReport $report = null): array
    {
        $rows = $request->input('findings', []);
        if (! is_array($rows)) {
            return [];
        }

        $remove = array_values(array_filter((array) $request->input('remove_dokumentasi', []), 'is_string'));
        $oldPaths = $this->evidencePaths($report);

        // Hanya path yang memang milik laporan ini yang boleh dipertahankan/dihapus.
        $postedExisting = [];
        foreach ($rows as $row) {
            if (! is_array($row)) {
                continue;
            }
            foreach ((array) ($row['dokumentasi_existing'] ?? []) as $path) {
                if (is_string($path) && $path !== '') {
                    $postedExisting[] = $path;
                }
            }
        }
        $postedExisting = array_values(array_unique($postedExisting));

        $drop = array_values(array_unique(array_merge(
            array_intersect($remove, $oldPaths),
            array_diff($oldPaths, $postedExisting)
        )));

        // Cek total (tersimpan + baru) per baris sebelum menyimpan/menghapus apa pun.
        foreach ($rows as $i => $row) {
            if (! is_array($row)) {
                continue;
            }
            $kept = $this->keptEvidence($row, $oldPaths, $remove);
            $uploads = $request->file("findings.{$i}.dokumentasi", []);
            if (count($kept) + count($uploads) > self::MAX_EVIDENCE_PER_FINDING) {
                throw ValidationException::withMessages([
                    "findings.{$i}.dokumentasi" => 'Total dokumentasi maksimal '.self::MAX_EVIDENCE_PER_FINDING.' file per temuan (termasuk file tersimpan).',
                ]);
            }
        }

        if ($drop !== []) {
            Storage::disk('public')->delete($drop);
        }

        foreach ($rows as $i => $row) {
            if (! is_array($row)) {
                continue;
            }

            $files = $this->keptEvidence($row, $oldPaths, $remove);
            foreach ($request->file("findings.{$i}.dokumentasi", []) as $file) {
                $files[] = $file->store(self::EVIDENCE_DIR, 'public');
            }

            $text = trim((string) ($row['dokumentasi_text'] ?? ''));
            $rows[$i]['dokumentasi'] = $files !== [] ? $files : ($text !== '' ? $text : []);
            unset($rows[$i]['dokumentasi_existing'], $rows[$i]['dokumentasi_text']);
        }

        return $rows;
    }

    private function keptEvidence(array $row, array $oldPaths, array $remove): array
    {
        $existing = array_values(array_intersect(
            array_filter((array) ($row['dokumentasi_existing'] ?? []), 'is_string'),
            $oldPaths
        ));

        return array_values(array_diff($existing, $remove));
    }

    private function evidencePaths(?K3InspectionReport $report): array
    {
        if (! $report) {
            return [];
        }

        $paths = [];
        foreach ($report->findings ?? [] as $row) {
            if (! is_array($row['dokumentasi'] ?? null)) {
                continue;
            }
            foreach ($row['dokumentasi'] as $path) {
                if (is_string($path) && $path !== '') {
                    $paths[] = $path;
                }
            }
        }

        return array_values(array_unique($paths));
    }

    private function deleteEvidence(K3InspectionReport $report): void
    {
        if ($paths = $this->evidencePaths($report)) {
            Storage::disk('public')->delete($paths);
        }
    }
}
