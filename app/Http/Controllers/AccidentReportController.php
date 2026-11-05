<?php

namespace App\Http\Controllers;

use App\Exports\AccidentReportTemplate;
use App\Imports\AccidentReportImport;
use App\Models\AccidentReport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Facades\Excel;

class AccidentReportController extends Controller
{
    private function filteredQuery(Request $request)
    {
        return AccidentReport::query()
            ->search($request->string('search')->toString())
            ->when($request->filled('bulan'), fn ($q) => $q->whereMonth('tanggal', $request->integer('bulan')))
            ->when($request->filled('tahun'), fn ($q) => $q->whereYear('tanggal', $request->integer('tahun')));
    }

    public function index(Request $request)
    {
        $reports = $this->filteredQuery($request)
            ->latest('tanggal')
            ->latest('created_at')
            ->paginate(10)
            ->withQueryString();

        // Dihitung di PHP agar kompatibel dengan MySQL maupun SQLite.
        $tahunOptions = AccidentReport::query()
            ->whereNotNull('tanggal')
            ->pluck('tanggal')
            ->map(fn ($tanggal) => (int) substr((string) $tanggal, 0, 4))
            ->unique()
            ->sortDesc()
            ->values()
            ->all();

        if (! in_array((int) date('Y'), $tahunOptions, true)) {
            array_unshift($tahunOptions, (int) date('Y'));
        }

        return view('berita-acara-kecelakaan.index', compact('reports', 'tahunOptions'));
    }

    public function create()
    {
        return view('berita-acara-kecelakaan.create', [
            'defaultNamaPelapor' => auth()->user()->name,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $this->validateReport($request);
        $validated['uraian_kejadian'] = AccidentReport::normalizeUraian($request->input('uraian_kejadian', []));
        $validated['created_by'] = auth()->id();

        AccidentReport::create($validated);

        if ($request->input('action') === 'save_another') {
            return redirect()->route('berita-acara-kecelakaan.create')
                ->with('success', 'Berita acara kecelakaan berhasil disimpan. Silakan isi form untuk berita acara berikutnya.');
        }

        return redirect()->route('berita-acara-kecelakaan.index')
            ->with('success', 'Berita acara kecelakaan berhasil disimpan.');
    }

    public function show(AccidentReport $accidentReport)
    {
        $accidentReport->load('creator');

        return view('berita-acara-kecelakaan.show', ['report' => $accidentReport]);
    }

    public function edit(AccidentReport $accidentReport)
    {
        return view('berita-acara-kecelakaan.edit', ['report' => $accidentReport]);
    }

    public function update(Request $request, AccidentReport $accidentReport)
    {
        $validated = $this->validateReport($request);
        $validated['uraian_kejadian'] = AccidentReport::normalizeUraian($request->input('uraian_kejadian', []));

        $accidentReport->update($validated);

        return redirect()->route('berita-acara-kecelakaan.index')
            ->with('success', 'Berita acara kecelakaan berhasil diperbarui.');
    }

    public function destroy(AccidentReport $accidentReport)
    {
        $accidentReport->delete();

        return redirect()->route('berita-acara-kecelakaan.index')
            ->with('success', 'Berita acara kecelakaan berhasil dihapus.');
    }

    public function bulkDestroy(Request $request)
    {
        $request->validate([
            'ids' => 'required|array|min:1',
            'ids.*' => 'exists:accident_reports,id',
        ]);

        AccidentReport::whereIn('id', $request->ids)->delete();

        return redirect()->route('berita-acara-kecelakaan.index')
            ->with('success', count($request->ids).' berita acara kecelakaan berhasil dihapus.');
    }

    public function print(AccidentReport $accidentReport)
    {
        return view('berita-acara-kecelakaan.print', ['report' => $accidentReport]);
    }

    public function template()
    {
        return Excel::download(new AccidentReportTemplate, 'template_berita_acara_kecelakaan.xlsx');
    }

    public function import()
    {
        return view('berita-acara-kecelakaan.import');
    }

    public function storeImport(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls|max:10240',
        ]);

        $import = new AccidentReportImport();
        try {
            Excel::import($import, $request->file('file'));
        } catch (\Exception $e) {
            return redirect()->route('berita-acara-kecelakaan.import')
                ->with('import_error', 'Gagal mengimport: '.$e->getMessage());
        }

        $message = "Berhasil mengimpor {$import->getImportedCount()} berita acara kecelakaan";
        if ($errors = $import->getErrors()) {
            $message .= '. '.count($errors).' baris gagal: '.implode('; ', array_slice($errors, 0, 5));
            if (count($errors) > 5) {
                $message .= ' dan '.(count($errors) - 5).' lagi...';
            }
        }

        return redirect()->route('berita-acara-kecelakaan.index')
            ->with('success', $message)
            ->with('import_errors', $import->getErrors());
    }

    private function validateReport(Request $request): array
    {
        $validator = Validator::make($request->all(), [
            'tanggal' => 'required|date',
            'nama_pelapor' => 'required|string|max:255',
            'jabatan_pelapor' => 'required|string|max:255',
            'nama_korban' => 'required|string|max:255',
            'alamat_korban' => 'required|string|max:255',
            'ttl_korban' => 'required|string|max:255',
            'unit_status_korban' => 'required|string|max:255',
            'uraian_kejadian' => 'required|array|min:1',
            'uraian_kejadian.*' => 'nullable|string|max:1000',
            'dampak' => 'required|string|max:2000',
            'saksi_satu' => 'required|string|max:255',
            'saksi_dua' => 'required|string|max:255',
        ], [
            'tanggal.required' => 'Tanggal berita acara wajib diisi.',
            'nama_pelapor.required' => 'Nama yang menerangkan wajib diisi.',
            'jabatan_pelapor.required' => 'Jabatan yang menerangkan wajib diisi.',
            'nama_korban.required' => 'Nama korban wajib diisi.',
            'alamat_korban.required' => 'Alamat korban wajib diisi.',
            'ttl_korban.required' => 'Tempat tanggal lahir korban wajib diisi.',
            'unit_status_korban.required' => 'Unit/status korban wajib diisi.',
            'uraian_kejadian.required' => 'Uraian kejadian wajib diisi minimal satu poin.',
            'uraian_kejadian.*.max' => 'Setiap poin uraian kejadian maksimal 1000 karakter.',
            'dampak.required' => 'Dampak dari kecelakaan kerja wajib diisi.',
            'saksi_satu.required' => 'Nama Saksi I wajib diisi.',
            'saksi_dua.required' => 'Nama Saksi II wajib diisi.',
        ]);

        // Baris kosong dari form dinamis dibuang; minimal harus ada satu poin isi.
        $validator->after(function ($validator) use ($request) {
            if (AccidentReport::normalizeUraian((array) $request->input('uraian_kejadian', [])) === []) {
                $validator->errors()->add('uraian_kejadian', 'Uraian kejadian wajib diisi minimal satu poin.');
            }
        });

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return $validator->validated();
    }
}
