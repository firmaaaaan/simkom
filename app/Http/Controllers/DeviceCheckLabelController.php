<?php

namespace App\Http\Controllers;

use App\Models\DeviceCheck;
use Illuminate\Http\Request;

class DeviceCheckLabelController extends Controller
{
    /**
     * Tampilkan dokumen berlabel kode perangkat (untuk cetak/PDF).
     *
     * Konten utama: daftar komputer dan status tiap item pengecekan,
     * header lab/tahun ajaran/tanggal/petugas, dan blok tanda tangan.
     * Dokumen ini dimaksudkan untuk dicetak/PDF dari halaman cetak yang
     * sama seperti label/kartu perangkat di modul peminjaman.
     */
    public function show(Request $request, DeviceCheck $deviceCheck)
    {
        return view('device-checks.label', [
            'deviceCheck' => $deviceCheck,
        ]);
    }
}
