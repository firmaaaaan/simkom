<?php

namespace App\Http\Controllers;

use App\Models\KomponenIotJaringan;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class KomponenIotJaringanController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    protected function getNextKomponenKode(string $prefix): string
    {
        $maxKode = KomponenIotJaringan::where('kode_komponen', 'like', $prefix . '%')->max('kode_komponen');
        $number = $maxKode ? (int) str_replace($prefix . '-', '', $maxKode) + 1 : 1;

        return $prefix . '-' . str_pad($number, 3, '0', STR_PAD_LEFT);
    }

    public function index(Request $request)
    {
        $query = KomponenIotJaringan::query();

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('nama_komponen', 'like', "%{$request->search}%")
                  ->orWhere('kode_komponen', 'like', "%{$request->search}%")
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

        $komponen = $query->latest()->get();

        return view('admin.komponen-iot-jaringan.index', compact('komponen'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $kode_komponen = $this->getNextKomponenKode('IOT');

        return view('admin.komponen-iot-jaringan.create', compact('kode_komponen'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_komponen' => ['required', 'string', 'max:255'],
            'kategori' => ['required', 'string', 'max:100'],
            'jenis' => ['required', Rule::in(['Satuan', 'Paket', 'Sistem', 'Box'])],
            'merek' => ['nullable', 'string', 'max:100'],
            'spesifikasi' => ['nullable', 'string'],
            'jumlah' => ['required', 'integer', 'min:1'],
            'lokasi' => ['nullable', 'string', 'max:255'],
            'status' => ['required', Rule::in(['tersedia', 'dipinjam', 'perbaikan', 'tidak_aktif'])],
            'catatan' => ['nullable', 'string'],
        ]);

        if ($validated['jenis'] === 'Satuan') {
            $validated['jumlah'] = 1;
        }

        $validated['kode_komponen'] = $this->getNextKomponenKode(
            $validated['kategori'] === 'Jaringan' ? 'JAR' : 'IOT'
        );

        KomponenIotJaringan::create($validated);

        return redirect()->route('admin.komponen-iot-jaringan.index')->with('success', 'Data komponen IoT & Jaringan berhasil ditambahkan.');
    }

    /**
     * Display the specified resource.
     */
    public function show(KomponenIotJaringan $komponenIotJaringan)
    {
        return view('admin.komponen-iot-jaringan.show', compact('komponenIotJaringan'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(KomponenIotJaringan $komponenIotJaringan)
    {
        return view('admin.komponen-iot-jaringan.edit', compact('komponenIotJaringan'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, KomponenIotJaringan $komponenIotJaringan)
    {
        $validated = $request->validate([
            'nama_komponen' => ['required', 'string', 'max:255'],
            'kategori' => ['required', 'string', 'max:100'],
            'jenis' => ['required', Rule::in(['Satuan', 'Paket', 'Sistem', 'Box'])],
            'merek' => ['nullable', 'string', 'max:100'],
            'spesifikasi' => ['nullable', 'string'],
            'jumlah' => ['required', 'integer', 'min:1'],
            'lokasi' => ['nullable', 'string', 'max:255'],
            'status' => ['required', Rule::in(['tersedia', 'dipinjam', 'perbaikan', 'tidak_aktif'])],
            'catatan' => ['nullable', 'string'],
        ]);

        if ($validated['jenis'] === 'Satuan') {
            $validated['jumlah'] = 1;
        }

        $komponenIotJaringan->update($validated);

        return redirect()->route('admin.komponen-iot-jaringan.index')->with('success', 'Data komponen IoT & Jaringan berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(KomponenIotJaringan $komponenIotJaringan)
    {
        $komponenIotJaringan->delete();

        return redirect()->route('admin.komponen-iot-jaringan.index')->with('success', 'Data komponen IoT & Jaringan berhasil dihapus.');
    }
}
