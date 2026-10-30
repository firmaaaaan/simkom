<?php

namespace App\Http\Controllers;

use App\Models\Laboratory;

class PublicSpecController extends Controller
{
    /**
     * Rincian spesifikasi komputer satu laboratorium (tanpa login).
     * Halaman khusus per lab — dibuka lewat QR pada cetak laporan
     * inventaris, tanpa filter apa pun di dalamnya.
     */
    public function show(Laboratory $laboratory)
    {
        $laboratory->load(['computers' => fn ($query) => $query->orderBy('code')->with('hardware')]);

        return view('public.spesifikasi', compact('laboratory'));
    }
}
