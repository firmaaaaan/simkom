<?php

namespace App\Http\Controllers;

use App\Models\Hardware;
use App\Models\KartuKendali;
use App\Models\Komputer;
use App\Models\Laboratorium;
use App\Models\PemeliharaanKomputer;
use App\Models\Software;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\Style\Font;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class KomputerController extends Controller
{
    protected function getNextKomputerKode(): string
    {
        $maxKode = Komputer::max('kode_komputer');
        $number = $maxKode ? (int) str_replace('KOM-', '', $maxKode) + 1 : 1;

        return 'KOM-' . str_pad($number, 3, '0', STR_PAD_LEFT);
    }

    public function index(Request $request)
    {
        $query = Komputer::with(['laboratorium', 'hardware', 'software']);

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('nama_komputer', 'like', "%{$request->search}%")
                  ->orWhere('kode_komputer', 'like', "%{$request->search}%");
            });
        }

        if ($request->filled('laboratorium_id')) {
            $query->where('laboratorium_id', $request->laboratorium_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $summary = [
            'total' => (clone $query)->count(),
            'aktif' => (clone $query)->where('status', 'aktif')->count(),
            'perbaikan' => (clone $query)->where('status', 'perbaikan')->count(),
            'rusak' => (clone $query)->where('status', 'rusak')->count(),
            'tidak_aktif' => (clone $query)->where('status', 'tidak_aktif')->count(),
        ];

        $komputers = $query->latest()->paginate(12)->withQueryString();

        $laboratoriums = Laboratorium::orderBy('nama_laboratorium')->get();

        return view('admin.komputer.index', compact('komputers', 'laboratoriums', 'summary'));
    }

    public function create()
    {
        $kode_komputer = $this->getNextKomputerKode();

        $laboratoriums = Laboratorium::orderBy('nama_laboratorium')->get();

        return view('admin.komputer.create', array_merge($this->formOptions(), [
            'kode_komputer' => $kode_komputer,
            'laboratoriums' => $laboratoriums,
        ]));
    }

    public function store(Request $request)
    {
        $validated = $this->validateInput($request);

        $validated['kode_komputer'] = $this->getNextKomputerKode();

        $fotoPath = $request->file('foto') ? $request->file('foto')->store('komputer', 'public') : null;
        $validated['foto'] = $fotoPath;

        $hardwareIds = $validated['hardware_ids'] ?? [];
        $softwareIds = $validated['software_ids'] ?? [];

        unset($validated['hardware_ids'], $validated['software_ids']);

        $komputer = Komputer::create($validated);

        $hardwareSync = [];
        foreach ($hardwareIds as $id) {
            $hardwareSync[$id] = ['jumlah' => 1];
        }
        $komputer->hardware()->sync($hardwareSync, false);

        if (!empty($softwareIds)) {
            $komputer->software()->attach(array_values($softwareIds));
        }

        return redirect()->route('admin.komputer.index')->with('success', 'Data komputer berhasil ditambahkan.');
    }

    public function show(Komputer $komputer)
    {
        $komputer->load(['laboratorium', 'hardware', 'software']);

        return view('admin.komputer.show', compact('komputer'));
    }

    public function createKartuKendali(Komputer $komputer)
    {
        $komputer->load(['laboratorium', 'hardware', 'software']);
        $riwayat = KartuKendali::where('inspectable_type', Komputer::class)
            ->where('inspectable_id', $komputer->id)
            ->latest('tanggal_pemeriksaan')
            ->get();
        $tahunAjaranList = \App\Models\TahunAjaran::orderBy('nama', 'desc')->get();

        return view('admin.komputer.kartu-kendali-form', compact('komputer', 'riwayat', 'tahunAjaranList'));
    }

    public function storeKartuKendali(Request $request, Komputer $komputer)
    {
        $validated = $request->validate([
            'tanggal_pemeriksaan' => ['required', 'date'],
            'tahun_ajaran_id' => ['required', 'integer', 'exists:tahun_ajaran,id'],
            'pemeriksa' => ['required', 'string', 'max:255'],
            'kondisi_keseluruhan' => ['required', Rule::in(['baik', 'cukup', 'rusak'])],
            'catatan' => ['nullable', 'string'],
            'items' => ['nullable', 'array'],
            'items.*.nama' => ['required', 'string', 'max:255'],
            'items.*.kondisi' => ['required', Rule::in(['baik', 'cukup', 'rusak'])],
            'items.*.catatan' => ['nullable', 'string'],
        ]);

        $komputer->kartuKendali()->create($validated);

        return redirect()->route('admin.komputer.show', $komputer)->with('success', 'Kartu kendali berhasil disimpan.');
    }

    public function printKartuKendali(Komputer $komputer)
    {
        $komputer->load(['laboratorium', 'hardware', 'software']);
        $riwayat = KartuKendali::where('inspectable_type', Komputer::class)
            ->where('inspectable_id', $komputer->id)
            ->latest('tanggal_pemeriksaan')
            ->get();

        return view('admin.komputer.kartu-kendali-print', compact('komputer', 'riwayat'));
    }

    public function qrCode(Komputer $komputer)
    {
        $komputer->load('laboratorium');
        $url = route('admin.komputer.pemeliharaan', $komputer);
        $stickers = collect(range(1, 20));

        return view('admin.komputer.qr-code', compact('komputer', 'url', 'stickers'));
    }

    public function riwayatPemeliharaan(Komputer $komputer)
    {
        $komputer->load('laboratorium');
        $pemeliharaan = PemeliharaanKomputer::where('komputer_id', $komputer->id)
            ->with('tahunAjaran')
            ->latest('tanggal_pemeliharaan')
            ->get();

        return view('admin.komputer.riwayat-pemeliharaan', compact('komputer', 'pemeliharaan'));
    }

    public function qrStikerByLaboratorium(Request $request)
    {
        $laboratoriums = Laboratorium::orderBy('nama_laboratorium')->get();
        $laboratorium = null;
        $komputers = collect();

        if ($request->filled('laboratorium_id')) {
            $laboratorium = Laboratorium::findOrFail($request->laboratorium_id);
            $komputers = Komputer::where('laboratorium_id', $laboratorium->id)
                ->with('laboratorium')
                ->orderBy('nama_komputer')
                ->get();
        }

        return view('admin.komputer.qr-stiker-laboratorium', compact('laboratoriums', 'laboratorium', 'komputers'));
    }

    public function edit(Komputer $komputer)
    {
        $komputer->load(['hardware', 'software']);
        $laboratoriums = Laboratorium::orderBy('nama_laboratorium')->get();

        return view('admin.komputer.edit', array_merge($this->formOptions($komputer), [
            'komputer' => $komputer,
            'laboratoriums' => $laboratoriums,
        ]));
    }

    public function update(Request $request, Komputer $komputer)
    {
        $validated = $this->validateInput($request);

        if ($request->hasFile('foto')) {
            if ($komputer->foto && Storage::disk('public')->exists($komputer->foto)) {
                Storage::disk('public')->delete($komputer->foto);
            }
            $validated['foto'] = $request->file('foto')->store('komputer', 'public');
        }

        $hardwareIds = $validated['hardware_ids'] ?? [];
        $softwareIds = $validated['software_ids'] ?? [];

        unset($validated['hardware_ids'], $validated['software_ids']);

        $komputer->update($validated);

        $hardwareSync = [];
        foreach ($hardwareIds as $id) {
            $hardwareSync[$id] = ['jumlah' => 1];
        }
        $komputer->hardware()->sync($hardwareSync);

        $komputer->software()->sync($softwareIds);

        return redirect()->route('admin.komputer.index')->with('success', 'Data komputer berhasil diperbarui.');
    }

    public function destroy(Komputer $komputer)
    {
        if ($komputer->foto && Storage::disk('public')->exists($komputer->foto)) {
            Storage::disk('public')->delete($komputer->foto);
        }

        $komputer->hardware()->detach();
        $komputer->software()->detach();
        $komputer->delete();

        return redirect()->route('admin.komputer.index')->with('success', 'Data komputer berhasil dihapus.');
    }

    public function exportExcel(Request $request)
    {
        $komputers = $this->getFilteredQuery($request)->with(['laboratorium', 'hardware', 'software'])->get();

        return Excel::download(new \App\Exports\KomputerExcelExport($komputers), 'data-komputer-' . now()->format('Y-m-d-H-i-s') . '.xlsx');
    }

    public function exportPdf(Request $request)
    {
        $komputers = $this->getFilteredQuery($request)->with(['laboratorium', 'hardware', 'software'])->get();

        $pdf = Pdf::loadView('admin.komputer.export-pdf', compact('komputers'));

        return $pdf->download('data-komputer-' . now()->format('Y-m-d-H-i-s') . '.pdf');
    }

    public function exportWord(Request $request)
    {
        $komputers = $this->getFilteredQuery($request)->with(['laboratorium', 'hardware', 'software'])->get();

        $phpWord = new PhpWord();
        $phpWord->setDefaultFontName('Arial');
        $phpWord->setDefaultFontSize(11);

        $section = $phpWord->addSection(['margin' => 100]);

        $title = $section->addText('Laporan Data Komputer', ['bold' => true, 'size' => 16], ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER]);
        $section->addText('', null, ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER]);
        $section->addText('Tanggal: ' . now()->translatedFormat('d F Y H:i'), ['size' => 10], ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER]);
        $section->addTextBreak(1);

        $table = $section->addTable(['borderSize' => 6, 'borderColor' => '000000']);

        $headers = ['No', 'Kode Komputer', 'Nama Komputer', 'Laboratorium', 'Status', 'Spesifikasi'];
        foreach ($headers as $header) {
            $table->addRow();
            $table->addCell(800)->addText($header, ['bold' => true]);
        }

        foreach ($komputers as $index => $komputer) {
            $table->addRow();
            $table->addCell(800)->addText($index + 1);
            $table->addCell(1500)->addText($komputer->kode_komputer);
            $table->addCell(2500)->addText($komputer->nama_komputer);
            $table->addCell(2000)->addText($komputer->laboratorium->nama_laboratorium ?? '-');
            $table->addCell(1500)->addText(ucfirst(str_replace('_', ' ', $komputer->status)));
            $table->addCell(4000)->addText($komputer->spesifikasi ?: '-');

            $hardware = $komputer->hardware->pluck('nama_hardware')->filter()->join(', ') ?: '-';
            $software = $komputer->software->pluck('nama_software')->filter()->join(', ') ?: '-';

            $table->addRow();
            $table->addCell(800);
            $table->addCell(1500);
            $table->addCell(2500);
            $table->addCell(2000);
            $table->addCell(1500);
            $table->addCell(2000)->addText('Hardware: ' . $hardware);

            $table->addRow();
            $table->addCell(800);
            $table->addCell(1500);
            $table->addCell(2500);
            $table->addCell(2000);
            $table->addCell(1500);
            $table->addCell(2000)->addText('Software: ' . $software);
        }

        $writer = IOFactory::createWriter($phpWord, 'Word2007');
        $fileName = 'data-komputer-' . now()->format('Y-m-d-H-i-s') . '.docx';
        $tempFile = storage_path('app/temp/' . $fileName);

        if (!file_exists(dirname($tempFile))) {
            mkdir(dirname($tempFile), 0755, true);
        }

        $writer->save($tempFile);

        return response()->download($tempFile)->deleteFileAfterSend();
    }

    protected function getFilteredQuery(Request $request)
    {
        $query = Komputer::query()->with(['laboratorium', 'hardware', 'software']);

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('nama_komputer', 'like', "%{$request->search}%")
                  ->orWhere('kode_komputer', 'like', "%{$request->search}%");
            });
        }

        if ($request->filled('laboratorium_id')) {
            $query->where('laboratorium_id', $request->laboratorium_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        return $query;
    }

    protected function validateInput(Request $request): array
    {
        return $request->validate([
            'nama_komputer' => ['required', 'string', 'max:255'],
            'laboratorium_id' => ['nullable', 'exists:laboratorium,id'],
            'foto' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:2048'],
            'status' => ['required', Rule::in(['aktif', 'tidak_aktif', 'perbaikan', 'rusak'])],
            'hardware_ids' => ['nullable', 'array'],
            'hardware_ids.*' => ['integer', 'exists:hardware,id'],
            'software_ids' => ['nullable', 'array'],
            'software_ids.*' => ['integer', 'exists:software,id'],
            'catatan' => ['nullable', 'string'],
        ]);
    }

    /**
     * Build selectable, searchable option arrays for the hardware/software picks.
     */
    protected function formOptions(?Komputer $komputer = null): array
    {
        $selectedHardware = $komputer
            ? $komputer->relationLoaded('hardware')
                ? $komputer->hardware->pluck('id')->toArray()
                : []
            : (array) old('hardware_ids', []);
        $selectedSoftware = $komputer
            ? $komputer->relationLoaded('software')
                ? $komputer->software->pluck('id')->toArray()
                : []
            : (array) old('software_ids', []);

        $hardwareOptions = Hardware::orderBy('nama_hardware')->get()->map(function ($h) use ($selectedHardware) {
            return [
                'id' => $h->id,
                'label' => $h->nama_hardware . ($h->merek ? ' (' . $h->merek . ')' : '') . ' [' . $h->kategori . ']',
                'spec' => $h->nama_hardware,
                'selected' => in_array($h->id, $selectedHardware),
            ];
        })->values()->all();

        $softwareOptions = Software::orderBy('nama_software')->get()->map(function ($s) use ($selectedSoftware) {
            return [
                'id' => $s->id,
                'label' => $s->nama_software . ($s->versi ? ' v' . $s->versi : '') . ' [' . $s->kategori . ']',
                'spec' => $s->nama_software,
                'selected' => in_array($s->id, $selectedSoftware),
            ];
        })->values()->all();

        return [
            'hardwareOptions' => $hardwareOptions,
            'softwareOptions' => $softwareOptions,
        ];
    }
}

