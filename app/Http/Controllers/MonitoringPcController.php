<?php

namespace App\Http\Controllers;

use App\Models\PcCommand;
use App\Models\PcMonitor;
use App\Services\SoftwareComparison;
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
     * Nyalakan PC via Wake-on-LAN (magic packet ke MAC address).
     */
    public function powerOn(Request $request, PcMonitor $pc): JsonResponse
    {
        if ($pc->effectiveStatus() === PcMonitor::STATUS_ONLINE) {
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
     * Buat perintah agent (shutdown/restart/lock/logout/screenshot):
     * antrean pending, agent di PC mengeksekusinya lalu meng-ack.
     */
    public function command(Request $request, PcMonitor $pc): JsonResponse
    {
        $data = $request->validate([
            'action' => 'required|in:'.implode(',', PcCommand::ACTIONS),
        ]);

        if ($pc->effectiveStatus() !== PcMonitor::STATUS_ONLINE) {
            return response()->json([
                'ok' => false,
                'message' => "{$pc->hostname} sedang offline.",
            ], 409);
        }

        if ($pc->hasPendingCommand()) {
            return response()->json([
                'ok' => false,
                'message' => "Masih ada perintah untuk {$pc->hostname} yang menunggu di agent.",
            ], 409);
        }

        PcCommand::create([
            'pc_monitor_id' => $pc->id,
            'action' => $data['action'],
            'status' => PcCommand::STATUS_PENDING,
            'requested_by' => $request->user()?->id,
        ]);

        $label = PcCommand::actionLabel($data['action']);

        return response()->json([
            'ok' => true,
            'message' => "Perintah {$label} {$pc->hostname} dikirim — agent mengeksekusi dalam ~10 detik.",
        ]);
    }

    /**
     * Matikan semua PC online sekaligus: antrekan perintah shutdown ke
     * tiap agent. PC offline & PC yang sudah punya perintah pending dilewati.
     */
    public function shutdownAll(Request $request): JsonResponse
    {
        $pcs = PcMonitor::all();

        $online = $pcs->filter(fn ($pc) => $pc->effectiveStatus() === PcMonitor::STATUS_ONLINE);
        $offlineCount = $pcs->count() - $online->count();
        $skippedPending = 0;
        $sent = 0;

        foreach ($online as $pc) {
            if ($pc->hasPendingCommand()) {
                $skippedPending++;

                continue;
            }

            PcCommand::create([
                'pc_monitor_id' => $pc->id,
                'action' => PcCommand::ACTION_SHUTDOWN,
                'status' => PcCommand::STATUS_PENDING,
                'requested_by' => $request->user()?->id,
            ]);

            $sent++;
        }

        if ($sent === 0) {
            $message = $offlineCount === 0 && $skippedPending === 0
                ? 'Tidak ada PC untuk dimatikan.'
                : "Tidak ada perintah terkirim — {$skippedPending} PC menunggu perintah lain";

            if ($offlineCount > 0) {
                $message .= ", {$offlineCount} PC offline";
            }

            return response()->json(['ok' => false, 'message' => $message.'.'], 409);
        }

        $parts = ["perintah matikan dikirim ke {$sent} PC"];
        if ($offlineCount > 0) {
            $parts[] = "{$offlineCount} PC offline dilewati";
        }
        if ($skippedPending > 0) {
            $parts[] = "{$skippedPending} PC masih menunggu perintah lain";
        }

        return response()->json([
            'ok' => true,
            'message' => 'Perintah '.implode(', ', $parts).'. Agent mengeksekusi dalam ~10 detik.',
        ]);
    }

    /**
     * Screenshot layar terakhir yang dikirim agent.
     */
    public function screenshot(PcMonitor $pc): mixed
    {
        $path = $pc->screenshotPath();

        if (! $pc->hasScreenshot()) {
            abort(404, 'Belum ada screenshot untuk PC ini.');
        }

        return response()->file($path, [
            'Content-Type' => 'image/jpeg',
            'Cache-Control' => 'no-store',
        ]);
    }

    /**
     * Daftar software terinstall pada PC (untuk modal dashboard),
     * dilengkapi flag `running` — true bila nama software cocok dengan
     * proses yang sedang berjalan (snapshot dari agent).
     */
    public function software(PcMonitor $pc): JsonResponse
    {
        $processes = $pc->processes()->get(['name', 'window_title']);

        // Blob teks proses ternormalisasi untuk pencocokan dua arah.
        $processBlob = $processes
            ->map(fn ($p) => $this->normalizeText($p->name.' '.($p->window_title ?? '')))
            ->implode(' ');

        $processTokens = $processes
            ->flatMap(fn ($p) => [
                $this->normalizeText($p->name),
                $this->normalizeText($p->window_title ?? ''),
            ])
            ->filter()
            ->values();

        $items = $pc->softwares()
            ->orderBy('name')
            ->get(['name', 'version', 'publisher', 'install_date', 'size_kb'])
            ->map(function ($software) use ($processBlob, $processTokens) {
                $software->running = $this->isSoftwareRunning(
                    $this->normalizeText($software->name),
                    $processBlob,
                    $processTokens
                );

                return $software;
            });

        return response()->json([
            'hostname' => $pc->hostname,
            'synced_at' => $pc->software_synced_at?->toIso8601String(),
            'processes_synced_at' => $pc->processes_synced_at?->toIso8601String(),
            'items' => $items,
        ]);
    }

    /** Normalisasi teks: huruf kecil, buang karakter non-alfanumerik. */
    private function normalizeText(string $text): string
    {
        return SoftwareComparison::normalize($text);
    }

    /**
     * Pencocokan best-effort: nama software dianggap "sedang berjalan"
     * bila terkandung di blob proses (nama+jendela), atau ada token
     * proses (≥4 karakter) yang terkandung di nama software.
     */
    private function isSoftwareRunning(string $normalizedSoftware, string $processBlob, $processTokens): bool
    {
        if ($normalizedSoftware === '' || $processBlob === '') {
            return false;
        }

        // Arah 1: nama software muncul di nama/jendela proses.
        if (str_contains($processBlob, $normalizedSoftware)) {
            return true;
        }

        // Arah 2: token proses yang cukup panjang muncul di nama software
        // (mis. "chrome" → "Google Chrome", "code" → "Visual Studio Code").
        foreach ($processTokens as $token) {
            if (strlen($token) >= 4 && str_contains($normalizedSoftware, $token)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Ringkasan + daftar PC untuk halaman/polling.
     *
     * Status efektif diturunkan langsung dari last_seen_at (lewat
     * STALE_SECONDS = offline) sehingga akurat walau command
     * monitoring:check-offline belum berjalan; kolom status tetap
     * disinkronkan oleh command tsb.
     */
    private function payload(Request $request): array
    {
        $pcs = PcMonitor::orderBy('hostname')
            ->withCount('softwares')
            ->get();

        $pcIds = $pcs->pluck('id');

        // Pending command & riwayat command terakhir — di-batch sekali query
        // (bukan hasPendingCommand() per PC = N+1).
        $pendingIds = PcCommand::whereIn('pc_monitor_id', $pcIds)
            ->where('status', PcCommand::STATUS_PENDING)
            ->where('created_at', '>=', now()->subSeconds(PcCommand::TTL_SECONDS))
            ->pluck('pc_monitor_id')
            ->flip()
            ->all();

        $lastCommands = PcCommand::whereIn('pc_monitor_id', $pcIds)
            ->whereRaw('created_at = (SELECT MAX(c2.created_at) FROM pc_commands c2 WHERE c2.pc_monitor_id = pc_commands.pc_monitor_id)')
            ->get()
            ->keyBy('pc_monitor_id');

        $items = $pcs->map(fn (PcMonitor $pc) => [
            'id' => $pc->id,
            'hostname' => $pc->hostname,
            'ip_address' => $pc->ip_address,
            'mac_address' => $pc->mac_address,
            'cpu_usage' => round($pc->cpu_usage, 1),
            'ram_usage' => round($pc->ram_usage, 1),
            'disk_usage' => round($pc->disk_usage, 1),
            'active_user' => $pc->active_user,
            'status' => $pc->effectiveStatus(),
            'last_seen_at' => $pc->last_seen_at?->toIso8601String(),
            'seconds_since_last_seen' => $pc->secondsSinceLastSeen(),
            'pending_command' => isset($pendingIds[$pc->id]),
            'last_command' => $lastCommands->has($pc->id)
                ? $this->lastCommandPayload($lastCommands->get($pc->id))
                : null,
            'screenshot_url' => $pc->hasScreenshot()
                ? route('monitoring-pc.screenshot', [$pc, 't' => $pc->updated_at?->timestamp])
                : null,
            'software_count' => (int) ($pc->softwares_count ?? 0),
            'software_synced_at' => $pc->software_synced_at?->toIso8601String(),
            'processes_synced_at' => $pc->processes_synced_at?->toIso8601String(),
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

    /** Ringkasan command terakhir (untuk chip gagal/sukses di kartu PC). */
    private function lastCommandPayload(PcCommand $command): array
    {
        return [
            'action' => $command->action,
            'action_label' => PcCommand::actionLabel($command->action),
            'status' => $command->status,
            'message' => $command->message,
            'at' => ($command->executed_at ?? $command->created_at)?->toIso8601String(),
        ];
    }
}
