<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\Computer;
use App\Models\ComputerCheck;
use App\Models\DeviceCheck;
use App\Models\MaintenanceChecklist;
use Illuminate\Http\Request;

class PublicComputerCardController extends Controller
{
    /**
     * Kartu kendali versi publik (tanpa login).
     * Hanya menampilkan riwayat pengecekan, tanpa form dan tanpa nama item.
     * Bisa disaring per bulan/tahun (?month=9&year=2026) serta per tahun
     * ajaran per tab (?academic_year_id=, ?device_academic_year_id=,
     * ?maintenance_academic_year_id=).
     */
    public function show(Request $request, Computer $computer)
    {
        $computer->load(['laboratory', 'hardware']);

        $month = $request->integer('month') ?: null;
        $year = $request->integer('year') ?: null;

        // Filter tahun ajaran per tab; nilai asing (bukan id valid) akan
        // cocok dengan nol baris sehingga tampil empty state, bukan bocor.
        $academicYearId = $request->filled('academic_year_id') ? $request->string('academic_year_id') : null;
        $deviceAcademicYearId = $request->filled('device_academic_year_id') ? $request->string('device_academic_year_id') : null;
        $maintenanceAcademicYearId = $request->filled('maintenance_academic_year_id') ? $request->string('maintenance_academic_year_id') : null;

        $checks = ComputerCheck::where('computer_id', $computer->id)
            ->forPeriod($month, $year)
            ->when($academicYearId, fn ($query, $id) => $query->where('academic_year_id', $id))
            ->with(['checkedBy', 'academicYear', 'hardwareChecks'])
            ->latest()
            ->get();

        $years = ComputerCheck::availableYears($computer);

        $deviceChecks = DeviceCheck::forComputer($computer->id)
            ->when($deviceAcademicYearId, fn ($query, $id) => $query->where('academic_year_id', $id))
            ->get();
        $maintenances = MaintenanceChecklist::forComputer($computer->id)
            ->when($maintenanceAcademicYearId, fn ($query, $id) => $query->where('academic_year_id', $id))
            ->get();

        $academicYears = AcademicYear::orderByDesc('start_year')->get();

        return view('computers.public-card', compact(
            'computer',
            'checks',
            'years',
            'month',
            'year',
            'academicYears',
            'deviceChecks',
            'maintenances',
        ));
    }
}
