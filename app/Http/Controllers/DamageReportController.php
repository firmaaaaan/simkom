<?php

namespace App\Http\Controllers;

use App\Models\DamageReport;
use Illuminate\Http\Request;

class DamageReportController extends Controller
{
    private function filteredQuery(Request $request)
    {
        return DamageReport::query()
            ->search($request->string('search')->toString())
            ->when($request->filled('jenis'), fn ($q) => $q->where('jenis_kejadian', $request->string('jenis')->toString()))
            ->when($request->filled('bulan'), fn ($q) => $q->whereMonth('tanggal_insiden', $request->integer('bulan')))
            ->when($request->filled('tahun'), fn ($q) => $q->whereYear('tanggal_insiden', $request->integer('tahun')));
    }

    public function index(Request $request)
    {
        $reports = $this->filteredQuery($request)
            ->latest('tanggal_insiden')
            ->latest('created_at')
            ->paginate(10)
            ->withQueryString();

        $tahunOptions = DamageReport::query()
            ->whereNotNull('tanggal_insiden')
            ->pluck('tanggal_insiden')
            ->map(fn ($tanggal) => (int) substr((string) $tanggal, 0, 4))
            ->unique()
            ->sortDesc()
            ->values()
            ->all();

        if (! in_array((int) date('Y'), $tahunOptions, true)) {
            array_unshift($tahunOptions, (int) date('Y'));
        }

        return view('damage-reports.index', compact('reports', 'tahunOptions'));
    }

    public function create()
    {
        return view('damage-reports.create');
    }

    public function store(Request $request)
    {
        $validated = $this->validateReport($request);
        $validated['peralatan'] = DamageReport::normalizePeralatan($request->input('peralatan', []));
        $validated['dilaporkan_ke_polisi'] = $request->boolean('dilaporkan_ke_polisi');
        $validated['created_by'] = auth()->id();

        DamageReport::create($validated);

        if ($request->input('action') === 'save_another') {
            return redirect()->route('damage-reports.create')
                ->with('success', 'Laporan kerusakan/kehilangan/pencurian berhasil disimpan. Silakan buat laporan berikutnya.');
        }

        return redirect()->route('damage-reports.index')
            ->with('success', 'Laporan kerusakan/kehilangan/pencurian berhasil disimpan.');
    }

    public function show(DamageReport $damageReport)
    {
        $damageReport->load('creator');

        return view('damage-reports.show', compact('damageReport'));
    }

    public function edit(DamageReport $damageReport)
    {
        return view('damage-reports.edit', compact('damageReport'));
    }

    public function update(Request $request, DamageReport $damageReport)
    {
        $validated = $this->validateReport($request);
        $validated['peralatan'] = DamageReport::normalizePeralatan($request->input('peralatan', []));
        $validated['dilaporkan_ke_polisi'] = $request->boolean('dilaporkan_ke_polisi');

        $damageReport->update($validated);

        return redirect()->route('damage-reports.index')
            ->with('success', 'Laporan kerusakan/kehilangan/pencurian berhasil diperbarui.');
    }

    public function destroy(DamageReport $damageReport)
    {
        $damageReport->delete();

        return redirect()->route('damage-reports.index')
            ->with('success', 'Laporan kerusakan/kehilangan/pencurian berhasil dihapus.');
    }

    public function bulkDestroy(Request $request)
    {
        $request->validate([
            'ids' => 'required|array|min:1',
            'ids.*' => 'exists:damage_reports,id',
        ]);

        DamageReport::whereIn('id', $request->ids)->delete();

        return redirect()->route('damage-reports.index')
            ->with('success', count($request->ids).' laporan berhasil dihapus.');
    }

    public function print(DamageReport $damageReport)
    {
        return view('damage-reports.print', compact('damageReport'));
    }

    private function validateReport(Request $request): array
    {
        return $request->validate([
            'pelapor_nama' => 'required|string|max:255',
            'pelapor_ruangan' => 'required|string|max:255',
            'pelapor_telepon' => 'nullable|string|max:50',
            'tanggal_insiden' => 'required|date',
            'waktu_kejadiaan' => 'nullable',
            'tanggal_dilaporkan' => 'nullable|date',
            'waktu_dilaporkan' => 'nullable',
            'lokasi_spesifik' => 'nullable|string|max:500',
            'jenis_kejadian' => 'required|in:kerusakan,kehilangan,pencurian',
            'deskripsi_kejadian' => 'required|string|max:5000',
            'perkiraan_biaya' => 'nullable|string|max:100',
            'penanggung_jawab' => 'nullable|string|max:255',
            'dilaporkan_ke_polisi' => 'nullable|boolean',
            'berkas_polisi' => 'nullable|string|max:255',
            'petugas_polisi' => 'nullable|string|max:255',
            'unit_kepolisian' => 'nullable|string|max:255',
            'telepon_polisi' => 'nullable|string|max:50',
            'peralatan' => 'nullable|array|max:20',
        ], [
            'pelapor_nama.required' => 'Nama pelapor wajib diisi.',
            'pelapor_ruangan.required' => 'Ruangan/laboratorium wajib diisi.',
            'tanggal_insiden.required' => 'Tanggal insiden wajib diisi.',
            'jenis_kejadian.required' => 'Jenis kejadian wajib dipilih.',
            'deskripsi_kejadian.required' => 'Deskripsi kejadian wajib diisi.',
        ]);
    }
}
