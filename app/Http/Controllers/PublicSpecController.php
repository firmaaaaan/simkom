<?php

namespace App\Http\Controllers;

use App\Models\Laboratory;

class PublicSpecController extends Controller
{
    /**
     * Rincian spesifikasi komputer versi publik (tanpa login).
     * Satu halaman berisi seluruh laboratorium beserta hardware tiap
     * komputer, dikelompokkan per kategori. Dipakai sebagai tujuan QR
     * pada cetak laporan inventaris.
     */
    public function index()
    {
        $laboratories = Laboratory::with(['computers' => fn ($query) => $query->orderBy('code')->with('hardware')])
            ->orderBy('name')
            ->get();

        return view('public.spesifikasi', compact('laboratories'));
    }
}
