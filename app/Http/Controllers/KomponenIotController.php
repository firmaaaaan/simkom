<?php

namespace App\Http\Controllers;

use App\Exports\KomponenIotExport;
use App\Models\KomponenIot;
use App\Models\KomponenIotJaringan;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpWord\IOFactory as WordIOFactory;
use PhpOffice\PhpWord\PhpWord;

class KomponenIotController extends Controller
{
    public function index(Request $request)
    {
        $query = KomponenIot::query();

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('nama_komponen', 'like', "%{$request->search}%")
                  ->orWhere('kode_komponen', 'like', "%{$request->search}%")
                  ->orWhere('merek', 'like', "%{$request->search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $komponen = $query->latest()->paginate(15)->withQueryString();

        return view('admin.komponen-iot.index', compact('komponen'));
    }

    protected function getFilteredQuery(Request $request)
    {
        $query = KomponenIot::query();

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('nama_komponen', 'like', "%{$request->search}%")
                  ->orWhere('kode_komponen', 'like', "%{$request->search}%")
                  ->orWhere('merek', 'like', "%{$request->search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        return $query;
    }

    public function create()
    {
        $kode_komponen = $this->getNextKomponenKode('IOT');

        return view('admin.komponen-iot.create', compact('kode_komponen'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_komponen' => ['required', 'string', 'max:255'],
            'merek' => ['nullable', 'string', 'max:100'],
            'spesifikasi' => ['nullable', 'string'],
            'jenis' => ['nullable', 'string', 'max:100'],
            'jumlah' => ['required', 'integer', 'min:0'],
            'lokasi' => ['nullable', 'string', 'max:255'],
            'status' => ['required', Rule::in(['tersedia', 'dipinjam', 'perbaikan', 'tidak_aktif'])],
            'catatan' => ['nullable', 'string'],
            'foto' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:2048'],
        ]);

        $validated['kategori'] = 'IoT';

        if ($request->hasFile('foto')) {
            $validated['foto'] = $request->file('foto')->store('komponen-iot', 'public');
        }

        $validated['kode_komponen'] = $this->getNextKomponenKode('IOT');

        KomponenIot::create($validated);

        return redirect()->route('admin.komponen-iot.index')->with('success', 'Data komponen IoT berhasil ditambahkan.');
    }

    public function show(KomponenIot $komponenIot)
    {
        return view('admin.komponen-iot.show', compact('komponenIot'));
    }

    public function edit(KomponenIot $komponenIot)
    {
        return view('admin.komponen-iot.edit', compact('komponenIot'));
    }

    public function update(Request $request, KomponenIot $komponenIot)
    {
        $validated = $request->validate([
            'nama_komponen' => ['required', 'string', 'max:255'],
            'merek' => ['nullable', 'string', 'max:100'],
            'spesifikasi' => ['nullable', 'string'],
            'jenis' => ['nullable', 'string', 'max:100'],
            'jumlah' => ['required', 'integer', 'min:0'],
            'lokasi' => ['nullable', 'string', 'max:255'],
            'status' => ['required', Rule::in(['tersedia', 'dipinjam', 'perbaikan', 'tidak_aktif'])],
            'catatan' => ['nullable', 'string'],
            'foto' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:2048'],
        ]);

        $validated['kategori'] = 'IoT';

        if ($request->hasFile('foto')) {
            if ($komponenIot->foto && Storage::disk('public')->exists($komponenIot->foto)) {
                Storage::disk('public')->delete($komponenIot->foto);
            }
            $validated['foto'] = $request->file('foto')->store('komponen-iot', 'public');
        }

        $komponenIot->update($validated);

        return redirect()->route('admin.komponen-iot.index')->with('success', 'Data komponen IoT berhasil diperbarui.');
    }

    public function destroy(KomponenIot $komponenIot)
    {
        if ($komponenIot->foto && Storage::disk('public')->exists($komponenIot->foto)) {
            Storage::disk('public')->delete($komponenIot->foto);
        }

        $komponenIot->delete();

        return redirect()->route('admin.komponen-iot.index')->with('success', 'Data komponen IoT berhasil dihapus.');
    }

    public function importForm()
    {
        return view('admin.komponen-iot.import');
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:xlsx,pdf,docx', 'max:10240'],
        ]);

        $extension = $request->file('file')->getClientOriginalExtension();

        try {
            if ($extension === 'xlsx') {
                return $this->importExcel($request);
            } elseif ($extension === 'pdf') {
                return $this->importPdf($request);
            } elseif ($extension === 'docx') {
                return $this->importWord($request);
            }
        } catch (\Throwable $e) {
            return back()->withErrors(['file' => 'Gagal import: ' . $e->getMessage()])->withInput();
        }

        return back()->withErrors(['file' => 'Format file tidak didukung.'])->withInput();
    }

