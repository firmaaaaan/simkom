<?php

namespace App\Http\Controllers;

use App\Models\Komputer;
use App\Models\Laboratorium;
use App\Models\PeminjamanKomputer;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpWord\IOFactory as WordIOFactory;
use PhpOffice\PhpWord\PhpWord;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Exports\PeminjamanKomputerExport;

class PeminjamanKomputerController extends Controller
{
    public function index(Request $request)
    {
        $laboratoriums = Laboratorium::orderBy('nama_laboratorium')->get();
        $query = Komputer::with('laboratorium');

        if ($request->filled('laboratorium_id')) {
            $query->where('laboratorium_id', $request->laboratorium_id);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nama_komputer', 'like', "%{$search}%")
                  ->orWhere('kode_komputer', 'like', "%{$search}%");
            });
        }

        $komputers = $query->orderBy('nama_komputer')->get();

        if ($request->ajax()) {
            return view('peminjaman-komputer.partials.komputer-grid', compact('komputers'));
        }

        return view('peminjaman-komputer.index', compact('komputers', 'laboratoriums'));
    }

    public function create(Komputer $komputer)
    {
        if ($komputer->status !== 'aktif') {
            return redirect()->route('peminjaman-komputer.index')->with('error', 'Komputer ini sedang tidak tersedia untuk dipinjam.');
        }

        return view('peminjaman-komputer.create', compact('komputer'));
    }

    public static function calculateEndTime(string $jamMulai): string
    {
        $hour = (int) substr($jamMulai, 0, 2);
        $minute = (int) substr($jamMulai, 3, 2);

        $totalMinutes = $hour * 60 + $minute;
        $slotSize = 120;

        $slots = (int) ceil($totalMinutes / $slotSize);
        $endMinutes = $slots * $slotSize;

        $endHour = (int) floor($endMinutes / 60);
        $endMinute = $endMinutes % 60;

        return sprintf('%02d:%02d', $endHour, $endMinute);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'komputer_id' => ['required', 'exists:komputer,id'],
            'nama_peminjam' => ['required', 'string', 'max:255'],
            'npm_nim' => ['required', 'string', 'max:50'],
            'nama_prodi' => ['required', 'string', 'max:255'],
            'catatan' => ['nullable', 'string'],
            'tanggal_pinjam' => ['required', 'date', 'after_or_equal:today'],
            'jam_mulai' => ['required', 'date_format:H:i'],
            'jam_selesai' => ['required', 'date_format:H:i'],
        ]);

        $komputer = Komputer::findOrFail($validated['komputer_id']);

        if ($komputer->status !== 'aktif') {
            return redirect()->route('peminjaman-komputer.index')->with('error', 'Komputer ini sedang tidak tersedia untuk dipinjam.');
        }

        $tanggalPinjam = \Carbon\Carbon::parse($validated['tanggal_pinjam']);
        $minTanggal = \Carbon\Carbon::tomorrow();

        if ($tanggalPinjam->lt($minTanggal)) {
            return redirect()->back()->withInput()->with('error', 'Peminjaman komputer minimal H-1 (1 hari sebelum peminjaman).');
        }

        $jamMulai = $validated['jam_mulai'];
        $jamSelesai = $validated['jam_selesai'];

        $mulaiMenit = (int) substr($jamMulai, 0, 2) * 60 + (int) substr($jamMulai, 3, 2);
        $selesaiMenit = (int) substr($jamSelesai, 0, 2) * 60 + (int) substr($jamSelesai, 3, 2);
        $durasi = $selesaiMenit - $mulaiMenit;

        if ($durasi < 120) {
            return redirect()->back()->withInput()->with('error', 'Durasi peminjaman minimal 2 jam.');
        }

        if ($durasi % 120 !== 0) {
            return redirect()->back()->withInput()->with('error', 'Durasi peminjaman harus kelipatan 2 jam.');
        }

        $conflict = PeminjamanKomputer::where('komputer_id', $komputer->id)
            ->where('tanggal_pinjam', $validated['tanggal_pinjam'])
            ->whereIn('status_peminjaman', ['menunggu', 'disetujui', 'dipinjam'])
            ->where(function ($q) use ($jamMulai, $jamSelesai) {
                $q->whereBetween('jam_mulai', [$jamMulai, $jamSelesai])
                  ->orWhereBetween('jam_selesai', [$jamMulai, $jamSelesai])
                  ->orWhere(function ($q2) use ($jamMulai, $jamSelesai) {
                      $q2->where('jam_mulai', '<=', $jamMulai)
                         ->where('jam_selesai', '>=', $jamSelesai);
                  });
            })
            ->exists();

        if ($conflict) {
            return redirect()->back()->withInput()->with('error', 'Komputer sudah dipinjam pada jam tersebut.');
        }

        $kodeTracker = 'PKM-' . date('Ymd') . '-' . strtoupper(Str::random(6));

        $peminjaman = PeminjamanKomputer::create([
            'komputer_id' => $komputer->id,
            'kode_tracker' => $kodeTracker,
            'nama_peminjam' => $validated['nama_peminjam'],
            'npm_nim' => $validated['npm_nim'],
            'nama_prodi' => $validated['nama_prodi'] ?? null,
            'catatan' => $validated['catatan'] ?? null,
            'status' => 'dipinjam',
            'status_peminjaman' => 'menunggu',
            'tanggal_pinjam' => $validated['tanggal_pinjam'],
            'jam_mulai' => $jamMulai,
            'jam_selesai' => $jamSelesai,
        ]);

        return redirect()->route('peminjaman-komputer.verifikasi', ['kode_tracker' => $peminjaman->kode_tracker]);
    }

    public function verifikasi($kode_tracker)
    {
        $peminjaman = PeminjamanKomputer::where('kode_tracker', $kode_tracker)->firstOrFail();
        
        return view('peminjaman-komputer.verifikasi', compact('peminjaman', 'kode_tracker'));
    }

    public function adminIndex(Request $request)
    {
        $query = PeminjamanKomputer::with('komputer');

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('nama_peminjam', 'like', "%{$request->search}%")
                  ->orWhere('npm_nim', 'like', "%{$request->search}%");
            });
        }

        if ($request->filled('status_peminjaman')) {
            $query->where('status_peminjaman', $request->status_peminjaman);
        }

        $peminjaman = $query->latest()->paginate(20)->withQueryString();

        return view('admin.peminjaman-komputer.index', compact('peminjaman'));
    }

    public function exportExcel(Request $request)
    {
        $query = PeminjamanKomputer::with('komputer');

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('nama_peminjam', 'like', "%{$request->search}%")
                  ->orWhere('npm_nim', 'like', "%{$request->search}%");
            });
        }

        if ($request->filled('status_peminjaman')) {
            $query->where('status_peminjaman', $request->status_peminjaman);
        }

        $peminjaman = $query->latest()->get();

        return Excel::download(new PeminjamanKomputerExport($peminjaman), 'peminjaman-komputer-' . now()->format('Y-m-d-H-i-s') . '.xlsx');
    }

    public function exportPdf(Request $request)
    {
        $query = PeminjamanKomputer::with('komputer');

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('nama_peminjam', 'like', "%{$request->search}%")
                  ->orWhere('npm_nim', 'like', "%{$request->search}%");
            });
        }

        if ($request->filled('status_peminjaman')) {
            $query->where('status_peminjaman', $request->status_peminjaman);
        }

        $peminjaman = $query->latest()->get();

        $pdf = Pdf::loadView('admin.peminjaman-komputer.export-pdf', compact('peminjaman'));

        return $pdf->download('peminjaman-komputer-' . now()->format('Y-m-d-H-i-s') . '.pdf');
    }

    public function exportWord(Request $request)
    {
        $query = PeminjamanKomputer::with('komputer');

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('nama_peminjam', 'like', "%{$request->search}%")
                  ->orWhere('npm_nim', 'like', "%{$request->search}%");
            });
        }

        if ($request->filled('status_peminjaman')) {
            $query->where('status_peminjaman', $request->status_peminjaman);
        }

        $peminjaman = $query->latest()->get();

        $phpWord = new PhpWord();
        $phpWord->setDefaultFontName('Arial');
        $phpWord->setDefaultFontSize(11);

        $section = $section = $phpWord->addSection(['margin' => 100]);

        $title = $section->addText('Laporan Peminjaman Komputer', ['bold' => true, 'size' => 16], ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER]);
        $section->addText('', null, ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER]);
        $section->addText('Tanggal: ' . now()->translatedFormat('d F Y H:i'), ['size' => 10], ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER]);
        $section->addText('Total: ' . $peminjaman->count() . ' data', ['size' => 10], ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER]);

        $table = $section->addTable(['borderSize' => 6, 'borderColor' => '000000']);

        $table->addRow();
        $table->addCell(500)->addText('No', ['bold' => true]);
        $table->addCell(1500)->addText('Komputer', ['bold' => true]);
        $table->addCell(1500)->addText('Peminjam', ['bold' => true]);
        $table->addCell(1200)->addText('NPM/NIM', ['bold' => true]);
        $table->addCell(1500)->addText('Kode Tracker', ['bold' => true]);
        $table->addCell(1200)->addText('Tanggal Pinjam', ['bold' => true]);
        $table->addCell(1000)->addText('Jam', ['bold' => true]);
        $table->addCell(1200)->addText('Status Peminjaman', ['bold' => true]);
        $table->addCell(1200)->addText('Status Komputer', ['bold' => true]);

        foreach ($peminjaman as $index => $item) {
            $table->addRow();
            $table->addCell(500)->addText((string)($index + 1));
            $table->addCell(1500)->addText($item->komputer->nama_komputer ?? '-');
            $table->addCell(1500)->addText($item->nama_peminjam);
            $table->addCell(1200)->addText($item->npm_nim);
            $table->addCell(1500)->addText($item->kode_tracker);
            $table->addCell(1200)->addText($item->tanggal_pinjam ? \Carbon\Carbon::parse($item->tanggal_pinjam)->translatedFormat('d F Y') : '-');
            $table->addCell(1000)->addText($item->jam_mulai . ' - ' . $item->jam_selesai);
            $table->addCell(1200)->addText(ucfirst($item->status_peminjaman));
            $table->addCell(1200)->addText(ucfirst($item->komputer->status ?? '-'));
        }

        $writer = WordIOFactory::createWriter($phpWord, 'Word2007');
        $fileName = 'peminjaman-komputer-' . now()->format('Y-m-d-H-i-s') . '.docx';
        $tempFile = tempnam(sys_get_temp_dir(), 'word') . '.docx';
        $writer->save($tempFile);

        return response()->download($tempFile, $fileName)->deleteFileAfterSend(true);
    }

    public function approve(PeminjamanKomputer $peminjaman)
    {
        $peminjaman->update([
            'status_peminjaman' => 'disetujui',
            'status' => 'dipinjam',
        ]);

        $peminjaman->komputer->update(['status' => 'dipinjam']);

        return redirect()->back()->with('success', 'Peminjaman komputer disetujui.');
    }

    public function reject(Request $request, PeminjamanKomputer $peminjaman)
    {
        $request->validate([
            'alasan_penolakan' => ['nullable', 'string', 'max:500'],
        ]);

        $peminjaman->update([
            'status_peminjaman' => 'ditolak',
            'status' => 'aktif',
            'catatan' => $peminjaman->catatan . ($request->alasan_penolakan ? '\n\nAlasan penolakan: ' . $request->alasan_penolakan : ''),
        ]);

        $peminjaman->komputer->update(['status' => 'aktif']);

        return redirect()->back()->with('success', 'Peminjaman komputer ditolak.');
    }

    public function returnItem(PeminjamanKomputer $peminjaman)
    {
        $peminjaman->update([
            'tanggal_kembali_aktual' => now()->toDateString(),
            'status' => 'dikembalikan',
            'status_peminjaman' => 'dikembalikan',
        ]);

        $peminjaman->komputer->update(['status' => 'aktif']);

        return redirect()->back()->with('success', 'Komputer berhasil dikembalikan.');
    }
}
