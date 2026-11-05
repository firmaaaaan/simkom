<?php

namespace App\Http\Controllers;

use App\Models\OccupationalDiseaseInvestigation;
use Illuminate\Http\Request;

class OccupationalDiseaseInvestigationController extends Controller
{
    public function index(Request $request)
    {
        $investigations = OccupationalDiseaseInvestigation::query()
            ->search($request->string('search')->toString())
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('occupational-disease-investigations.index', compact('investigations'));
    }

    public function create()
    {
        return view('occupational-disease-investigations.create');
    }

    public function store(Request $request)
    {
        $validated = $this->validateInvestigation($request);
        $validated['created_by'] = auth()->id();

        OccupationalDiseaseInvestigation::create($validated);

        if ($request->input('action') === 'save_another') {
            return redirect()->route('occupational-disease-investigations.create')
                ->with('success', 'Penyelidikan PAK berhasil disimpan.');
        }

        return redirect()->route('occupational-disease-investigations.index')
            ->with('success', 'Penyelidikan PAK berhasil disimpan.');
    }

    public function show(OccupationalDiseaseInvestigation $occupationalDiseaseInvestigation)
    {
        $occupationalDiseaseInvestigation->load('creator');

        return view('occupational-disease-investigations.show', compact('occupationalDiseaseInvestigation'));
    }

    public function edit(OccupationalDiseaseInvestigation $occupationalDiseaseInvestigation)
    {
        return view('occupational-disease-investigations.edit', compact('occupationalDiseaseInvestigation'));
    }

    public function update(Request $request, OccupationalDiseaseInvestigation $occupationalDiseaseInvestigation)
    {
        $validated = $this->validateInvestigation($request);
        $occupationalDiseaseInvestigation->update($validated);

        return redirect()->route('occupational-disease-investigations.index')
            ->with('success', 'Penyelidikan PAK berhasil diperbarui.');
    }

    public function destroy(OccupationalDiseaseInvestigation $occupationalDiseaseInvestigation)
    {
        $occupationalDiseaseInvestigation->delete();

        return redirect()->route('occupational-disease-investigations.index')
            ->with('success', 'Penyelidikan PAK berhasil dihapus.');
    }

    public function print(OccupationalDiseaseInvestigation $occupationalDiseaseInvestigation)
    {
        return view('occupational-disease-investigations.print', compact('occupationalDiseaseInvestigation'));
    }

    private function validateInvestigation(Request $request): array
    {
        return $request->validate([
            'nama' => 'required|string|max:255',
            'nip' => 'nullable|string|max:100',
            'bagian' => 'nullable|string|max:255',
            'jabatan' => 'nullable|string|max:255',
            'jenis_kelamin' => 'nullable|in:Laki-laki,Perempuan',
            'lama_bekerja' => 'nullable|string|max:100',
            'keluhan' => 'nullable|string|max:5000',
            'riwayat_penyakit' => 'nullable|string|max:5000',
            'riwayat_penyakit_keluarga' => 'nullable|string|max:5000',
            'riwayat_pekerjaan' => 'nullable|string|max:5000',
            'pemeriksaan_fisik' => 'nullable|string|max:5000',
            'pemeriksaan_radiologi' => 'nullable|string|max:5000',
            'pemeriksaan_laboratorium' => 'nullable|string|max:5000',
            'pemeriksaan_penunjang' => 'nullable|string|max:5000',
            'pemeriksaan_patologi_anatomi' => 'nullable|string|max:5000',
            'perbandingan_pemeriksaan_awal' => 'nullable|string|max:5000',
            'perbandingan_pemeriksaan_berkala' => 'nullable|string|max:5000',
            'tanggal_pemeriksaan' => 'nullable|date',
            'diperiksa_oleh' => 'nullable|string|max:255',
        ], [
            'nama.required' => 'Nama wajib diisi.',
        ]);
    }
}
