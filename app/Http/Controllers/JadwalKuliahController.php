<?php

namespace App\Http\Controllers;

use App\Models\JadwalSetting;
use App\Models\Laboratorium;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;

class JadwalKuliahController extends Controller
{
    public function index()
    {
        $laboratoriums = Laboratorium::orderBy('nama_laboratorium')->get();

        return view('jadwal-kuliah.index', compact('laboratoriums'));
    }

    public function adminIndex()
    {
        $settings = JadwalSetting::all();

        return view('admin.jadwal-kuliah.settings', compact('settings'));
    }

    public function adminStore(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'api_url' => ['required', 'url'],
            'api_token' => ['nullable', 'string'],
            'tipe' => ['required', 'in:kuliah,non_kuliah'],
            'refresh_interval' => ['required', 'integer', 'min:5'],
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        JadwalSetting::create([
            'api_url' => $request->api_url,
            'api_token' => $request->api_token,
            'tipe' => $request->tipe,
            'refresh_interval' => $request->refresh_interval,
            'is_active' => true,
        ]);

        return redirect()->back()->with('success', 'Pengaturan API jadwal berhasil disimpan.');
    }

    public function adminDestroy($id)
    {
        $setting = JadwalSetting::findOrFail($id);
        $setting->delete();

        return redirect()->back()->with('success', 'Pengaturan API jadwal berhasil dihapus.');
    }

    public function fetchJadwal(Request $request)
    {
        $setting = JadwalSetting::where('is_active', true)->first();

        if (!$setting) {
            return response()->json(['error' => 'Tidak ada pengaturan API yang aktif'], 404);
        }

        try {
            $response = Http::timeout(30)
                ->withHeaders($setting->api_token ? ['Authorization' => 'Bearer ' . $setting->api_token] : [])
                ->get($setting->api_url, $request->only(['laboratorium_id']));

            if ($response->successful()) {
                $setting->update(['last_sync' => now()]);

                return response()->json($response->json());
            }

            return response()->json(['error' => 'Gagal mengambil data dari API'], $response->status());
        } catch (\Exception $e) {
            return response()->json(['error' => 'Terjadi kesalahan: ' . $e->getMessage()], 500);
        }
    }
}
