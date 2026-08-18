<?php

namespace App\Http\Controllers;

use App\Models\InventarisIoTJaringan;
use App\Models\PeminjamanInventarisIoTJaringan;
use App\Exports\PeminjamanInventarisIoTJaringanExport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;

class PeminjamanInventarisIoTJaringanController extends Controller
{
    public function index(Request $request)
    {
        $query = PeminjamanInventarisIoTJaringan::with('inventaris', 'user');

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('nama_peminjam', 'like', "%{$request->search}%")
                  ->orWhere('npm_nim', 'like', "%{$request->search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $peminjaman = $query->latest()->paginate(20)->withQueryString();

        return view('admin.peminjaman-inventaris-iot-jaringan.index', compact('peminjaman'));
    }

    public function exportExcel(Request $request)
    {
        $query = PeminjamanInventarisIoTJaringan::with('inventaris', 'user');

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('nama_peminjam', 'like', "%{$request->search}%")
                  ->orWhere('npm_nim', 'like', "%{$request->search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $peminjaman = $query->latest()->get();

        return Excel::download(new PeminjamanInventarisIoTJaringanExport($peminjaman), 'peminjaman-iot-jaringan-' . now()->format('Y-m-d') . '.xlsx');
    }

    public function create(InventarisIoTJaringan $inventaris_iot_jaringan)
    {
        if ($inventaris_iot_jaringan->status_ketersediaan !== 'tersedia') {
            return redirect()->back()->with('error', 'Barang sedang tidak tersedia untuk dipinjam.');
        }

        return view('inventaris-iot-jaringan.peminjaman', compact('inventaris_iot_jaringan'));
    }

    public function store(Request $request, InventarisIoTJaringan $inventaris_iot_jaringan)
    {
        if ($inventaris_iot_jaringan->status_ketersediaan !== 'tersedia') {
            return redirect()->back()->with('error', 'Barang sedang tidak tersedia untuk dipinjam.');
        }

        $validated = $request->validate([
            'nama_peminjam' => ['required', 'string', 'max:255'],
            'npm_nim' => ['required', 'string', 'max:50'],
            'tanggal_pinjam' => ['required', 'date', 'after_or_equal:today'],
            'tanggal_kembali_direncanakan' => ['required', 'date', 'after_or_equal:tanggal_pinjam'],
            'catatan' => ['nullable', 'string'],
        ]);

        $peminjaman = PeminjamanInventarisIoTJaringan::create([
            'inventaris_iot_jaringan_id' => $inventaris_iot_jaringan->id,
            'nama_peminjam' => $validated['nama_peminjam'],
            'npm_nim' => $validated['npm_nim'],
            'tanggal_pinjam' => $validated['tanggal_pinjam'],
            'tanggal_kembali_direncanakan' => $validated['tanggal_kembali_direncanakan'],
            'catatan' => $validated['catatan'] ?? null,
            'status' => 'dipinjam',
        ]);

        $inventaris_iot_jaringan->update(['status_ketersediaan' => 'dipinjam']);

        session()->flash('peminjaman_data', [
            'nama_peminjam' => $validated['nama_peminjam'],
            'npm_nim' => $validated['npm_nim'],
            'tanggal_pinjam' => $validated['tanggal_pinjam'],
            'tanggal_kembali_direncanakan' => $validated['tanggal_kembali_direncanakan'],
            'catatan' => $validated['catatan'] ?? null,
        ]);

        return redirect()->route('inventaris-iot-jaringan.peminjaman.success', $inventaris_iot_jaringan)
            ->with('success', 'Peminjaman berhasil dicatat. Silakan tunjukkan bukti peminjaman kepada admin.');
    }

    public function success(InventarisIoTJaringan $inventaris_iot_jaringan)
    {
        return view('inventaris-iot-jaringan.peminjaman-success', compact('inventaris_iot_jaringan'));
    }

    public function returnItem(PeminjamanInventarisIoTJaringan $peminjaman)
    {
        $peminjaman->update([
            'tanggal_kembali_aktual' => now()->toDateString(),
            'status' => 'dikembalikan',
        ]);

        $peminjaman->inventaris->update(['status_ketersediaan' => 'tersedia']);

        return redirect()->back()->with('success', 'Barang berhasil dikembalikan.');
    }
}
