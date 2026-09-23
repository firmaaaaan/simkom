<?php

namespace App\Http\Controllers;

use App\Models\Box;
use App\Models\BoxReturnNote;
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

    public function returnBox(Request $request, $boxUsageId)
    {
        $boxUsage = BoxUsage::where('status', 'Using')->findOrFail($boxUsageId);

        $boxUsage->update([
            'status'      => 'Returned',
            'returned_at' => now(),
        ]);

        $note = $request->input('note');
        if ($note !== null && $note !== '') {
            BoxReturnNote::create([
                'box_usage_id' => $boxUsage->id,
                'note'         => $note,
            ]);
        }

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

    public function available($boxCode)
    {
        $box = Box::where('code', $boxCode)->firstOrFail();

        $prefix = $this->extractPrefix($box->code);

        $boxes = Box::where('code', 'like', "BOX-{$prefix}-%")
            ->where('code', '!=', $box->code)
            ->whereDoesntHave('boxUsages', function ($q) {
                $q->where('status', 'Using');
            })
            ->orderBy('code')
            ->get(['id', 'code', 'name', 'location']);

        return response()->json([
            'success' => true,
            'prefix'  => $prefix,
            'boxes'   => $boxes,
        ]);
    }

    public function multiUse(Request $request)
    {
        $validated = $request->validate([
            'box_codes'  => 'required|array|min:1',
            'box_codes.*' => 'string|max:50',
            'user_name'  => 'required|string|max:255',
            'user_nim'   => 'required|string|max:50',
            'user_kelas' => 'required|string|max:100',
        ]);

        DB::beginTransaction();

        try {
            $boxes = Box::whereIn('code', $validated['box_codes'])->get();

            if ($boxes->count() !== count($validated['box_codes'])) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'Beberapa box tidak ditemukan',
                ], 422);
            }

            foreach ($boxes as $box) {
                if ($box->getActiveUsage()) {
                    DB::rollBack();
                    return response()->json([
                        'success' => false,
                        'message' => "Box {$box->code} sedang digunakan oleh " . $box->getActiveUsage()->user_name,
                    ], 422);
                }
            }

            $usages = [];
            foreach ($boxes as $box) {
                $usages[] = BoxUsage::create([
                    'box_id'     => $box->id,
                    'user_name'  => $validated['user_name'],
                    'user_nim'   => $validated['user_nim'],
                    'user_kelas' => $validated['user_kelas'],
                    'status'     => 'Using',
                    'used_at'    => now(),
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => count($usages) . ' box berhasil digunakan',
                'usages'  => $usages,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function multiQuickUse(Request $request)
    {
        $validated = $request->validate([
            'box_codes'  => 'required|array|min:1',
            'box_codes.*' => 'string|max:50',
            'nim'        => 'required|string|max:50',
        ]);

        DB::beginTransaction();

        try {
            $lastUsage = BoxUsage::where('user_nim', $validated['nim'])
                ->latest()
                ->first();

            if (!$lastUsage) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'Data tidak ditemukan. Silakan isi nama dan NIM terlebih dahulu.',
                ], 422);
            }

            $boxes = Box::whereIn('code', $validated['box_codes'])->get();

            if ($boxes->count() !== count($validated['box_codes'])) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'Beberapa box tidak ditemukan',
                ], 422);
            }

            foreach ($boxes as $box) {
                if ($box->getActiveUsage()) {
                    DB::rollBack();
                    return response()->json([
                        'success' => false,
                        'message' => "Box {$box->code} sedang digunakan oleh " . $box->getActiveUsage()->user_name,
                    ], 422);
                }
            }

            $usages = [];
            foreach ($boxes as $box) {
                $usages[] = BoxUsage::create([
                    'box_id'     => $box->id,
                    'user_name'  => $lastUsage->user_name,
                    'user_nim'   => $lastUsage->user_nim,
                    'user_kelas' => $lastUsage->user_kelas,
                    'status'     => 'Using',
                    'used_at'    => now(),
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => count($usages) . ' box berhasil digunakan',
                'usages'  => $usages,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function multiReturn(Request $request)
    {
        $validated = $request->validate([
            'items'            => 'required|array|min:1',
            'items.*.usage_id' => 'required|string|max:50',
            'items.*.note'     => 'nullable|string|max:500',
        ]);

        DB::beginTransaction();

        try {
            $usageIds = array_column($validated['items'], 'usage_id');

            $usages = BoxUsage::whereIn('id', $usageIds)
                ->where('status', 'Using')
                ->get();

            if ($usages->isEmpty()) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'Tidak ada box yang bisa dikembalikan',
                ], 422);
            }

            foreach ($usages as $usage) {
                $usage->update([
                    'status'      => 'Returned',
                    'returned_at' => now(),
                ]);
            }

            foreach ($validated['items'] as $item) {
                if (!empty($item['note'])) {
                    $exists = $usages->firstWhere('id', $item['usage_id']);
                    if ($exists) {
                        BoxReturnNote::create([
                            'box_usage_id' => $item['usage_id'],
                            'note'         => $item['note'],
                        ]);
                    }
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => $usages->count() . ' box berhasil dikembalikan',
                'count'   => $usages->count(),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function activeUsagesByNim($boxCode)
    {
        $box = Box::where('code', $boxCode)->firstOrFail();

        $activeUsage = $box->getActiveUsage();

        if (!$activeUsage) {
            return response()->json([
                'success' => true,
                'usages'  => [],
            ]);
        }

        $usages = BoxUsage::with('box')
            ->where('user_nim', $activeUsage->user_nim)
            ->where('status', 'Using')
            ->orderBy('used_at')
            ->get();

        return response()->json([
            'success' => true,
            'usages'  => $usages,
        ]);
    }

    private function extractPrefix(string $code): string
    {
        // Format: BOX-XXX-NNN → ambil XXX
        $parts = explode('-', $code);
        return $parts[1] ?? '';
    }
}
