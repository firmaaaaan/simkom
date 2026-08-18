<?php

namespace App\Http\Controllers;

use App\Models\Komputer;
use App\Models\LaporanKendalaKomputer;
use App\Exports\LaporanKendalaKomputerExport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;

class LaporanKendalaKomputerController extends Controller
{
    public function create()
    {
        $komputers = Komputer::with('laboratorium')->orderBy('nama_komputer')->get();
        $laboratoriums = \App\Models\Laboratorium::orderBy('nama_laboratorium')->get();

        return view('laporan-kendala-komputer.create', compact('komputers', 'laboratoriums'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'komputer_id' => ['required', 'exists:komputer,id'],
            'nama_pelapor' => ['required', 'string', 'max:255'],
            'npm_nim' => ['required', 'string', 'max:50'],
            'nama_prodi' => ['nullable', 'string', 'max:255'],
            'kategori_kerusakan' => ['nullable', 'in:hardware,software,jaringan,lainnya'],
            'deskripsi_kendala' => ['required', 'string'],
            'kondisi' => ['required', 'in:ringan,berat'],
            'gambar' => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:2048'],
        ]);

        $komputer = Komputer::findOrFail($validated['komputer_id']);

        $statusKomputer = $validated['kondisi'] === 'berat' ? 'rusak' : 'perbaikan';

        $kodeTracker = 'LKD-' . date('Ymd') . '-' . strtoupper(Str::random(6));

        $data = [
            'komputer_id' => $komputer->id,
            'nama_pelapor' => $validated['nama_pelapor'],
            'npm_nim' => $validated['npm_nim'],
            'nama_prodi' => $validated['nama_prodi'] ?? null,
            'kategori_kerusakan' => $validated['kategori_kerusakan'] ?? null,
            'deskripsi_kendala' => $validated['deskripsi_kendala'],
            'status_kendala' => 'menunggu',
            'kode_tracker' => $kodeTracker,
            'tanggal_lapor' => now()->toDateString(),
        ];

        if ($request->hasFile('gambar')) {
            $data['gambar'] = $request->file('gambar')->store('laporan-kendala', 'public');
        }

        $laporan = LaporanKendalaKomputer::create($data);

        $komputer->update(['status' => $statusKomputer]);

        return redirect()->route('laporan-kendala-komputer.success', ['kode_tracker' => $laporan->kode_tracker])
            ->with('success', 'Laporan kendala berhasil dikirim. Admin akan segera menangani.');
    }

    public function success()
    {
        return view('laporan-kendala-komputer.success');
    }

    public function track(Request $request)
    {
        $laporan = null;
        $peminjaman = null;
        $error = null;
        $kodeTracker = $request->input('kode_tracker');

        if ($request->filled('kode_tracker')) {
            if (str_starts_with(strtoupper($kodeTracker), 'LKD-')) {
                $laporan = LaporanKendalaKomputer::with('komputer')->where('kode_tracker', $kodeTracker)->first();

                if (!$laporan) {
                    $error = 'Kode tracker laporan kendala tidak ditemukan. Periksa kembali kode Anda.';
                }
            } elseif (str_starts_with(strtoupper($kodeTracker), 'PKM-')) {
                $peminjaman = \App\Models\PeminjamanKomputer::with('komputer')->where('kode_tracker', $kodeTracker)->first();

                if (!$peminjaman) {
                    $error = 'Kode tracker peminjaman komputer tidak ditemukan. Periksa kembali kode Anda.';
                }
            } else {
                $error = 'Format kode tracker tidak valid. Gunakan prefix LKD- untuk laporan kendala atau PKM- untuk peminjaman komputer.';
            }
        }

        return view('laporan-kendala-komputer.track', compact('laporan', 'peminjaman', 'error', 'kodeTracker'));
    }

    public function index(Request $request)
    {
        $query = LaporanKendalaKomputer::with('komputer');

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('nama_pelapor', 'like', "%{$request->search}%")
                  ->orWhere('npm_nim', 'like', "%{$request->search}%")
                  ->orWhere('deskripsi_kendala', 'like', "%{$request->search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status_kendala', $request->status);
        }

        $laporan = $query->latest()->paginate(20)->withQueryString();

        return view('admin.laporan-kendala-komputer.index', compact('laporan'));
    }

    public function exportExcel(Request $request)
    {
        $query = LaporanKendalaKomputer::with('komputer');

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('nama_pelapor', 'like', "%{$request->search}%")
                  ->orWhere('npm_nim', 'like', "%{$request->search}%")
                  ->orWhere('deskripsi_kendala', 'like', "%{$request->search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status_kendala', $request->status);
        }

        $laporan = $query->latest()->get();

        return Excel::download(new LaporanKendalaKomputerExport($laporan), 'laporan-kendala-komputer-' . now()->format('Y-m-d') . '.xlsx');
    }

    public function show(LaporanKendalaKomputer $laporanKendalaKomputer)
    {
        $laporanKendalaKomputer->load('komputer');

        return view('admin.laporan-kendala-komputer.show', compact('laporanKendalaKomputer'));
    }

    public function updateStatus(Request $request, LaporanKendalaKomputer $laporanKendalaKomputer)
    {
        $validated = $request->validate([
            'status_kendala' => ['required', 'in:menunggu,diperbaiki,selesai'],
            'catatan_admin' => ['nullable', 'string'],
            'tanggal_perbaikan' => ['nullable', 'date'],
        ]);

        $laporanKendalaKomputer->update($validated);

        if ($validated['status_kendala'] === 'diperbaiki') {
            $laporanKendalaKomputer->komputer->update(['status' => 'perbaikan']);
        } elseif ($validated['status_kendala'] === 'selesai') {
            $laporanKendalaKomputer->komputer->update(['status' => 'aktif']);
        }

        return redirect()->back()->with('success', 'Status laporan berhasil diperbarui.');
    }
}
