<?php

namespace App\Http\Controllers;

use App\Models\TahunAjaran;
use Illuminate\Http\Request;

class TahunAjaranController extends Controller
{
    public function index()
    {
        $tahunAjaran = TahunAjaran::orderBy('nama', 'desc')->get();

        return view('admin.tahun-ajaran.index', compact('tahunAjaran'));
    }

    public function create()
    {
        return view('admin.tahun-ajaran.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama' => ['required', 'string', 'max:20', 'unique:tahun_ajaran,nama'],
            'status' => ['required', 'in:aktif,non-aktif'],
        ]);

        TahunAjaran::create($validated);

        return redirect()->route('admin.tahun-ajaran.index')->with('success', 'Data tahun ajaran berhasil ditambahkan.');
    }

    public function edit(TahunAjaran $tahun_ajaran)
    {
        return view('admin.tahun-ajaran.edit', compact('tahun_ajaran'));
    }

    public function update(Request $request, TahunAjaran $tahun_ajaran)
    {
        $validated = $request->validate([
            'nama' => ['required', 'string', 'max:20', 'unique:tahun_ajaran,nama,' . $tahun_ajaran->id],
            'status' => ['required', 'in:aktif,non-aktif'],
        ]);

        $tahun_ajaran->update($validated);

        return redirect()->route('admin.tahun-ajaran.index')->with('success', 'Data tahun ajaran berhasil diperbarui.');
    }

    public function destroy(TahunAjaran $tahun_ajaran)
    {
        $tahun_ajaran->delete();

        return redirect()->route('admin.tahun-ajaran.index')->with('success', 'Data tahun ajaran berhasil dihapus.');
    }
}
