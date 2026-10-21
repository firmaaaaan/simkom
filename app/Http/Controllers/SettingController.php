<?php

namespace App\Http\Controllers;

use App\Models\PcSoftware;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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

    /**
     * Halaman pengaturan whitelist software yang tampil di halaman publik.
     */
    public function editPublicSoftware()
    {
        $whitelist = collect(Setting::publicSoftwareWhitelist());
        $whitelistLower = $whitelist->map(fn ($name) => strtolower($name));

        $detected = PcSoftware::query()
            ->select('name')
            ->distinct()
            ->orderBy('name')
            ->pluck('name')
            ->mapWithKeys(fn ($name) => [strtolower($name) => $name]);

        $names = $detected
            ->merge($whitelist->mapWithKeys(fn ($name) => [strtolower($name) => $name]))
            ->sort()
            ->values();

        $pcCounts = PcSoftware::query()
            ->select('name', DB::raw('COUNT(DISTINCT pc_monitor_id) as pc_count'))
            ->groupBy('name')
            ->pluck('pc_count', 'name');

        return view('settings.public-software', compact('names', 'whitelistLower', 'pcCounts'));
    }

    /**
     * Simpan whitelist software publik.
     */
    public function updatePublicSoftware(Request $request)
    {
        $request->validate([
            'names' => 'nullable|array',
            'names.*' => 'string|max:300',
            'manual_names' => 'nullable|string',
        ]);

        $names = collect($request->input('names', []))
            ->merge(collect(preg_split('/\r\n|\r|\n/', (string) $request->input('manual_names'))))
            ->map(fn ($name) => trim((string) $name))
            ->filter()
            ->unique(fn ($name) => strtolower($name))
            ->values()
            ->all();

        Setting::setPublicSoftwareWhitelist($names);

        return redirect()
            ->route('settings.public-software.edit')
            ->with('success', 'Filter software publik berhasil disimpan.');
    }
}
