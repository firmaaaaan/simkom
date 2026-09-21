<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    /**
     * Saklar tombol "Lihat Spesifikasi" pada halaman publik.
     */
    public function togglePublicSpec()
    {
        $enabled = ! Setting::publicSpecEnabled();

        Setting::set(Setting::PUBLIC_SPEC_ID, $enabled ? '1' : '0');

        return back()->with('success', $enabled
            ? 'Tombol "Lihat Spesifikasi" ditampilkan di halaman publik.'
            : 'Tombol "Lihat Spesifikasi" disembunyikan dari halaman publik.');
    }

    /**
     * URL jadwal real-time yang ditautkan dari halaman jadwal publik.
     * Kosongkan untuk menyembunyikan tautan.
     */
    public function updateRealtimeScheduleUrl(Request $request)
    {
        $validated = $request->validate([
            'realtime_url' => 'nullable|url|max:500',
        ], [
            'realtime_url.url' => 'URL jadwal real-time tidak valid (harus diawali https:// atau http://).',
        ]);

        $url = $validated['realtime_url'] ?? null;
        Setting::set(Setting::REALTIME_SCHEDULE_URL_ID, $url);

        return back()->with('success', $url
            ? 'Tautan jadwal real-time disimpan dan tampil di halaman jadwal publik.'
            : 'Tautan jadwal real-time dihapus dari halaman jadwal publik.');
    }
}
