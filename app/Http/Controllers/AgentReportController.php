<?php

namespace App\Http\Controllers;

use App\Models\PcMonitor;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class AgentReportController extends Controller
{
    private const SCREENSHOTS_DIR = 'pc-screenshots';

    /**
     * Terima screenshot layar dari agent (JPEG base64) — disimpan per
     * hostname (file lama ditimpa). Hostname harus sudah dikenal
     * (agent wajib lapor telemetry dulu).
     */
    public function screenshot(Request $request): JsonResponse
    {
        $data = $request->validate([
            'hostname' => 'required|string|max:255',
            'image_base64' => 'required|string|max:4000000',
        ]);

        $pc = PcMonitor::where('hostname', $data['hostname'])->first();
        if (! $pc) {
            return response()->json(['ok' => false, 'message' => 'Hostname tidak dikenal.'], 404);
        }

        $binary = base64_decode($data['image_base64'], true);
        if ($binary === false || strlen($binary) < 100) {
            return response()->json(['ok' => false, 'message' => 'Gambar tidak valid.'], 422);
        }

        // Validasi singkat magic bytes JPEG (FFD8) — tolak payload asing.
        if (substr($binary, 0, 2) !== "\xFF\xD8") {
            return response()->json(['ok' => false, 'message' => 'Format screenshot bukan JPEG.'], 422);
        }

        $safe = preg_replace('/[^A-Za-z0-9\-_.]/', '_', $pc->hostname);
        Storage::disk('local')->put(self::SCREENSHOTS_DIR.'/'.$safe.'.jpg', $binary);

        // Ubah updated_at agar cache-buster screenshot_url ikut berganti.
        $pc->touch();

        return response()->json(['ok' => true]);
    }

    /**
     * Terima inventaris software terinstall dari agent — replace semua
     * baris PC ini (snapshot terbaru menang).
     */
    public function software(Request $request): JsonResponse
    {
        $data = $request->validate([
            'hostname' => 'required|string|max:255',
            'items' => 'required|array|max:2000',
            'items.*.name' => 'required|string|max:300',
            'items.*.version' => 'nullable|string|max:100',
            'items.*.publisher' => 'nullable|string|max:300',
            'items.*.install_date' => 'nullable|string|max:50',
            'items.*.size_kb' => 'nullable|integer|min:0',
        ]);

        $pc = PcMonitor::where('hostname', $data['hostname'])->first();
        if (! $pc) {
            return response()->json(['ok' => false, 'message' => 'Hostname tidak dikenal.'], 404);
        }

        DB::transaction(function () use ($pc, $data) {
            $pc->softwares()->delete();

            foreach ($data['items'] as $item) {
                $pc->softwares()->create($item);
            }

            $pc->update(['software_synced_at' => now()]);
        });

        return response()->json([
            'ok' => true,
            'count' => count($data['items']),
        ]);
    }
}
