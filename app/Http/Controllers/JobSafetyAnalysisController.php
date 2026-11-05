<?php

namespace App\Http\Controllers;

use App\Exports\JsaTemplate;
use App\Imports\JsaImport;
use App\Imports\K3ImportContext;
use App\Models\JobSafetyAnalysis;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Facades\Excel;

class JobSafetyAnalysisController extends Controller
{
    private function filteredQuery(Request $request)
    {
        return JobSafetyAnalysis::query()
            ->search($request->string('search')->toString())
            ->when($request->filled('bulan'), fn ($q) => $q->whereMonth('tanggal_pekerjaan', $request->integer('bulan')))
            ->when($request->filled('tahun'), fn ($q) => $q->whereYear('tanggal_pekerjaan', $request->integer('tahun')));
    }

    public function index(Request $request)
    {
        $jsas = $this->filteredQuery($request)
            ->latest('tanggal_pekerjaan')
            ->latest('created_at')
            ->paginate(10)
            ->withQueryString();

        // Dihitung di PHP agar kompatibel dengan MySQL maupun SQLite.
        $tahunOptions = JobSafetyAnalysis::query()
            ->whereNotNull('tanggal_pekerjaan')
            ->pluck('tanggal_pekerjaan')
            ->map(fn ($tanggal) => (int) substr((string) $tanggal, 0, 4))
            ->unique()
            ->sortDesc()
            ->values()
            ->all();

        if (! in_array((int) date('Y'), $tahunOptions, true)) {
            array_unshift($tahunOptions, (int) date('Y'));
        }

        return view('analisis-keselamatan-pekerjaan.index', compact('jsas', 'tahunOptions'));
    }

    public function create()
    {
        return view('analisis-keselamatan-pekerjaan.create');
    }

    public function store(Request $request)
    {
        $validated = $this->validateJsa($request);
        $validated['apd'] = JobSafetyAnalysis::normalizeApd($request->input('apd', []));
        $validated['steps'] = JobSafetyAnalysis::normalizeSteps($request->input('steps', []));
        $validated['created_by'] = auth()->id();

        JobSafetyAnalysis::create($validated);

        if ($request->input('action') === 'save_another') {
            return redirect()->route('analisis-keselamatan-pekerjaan.create')
                ->with('success', 'Formulir JSA berhasil disimpan. Silakan isi form untuk pekerjaan berikutnya.');
        }

        return redirect()->route('analisis-keselamatan-pekerjaan.index')
            ->with('success', 'Formulir JSA berhasil disimpan.');
    }

    public function show(JobSafetyAnalysis $jobSafetyAnalysis)
    {
        $jobSafetyAnalysis->load('creator');

        return view('analisis-keselamatan-pekerjaan.show', ['jsa' => $jobSafetyAnalysis]);
    }

    public function edit(JobSafetyAnalysis $jobSafetyAnalysis)
    {
        return view('analisis-keselamatan-pekerjaan.edit', ['jsa' => $jobSafetyAnalysis]);
    }

    public function update(Request $request, JobSafetyAnalysis $jobSafetyAnalysis)
    {
        $validated = $this->validateJsa($request);
        $validated['apd'] = JobSafetyAnalysis::normalizeApd($request->input('apd', []));
        $validated['steps'] = JobSafetyAnalysis::normalizeSteps($request->input('steps', []));

        $jobSafetyAnalysis->update($validated);

        return redirect()->route('analisis-keselamatan-pekerjaan.index')
            ->with('success', 'Formulir JSA berhasil diperbarui.');
    }

    public function destroy(JobSafetyAnalysis $jobSafetyAnalysis)
    {
        $jobSafetyAnalysis->delete();

        return redirect()->route('analisis-keselamatan-pekerjaan.index')
            ->with('success', 'Formulir JSA berhasil dihapus.');
    }

