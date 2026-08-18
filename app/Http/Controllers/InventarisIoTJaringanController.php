<?php

namespace App\Http\Controllers;

use App\Models\InventarisIoTJaringan;
use App\Models\KartuKendali;
use App\Models\KomponenIotJaringan;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpWord\IOFactory as WordIOFactory;
use PhpOffice\PhpWord\PhpWord;
use Barryvdh\DomPDF\Facade\Pdf;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class InventarisIoTJaringanController extends Controller
{
    protected function getNextInventarisKode(): string
    {
        $maxKode = InventarisIoTJaringan::where('kode_perangkat', 'like', 'INV-%')->max('kode_perangkat');
        $number = $maxKode ? (int) str_replace('INV-', '', $maxKode) + 1 : 1;

        return 'INV-' . str_pad($number, 3, '0', STR_PAD_LEFT);
    }

    public function index(Request $request)
    {
        $query = InventarisIoTJaringan::query();

        if ($request->filled('search')) {
            $query->where('nama_inventaris', 'like', "%{$request->search}%");
        }

        if ($request->filled('kategori')) {
            $query->where('kategori', $request->kategori);
        }

        if ($request->filled('jenis')) {
            $query->where('jenis', $request->jenis);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $summary = [
            'total' => (clone $query)->count(),
            'tersedia' => (clone $query)->where('status', 'tersedia')->count(),
            'dipinjam' => (clone $query)->where('status', 'dipinjam')->count(),
            'perbaikan' => (clone $query)->where('status', 'perbaikan')->count(),
            'tidak_aktif' => (clone $query)->where('status', 'tidak_aktif')->count(),
        ];

        $inventaris = $query->latest()->with('items.komponen')->paginate(15)->withQueryString();

        return view('admin.inventaris-iot-jaringan.index', compact('inventaris', 'summary'));
    }

    public function create()
    {
        $komponen = KomponenIotJaringan::orderBy('nama_komponen')->get();
        $laboratoriums = \App\Models\Laboratorium::orderBy('nama_laboratorium')->get();
        $kode_perangkat = $this->getNextInventarisKode();

        return view('admin.inventaris-iot-jaringan.create', compact('komponen', 'laboratoriums', 'kode_perangkat'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_inventaris' => ['required', 'string', 'max:255'],
            'kategori' => ['required', Rule::in(['IoT', 'Jaringan'])],
            'jenis' => ['required', Rule::in(['Satuan', 'Paket', 'Sistem', 'Box'])],
            'lokasi' => ['nullable', 'string', 'max:255'],
            'status' => ['required', Rule::in(['tersedia', 'dipinjam', 'perbaikan', 'tidak_aktif'])],
            'catatan' => ['nullable', 'string'],
            'jumlah_inventaris' => ['required', 'integer', 'min:1'],
            'items' => ['nullable', 'array'],
            'items.*.komponen_iot_jaringan_id' => ['required', 'integer', 'exists:komponen_iot_jaringan,id'],
            'items.*.jumlah' => ['required', 'integer', 'min:1'],
        ]);

        if ($validated['jenis'] === 'Satuan') {
            $items = $validated['items'] ?? [];
            if (count($items) !== 1) {
                return back()->withErrors(['items' => 'Jenis Satuan harus memilih tepat 1 komponen.'])->withInput();
            }
        }

        $items = $validated['items'] ?? [];
        unset($validated['items']);

        $jumlah = (int) $validated['jumlah_inventaris'];
        unset($validated['jumlah_inventaris']);

        $baseKode = $this->getNextInventarisKode();

        for ($i = 1; $i <= $jumlah; $i++) {
            $nama = $validated['nama_inventaris'];
            if ($jumlah > 1) {
                $nama .= ' (' . $i . ')';
            }

            $kodePerangkat = $jumlah > 1 ? $baseKode . '-' . $i : $baseKode;

            $inventaris = InventarisIoTJaringan::create(array_merge($validated, [
                'nama_inventaris' => $nama,
                'kode_perangkat' => $kodePerangkat,
            ]));

            foreach ($items as $item) {
                $inventaris->items()->create($item);
            }
        }

        return redirect()->route('admin.inventaris-iot-jaringan.index')->with('success', $jumlah . ' data inventaris IoT & Jaringan berhasil ditambahkan.');
    }

    public function show(InventarisIoTJaringan $inventaris_iot_jaringan)
    {
        $inventaris_iot_jaringan->load('items.komponen');

        return view('admin.inventaris-iot-jaringan.show', compact('inventaris_iot_jaringan'));
    }

    public function createKartuKendali(InventarisIoTJaringan $inventaris_iot_jaringan)
    {
        $inventaris_iot_jaringan->load('items.komponen');
        $riwayat = KartuKendali::where('inspectable_type', InventarisIoTJaringan::class)
            ->where('inspectable_id', $inventaris_iot_jaringan->id)
            ->latest('tanggal_pemeriksaan')
            ->get();
        $tahunAjaranList = \App\Models\TahunAjaran::orderBy('nama', 'desc')->get();

        return view('admin.inventaris-iot-jaringan.kartu-kendali-form', compact('inventaris_iot_jaringan', 'riwayat', 'tahunAjaranList'));
    }

    public function storeKartuKendali(Request $request, InventarisIoTJaringan $inventaris_iot_jaringan)
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

        $inventaris_iot_jaringan->kartuKendali()->create($validated);

        return redirect()->route('admin.inventaris-iot-jaringan.show', $inventaris_iot_jaringan)->with('success', 'Kartu kendali berhasil disimpan.');
    }

    public function printKartuKendali(InventarisIoTJaringan $inventaris_iot_jaringan)
    {
        $inventaris_iot_jaringan->load('items.komponen');
        $riwayat = KartuKendali::where('inspectable_type', InventarisIoTJaringan::class)
            ->where('inspectable_id', $inventaris_iot_jaringan->id)
            ->latest('tanggal_pemeriksaan')
            ->get();

        return view('admin.inventaris-iot-jaringan.kartu-kendali-print', compact('inventaris_iot_jaringan', 'riwayat'));
    }

    public function qrCode(InventarisIoTJaringan $inventaris_iot_jaringan)
    {
        $url = route('inventaris-iot-jaringan.peminjaman.create', $inventaris_iot_jaringan);
        $stickers = collect(range(1, 20));

        return view('admin.inventaris-iot-jaringan.qr-code', compact('inventaris_iot_jaringan', 'url', 'stickers'));
    }

    public function qrStikerByKategori(Request $request)
    {
        $kategoriOptions = ['IoT', 'Jaringan'];
        $selectedKategori = $request->filled('kategori') ? $request->kategori : null;
        $inventaris = collect();

        if ($selectedKategori) {
            $inventaris = InventarisIoTJaringan::where('kategori', $selectedKategori)
                ->orderBy('nama_inventaris')
                ->get();
        }

        return view('admin.inventaris-iot-jaringan.qr-stiker-kategori', compact('kategoriOptions', 'selectedKategori', 'inventaris'));
    }

    public function edit(InventarisIoTJaringan $inventaris_iot_jaringan)
    {
        $inventaris_iot_jaringan->load('items.komponen');
        $komponen = KomponenIotJaringan::orderBy('nama_komponen')->get();
        $laboratoriums = \App\Models\Laboratorium::orderBy('nama_laboratorium')->get();

        return view('admin.inventaris-iot-jaringan.edit', compact('inventaris_iot_jaringan', 'komponen', 'laboratoriums'));
    }

    public function update(Request $request, InventarisIoTJaringan $inventaris_iot_jaringan)
    {
        $validated = $request->validate([
            'nama_inventaris' => ['required', 'string', 'max:255'],
            'kategori' => ['required', Rule::in(['IoT', 'Jaringan'])],
            'jenis' => ['required', Rule::in(['Satuan', 'Paket', 'Sistem', 'Box'])],
            'lokasi' => ['nullable', 'string', 'max:255'],
            'status' => ['required', Rule::in(['tersedia', 'dipinjam', 'perbaikan', 'tidak_aktif'])],
            'catatan' => ['nullable', 'string'],
            'items' => ['nullable', 'array'],
            'items.*.komponen_iot_jaringan_id' => ['required', 'integer', 'exists:komponen_iot_jaringan,id'],
            'items.*.jumlah' => ['required', 'integer', 'min:1'],
        ]);

        if ($validated['jenis'] === 'Satuan') {
            $items = $validated['items'] ?? [];
            if (count($items) !== 1) {
                return back()->withErrors(['items' => 'Jenis Satuan harus memilih tepat 1 komponen.'])->withInput();
            }
        }

        $items = $validated['items'] ?? [];
        unset($validated['items']);

        $inventaris_iot_jaringan->update($validated);

        $inventaris_iot_jaringan->items()->delete();
        foreach ($items as $item) {
            $inventaris_iot_jaringan->items()->create($item);
        }

        return redirect()->route('admin.inventaris-iot-jaringan.index')->with('success', 'Data inventaris IoT & Jaringan berhasil diperbarui.');
    }

    public function destroy(InventarisIoTJaringan $inventaris_iot_jaringan)
    {
        $inventaris_iot_jaringan->items()->delete();
        $inventaris_iot_jaringan->delete();

        return redirect()->route('admin.inventaris-iot-jaringan.index')->with('success', 'Data inventaris IoT & Jaringan berhasil dihapus.');
    }

    public function exportExcel(Request $request)
    {
        $inventaris = $this->getFilteredQuery($request)->with(['items.komponen'])->get();

        return Excel::download(new \App\Exports\InventarisIoTJaringanExcelExport($inventaris), 'data-inventaris-iot-jaringan-' . now()->format('Y-m-d-H-i-s') . '.xlsx');
    }

    public function exportPdf(Request $request)
    {
        $inventaris = $this->getFilteredQuery($request)->with(['items.komponen'])->get();

        $pdf = Pdf::loadView('admin.inventaris-iot-jaringan.export-pdf', compact('inventaris'));

        return $pdf->download('data-inventaris-iot-jaringan-' . now()->format('Y-m-d-H-i-s') . '.pdf');
    }

    public function exportWord(Request $request)
    {
        $inventaris = $this->getFilteredQuery($request)->with(['items.komponen'])->get();

        $phpWord = new PhpWord();
        $phpWord->setDefaultFontName('Arial');
        $phpWord->setDefaultFontSize(11);

        $section = $phpWord->addSection(['margin' => 100]);

        $title = $section->addText('Laporan Data Inventaris IoT & Jaringan', ['bold' => true, 'size' => 16], ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER]);
        $section->addText('', null, ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER]);
        $section->addText('Tanggal: ' . now()->translatedFormat('d F Y H:i'), ['size' => 10], ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER]);
        $section->addBreak();

        $table = $section->addTable(['borderSize' => 6, 'borderColor' => '000000']);

        $headers = ['No', 'Nama Inventaris', 'Kategori', 'Jenis', 'Lokasi', 'Status', 'Komponen'];
        foreach ($headers as $header) {
            $table->addRow();
            $table->addCell(800)->addText($header, ['bold' => true]);
        }

        foreach ($inventaris as $index => $item) {
            $komponenList = $item->items->map(function ($sub) {
                return $sub->komponen->nama_komponen . ' (x' . $sub->jumlah . ')';
            })->join(', ');

            $table->addRow();
            $table->addCell(800)->addText($index + 1);
            $table->addCell(2500)->addText($item->nama_inventaris);
            $table->addCell(1500)->addText($item->kategori);
            $table->addCell(1500)->addText($item->jenis);
            $table->addCell(2000)->addText($item->lokasi ?: '-');
            $table->addCell(1500)->addText(ucfirst($item->status));
            $table->addCell(4000)->addText($komponenList ?: '-');
        }

        $writer = IOFactory::createWriter($phpWord, 'Word2007');
        $fileName = 'data-inventaris-iot-jaringan-' . now()->format('Y-m-d-H-i-s') . '.docx';
        $tempFile = storage_path('app/temp/' . $fileName);

        if (!file_exists(dirname($tempFile))) {
            mkdir(dirname($tempFile), 0755, true);
        }

        $writer->save($tempFile);

        return response()->download($tempFile)->deleteFileAfterSend();
    }

    protected function getFilteredQuery(Request $request)
    {
        $query = InventarisIoTJaringan::query();

        if ($request->filled('search')) {
            $query->where('nama_inventaris', 'like', "%{$request->search}%");
        }

        if ($request->filled('kategori')) {
            $query->where('kategori', $request->kategori);
        }

        if ($request->filled('jenis')) {
            $query->where('jenis', $request->jenis);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        return $query;
    }

    public function importForm()
    {
        return view('admin.inventaris-iot-jaringan.import');
    }

    public function importExcel(Request $request)
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:xlsx,xls', 'max:10240'],
        ]);

        try {
            $path = $request->file('file')->getRealPath();
            $spreadsheet = SpreadsheetIOFactory::load($path);
            $sheet = $spreadsheet->getActiveSheet();
            $rows = $sheet->toArray();

            $imported = 0;
            foreach ($rows as $index => $row) {
                if ($index === 0) continue;

                $nama_inventaris = $row[0] ?? null;
                $kategori = $row[1] ?? null;
                $jenis = $row[2] ?? null;
                $lokasi = $row[3] ?? null;
                $status = $row[4] ?? null;
                $catatan = $row[5] ?? null;
                $komponen_nama = $row[6] ?? null;
                $item_jumlah = $row[7] ?? 1;

                if (!$nama_inventaris || !$kategori || !$jenis) {
                    continue;
                }

                $status = strtolower(trim((string) ($row[4] ?? '')));
                if (!in_array($status, ['tersedia', 'dipinjam', 'perbaikan', 'tidak_aktif'], true)) {
                    $status = 'tersedia';
                }

                $kode_perangkat = $this->getNextInventarisKode();

                if (InventarisIoTJaringan::where('kode_perangkat', $kode_perangkat)->exists()) {
                    continue;
                }

                $inventaris = InventarisIoTJaringan::create([
                    'nama_inventaris' => $nama_inventaris,
                    'kategori' => $kategori,
                    'jenis' => $jenis,
                    'lokasi' => $lokasi,
                    'status' => $status,
                    'catatan' => $catatan,
                    'kode_perangkat' => $kode_perangkat,
                ]);

                if ($komponen_nama) {
                    $komponen = KomponenIotJaringan::where('nama_komponen', $komponen_nama)->first();
                    if ($komponen) {
                        $inventaris->items()->create([
                            'komponen_iot_jaringan_id' => $komponen->id,
                            'jumlah' => $item_jumlah ?: 1,
                        ]);
                    }
                }

                $imported++;
            }

            return redirect()->route('admin.inventaris-iot-jaringan.index')->with('success', "Import Excel berhasil. $imported data inventaris ditambahkan.");
        } catch (\Throwable $e) {
            return back()->withErrors(['file' => 'Gagal import Excel: ' . $e->getMessage()])->withInput();
        }
    }

    public function importPdf(Request $request)
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:pdf', 'max:10240'],
        ]);

        try {
            $path = $request->file('file')->getRealPath();
            $parser = new \Smalot\PdfParser\Parser();
            $pdf = $parser->parseFile($path);
            $text = $pdf->getText();

            $imported = 0;
            $lines = explode("\n", $text);
            foreach ($lines as $line) {
                $parts = array_map('trim', explode(',', $line));
                if (count($parts) < 3) continue;

                $nama_inventaris = $parts[0] ?? null;
                $kategori = $parts[1] ?? null;
                $jenis = $parts[2] ?? null;

                if (!$nama_inventaris || !$kategori || !$jenis) {
                    continue;
                }

                $status = strtolower(trim((string) ($parts[4] ?? '')));
                if (!in_array($status, ['tersedia', 'dipinjam', 'perbaikan', 'tidak_aktif'], true)) {
                    $status = 'tersedia';
                }

                $kode_perangkat = $this->getNextInventarisKode();

                if (InventarisIoTJaringan::where('kode_perangkat', $kode_perangkat)->exists()) {
                    continue;
                }

                $inventaris = InventarisIoTJaringan::create([
                    'nama_inventaris' => $nama_inventaris,
                    'kategori' => $kategori,
                    'jenis' => $jenis,
                    'lokasi' => $parts[3] ?? null,
                    'status' => $status,
                    'catatan' => $parts[5] ?? null,
                    'kode_perangkat' => $kode_perangkat,
                ]);

                $imported++;
            }

            return redirect()->route('admin.inventaris-iot-jaringan.index')->with('success', "Import PDF berhasil. $imported data inventaris ditambahkan.");
        } catch (\Throwable $e) {
            return back()->withErrors(['file' => 'Gagal import PDF: ' . $e->getMessage()])->withInput();
        }
    }

    public function importWord(Request $request)
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:docx', 'max:10240'],
        ]);

        try {
            $path = $request->file('file')->getRealPath();
            $phpWord = WordIOFactory::load($path);
            $text = '';

            foreach ($phpWord->getSections() as $section) {
                foreach ($section->getElements() as $element) {
                    if (method_exists($element, 'getText')) {
                        $text .= $element->getText() . "\n";
                    }
                }
            }

            $imported = 0;
            $lines = explode("\n", $text);
            foreach ($lines as $line) {
                $parts = array_map('trim', explode(',', $line));
                if (count($parts) < 3) continue;

                $nama_inventaris = $parts[0] ?? null;
                $kategori = $parts[1] ?? null;
                $jenis = $parts[2] ?? null;

                if (!$nama_inventaris || !$kategori || !$jenis) {
                    continue;
                }

                $status = strtolower(trim((string) ($parts[4] ?? '')));
                if (!in_array($status, ['tersedia', 'dipinjam', 'perbaikan', 'tidak_aktif'], true)) {
                    $status = 'tersedia';
                }

                $kode_perangkat = $this->getNextInventarisKode();

                if (InventarisIoTJaringan::where('kode_perangkat', $kode_perangkat)->exists()) {
                    continue;
                }

                $inventaris = InventarisIoTJaringan::create([
                    'nama_inventaris' => $nama_inventaris,
                    'kategori' => $kategori,
                    'jenis' => $jenis,
                    'lokasi' => $parts[3] ?? null,
                    'status' => $status,
                    'catatan' => $parts[5] ?? null,
                    'kode_perangkat' => $kode_perangkat,
                ]);
                $inventaris->update([
                    'kode_perangkat' => 'INV-' . str_pad($inventaris->id, 3, '0', STR_PAD_LEFT),
                ]);

                $imported++;
            }

            return redirect()->route('admin.inventaris-iot-jaringan.index')->with('success', "Import Word berhasil. $imported data inventaris ditambahkan.");
        } catch (\Throwable $e) {
            return back()->withErrors(['file' => 'Gagal import Word: ' . $e->getMessage()])->withInput();
        }
    }
}
