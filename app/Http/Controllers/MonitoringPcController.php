<?php

namespace App\Http\Controllers;

use App\Models\PcMonitor;
use App\Models\PcPowerCommand;
use App\Services\WakeOnLanService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use InvalidArgumentException;
use RuntimeException;

class MonitoringPcController extends Controller
{
    /**
     * Halaman dashboard monitoring PC.
     */
    public function index(Request $request)
    {
        return view('monitoring-pc.index', [
            'payload' => $this->payload($request),
        ]);
    }

    /**
     * Data JSON untuk polling refresh tiap 5 detik (tanpa reload halaman).
     */
    public function data(Request $request): JsonResponse
    {
        return response()->json($this->payload($request));
    }

    /**
     * Nyalakan PC via Wake-on-Lan (magic packet ke MAC address).
     */
    public function powerOn(Request $request, PcMonitor $pc): JsonResponse
    {
        if ($this->effectiveStatus($pc) === PcMonitor::STATUS_ONLINE) {
            return response()->json([
                'ok' => false,
                'message' => "{$pc->hostname} masih online — tidak perlu dinyalakan.",
            ], 409);
        }

        if (! $pc->mac_address) {
            return response()->json([
                'ok' => false,
                'message' => "MAC address {$pc->hostname} belum diketahui. Jalankan agent dulu.",
            ], 409);
        }

        try {
            app(WakeOnLanService::class)->send($pc->mac_address, $pc->ip_address);
        } catch (InvalidArgumentException|RuntimeException $e) {
            return response()->json([
                'ok' => false,
                'message' => $e->getMessage(),
            ], 500);
        }

        return response()->json([
            'ok' => true,
            'message' => "Paket Wake-on-LAN dikirim ke {$pc->hostname} ({$pc->mac_address}). Pastikan BIOS/NIC mengaktifkan Wake-on-LAN.",
        ]);
    }

    /**
     * Matikan PC: buat perintah pending, agent di PC mengeksekusi shutdown.
     */
    public function powerOff(Request $request, PcMonitor $pc): JsonResponse
    {
        if ($this->effectiveStatus($pc) !== PcMonitor::STATUS_ONLINE) {
            return response()->json([
                'ok' => false,
                'message' => "{$pc->hostname} sedang offline.",
            ], 409);
        }

        $hasPending = $pc->powerCommands()->pending()
            ->where('created_at', '>=', now()->subSeconds(PcPowerCommand::TTL_SECONDS))
            ->exists();

        if ($hasPending) {
            return response()->json([
                'ok' => false,
                'message' => "Perintah mematikan {$pc->hostname} masih menunggu di agent.",
            ], 409);
        }

        PcPowerCommand::create([
            'pc_monitor_id' => $pc->id,
            'action' => PcPowerCommand::ACTION_SHUTDOWN,
            'status' => PcPowerCommand::STATUS_PENDING,
            'requested_by' => $request->user()?->id,
        ]);

        return response()->json([
            'ok' => true,
            'message' => "Perintah mematikan {$pc->hostname} dikirim — PC mati dalam ~15 detik bila agent berjalan.",
        ]);
    }

    /**
     * Status & statistik untuk halaman/polling.
     *
     * Status efektif diturunkan langsung dari last_seen_at (>30 detik =
     * offline) sehingga akurat walau command monitoring:check-offline
     * belum berjalan; kolom status tetap disinkronkan oleh command tsb.
     */
    private function payload(Request $request): array
    {
        $pcs = PcMonitor::orderBy('hostname')->get();

        $items = $pcs->map(fn (PcMonitor $pc) => [
            'id' => $pc->id,
            'hostname' => $pc->hostname,
            'ip_address' => $pc->ip_address,
            'mac_address' => $pc->mac_address,
            'cpu_usage' => round($pc->cpu_usage, 1),
            'ram_usage' => round($pc->ram_usage, 1),
            'disk_usage' => round($pc->disk_usage, 1),
            'active_user' => $pc->active_user,
            'status' => $this->effectiveStatus($pc),
            'last_seen_at' => $pc->last_seen_at?->toIso8601String(),
            'seconds_since_last_seen' => $pc->secondsSinceLastSeen(),
            'pending_power' => $pc->powerCommands()->pending()
                ->where('created_at', '>=', now()->subSeconds(PcPowerCommand::TTL_SECONDS))
                ->exists(),
        ])->values();

        $statuses = $items->pluck('status');

        return [
            'stats' => [
                'total' => $items->count(),
                'online' => $statuses->filter(fn ($s) => $s === PcMonitor::STATUS_ONLINE)->count(),
                'offline' => $statuses->filter(fn ($s) => $s === PcMonitor::STATUS_OFFLINE)->count(),
                'avg_cpu' => round($pcs->avg('cpu_usage') ?? 0, 1),
                'avg_ram' => round($pcs->avg('ram_usage') ?? 0, 1),
                'avg_disk' => round($pcs->avg('disk_usage') ?? 0, 1),
            ],
            'pcs' => $items,
            'can_power' => (bool) $request->user()?->hasPermission('control-monitoring'),
            'generated_at' => now()->toIso8601String(),
        ];
    }

    private function effectiveStatus(PcMonitor $pc): string
    {
        return $pc->isStale(30) ? PcMonitor::STATUS_OFFLINE : PcMonitor::STATUS_ONLINE;
    }
}
