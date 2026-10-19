<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\Pengadaan;
use Illuminate\Http\Request;

class PengadaanController extends Controller
{
    private function filteredQuery(Request $request)
    {
        return Pengadaan::query()
            ->with('academicYear')
            ->search($request->string('search')->toString())
            ->kategori($request->string('kategori')->toString())
            ->status($request->string('status')->toString())
            ->when($request->filled('academic_year_id'), fn ($q) => $q->where('academic_year_id', $request->academic_year_id));
    }

    public function index(Request $request)
    {
        $pengadaans = $this->filteredQuery($request)
            ->latest('tanggal')
            ->paginate(10)
            ->withQueryString();

        $academicYears = AcademicYear::orderByDesc('start_year')->get();

        return view('pengadaan.index', compact('pengadaans', 'academicYears'));
    }

    public function create()
    {
        return view('pengadaan.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_barang' => 'required|string|max:255',
            'kategori' => 'required|in:Software,Hardware,PC',
            'jumlah' => 'required|integer|min:1',
            'harga' => 'required|integer|min:0',
            'tanggal' => 'required|date',
            'keterangan' => 'nullable|string',
        ]);

        $validated['status'] = 'Proses';
        $validated['academic_year_id'] = AcademicYear::current()?->id;
        $validated['created_by'] = auth()->id();

        Pengadaan::create($validated);

        if ($request->input('action') === 'save_another') {
            return redirect()->route('pengadaan.create')
                ->with('success', 'Pengadaan berhasil dibuat. Silakan isi form untuk data berikutnya.');
        }

        return redirect()->route('pengadaan.index')->with('success', 'Pengadaan berhasil dibuat.');
    }

    public function show(Pengadaan $pengadaan)
    {
        $pengadaan->load('creator');

        return view('pengadaan.show', compact('pengadaan'));
    }

    public function edit(Pengadaan $pengadaan)
    {
        return view('pengadaan.edit', compact('pengadaan'));
    }

    public function update(Request $request, Pengadaan $pengadaan)
    {
        $validated = $request->validate([
            'nama_barang' => 'required|string|max:255',
            'kategori' => 'required|in:Software,Hardware,PC',
            'jumlah' => 'required|integer|min:1',
            'harga' => 'required|integer|min:0',
            'tanggal' => 'required|date',
            'keterangan' => 'nullable|string',
        ]);

        $pengadaan->update($validated);

        return redirect()->route('pengadaan.index')->with('success', 'Data pengadaan berhasil diperbarui.');
    }

    public function destroy(Pengadaan $pengadaan)
    {
        $pengadaan->delete();

        return redirect()->route('pengadaan.index')->with('success', 'Pengadaan berhasil dihapus.');
    }

    public function bulkDestroy(Request $request)
    {
        $request->validate([
            'ids' => 'required|array|min:1',
            'ids.*' => 'exists:pengadaans,id',
        ]);

        $count = Pengadaan::whereIn('id', $request->ids)->delete();

        return redirect()->route('pengadaan.index')->with('success', $count.' pengadaan berhasil dihapus.');
    }

    public function updateStatus(Request $request, Pengadaan $pengadaan)
    {
        $validated = $request->validate([
            'status' => 'required|in:Proses,Acc,Ditolak',
        ]);

        $pengadaan->update($validated);

        $messages = [
            'Acc' => 'Pengadaan disetujui (ACC).',
            'Ditolak' => 'Pengadaan ditolak.',
            'Proses' => 'Status pengadaan dikembalikan ke proses.',
        ];

        return back()->with('success', $messages[$validated['status']] ?? 'Status pengadaan diperbarui.');
    }
}
