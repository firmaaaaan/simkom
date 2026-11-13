<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\Computer;
use App\Models\JadwalPemeliharaan;
use App\Models\JadwalPemeliharaanItem;
use App\Models\Laboratory;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class JadwalPemeliharaanController extends Controller
{
    private function filteredQuery(Request $request)
    {
        return JadwalPemeliharaan::query()
            ->with(['laboratory', 'academicYear', 'items'])
            ->search($request->string('search')->toString())
            ->when($request->filled('laboratory_id') && $request->input('laboratory_id') !== 'all',
                fn ($q) => $q->where('laboratory_id', $request->input('laboratory_id')))
            ->when($request->filled('academic_year_id') && $request->input('academic_year_id') !== 'all',
                fn ($q) => $q->where('academic_year_id', $request->input('academic_year_id')))
            ->when($request->filled('status') && $request->input('status') !== 'all',
                fn ($q) => $q->filterStatus($request->input('status')))
            ->latest('tanggal_penyusunan')
            ->latest('created_at');
    }

    public function index(Request $request)
    {
        $jadwals = $this->filteredQuery($request)->paginate(10)->withQueryString();

        return view('jadwal-pemeliharaan.index', [
            'jadwals' => $jadwals,
            'laboratories' => Laboratory::orderBy('name')->get(),
            'academicYears' => AcademicYear::orderByDesc('start_year')->get(),
            'selectedLaboratoryId' => $request->input('laboratory_id', 'all'),
            'selectedAcademicYearId' => $request->input('academic_year_id', 'all'),
            'selectedStatus' => $request->input('status', 'all'),
        ]);
    }

    public function create()
    {
        return view('jadwal-pemeliharaan.create', [
            'jadwalPemeliharaan' => null,
            'laboratories' => Laboratory::orderBy('name')->get(),
            'academicYears' => AcademicYear::orderByDesc('start_year')->get(),
            'currentAcademicYearId' => AcademicYear::current()?->id,
            'defaultPic' => auth()->user()?->name,
            'jenisList' => JadwalPemeliharaan::JENIS_PEMELIHARAAN,
            'frekuensiList' => JadwalPemeliharaan::FREKUENSI,
            'statusList' => array_keys(JadwalPemeliharaanItem::STATUSES),
        ]);
    }

     /**
     * Daftar inventaris per lab untuk dropdown Kode/No. Inventaris.
     * Sumber: computers yang terikat laboratory_id (kode = label inventaris).
     */
    public function inventaris(Request $request)
    {
        $request->validate([
            'laboratory_id' => 'required|exists:laboratories,id',
        ]);

        $computers = Computer::where('laboratory_id', $request->input('laboratory_id'))
            ->orderBy('code')
            ->get(['id', 'code', 'description']);

        return response()->json([
            'data' => $computers->map(fn ($c) => [
                'code' => $c->code,
                'label' => $c->code.($c->description ? ' — '.$c->description : ''),
            ])->values(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $this->validateRequest($request);
        $validated['created_by'] = auth()->id();
        $validated['kode_dokumen'] = JadwalPemeliharaan::DOC_NUMBER;

        $jadwal = JadwalPemeliharaan::create($validated);
        $this->syncItems($jadwal, $request->input('items', []));

        if ($request->input('action') === 'save_another') {
            return redirect()->route('jadwal-pemeliharaan.create')
                ->with('success', 'Jadwal pemeliharaan berhasil disimpan. Silakan isi jadwal berikutnya.');
        }

        return redirect()->route('jadwal-pemeliharaan.show', $jadwal)
            ->with('success', 'Jadwal pemeliharaan berhasil disimpan.');
    }

    public function show(JadwalPemeliharaan $jadwalPemeliharaan)
    {
        $jadwalPemeliharaan->load(['laboratory', 'academicYear', 'creator', 'items']);

        return view('jadwal-pemeliharaan.show', compact('jadwalPemeliharaan'));
    }

    public function edit(JadwalPemeliharaan $jadwalPemeliharaan)
    {
        $jadwalPemeliharaan->load('items');

        $items = old('items', $jadwalPemeliharaan->items->map(fn ($item) => [
            'nama_alat_bahan' => $item->nama_alat_bahan,
            'kode_inventaris' => $item->kode_list,
            'lokasi' => $item->lokasi,
            'jenis_pemeliharaan' => $item->jenis_pemeliharaan,
            'frekuensi' => $item->frekuensi,
            'jadwal' => $item->jadwal?->toDateString(),
            'pic' => $item->pic,
            'status' => $item->status,
            'catatan_hasil' => $item->catatan_hasil,
        ])->values()->all());

        return view('jadwal-pemeliharaan.edit', [
            'jadwalPemeliharaan' => $jadwalPemeliharaan,
            'laboratories' => Laboratory::orderBy('name')->get(),
            'academicYears' => AcademicYear::orderByDesc('start_year')->get(),
            'items' => $items,
            'jenisList' => JadwalPemeliharaan::JENIS_PEMELIHARAAN,
            'frekuensiList' => JadwalPemeliharaan::FREKUENSI,
            'statusList' => array_keys(JadwalPemeliharaanItem::STATUSES),
        ]);
    }

    public function update(Request $request, JadwalPemeliharaan $jadwalPemeliharaan)
    {
        $validated = $this->validateRequest($request);
        $validated['kode_dokumen'] = JadwalPemeliharaan::DOC_NUMBER;

        $jadwalPemeliharaan->update($validated);
        $jadwalPemeliharaan->items()->delete();
        $this->syncItems($jadwalPemeliharaan, $request->input('items', []));

        return redirect()->route('jadwal-pemeliharaan.show', $jadwalPemeliharaan)
            ->with('success', 'Jadwal pemeliharaan berhasil diperbarui.');
    }

    public function destroy(JadwalPemeliharaan $jadwalPemeliharaan)
    {
        $jadwalPemeliharaan->items()->delete();
        $jadwalPemeliharaan->delete();

        return redirect()->route('jadwal-pemeliharaan.index')
            ->with('success', 'Jadwal pemeliharaan berhasil dihapus.');
    }

    public function bulkDestroy(Request $request)
    {
        $request->validate([
            'ids' => 'required|array|min:1',
            'ids.*' => 'exists:jadwal_pemeliharaans,id',
        ]);

        JadwalPemeliharaanItem::whereIn('jadwal_pemeliharaan_id', $request->ids)->delete();
        JadwalPemeliharaan::whereIn('id', $request->ids)->delete();

        return redirect()->route('jadwal-pemeliharaan.index')
            ->with('success', count($request->ids).' jadwal pemeliharaan berhasil dihapus.');
    }

    public function print(JadwalPemeliharaan $jadwalPemeliharaan)
    {
        $jadwalPemeliharaan->load(['laboratory', 'academicYear', 'items']);

        return view('jadwal-pemeliharaan.print', compact('jadwalPemeliharaan'));
    }

    private function validateRequest(Request $request): array
    {
        $validated = $request->validate([
            'laboratory_id' => 'nullable|exists:laboratories,id',
            'laboratorium_name' => 'nullable|string|max:255',
            'academic_year_id' => 'required|exists:academic_years,id',
            'pic' => 'required|string|max:255',
            'tanggal_penyusunan' => 'required|date',
            'disusun_oleh' => 'nullable|string|max:255',
            'disusun_tanggal' => 'nullable|date',
            'diperiksa_oleh' => 'nullable|string|max:255',
            'diperiksa_tanggal' => 'nullable|date',
            'mengetahui_oleh' => 'nullable|string|max:255',
            'mengetahui_tanggal' => 'nullable|date',
            'items' => 'required|array|min:1|max:100',
            'items.*.nama_alat_bahan' => 'required|string|max:255',
            'items.*.kode_inventaris' => 'nullable',
            'items.*.kode_inventaris.*' => 'string|max:100',
            'items.*.lokasi' => 'nullable|string|max:255',
            'items.*.jenis_pemeliharaan' => 'nullable|string|max:255',
            'items.*.frekuensi' => 'nullable|string|max:255',
            'items.*.jadwal' => 'nullable|date',
            'items.*.pic' => 'nullable|string|max:255',
            'items.*.status' => 'in:'.implode(',', array_keys(JadwalPemeliharaanItem::STATUSES)),
            'items.*.catatan_hasil' => 'nullable|string',
        ], [
            'academic_year_id.required' => 'Periode (Tahun Ajaran) wajib dipilih dari sistem.',
            'pic.required' => 'PIC/Petugas wajib diisi.',
            'tanggal_penyusunan.required' => 'Tanggal penyusunan wajib diisi.',
            'items.required' => 'Minimal satu baris item alat/bahan wajib diisi.',
            'items.*.nama_alat_bahan.required' => 'Nama alat/bahan pada setiap baris wajib diisi.',
        ]);

        if (empty($validated['laboratory_id']) && empty($validated['laboratorium_name'])) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'laboratorium_name' => 'Pilih laboratorium atau isi nama laboratorium/unit.',
            ]);
        }

        if (! empty($validated['laboratory_id'])) {
            $validated['laboratorium_name'] = Laboratory::find($validated['laboratory_id'])?->name;
        }

        $academicYear = AcademicYear::find($validated['academic_year_id']);
        $validated['periode'] = $academicYear?->name ?? $validated['periode'] ?? '';

        return collect($validated)->except('items')->all();
    }

    private function syncItems(JadwalPemeliharaan $jadwal, array $items): void
    {
        $rows = [];
        $now = now();
        $order = 1;

        foreach ($items as $item) {
            if (empty($item['nama_alat_bahan'] ?? null)) {
                continue;
            }
            $rows[] = [
                'id' => (string) Str::uuid(),
                'jadwal_pemeliharaan_id' => $jadwal->id,
                'nama_alat_bahan' => $item['nama_alat_bahan'],
                'kode_inventaris' => JadwalPemeliharaanItem::normalizeKode($item['kode_inventaris'] ?? null),
                'lokasi' => $item['lokasi'] ?? null,
                'jenis_pemeliharaan' => $item['jenis_pemeliharaan'] ?? null,
                'frekuensi' => $item['frekuensi'] ?? null,
                'jadwal' => $item['jadwal'] ?? null,
                'pic' => $item['pic'] ?? $jadwal->pic,
                'status' => in_array($item['status'] ?? 'Terjadwal', array_keys(JadwalPemeliharaanItem::STATUSES), true)
                    ? $item['status']
                    : 'Terjadwal',
                'catatan_hasil' => $item['catatan_hasil'] ?? null,
                'sort_order' => $order++,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        foreach (array_chunk($rows, 500) as $chunk) {
            JadwalPemeliharaanItem::insert($chunk);
        }
    }
}
