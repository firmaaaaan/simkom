<?php

namespace App\Http\Controllers;

use App\Models\PcMonitor;

class MonitoringPcController extends Controller
{
    public function index()
    {
        $pcs = PcMonitor::orderBy('hostname')->get();

        return view('monitoring-pc.index', [
            'pcs' => $pcs,
            'stats' => [
                'total' => $pcs->count(),
                'online' => $pcs->where('status', PcMonitor::STATUS_ONLINE)->count(),
                'offline' => $pcs->where('status', PcMonitor::STATUS_OFFLINE)->count(),
                'avg_cpu' => round($pcs->avg('cpu_usage') ?? 0, 1),
                'avg_ram' => round($pcs->avg('ram_usage') ?? 0, 1),
                'avg_disk' => round($pcs->avg('disk_usage') ?? 0, 1),
            ],
        ]);
    }
}
