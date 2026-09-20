<?php

namespace App\Http\Controllers;

use App\Models\Computer;
use App\Models\ComputerCheck;
use Illuminate\Http\Request;

class PublicComputerCardController extends Controller
{
    /**
     * Kartu kendali versi publik (tanpa login).
     * Hanya menampilkan riwayat pengecekan, tanpa form dan tanpa nama item.
     * Bisa disaring per bulan/tahun lewat query string (?month=9&year=2026).
     */
    public function show(Request $request, Computer $computer)
    {
        $computer->load('laboratory');

        $month = $request->integer('month') ?: null;
        $year = $request->integer('year') ?: null;

        $checks = ComputerCheck::where('computer_id', $computer->id)
            ->forPeriod($month, $year)
            ->with(['checkedBy', 'academicYear', 'hardwareChecks'])
            ->latest()
            ->get();

        $years = ComputerCheck::availableYears($computer);

        return view('computers.public-card', compact('computer', 'checks', 'years', 'month', 'year'));
    }
}
