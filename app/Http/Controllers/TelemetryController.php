<?php

namespace App\Http\Controllers;

use App\Models\PcMonitor;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TelemetryController extends Controller
{
    /**
     * Terima laporan telemetri dari agent (dilindungi header X-API-KEY).
     * Satu baris per hostname — terakhir menang, status disegarkan ke online.
     */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'hostname' => 'required|string|max:255',
            'ip_address' => 'required|string|max:45',
            'mac_address' => 'nullable|string|max:32',
            'cpu_usage' => 'required|numeric|min:0|max:100',
            'ram_usage' => 'required|numeric|min:0|max:100',
            'disk_usage' => 'required|numeric|min:0|max:100',
            'active_user' => 'nullable|string|max:255',
        ]);

        PcMonitor::updateOrCreate(
            ['hostname' => $data['hostname']],
            [
                ...$data,
                'status' => PcMonitor::STATUS_ONLINE,
                'last_seen_at' => now(),
            ]
        );

        return response()->json(['ok' => true]);
    }
}
