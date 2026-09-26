<?php

namespace App\Http\Controllers;

use App\Models\LabUsage;
use App\Models\Laboratory;
use Illuminate\Support\Collection;

class LabScanController extends Controller
{
    private const DAYS = [
        'Sunday'    => 'Minggu',
        'Monday'    => 'Senin',
        'Tuesday'   => 'Selasa',
        'Wednesday' => 'Rabu',
        'Thursday'  => 'Kamis',
        'Friday'    => 'Jumat',
        'Saturday'  => 'Sabtu',
    ];

    private const MONTHS = [
        1  => 'Januari',
        2  => 'Februari',
        3  => 'Maret',
        4  => 'April',
        5  => 'Mei',
        6  => 'Juni',
        7  => 'Juli',
        8  => 'Agustus',
        9  => 'September',
        10 => 'Oktober',
        11 => 'November',
        12 => 'Desember',
    ];

    /**
     * Halaman check-in umum: pengguna memilih laboratorium via dropdown (?lab=KODE).
     * Kode tidak dikenal dibiarkan jatuh ke kondisi "belum memilih" (tanpa 404).
     */
    public function pick()
    {
        $laboratories = Laboratory::orderBy('name')->get(['id', 'name', 'code', 'location']);

        $code = request()->query('lab');
        $lab = $code ? $laboratories->firstWhere('code', $code) : null;

        return $this->render($lab, $laboratories);
    }

    public function scan($labCode)
    {
        $lab = Laboratory::where('code', $labCode)->firstOrFail();

        return $this->render($lab);
    }

    private function render(?Laboratory $lab, ?Collection $laboratories = null)
    {
        $now = now();

        return view('lab-scan.scan', [
            'lab'           => $lab,
            'laboratories'  => $laboratories ?? Laboratory::orderBy('name')->get(['id', 'name', 'code', 'location']),
            'day'           => self::DAYS[$now->format('l')] ?? $now->format('l'),
            'date'          => $now->format('j') . ' ' . (self::MONTHS[(int) $now->format('n')] ?? $now->format('F')) . ' ' . $now->format('Y'),
            'time'          => $now->format('H:i'),
        ]);
    }

    public function checkIn($labCode)
    {
        $lab = Laboratory::where('code', $labCode)->firstOrFail();

        $validated = request()->validate([
            'user_name' => 'required|string|max:255',
            'user_prodi' => 'required|string|max:255',
            'purpose'   => 'required|string|max:255',
        ], [
            'user_name.required'  => 'Nama lengkap wajib diisi.',
            'user_prodi.required' => 'Prodi wajib diisi.',
            'purpose.required'    => 'Keperluan wajib diisi.',
        ]);

        $duplicate = LabUsage::where('laboratory_id', $lab->id)
            ->where('status', 'In')
            ->get()
            ->first(fn ($usage) => strcasecmp(trim($usage->user_name), trim($validated['user_name'])) === 0
                && strcasecmp(trim($usage->user_prodi), trim($validated['user_prodi'])) === 0);

        if ($duplicate) {
            return redirect()
                ->route('lab-scan.scan', $lab->code)
                ->withErrors(['duplicate' => 'Anda sudah check-in di laboratorium ini dan belum divalidasi keluar oleh admin.']);
        }

        $now = now();

        LabUsage::create([
            'laboratory_id' => $lab->id,
            'user_name'     => $validated['user_name'],
            'user_prodi'    => $validated['user_prodi'],
            'purpose'       => $validated['purpose'],
            'day'           => self::DAYS[$now->format('l')] ?? $now->format('l'),
            'status'        => 'In',
            'checked_in_at' => $now,
        ]);

        return redirect()
            ->route('lab-scan.scan', $lab->code)
            ->with('success', 'Check-in berhasil. Menunggu validasi keluar oleh admin.');
    }
}
