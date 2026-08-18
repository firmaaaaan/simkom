<?php

namespace App\Http\Controllers;

use App\Models\LaporanKendalaKomputer;
use App\Models\PeminjamanKomputer;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $pendingLaporan = LaporanKendalaKomputer::where('status_kendala', 'menunggu')->count();
        $pendingPeminjaman = PeminjamanKomputer::where('status_peminjaman', 'menunggu')->count();

        return view('admin.dashboard', compact('pendingLaporan', 'pendingPeminjaman'));
    }

    public function notifications()
    {
        $pendingLaporan = LaporanKendalaKomputer::where('status_kendala', 'menunggu')->count();
        $pendingPeminjaman = PeminjamanKomputer::where('status_peminjaman', 'menunggu')->count();

        return response()->json([
            'pending_laporan' => $pendingLaporan,
            'pending_peminjaman' => $pendingPeminjaman,
            'total' => $pendingLaporan + $pendingPeminjaman,
        ]);
    }
}
