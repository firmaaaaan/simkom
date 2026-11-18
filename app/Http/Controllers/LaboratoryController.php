<?php

namespace App\Http\Controllers;

use App\Exports\LaboratoryExport;
use App\Models\Laboratory;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Facades\Excel;

class LaboratoryController extends Controller
{
    /**
     * Query daftar laboratorium dengan filter yang sedang aktif — dipakai
     * bersama oleh halaman daftar dan export Excel.
     */
    private function filteredQuery(Request $request)
    {
        $query = Laboratory::query();

        if ($search = $request->search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%")
                    ->orWhere('location', 'like', "%{$search}%");
            });
        }

        return $query;
    }

    public function index(Request $request)
    {
        $laboratories = $this->filteredQuery($request)->latest()->paginate(10)->withQueryString();

        $allCount = Laboratory::query()->count();
        $activeCount = Laboratory::query()->where('status', 'Aktif')->count();

        return view('laboratories.index', [
            'laboratories' => $laboratories,
            'allCount' => $allCount,
            'allActive' => $allCount > 0 && $activeCount === $allCount,
        ]);
    }

    public function export(Request $request)
    {
        return Excel::download(
            new LaboratoryExport($this->filteredQuery($request)->latest()),
            'laboratorium-'.now()->format('Y-m-d').'.xlsx'
        );
    }

    public function create()
    {
        return view('laboratories.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:laboratories,code',
            'location' => 'required|string|max:255',
            'capacity' => 'required|integer|min:0',
            'temp_min' => 'nullable|numeric|between:0,100',
            'temp_max' => 'nullable|numeric|between:0,100',
            'status' => 'required|in:Aktif,Tidak Aktif,Maintenance',
            'show_in_computer_list' => 'nullable|boolean',
            'description' => 'nullable|string',
        ]);

        $this->validateTemperatureRange($validated);

        Laboratory::create($validated);

        if ($request->input('action') === 'save_another') {
            return redirect()->route('laboratories.create')
                ->with('success', 'Laboratorium berhasil ditambahkan. Silakan isi form untuk data berikutnya.');
        }

        return redirect()->route('laboratories.index')->with('success', 'Laboratorium berhasil ditambahkan.');
    }

    public function show(Laboratory $laboratory)
    {
        return view('laboratories.show', compact('laboratory'));
    }

    public function edit(Laboratory $laboratory)
    {
        return view('laboratories.edit', compact('laboratory'));
    }

    public function update(Request $request, Laboratory $laboratory)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:laboratories,code,'.$laboratory->id,
            'location' => 'required|string|max:255',
            'capacity' => 'required|integer|min:0',
            'temp_min' => 'nullable|numeric|between:0,100',
            'temp_max' => 'nullable|numeric|between:0,100',
            'status' => 'required|in:Aktif,Tidak Aktif,Maintenance',
            'show_in_computer_list' => 'nullable|boolean',
            'description' => 'nullable|string',
        ]);

        $this->validateTemperatureRange($validated);

        $laboratory->update($validated);

        return redirect()->route('laboratories.index')->with('success', 'Laboratorium berhasil diperbarui.');
    }

    /**
     * Ambang suhu opsional, tapi bila keduanya diisi maka min harus < max.
     */
    private function validateTemperatureRange(array $data): void
    {
        $min = $data['temp_min'] ?? null;
        $max = $data['temp_max'] ?? null;

        if ($min !== null && $max !== null && (float) $min >= (float) $max) {
            throw ValidationException::withMessages([
                'temp_max' => 'Suhu maksimal harus lebih besar dari suhu minimal.',
            ]);
        }
    }

    public function destroy(Laboratory $laboratory)
    {
        $laboratory->delete();

        return redirect()->route('laboratories.index')->with('success', 'Laboratorium berhasil dihapus.');
    }

    public function bulkDestroy(Request $request)
    {
        $request->validate([
            'ids' => 'required|array|min:1',
            'ids.*' => 'exists:laboratories,id',
        ]);

        Laboratory::whereIn('id', $request->ids)->delete();

        return redirect()->route('laboratories.index')->with('success', count($request->ids).' laboratorium berhasil dihapus.');
    }

    /**
     * Ubah status banyak laboratorium sekaligus — scope "selected" untuk
     * baris yang dicentang, scope "all" untuk seluruh data laboratorium.
     */
    public function bulkStatus(Request $request)
    {
        $request->validate([
            'status' => 'required|in:Aktif,Tidak Aktif,Maintenance',
            'scope' => 'required|in:selected,all',
            'ids' => 'nullable|array',
            'ids.*' => 'exists:laboratories,id',
        ], [
            'status.in' => 'Status laboratorium tidak valid.',
        ]);

        $ids = array_values(array_filter((array) $request->input('ids', [])));

        if ($request->input('scope') === 'selected' && $ids === []) {
            return back()->with('error', 'Pilih minimal satu laboratorium untuk diubah statusnya.');
        }

        $count = $request->input('scope') === 'all'
            ? Laboratory::query()->update(['status' => $request->input('status')])
            : Laboratory::whereIn('id', $ids)->update(['status' => $request->input('status')]);

        $message = $request->input('scope') === 'all'
            ? 'Seluruh '.$count.' laboratorium diubah statusnya menjadi '.$request->input('status').'.'
            : $count.' laboratorium terpilih diubah statusnya menjadi '.$request->input('status').'.';

        return back()->with('success', $message);
    }
}