    public function bulkDestroy(Request $request)
    {
        $request->validate([
            'ids' => 'required|array|min:1',
            'ids.*' => 'exists:job_safety_analyses,id',
        ]);

        JobSafetyAnalysis::whereIn('id', $request->ids)->delete();

        return redirect()->route('analisis-keselamatan-pekerjaan.index')
            ->with('success', count($request->ids).' formulir JSA berhasil dihapus.');
    }

    public function print(JobSafetyAnalysis $jobSafetyAnalysis)
    {
        return view('analisis-keselamatan-pekerjaan.print', ['jsa' => $jobSafetyAnalysis]);
    }

    public function template()
    {
        return Excel::download(new JsaTemplate, 'template_analisis_keselamatan_pekerjaan.xlsx');
    }

    public function import()
    {
        return view('analisis-keselamatan-pekerjaan.import');
    }

    public function storeImport(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls|max:10240',
        ]);

        $import = new JsaImport(new K3ImportContext());
        try {
            Excel::import($import, $request->file('file'));
        } catch (\Exception $e) {
            return redirect()->route('analisis-keselamatan-pekerjaan.import')
                ->with('import_error', 'Gagal mengimport: '.$e->getMessage());
        }

        $message = "Berhasil mengimpor {$import->getImportedCount()} formulir JSA";
        if ($errors = $import->getErrors()) {
            $message .= '. '.count($errors).' baris gagal: '.implode('; ', array_slice($errors, 0, 5));
            if (count($errors) > 5) {
                $message .= ' dan '.(count($errors) - 5).' lagi...';
            }
        }

        return redirect()->route('analisis-keselamatan-pekerjaan.index')
            ->with('success', $message)
            ->with('import_errors', $import->getErrors());
    }

    private function validateJsa(Request $request): array
    {
        $validator = Validator::make($request->all(), [
            'nama_pekerja' => 'required|string|max:255',
            'nama_pekerjaan' => 'required|string|max:255',
            'tanggal_pekerjaan' => 'required|date',
            'tempat' => 'required|string|max:255',
            'apd' => 'nullable|array',
            'apd_lain' => 'nullable|string|max:255',
            'steps' => 'required|array|min:1',
            'steps.*.langkah' => 'nullable|string|max:1000',
            'steps.*.bahaya' => 'nullable|string|max:1000',
            'steps.*.pengendalian' => 'nullable|string|max:1000',
            'steps.*.penanggung_jawab' => 'nullable|string|max:255',
        ], [
            'nama_pekerja.required' => 'Nama pekerja wajib diisi.',
            'nama_pekerjaan.required' => 'Nama pekerjaan wajib diisi.',
            'tanggal_pekerjaan.required' => 'Tanggal pekerjaan wajib diisi.',
            'tempat.required' => 'Tempat pekerjaan wajib diisi.',
            'steps.required' => 'Langkah pekerjaan wajib diisi minimal satu baris.',
            'steps.*.langkah.max' => 'Setiap langkah pekerjaan maksimal 1000 karakter.',
            'steps.*.bahaya.max' => 'Setiap identifikasi bahaya maksimal 1000 karakter.',
            'steps.*.pengendalian.max' => 'Setiap pengendalian maksimal 1000 karakter.',
            'steps.*.penanggung_jawab.max' => 'Setiap penanggung jawab maksimal 255 karakter.',
        ]);

        // Baris kosong dari form dinamis dibuang; baris yang tersisa
        // wajib punya uraian langkah pekerjaan.
        $validator->after(function ($validator) use ($request) {
            $steps = JobSafetyAnalysis::normalizeSteps((array) $request->input('steps', []));

            if ($steps === []) {
                $validator->errors()->add('steps', 'Langkah pekerjaan wajib diisi minimal satu baris.');

                return;
            }

            foreach ($steps as $index => $step) {
                if (($step['langkah'] ?? '') === '') {
                    $validator->errors()->add('steps', 'Uraian langkah pekerjaan pada baris ke-'.($index + 1).' wajib diisi.');
                }
            }
        });

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return $validator->validated();
    }
}