    protected function getNextKomponenKode(string $prefix): string
    {
        $maxKode = KomponenIotJaringan::where('kode_komponen', 'like', $prefix . '%')->max('kode_komponen');
        $number = $maxKode ? (int) str_replace($prefix . '-', '', $maxKode) + 1 : 1;

        return $prefix . '-' . str_pad($number, 3, '0', STR_PAD_LEFT);
    }

    protected function importExcel(Request $request)
    {
        $path = $request->file('file')->getRealPath();
        $import = new class implements \Maatwebsite\Excel\Concerns\ToCollection {
            public function collection(\Illuminate\Support\Collection $collection)
            {
                return $collection;
            }
        };
        $rows = Excel::toArray($import, $path)[0] ?? [];
        $rows = array_slice($rows, 1);

        $imported = 0;
        foreach ($rows as $row) {
            $nama_komponen = $row[0] ?? null;
            $merek = $row[1] ?? null;
            $spesifikasi = $row[2] ?? null;
            $jumlah = $row[3] ?? null;
            $lokasi = $row[4] ?? null;
            $status = $row[5] ?? null;
            $catatan = $row[6] ?? null;

            if (!$nama_komponen) {
                continue;
            }

            $kode_komponen = $this->getNextKomponenKode('IOT');

            if (KomponenIotJaringan::where('kode_komponen', $kode_komponen)->exists()) {
                continue;
            }

            $status = strtolower(trim((string) ($status)));
            if (!in_array($status, ['tersedia', 'dipinjam', 'perbaikan', 'tidak_aktif'], true)) {
                $status = 'tersedia';
            }

            KomponenIot::create([
                'kode_komponen' => $kode_komponen,
                'nama_komponen' => $nama_komponen,
                'kategori' => 'IoT',
                'merek' => $merek,
                'spesifikasi' => $spesifikasi,
                'jumlah' => $jumlah ?: 0,
                'lokasi' => $lokasi,
                'status' => $status,
                'catatan' => $catatan,
                'foto' => null,
            ]);

            $imported++;
        }

        return redirect()->route('admin.komponen-iot.index')->with('success', "Import berhasil. $imported data komponen IoT ditambahkan.");
    }

    protected function importPdf(Request $request)
    {
        $path = $request->file('file')->getRealPath();
        $parser = new \Smalot\PdfParser\Parser();
        $pdf = $parser->parseFile($path);
        $text = $pdf->getText();

        $imported = 0;
        $lines = explode("\n", $text);
        foreach ($lines as $line) {
            $parts = array_map('trim', explode(',', $line));
            if (count($parts) < 1) continue;

            $nama_komponen = $parts[0] ?? null;

            if (!$nama_komponen) {
                continue;
            }

            $kode_komponen = $this->getNextKomponenKode('IOT');

            if (KomponenIotJaringan::where('kode_komponen', $kode_komponen)->exists()) {
                continue;
            }

            $status = strtolower(trim((string) ($parts[5] ?? '')));
            if (!in_array($status, ['tersedia', 'dipinjam', 'perbaikan', 'tidak_aktif'], true)) {
                $status = 'tersedia';
            }

            KomponenIot::create([
                'kode_komponen' => $kode_komponen,
                'nama_komponen' => $nama_komponen,
                'kategori' => 'IoT',
                'merek' => $parts[1] ?? null,
                'spesifikasi' => $parts[2] ?? null,
                'jumlah' => $parts[3] ?? 0,
                'lokasi' => $parts[4] ?? null,
                'status' => $status,
                'catatan' => $parts[5] ?? null,
                'foto' => null,
            ]);

            $imported++;
        }

        return redirect()->route('admin.komponen-iot.index')->with('success', "Import PDF berhasil. $imported data komponen IoT ditambahkan.");
    }

