<?php

namespace App\Http\Controllers;

use App\Exports\HardwareExport;
use App\Models\Hardware;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpWord\IOFactory as WordIOFactory;
use PhpOffice\PhpWord\PhpWord;

class HardwareController extends Controller
{
    protected function getNextHardwareKode(): string
    {
        $maxKode = Hardware::max('kode_hardware');
        $number = $maxKode ? (int) str_replace('HARD-', '', $maxKode) + 1 : 1;

        return 'HARD-' . str_pad($number, 3, '0', STR_PAD_LEFT);
    }

    public function index(Request $request)
    {
        $query = Hardware::query();

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('nama_hardware', 'like', "%{$request->search}%")
                  ->orWhere('kode_hardware', 'like', "%{$request->search}%")
                  ->orWhere('kategori', 'like', "%{$request->search}%")
                  ->orWhere('merek', 'like', "%{$request->search}%");
            });
        }

        if ($request->filled('kategori')) {
            $query->where('kategori', $request->kategori);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $hardware = $query->latest()->paginate(15)->withQueryString();

        return view('admin.hardware.index', compact('hardware'));
    }

    protected function getFilteredQuery(Request $request)
    {
        $query = Hardware::query();

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('nama_hardware', 'like', "%{$request->search}%")
                  ->orWhere('kode_hardware', 'like', "%{$request->search}%")
                  ->orWhere('kategori', 'like', "%{$request->search}%")
                  ->orWhere('merek', 'like', "%{$request->search}%");
            });
        }

        if ($request->filled('kategori')) {
            $query->where('kategori', $request->kategori);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        return $query;
    }

    public function create()
    {
        $kode_hardware = $this->getNextHardwareKode();

        return view('admin.hardware.create', compact('kode_hardware'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_hardware' => ['required', 'string', 'max:255'],
            'kategori' => ['required', 'string', 'max:100'],
            'merek' => ['nullable', 'string', 'max:100'],
            'spesifikasi' => ['nullable', 'string'],
            'jumlah' => ['required', 'integer', 'min:0'],
            'lokasi' => ['nullable', 'string', 'max:255'],
            'status' => ['required', Rule::in(['tersedia', 'dipinjam', 'perbaikan', 'tidak_aktif'])],
            'catatan' => ['nullable', 'string'],
        ]);

        $validated['kode_hardware'] = $this->getNextHardwareKode();

        Hardware::create($validated);

        return redirect()->route('admin.hardware.index')->with('success', 'Data hardware berhasil ditambahkan.');
    }

    public function show(Hardware $hardware)
    {
        return view('admin.hardware.show', compact('hardware'));
    }

    public function edit(Hardware $hardware)
    {
        return view('admin.hardware.edit', compact('hardware'));
    }

    public function update(Request $request, Hardware $hardware)
    {
        $validated = $request->validate([
            'nama_hardware' => ['required', 'string', 'max:255'],
            'kategori' => ['required', 'string', 'max:100'],
            'merek' => ['nullable', 'string', 'max:100'],
            'spesifikasi' => ['nullable', 'string'],
            'jumlah' => ['required', 'integer', 'min:0'],
            'lokasi' => ['nullable', 'string', 'max:255'],
            'status' => ['required', Rule::in(['tersedia', 'dipinjam', 'perbaikan', 'tidak_aktif'])],
            'catatan' => ['nullable', 'string'],
        ]);

        $hardware->update($validated);

        return redirect()->route('admin.hardware.index')->with('success', 'Data hardware berhasil diperbarui.');
    }

    public function destroy(Hardware $hardware)
    {
        $hardware->delete();

        return redirect()->route('admin.hardware.index')->with('success', 'Data hardware berhasil dihapus.');
    }

    public function importForm()
    {
        return view('admin.hardware.import');
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

    protected function importExcel(Request $request)
    {
        $file = $request->file('file');
        $import = new class implements \Maatwebsite\Excel\Concerns\ToCollection {
            public function collection(\Illuminate\Support\Collection $collection)
            {
                return $collection;
            }
        };
        $rows = Excel::toArray($import, $file)[0] ?? [];
        $rows = array_slice($rows, 1);

        $imported = 0;
        foreach ($rows as $row) {
            $nama_hardware = $row[0] ?? null;
            $kategori = $row[1] ?? null;
            $merek = $row[2] ?? null;
            $spesifikasi = $row[3] ?? null;
            $jumlah = $row[4] ?? null;
            $lokasi = $row[5] ?? null;
            $status = $row[6] ?? null;
            $catatan = $row[7] ?? null;

            if (!$nama_hardware || !$kategori) {
                continue;
            }

            $kode_hardware = $this->getNextHardwareKode();

            if (Hardware::where('kode_hardware', $kode_hardware)->exists()) {
                continue;
            }

            $status = strtolower(trim((string) ($row[6] ?? '')));
            if (!in_array($status, ['tersedia', 'dipinjam', 'perbaikan', 'tidak_aktif'], true)) {
                $status = 'tersedia';
            }

            Hardware::create([
                'kode_hardware' => $kode_hardware,
                'nama_hardware' => $nama_hardware,
                'kategori' => $kategori,
                'merek' => $merek,
                'spesifikasi' => $spesifikasi,
                'jumlah' => $jumlah ?: 0,
                'lokasi' => $lokasi,
                'status' => $status,
                'catatan' => $catatan,
            ]);

            $imported++;
        }

        return redirect()->route('admin.hardware.index')->with('success', "Import berhasil. $imported data hardware ditambahkan.");
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
            if (count($parts) < 2) continue;

            $nama_hardware = $parts[0] ?? null;
            $kategori = $parts[1] ?? null;

            if (!$nama_hardware || !$kategori) {
                continue;
            }

            $kode_hardware = $this->getNextHardwareKode();

            if (Hardware::where('kode_hardware', $kode_hardware)->exists()) {
                continue;
            }

            $status = strtolower(trim((string) ($parts[6] ?? '')));
            if (!in_array($status, ['tersedia', 'dipinjam', 'perbaikan', 'tidak_aktif'], true)) {
                $status = 'tersedia';
            }

            Hardware::create([
                'kode_hardware' => $kode_hardware,
                'nama_hardware' => $nama_hardware,
                'kategori' => $kategori,
                'merek' => $parts[2] ?? null,
                'spesifikasi' => $parts[3] ?? null,
                'jumlah' => $parts[4] ?? 0,
                'lokasi' => $parts[5] ?? null,
                'status' => $status,
                'catatan' => $parts[7] ?? null,
            ]);

            $imported++;
        }

        return redirect()->route('admin.hardware.index')->with('success', "Import PDF berhasil. $imported data hardware ditambahkan.");
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
            if (count($parts) < 2) continue;

            $nama_hardware = $parts[0] ?? null;
            $kategori = $parts[1] ?? null;

            if (!$nama_hardware || !$kategori) {
                continue;
            }

            $kode_hardware = $this->getNextHardwareKode();

            if (Hardware::where('kode_hardware', $kode_hardware)->exists()) {
                continue;
            }

            $status = strtolower(trim((string) ($parts[6] ?? '')));
            if (!in_array($status, ['tersedia', 'dipinjam', 'perbaikan', 'tidak_aktif'], true)) {
                $status = 'tersedia';
            }

            Hardware::create([
                'kode_hardware' => $kode_hardware,
                'nama_hardware' => $nama_hardware,
                'kategori' => $kategori,
                'merek' => $parts[2] ?? null,
                'spesifikasi' => $parts[3] ?? null,
                'jumlah' => $parts[4] ?? 0,
                'lokasi' => $parts[5] ?? null,
                'status' => $status,
                'catatan' => $parts[7] ?? null,
            ]);

            $imported++;
        }

        return redirect()->route('admin.hardware.index')->with('success', "Import Word berhasil. $imported data hardware ditambahkan.");
    }

    public function exportExcel(Request $request)
    {
        $hardware = $this->getFilteredQuery($request)->get();

        return Excel::download(new HardwareExport($hardware), 'data-hardware-' . now()->format('Y-m-d-H-i-s') . '.xlsx');
    }

    public function exportPdf(Request $request)
    {
        $hardware = $this->getFilteredQuery($request)->get();

        $pdf = Pdf::loadView('admin.hardware.export-pdf', compact('hardware'));

        return $pdf->download('data-hardware-' . now()->format('Y-m-d-H-i-s') . '.pdf');
    }

    public function exportWord(Request $request)
    {
        $hardware = $this->getFilteredQuery($request)->get();

        $phpWord = new PhpWord();
        $phpWord->setDefaultFontName('Arial');
        $phpWord->setDefaultFontSize(11);

        $section = $phpWord->addSection(['margin' => 100]);

        $title = $section->addText('Laporan Data Hardware', ['bold' => true, 'size' => 16], ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER]);
        $section->addText('', null, ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER]);
        $section->addText('Tanggal: ' . now()->translatedFormat('d F Y H:i'), ['size' => 10], ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER]);
        $section->addBreak();

        $table = $section->addTable(['borderSize' => 6, 'borderColor' => '000000']);

        $headers = ['Nama Hardware', 'Kategori', 'Merek', 'Spesifikasi', 'Jumlah', 'Lokasi', 'Status', 'Catatan'];
        foreach ($headers as $header) {
            $table->addRow();
            $table->addCell(800)->addText($header, ['bold' => true]);
        }

        foreach ($hardware as $index => $item) {
            $table->addRow();
            $table->addCell(2500)->addText($item->nama_hardware);
            $table->addCell(1500)->addText($item->kategori);
            $table->addCell(1500)->addText($item->merek ?: '-');
            $table->addCell(2500)->addText($item->spesifikasi ?: '-');
            $table->addCell(1200)->addText((string) $item->jumlah);
            $table->addCell(2000)->addText($item->lokasi ?: '-');
            $table->addCell(1500)->addText(ucfirst($item->status));
            $table->addCell(2500)->addText($item->catatan ?: '-');
        }

        $writer = WordIOFactory::createWriter($phpWord, 'Word2007');
        $fileName = 'data-hardware-' . now()->format('Y-m-d-H-i-s') . '.docx';
        $tempFile = storage_path('app/temp/' . $fileName);

        if (!file_exists(dirname($tempFile))) {
            mkdir(dirname($tempFile), 0755, true);
        }

        $writer->save($tempFile);

        return response()->download($tempFile)->deleteFileAfterSend();
    }
}
