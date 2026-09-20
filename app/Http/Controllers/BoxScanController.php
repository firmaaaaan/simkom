<?php

namespace App\Http\Controllers;

use App\Models\Box;
use App\Models\BoxUsage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BoxScanController extends Controller
{
    public function scan($boxCode)
    {
        $box = Box::with('boxComponents.component')->where('code', $boxCode)->firstOrFail();

        $activeUsage = $box->getActiveUsage();

        $lastUsageByNim = null;
        $nim = request('nim');
        if ($nim) {
            $lastUsageByNim = $box->boxUsages()
                ->where('user_nim', $nim)
                ->latest()
                ->first();
        }

        return view('box-scan.scan', compact('box', 'activeUsage', 'lastUsageByNim', 'nim'));
    }

    public function useBox(Request $request, $boxCode)
    {
        $box = Box::where('code', $boxCode)->firstOrFail();

        $activeUsage = $box->getActiveUsage();

        if ($activeUsage) {
            return response()->json([
                'success' => false,
                'message' => 'Box sedang digunakan oleh ' . $activeUsage->user_name,
            ], 422);
        }

        $validated = $request->validate([
            'user_name'  => 'required|string|max:255',
            'user_nim'   => 'required|string|max:50',
            'user_kelas' => 'required|string|max:100',
        ]);

        $boxUsage = BoxUsage::create([
            'box_id'     => $box->id,
            'user_name'  => $validated['user_name'],
            'user_nim'   => $validated['user_nim'],
            'user_kelas' => $validated['user_kelas'],
            'status'     => 'Using',
            'used_at'    => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Box berhasil digunakan',
            'usage'   => $boxUsage,
        ]);
    }

    public function quickUse($boxCode)
    {
        $box = Box::where('code', $boxCode)->firstOrFail();

        $activeUsage = $box->getActiveUsage();

        if ($activeUsage) {
            return response()->json([
                'success' => false,
                'message' => 'Box sedang digunakan oleh ' . $activeUsage->user_name,
            ], 422);
        }

        $nim = request('nim');

        $lastUsage = $box->boxUsages()
            ->where('user_nim', $nim)
            ->latest()
            ->first();

        if (!$lastUsage) {
            return response()->json([
                'success' => false,
                'message' => 'Data tidak ditemukan. Silakan isi nama dan NIM terlebih dahulu.',
            ], 422);
        }

        $boxUsage = BoxUsage::create([
            'box_id'     => $box->id,
            'user_name'  => $lastUsage->user_name,
            'user_nim'   => $lastUsage->user_nim,
            'user_kelas' => $lastUsage->user_kelas,
            'status'     => 'Using',
            'used_at'    => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Box berhasil digunakan',
            'usage'   => $boxUsage,
        ]);
    }

    public function returnBox($boxUsageId)
    {
        $boxUsage = BoxUsage::where('status', 'Using')->findOrFail($boxUsageId);

        $boxUsage->update([
            'status'      => 'Returned',
            'returned_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Box berhasil dikembalikan',
        ]);
    }

    public function checkStatus($boxCode)
    {
        $box = Box::where('code', $boxCode)->firstOrFail();

        $activeUsage = $box->getActiveUsage();

        return response()->json([
            'box'        => $box,
            'is_used'    => $activeUsage !== null,
            'usage'      => $activeUsage,
        ]);
    }
}