    protected function importWord(Request $request)
    {
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
            if (count($parts) < 1) continue;

            $nama_komponen = $parts[0] ?? null;

            if (!$nama_komponen) {
                continue;
            }

            $kode_komponen = $this->getNextKomponenKode('IOT');

            if (KomponenIotJaringan::where('kode_komponen', $kode_komponen)->exists()) {
                continue;
            }

            $status = strtolower(trim((string) ($parts[5] ?? '')));
            if (!in_array($status, ['tersedia', 'dipinjam', 'perbaikan', 'tidak_aktif'], true)) {
                $status = 'tersedia';
            }

            KomponenIot::create([
                'kode_komponen' => $kode_komponen,
                'nama_komponen' => $nama_komponen,
                'kategori' => 'IoT',
                'merek' => $parts[1] ?? null,
                'spesifikasi' => $parts[2] ?? null,
                'jumlah' => $parts[3] ?? 0,
                'lokasi' => $parts[4] ?? null,
                'status' => $status,
                'catatan' => $parts[6] ?? null,
                'foto' => null,
            ]);

            $imported++;
        }

        return redirect()->route('admin.komponen-iot.index')->with('success', "Import Word berhasil. $imported data komponen IoT ditambahkan.");
    }

    public function exportExcel(Request $request)
    {
        $komponen = $this->getFilteredQuery($request)->get();

        return Excel::download(new KomponenIotExport($komponen), 'data-komponen-iot-' . now()->format('Y-m-d-H-i-s') . '.xlsx');
    }

    public function exportPdf(Request $request)
    {
        $komponen = $this->getFilteredQuery($request)->get();

        $pdf = Pdf::loadView('admin.komponen-iot.export-pdf', compact('komponen'));

        return $pdf->download('data-komponen-iot-' . now()->format('Y-m-d-H-i-s') . '.pdf');
    }

    public function exportWord(Request $request)
    {
        $komponen = $this->getFilteredQuery($request)->get();

        $phpWord = new PhpWord();
        $phpWord->setDefaultFontName('Arial');
        $phpWord->setDefaultFontSize(11);

        $section = $phpWord->addSection(['margin' => 100]);

        $title = $section->addText('Laporan Data Komponen IoT', ['bold' => true, 'size' => 16], ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER]);
        $section->addText('', null, ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER]);
        $section->addText('Tanggal: ' . now()->translatedFormat('d F Y H:i'), ['size' => 10], ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER]);
        $section->addBreak();

        $table = $section->addTable(['borderSize' => 6, 'borderColor' => '000000']);

        $headers = ['Nama Komponen', 'Merek', 'Spesifikasi', 'Jumlah', 'Lokasi', 'Status', 'Catatan'];
        foreach ($headers as $header) {
            $table->addRow();
            $table->addCell(800)->addText($header, ['bold' => true]);
        }

        foreach ($komponen as $index => $item) {
            $table->addRow();
            $table->addCell(2500)->addText($item->nama_komponen);
            $table->addCell(1500)->addText($item->merek ?: '-');
            $table->addCell(2500)->addText($item->spesifikasi ?: '-');
            $table->addCell(1200)->addText((string) $item->jumlah);
            $table->addCell(2000)->addText($item->lokasi ?: '-');
            $table->addCell(1500)->addText(ucfirst($item->status));
            $table->addCell(2500)->addText($item->catatan ?: '-');
        }

        $writer = WordIOFactory::createWriter($phpWord, 'Word2007');
        $fileName = 'data-komponen-iot-' . now()->format('Y-m-d-H-i-s') . '.docx';
        $tempFile = storage_path('app/temp/' . $fileName);

        if (!file_exists(dirname($tempFile))) {
            mkdir(dirname($tempFile), 0755, true);
        }

        $writer->save($tempFile);

        return response()->download($tempFile)->deleteFileAfterSend();
    }
}
