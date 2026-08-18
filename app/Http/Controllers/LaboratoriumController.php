<?php

namespace App\Http\Controllers;

use App\Models\Laboratorium;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class LaboratoriumController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Laboratorium::query();

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('nama_laboratorium', 'like', "%{$request->search}%")
                  ->orWhere('kode_laboratorium', 'like', "%{$request->search}%")
                  ->orWhere('gedung', 'like', "%{$request->search}%")
                  ->orWhere('ruangan', 'like', "%{$request->search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $laboratorium = $query->latest()->paginate(15)->withQueryString();

        return view('admin.laboratorium.index', compact('laboratorium'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.laboratorium.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'kode_laboratorium' => ['required', 'string', 'max:50', 'unique:laboratorium,kode_laboratorium'],
            'nama_laboratorium' => ['required', 'string', 'max:255'],
            'gedung' => ['required', 'string', 'max:255'],
            'ruangan' => ['required', 'string', 'max:255'],
            'kapasitas' => ['nullable', 'integer', 'min:0'],
            'fasilitas' => ['nullable', 'string'],
            'status' => ['required', Rule::in(['aktif', 'non-aktif'])],
            'catatan' => ['nullable', 'string'],
        ]);

        Laboratorium::create($validated);

        return redirect()->route('admin.laboratorium.index')->with('success', 'Data laboratorium berhasil ditambahkan.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Laboratorium $laboratorium)
    {
        return view('admin.laboratorium.show', compact('laboratorium'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Laboratorium $laboratorium)
    {
        return view('admin.laboratorium.edit', compact('laboratorium'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Laboratorium $laboratorium)
    {
        $validated = $request->validate([
            'kode_laboratorium' => ['required', 'string', 'max:50', Rule::unique('laboratorium', 'kode_laboratorium')->ignore($laboratorium->id)],
            'nama_laboratorium' => ['required', 'string', 'max:255'],
            'gedung' => ['required', 'string', 'max:255'],
            'ruangan' => ['required', 'string', 'max:255'],
            'kapasitas' => ['nullable', 'integer', 'min:0'],
            'fasilitas' => ['nullable', 'string'],
            'status' => ['required', Rule::in(['aktif', 'non-aktif'])],
            'catatan' => ['nullable', 'string'],
        ]);

        $laboratorium->update($validated);

        return redirect()->route('admin.laboratorium.index')->with('success', 'Data laboratorium berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Laboratorium $laboratorium)
    {
        $laboratorium->delete();

        return redirect()->route('admin.laboratorium.index')->with('success', 'Data laboratorium berhasil dihapus.');
    }
}
