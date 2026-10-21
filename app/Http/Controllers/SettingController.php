<?php

namespace App\Http\Controllers;

use App\Models\Setting;

class SettingController extends Controller
{
    /**
     * Saklar tombol "Lihat Software" pada halaman publik.
     */
    public function togglePublicSpec()
    {
        $enabled = ! Setting::publicSpecEnabled();

        Setting::set(Setting::PUBLIC_SPEC_ID, $enabled ? '1' : '0');

        return back()->with('success', $enabled
            ? 'Tombol "Lihat Software" ditampilkan di halaman publik.'
            : 'Tombol "Lihat Software" disembunyikan dari halaman publik.');
    }
}
