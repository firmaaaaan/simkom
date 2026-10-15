<?php

namespace App\Http\Controllers;

use App\Models\PcMonitor;
use Illuminate\Http\Request;

class TelemetryController extends Controller
{
    /**
     * Simpan telemetri dari agent PC lab (POST /api/v1/telemetry).
     * Autentikasi sederhana: header X-API-KEY harus cocok dengan
     * MONITORING_API_KEY (config: services.monitoring.key).
     */
    public function store(Request $request)
    {
        $expected = (string) config('services.monitoring.key');
        $given = (string) $request->header('X-API-KEY');

        if ($expected === '' || $given === '' || ! hash_equals($expected, $given)) {
            abort(403, 'API key tidak valid.');
        }

        $data = $request->validate([
            'hostname' => ['required', 'string', 'max:255'],
            'ip_address' => ['required', 'string', 'max:45'],
            'mac_address' => ['nullable', 'string', 'max:64'],
            'cpu_usage' => ['required', 'numeric', 'min:0', 'max:100'],
            'ram_usage' => ['required', 'numeric', 'min:0', 'max:100'],
            'disk_usage' => ['required', 'numeric', 'min:0', 'max:100'],
            'active_user' => ['nullable', 'string', 'max:255'],
        ]);

        $pc = PcMonitor::updateOrCreate(
            ['hostname' => $data['hostname']],
            [
                ...$data,
                'status' => PcMonitor::STATUS_ONLINE,
                'last_seen_at' => now(),
            ]
        );

        return response()->json([
            'status' => 'ok',
            'hostname' => $pc->hostname,
            'last_seen_at' => $pc->last_seen_at->toIso8601String(),
        ]);
    }
}
