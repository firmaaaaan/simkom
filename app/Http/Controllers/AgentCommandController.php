<?php

namespace App\Http\Controllers;

use App\Models\PcMonitor;
use App\Models\PcPowerCommand;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AgentCommandController extends Controller
{
    /**
     * Daftar perintah pending untuk PC (GET /api/v1/commands?hostname=).
     * Dipoll agent tiap interval; perintah lewat TTL ditandai expired
     * dan tidak dikirim lagi.
     */
    public function index(Request $request): JsonResponse
    {
        $this->authorizeKey($request);

        $hostname = $request->query('hostname');
        if (! is_string($hostname) || $hostname === '') {
            abort(422, 'Parameter hostname wajib diisi.');
        }

        $pc = PcMonitor::where('hostname', $hostname)->first();

        if (! $pc) {
            return response()->json(['commands' => []]);
        }

        // Perintah yang sudah lewat masa berlaku tidak lagi dieksekusi.
        $pc->powerCommands()->pending()
            ->where('created_at', '<', now()->subSeconds(PcPowerCommand::TTL_SECONDS))
            ->update(['status' => PcPowerCommand::STATUS_EXPIRED]);

        $commands = $pc->powerCommands()->pending()
            ->where('created_at', '>=', now()->subSeconds(PcPowerCommand::TTL_SECONDS))
            ->orderBy('created_at')
            ->get(['id', 'action', 'created_at'])
            ->map(fn (PcPowerCommand $command) => [
                'id' => $command->id,
                'action' => $command->action,
            ]);

        return response()->json(['commands' => $commands]);
    }

    /**
     * Agent melaporkan hasil eksekusi perintah (POST /api/v1/commands/{id}/ack).
     */
    public function ack(Request $request, PcPowerCommand $command): JsonResponse
    {
        $this->authorizeKey($request);

        $data = $request->validate([
            'hostname' => ['required', 'string', 'max:255'],
            'status' => ['required', 'in:done,failed'],
            'message' => ['nullable', 'string', 'max:255'],
        ]);

        if ($command->pc?->hostname !== $data['hostname']) {
            abort(403, 'Perintah bukan milik PC ini.');
        }

        if ($command->status !== PcPowerCommand::STATUS_PENDING) {
            return response()->json([
                'status' => 'ignored',
                'message' => 'Perintah sudah diproses sebelumnya.',
            ]);
        }

        $command->update([
            'status' => $data['status'],
            'message' => $data['message'] ?? null,
            'executed_at' => now(),
        ]);

        return response()->json([
            'status' => 'ok',
            'command_status' => $command->status,
        ]);
    }

    /**
     * Autentikasi agent: header X-API-KEY harus cocok dengan
     * MONITORING_API_KEY (config: services.monitoring.key).
     */
    private function authorizeKey(Request $request): void
    {
        $expected = (string) config('services.monitoring.key');
        $given = (string) $request->header('X-API-KEY');

        if ($expected === '' || $given === '' || ! hash_equals($expected, $given)) {
            abort(403, 'API key tidak valid.');
        }
    }
}
