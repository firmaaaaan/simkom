<?php

namespace App\Http\Controllers;

use App\Models\PcMonitor;
use App\Models\PcPowerCommand;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AgentCommandController extends Controller
{
    /**
     * Perintah pending untuk PC tertentu — diambil agent tiap siklus polling.
     * Hostname tidak dikenal menghasilkan daftar kosong (agent boleh lapor
     * telemetry dulu sebelum perintah pertama muncul).
     */
    public function index(Request $request): JsonResponse
    {
        $hostname = (string) $request->query('hostname', '');

        $pc = $hostname !== '' ? PcMonitor::where('hostname', $hostname)->first() : null;

        if (! $pc) {
            return response()->json(['commands' => []]);
        }

        $commands = $pc->powerCommands()->pending()->get()
            ->map(fn (PcPowerCommand $command) => [
                'id' => $command->id,
                'action' => $command->action,
            ]);

        return response()->json(['commands' => $commands]);
    }

    /**
     * Konfirmasi hasil eksekusi perintah oleh agent (completed | failed).
     */
    public function ack(Request $request, string $command): JsonResponse
    {
        $data = $request->validate([
            'status' => 'required|in:completed,failed',
        ]);

        $powerCommand = PcPowerCommand::findOrFail($command);

        $powerCommand->update([
            'status' => $data['status'],
            'executed_at' => now(),
        ]);

        return response()->json(['ok' => true]);
    }
}
