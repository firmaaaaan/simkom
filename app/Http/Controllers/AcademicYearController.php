<?php

namespace App\Http\Controllers;

use App\Exports\AcademicYearExport;
use App\Models\AcademicYear;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class AcademicYearController extends Controller
{
    /**
     * Query daftar tahun ajaran dengan filter yang sedang aktif — dipakai
     * bersama oleh halaman daftar dan export Excel.
     */
    private function filteredQuery(Request $request)
    {
        $query = AcademicYear::query();

        if ($search = $request->search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('status', 'like', "%{$search}%");
            });
        }

        return $query;
    }

    public function index(Request $request)
    {
        $academicYears = $this->filteredQuery($request)->latest()->paginate(10)->withQueryString();
        return view('academic-years.index', compact('academicYears'));
    }

    public function export(Request $request)
    {
        return Excel::download(
            new AcademicYearExport($this->filteredQuery($request)->latest()),
            'tahun-ajaran-' . now()->format('Y-m-d') . '.xlsx'
        );
    }

    public function create()
    {
        return view('academic-years.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'start_year' => 'required|integer|min:2000|max:2100',
            'end_year' => 'required|integer|min:2000|max:2100|gt:start_year',
            'status' => 'required|in:Aktif,Non Aktif',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);

        AcademicYear::create($validated);

        return redirect()->route('academic-years.index')->with('success', 'Tahun ajaran berhasil ditambahkan.');
    }

    public function show(AcademicYear $academicYear)
    {
        return view('academic-years.show', compact('academicYear'));
    }

    public function edit(AcademicYear $academicYear)
    {
        return view('academic-years.edit', compact('academicYear'));
    }

    public function update(Request $request, AcademicYear $academicYear)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'start_year' => 'required|integer|min:2000|max:2100',
            'end_year' => 'required|integer|min:2000|max:2100|gt:start_year',
            'status' => 'required|in:Aktif,Non Aktif',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);

        $academicYear->update($validated);

        return redirect()->route('academic-years.index')->with('success', 'Tahun ajaran berhasil diperbarui.');
    }

    public function destroy(AcademicYear $academicYear)
    {
        $academicYear->delete();

        return redirect()->route('academic-years.index')->with('success', 'Tahun ajaran berhasil dihapus.');
    }

    public function bulkDestroy(Request $request)
    {
        $request->validate([
            'ids' => 'required|array|min:1',
            'ids.*' => 'exists:academic_years,id',
        ]);

        AcademicYear::whereIn('id', $request->ids)->delete();

        return redirect()->route('academic-years.index')->with('success', count($request->ids) . ' tahun ajaran berhasil dihapus.');
    }
}
