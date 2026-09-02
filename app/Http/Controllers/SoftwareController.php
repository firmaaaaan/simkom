<?php

namespace App\Http\Controllers;

use App\Exports\SoftwareExport;
use App\Models\Software;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpWord\IOFactory as WordIOFactory;
use PhpOffice\PhpWord\PhpWord;

class SoftwareController extends Controller
{
    protected function getNextSoftwareKode(): string
    {
        $maxKode = Software::max('kode_software');
        $number = $maxKode ? (int) str_replace('SOFT-', '', $maxKode) + 1 : 1;

        return 'SOFT-' . str_pad($number, 3, '0', STR_PAD_LEFT);
    }

    public function index(Request $request)
    {
        $query = Software::query();

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('nama_software', 'like', "%{$request->search}%")
                  ->orWhere('kode_software', 'like', "%{$request->search}%")
                  ->orWhere('kategori', 'like', "%{$request->search}%")
                  ->orWhere('versi', 'like', "%{$request->search}%");
            });
        }

        if ($request->filled('kategori')) {
            $query->where('kategori', $request->kategori);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $software = $query->latest()->paginate(15)->withQueryString();

        return view('admin.software.index', compact('software'));
    }

    protected function getFilteredQuery(Request $request)
    {
        $query = Software::query();

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('nama_software', 'like', "%{$request->search}%")
                  ->orWhere('kode_software', 'like', "%{$request->search}%")
                  ->orWhere('kategori', 'like', "%{$request->search}%")
                  ->orWhere('versi', 'like', "%{$request->search}%");
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
        $kode_software = $this->getNextSoftwareKode();

        return view('admin.software.create', compact('kode_software'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_software' => ['required', 'string', 'max:255'],
            'kategori' => ['required', 'string', 'max:100'],
            'versi' => ['nullable', 'string', 'max:50'],
            'lisensi' => ['required', Rule::in(['gratis', 'berbayar', 'edukasi', 'trial', 'open_source'])],
            'tanggal_instalasi' => ['nullable', 'date'],
            'tanggal_expired' => ['nullable', 'date', 'after_or_equal:tanggal_instalasi'],
            'status' => ['required', Rule::in(['aktif', 'tidak_aktif', 'trial'])],
            'catatan' => ['nullable', 'string'],
        ]);

        $validated['kode_software'] = $this->getNextSoftwareKode();

        Software::create($validated);

        return redirect()->route('admin.software.index')->with('success', 'Data software berhasil ditambahkan.');
    }

    public function show(Software $software)
    {
        return view('admin.software.show', compact('software'));
    }

    public function edit(Software $software)
    {
        return view('admin.software.edit', compact('software'));
    }

    public function update(Request $request, Software $software)
    {
        $validated = $request->validate([
            'nama_software' => ['required', 'string', 'max:255'],
            'kategori' => ['required', 'string', 'max:100'],
            'versi' => ['nullable', 'string', 'max:50'],
            'lisensi' => ['required', Rule::in(['gratis', 'berbayar', 'edukasi', 'trial', 'open_source'])],
            'tanggal_instalasi' => ['nullable', 'date'],
            'tanggal_expired' => ['nullable', 'date', 'after_or_equal:tanggal_instalasi'],
            'status' => ['required', Rule::in(['aktif', 'tidak_aktif', 'trial'])],
            'catatan' => ['nullable', 'string'],
        ]);

        $software->update($validated);

        return redirect()->route('admin.software.index')->with('success', 'Data software berhasil diperbarui.');
    }

    public function destroy(Software $software)
    {
        $software->delete();

        return redirect()->route('admin.software.index')->with('success', 'Data software berhasil dihapus.');
    }

    public function importForm()
    {
        return view('admin.software.import');
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
            $nama_software = $row[0] ?? null;
            $kategori = $row[1] ?? null;
            $versi = $row[2] ?? null;
            $lisensi = $row[3] ?? null;
            $tanggal_instalasi = $row[4] ?? null;
            $tanggal_expired = $row[5] ?? null;
            $status = $row[6] ?? null;
            $catatan = $row[7] ?? null;

            if (!$nama_software || !$kategori) {
                continue;
            }

            $kode_software = $this->getNextSoftwareKode();

            if (Software::where('kode_software', $kode_software)->exists()) {
                continue;
            }

            $status = strtolower(trim((string) ($status)));
            if (!in_array($status, ['aktif', 'tidak_aktif', 'trial'], true)) {
                $status = 'aktif';
            }

            $tanggalInstalasi = $tanggal_instalasi && $tanggal_instalasi !== '-' ? \Carbon\Carbon::createFromFormat('d-m-Y', $tanggal_instalasi)->format('Y-m-d') : null;
            $tanggalExpired = $tanggal_expired && $tanggal_expired !== '-' ? \Carbon\Carbon::createFromFormat('d-m-Y', $tanggal_expired)->format('Y-m-d') : null;

            Software::create([
                'kode_software' => $kode_software,
                'nama_software' => $nama_software,
                'kategori' => $kategori,
                'versi' => $versi,
                'lisensi' => $lisensi ?: 'gratis',
                'tanggal_instalasi' => $tanggalInstalasi,
                'tanggal_expired' => $tanggalExpired,
                'status' => $status,
                'catatan' => $catatan,
            ]);

            $imported++;
        }

        return redirect()->route('admin.software.index')->with('success', "Import berhasil. $imported data software ditambahkan.");
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

            $nama_software = $parts[0] ?? null;
            $kategori = $parts[1] ?? null;

            if (!$nama_software || !$kategori) {
                continue;
            }

            $kode_software = $this->getNextSoftwareKode();

            if (Software::where('kode_software', $kode_software)->exists()) {
                continue;
            }

            $status = strtolower(trim((string) ($parts[6] ?? '')));
            if (!in_array($status, ['aktif', 'tidak_aktif', 'trial'], true)) {
                $status = 'aktif';
            }

            $tanggalInstalasi = ($parts[4] ?? null) && $parts[4] !== '-' ? \Carbon\Carbon::createFromFormat('d-m-Y', $parts[4])->format('Y-m-d') : null;
            $tanggalExpired = ($parts[5] ?? null) && $parts[5] !== '-' ? \Carbon\Carbon::createFromFormat('d-m-Y', $parts[5])->format('Y-m-d') : null;

            Software::create([
                'kode_software' => $kode_software,
                'nama_software' => $nama_software,
                'kategori' => $kategori,
                'versi' => $parts[2] ?? null,
                'lisensi' => $parts[3] ?? 'gratis',
                'tanggal_instalasi' => $tanggalInstalasi,
                'tanggal_expired' => $tanggalExpired,
                'status' => $status,
                'catatan' => $parts[7] ?? null,
            ]);

            $imported++;
        }

        return redirect()->route('admin.software.index')->with('success', "Import PDF berhasil. $imported data software ditambahkan.");
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

            $nama_software = $parts[0] ?? null;
            $kategori = $parts[1] ?? null;

            if (!$nama_software || !$kategori) {
                continue;
            }

            $kode_software = $this->getNextSoftwareKode();

            if (Software::where('kode_software', $kode_software)->exists()) {
                continue;
            }

            $status = strtolower(trim((string) ($parts[6] ?? '')));
            if (!in_array($status, ['aktif', 'tidak_aktif', 'trial'], true)) {
                $status = 'aktif';
            }

            $tanggalInstalasi = ($parts[4] ?? null) && $parts[4] !== '-' ? \Carbon\Carbon::createFromFormat('d-m-Y', $parts[4])->format('Y-m-d') : null;
            $tanggalExpired = ($parts[5] ?? null) && $parts[5] !== '-' ? \Carbon\Carbon::createFromFormat('d-m-Y', $parts[5])->format('Y-m-d') : null;

            Software::create([
                'kode_software' => $kode_software,
                'nama_software' => $nama_software,
                'kategori' => $kategori,
                'versi' => $parts[2] ?? null,
                'lisensi' => $parts[3] ?? 'gratis',
                'tanggal_instalasi' => $tanggalInstalasi,
                'tanggal_expired' => $tanggalExpired,
                'status' => $status,
                'catatan' => $parts[7] ?? null,
            ]);

            $imported++;
        }

        return redirect()->route('admin.software.index')->with('success', "Import Word berhasil. $imported data software ditambahkan.");
    }

    public function exportExcel(Request $request)
    {
        $software = $this->getFilteredQuery($request)->get();

        return Excel::download(new SoftwareExport($software), 'data-software-' . now()->format('Y-m-d-H-i-s') . '.xlsx');
    }

    public function exportPdf(Request $request)
    {
        $software = $this->getFilteredQuery($request)->get();

        $pdf = Pdf::loadView('admin.software.export-pdf', compact('software'));

        return $pdf->download('data-software-' . now()->format('Y-m-d-H-i-s') . '.pdf');
    }

    public function exportWord(Request $request)
    {
        $software = $this->getFilteredQuery($request)->get();

        $phpWord = new PhpWord();
        $phpWord->setDefaultFontName('Arial');
        $phpWord->setDefaultFontSize(11);

        $section = $phpWord->addSection(['margin' => 100]);

        $title = $section->addText('Laporan Data Software', ['bold' => true, 'size' => 16], ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER]);
        $section->addText('', null, ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER]);
        $section->addText('Tanggal: ' . now()->translatedFormat('d F Y H:i'), ['size' => 10], ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER]);
        $section->addBreak();

        $table = $section->addTable(['borderSize' => 6, 'borderColor' => '000000']);

        $headers = ['Nama Software', 'Kategori', 'Versi', 'Lisensi', 'Tanggal Instalasi', 'Tanggal Expired', 'Status', 'Catatan'];
        foreach ($headers as $header) {
            $table->addRow();
            $table->addCell(800)->addText($header, ['bold' => true]);
        }

        foreach ($software as $index => $item) {
            $table->addRow();
            $table->addCell(2500)->addText($item->nama_software);
            $table->addCell(1500)->addText($item->kategori);
            $table->addCell(1200)->addText($item->versi ?: '-');
            $table->addCell(1500)->addText(ucfirst($item->lisensi));
            $table->addCell(2000)->addText($item->tanggal_instalasi ? $item->tanggal_instalasi->format('d-m-Y') : '-');
            $table->addCell(2000)->addText($item->tanggal_expired ? $item->tanggal_expired->format('d-m-Y') : '-');
            $table->addCell(1500)->addText(ucfirst($item->status));
            $table->addCell(2500)->addText($item->catatan ?: '-');
        }

        $writer = WordIOFactory::createWriter($phpWord, 'Word2007');
        $fileName = 'data-software-' . now()->format('Y-m-d-H-i-s') . '.docx';
        $tempFile = storage_path('app/temp/' . $fileName);

        if (!file_exists(dirname($tempFile))) {
            mkdir(dirname($tempFile), 0755, true);
        }

        $writer->save($tempFile);

        return response()->download($tempFile)->deleteFileAfterSend();
    }
}
