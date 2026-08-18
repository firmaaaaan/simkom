<?php

namespace App\Http\Controllers;

use App\Models\Komputer;
use App\Models\Laboratorium;
use App\Models\PemeliharaanKomputer;
use App\Models\TahunAjaran;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Facades\Excel;

class PemeliharaanKomputerController extends Controller
{
    public function index(Request $request)
    {
        $query = PemeliharaanKomputer::query()->with(['komputer.laboratorium', 'tahunAjaran']);

        if ($request->filled('tahun_ajaran_id')) {
            $query->where('tahun_ajaran_id', $request->tahun_ajaran_id);
        }

        if ($request->filled('jenis')) {
            $query->where('jenis_pemeliharaan', $request->jenis);
        }

        if ($request->filled('laboratorium_id')) {
            $query->whereHas('komputer', function ($q) use ($request) {
                $q->where('laboratorium_id', $request->laboratorium_id);
            });
        }

        $pemeliharaan = $query->latest('tanggal_pemeliharaan')->paginate(10)->withQueryString();
        $laboratoriums = Laboratorium::orderBy('nama_laboratorium')->get();
        $komputers = Komputer::orderBy('nama_komputer')->get();
        $tahunList = TahunAjaran::orderBy('nama', 'desc')->get();

        return view('admin.pemeliharaan-komputer.index', compact('pemeliharaan', 'laboratoriums', 'komputers', 'tahunList'));
    }

    public function exportExcel(Request $request)
    {
        $query = PemeliharaanKomputer::query()->with(['komputer.laboratorium', 'tahunAjaran']);

        if ($request->filled('tahun_ajaran_id')) {
            $query->where('tahun_ajaran_id', $request->tahun_ajaran_id);
        }

        if ($request->filled('jenis')) {
            $query->where('jenis_pemeliharaan', $request->jenis);
        }

        if ($request->filled('laboratorium_id')) {
            $query->whereHas('komputer', function ($q) use ($request) {
                $q->where('laboratorium_id', $request->laboratorium_id);
            });
        }

        $pemeliharaan = $query->latest('tanggal_pemeliharaan')->get();

        return Excel::download(new \App\Exports\PemeliharaanKomputerExport($pemeliharaan), 'data-pemeliharaan-komputer-' . now()->format('Y-m-d-H-i-s') . '.xlsx');
    }

    public function create()
    {
        $laboratoriums = Laboratorium::orderBy('nama_laboratorium')->get();
        $komputers = Komputer::orderBy('nama_komputer')->get();
        $tahunAjaranList = TahunAjaran::orderBy('nama', 'desc')->get();

        return view('admin.pemeliharaan-komputer.create', compact('laboratoriums', 'komputers', 'tahunAjaranList'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'komputer_id' => ['required', 'integer', 'exists:komputer,id'],
            'tahun_ajaran_id' => ['required', 'integer', 'exists:tahun_ajaran,id'],
            'tanggal_pemeliharaan' => ['required', 'date'],
            'jenis_pemeliharaan' => ['required', Rule::in(['preventif', 'korektif', 'upgrade', 'penggantian', 'lainnya'])],
            'deskripsi' => ['required', 'string'],
            'biaya' => ['nullable', 'numeric', 'min:0'],
            'pic' => ['nullable', 'string', 'max:255'],
        ]);

        PemeliharaanKomputer::create($validated);

        return redirect()->route('admin.pemeliharaan-komputer.index')->with('success', 'Data pemeliharaan berhasil ditambahkan.');
    }

    public function edit(PemeliharaanKomputer $pemeliharaan)
    {
        $laboratoriums = Laboratorium::orderBy('nama_laboratorium')->get();
        $komputers = Komputer::orderBy('nama_komputer')->get();
        $tahunAjaranList = TahunAjaran::orderBy('nama', 'desc')->get();

        return view('admin.pemeliharaan-komputer.edit', compact('pemeliharaan', 'laboratoriums', 'komputers', 'tahunAjaranList'));
    }

    public function update(Request $request, PemeliharaanKomputer $pemeliharaan)
    {
        $validated = $request->validate([
            'komputer_id' => ['required', 'integer', 'exists:komputer,id'],
            'tahun_ajaran_id' => ['required', 'integer', 'exists:tahun_ajaran,id'],
            'tanggal_pemeliharaan' => ['required', 'date'],
            'jenis_pemeliharaan' => ['required', Rule::in(['preventif', 'korektif', 'upgrade', 'penggantian', 'lainnya'])],
            'deskripsi' => ['required', 'string'],
            'biaya' => ['nullable', 'numeric', 'min:0'],
            'pic' => ['nullable', 'string', 'max:255'],
        ]);

        $pemeliharaan->update($validated);

        return redirect()->route('admin.pemeliharaan-komputer.index')->with('success', 'Data pemeliharaan berhasil diperbarui.');
    }

    public function destroy(PemeliharaanKomputer $pemeliharaan)
    {
        $pemeliharaan->delete();

        return redirect()->route('admin.pemeliharaan-komputer.index')->with('success', 'Data pemeliharaan berhasil dihapus.');
    }

    public function exportExcelByComputer(Komputer $komputer)
    {
        $pemeliharaan = PemeliharaanKomputer::where('komputer_id', $komputer->id)
            ->latest('tanggal_pemeliharaan')
            ->get();

        return Excel::download(new \App\Exports\PemeliharaanKomputerExport($pemeliharaan), 'data-pemeliharaan-' . $komputer->nama_komputer . '-' . now()->format('Y-m-d-H-i-s') . '.xlsx');
    }
}
