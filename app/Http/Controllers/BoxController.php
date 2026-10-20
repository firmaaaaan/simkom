<?php

namespace App\Http\Controllers;

use App\Exports\BoxExport;
use App\Exports\UsedBoxExport;
use App\Models\Box;
use App\Models\BoxComponent;
use App\Models\Component;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class BoxController extends Controller
{
    public function export()
    {
        return Excel::download(
            new BoxExport(Box::query()->orderBy('name')->orderBy('code')),
            'box-penyimpanan-'.now()->format('Y-m-d').'.xlsx'
        );
    }

    public function exportUsedBoxes()
    {
        return Excel::download(
            new UsedBoxExport(Box::query()
                ->whereHas('boxUsages')
                ->orderByDesc('last_used_at')
                ->orderBy('code')),
            'box-pernah-dipakai-'.now()->format('Y-m-d').'.xlsx'
        );
    }

    public function index(Request $request)
    {
        $tab = $request->input('tab', 'boxes');

        return redirect()->route('components.index', ['tab' => $tab] + $request->query());
    }

    public function printLabels(Request $request)
    {
        $group = $request->input('group');

        $query = Box::orderBy('name')->orderBy('code');

        if ($group) {
            $query->where('name', $group);
        }

        $boxes = $query->get();

        $groups = Box::select('name')->distinct()->orderBy('name')->pluck('name');

        return view('components.print-labels', compact('boxes', 'groups', 'group'));
    }

    public function show(Box $box)
    {
        $box->load(['boxComponents.component']);

        $activeUsage = $box->boxUsages()->where('status', 'Using')->first();

        $components = Component::orderBy('name')->get();

        return response()->json([
            'box' => $box,
            'components' => $components,
            'active_usage' => $activeUsage,
        ]);
    }

    public function update(Request $request, Box $box)
    {
        $validated = $request->validate([
            'location' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        $oldName = $box->name;
        $box->update($validated);

        // Sync location & notes ke semua box dengan nama yang sama
        Box::where('name', $oldName)
            ->where('id', '!=', $box->id)
            ->update([
                'location' => $validated['location'] ?? null,
                'notes' => $validated['notes'] ?? null,
            ]);

        return response()->json([
            'success' => true,
            'message' => 'Box berhasil diperbarui untuk grup: '.$oldName,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'prefix' => 'required|string|max:10',
            'location' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
            'quantity' => 'required|integer|min:1|max:100',
            'components' => 'nullable|array',
            'components.*' => 'required|string',
        ]);

        $prefix = strtoupper($validated['prefix']);
        $prefixLength = strlen($prefix);
        $lastCode = Box::where('code', 'like', "BOX-{$prefix}-%")
            ->orderByRaw('CAST(SUBSTR(code, '.($prefixLength + 5).') AS UNSIGNED) DESC')
            ->value('code');
        $lastNum = $lastCode ? (int) substr($lastCode, $prefixLength + 5) : 0;

        $componentsData = [];
        if (! empty($validated['components'])) {
            foreach ($validated['components'] as $compJson) {
                $comp = json_decode($compJson, true);
                if ($comp && isset($comp['id']) && isset($comp['quantity'])) {
                    $componentsData[] = $comp;
                }
            }
        }

        DB::beginTransaction();

        try {
            $boxes = [];
            for ($i = 1; $i <= $validated['quantity']; $i++) {
                $box = Box::create([
                    'name' => $validated['name'],
                    'code' => 'BOX-'.$prefix.'-'.str_pad($lastNum + $i, 3, '0', STR_PAD_LEFT),
                    'location' => $validated['location'] ?? null,
                    'notes' => $validated['notes'] ?? null,
                    'description' => null,
                ]);

                foreach ($componentsData as $comp) {
                    $component = Component::find($comp['id']);
                    if ($component && $component->quantity >= $comp['quantity']) {
                        BoxComponent::create([
                            'box_id' => $box->id,
                            'component_id' => $comp['id'],
                            'quantity' => $comp['quantity'],
                        ]);
                        $component->decrement('quantity', $comp['quantity']);
                    }
                }

                $boxes[] = $box;
            }

            DB::commit();

            $msg = count($boxes).' box berhasil dibuat';
            if (count($componentsData) > 0) {
                $msg .= ' dengan '.count($componentsData).' jenis komponen';
            }

            return response()->json([
                'success' => true,
                'message' => $msg,
                'boxes' => $boxes,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Gagal membuat box: '.$e->getMessage(),
            ], 500);
        }
    }

    public function destroy(Box $box)
    {
        DB::beginTransaction();

        try {
            $boxComponents = $box->boxComponents()->with('component')->get();

            foreach ($boxComponents as $bc) {
                $bc->component->increment('quantity', $bc->quantity);
            }

            $box->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Box berhasil dihapus. Stok komponen telah dikembalikan.',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus box: '.$e->getMessage(),
            ], 500);
        }
    }

    public function bulkAddComponent(Request $request)
    {
        $validated = $request->validate([
            'component_id' => 'nullable|exists:components,id|required_without:components',
            'quantity' => 'nullable|integer|min:1|required_without:components',
            'components' => 'nullable|array',
            'components.*.component_id' => 'required|exists:components,id',
            'components.*.quantity' => 'required|integer|min:1',
            'box_names' => 'nullable|array|required_without:box_ids',
            'box_names.*' => 'required|string',
            'box_ids' => 'nullable|array|required_without:box_names',
            'box_ids.*' => 'required|exists:boxes,id',
        ], [
            'component_id.required_without' => 'Pilih minimal satu komponen',
            'quantity.required_without' => 'Jumlah wajib diisi',
            'box_names.required_without' => 'Pilih minimal satu box',
            'box_ids.required_without' => 'Pilih minimal satu box',
        ]);

        // Payload lama (component_id + quantity) dinormalkan ke daftar item;
        // duplikat digabung agar stok tidak dicek/didekremen ganda.
        $items = collect($validated['components'] ?? [['component_id' => $validated['component_id'], 'quantity' => $validated['quantity']]])
            ->groupBy('component_id')
            ->map(fn ($group) => [
                'component_id' => $group->first()['component_id'],
                'quantity' => (int) $group->sum('quantity'),
            ])
            ->values();

        $components = Component::whereIn('id', $items->pluck('component_id'))->get()->keyBy('id');

        // Terima target berdasarkan nama grup (modal lama) maupun id unit
        // (rangkuman "Box Dipakai" — per kode box fisik).
        $boxes = collect()
            ->concat(Box::whereIn('name', $validated['box_names'] ?? [])->get())
            ->concat(Box::whereIn('id', $validated['box_ids'] ?? [])->get())
            ->unique('id');

        if ($boxes->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak ada box ditemukan dengan nama atau id yang dipilih',
            ], 422);
        }

        // Cek stok semua komponen dulu — batal semua bila ada yang kurang.
        $totals = [];
        foreach ($items as $item) {
            $component = $components[$item['component_id']] ?? null;
            $totalNeeded = $item['quantity'] * $boxes->count();

            if (! $component) {
                return response()->json([
                    'success' => false,
                    'message' => 'Komponen tidak ditemukan',
                ], 422);
            }

            if ($component->quantity < $totalNeeded) {
                return response()->json([
                    'success' => false,
                    'message' => "Stok tidak cukup untuk {$component->name}. Dibutuhkan {$totalNeeded}, stok tersedia: {$component->quantity}",
                ], 422);
            }

            $totals[$item['component_id']] = $totalNeeded;
        }

        DB::beginTransaction();

        try {
            foreach ($boxes as $box) {
                foreach ($items as $item) {
                    $existing = BoxComponent::where('box_id', $box->id)
                        ->where('component_id', $item['component_id'])
                        ->first();

                    if ($existing) {
                        $existing->increment('quantity', $item['quantity']);
                    } else {
                        BoxComponent::create([
                            'box_id' => $box->id,
                            'component_id' => $item['component_id'],
                            'quantity' => $item['quantity'],
                        ]);
                    }
                }
            }

            foreach ($totals as $componentId => $totalNeeded) {
                $components[$componentId]->decrement('quantity', $totalNeeded);
            }

            DB::commit();

            $totalItems = array_sum($totals);

            return response()->json([
                'success' => true,
                'message' => "{$items->count()} jenis komponen ({$totalItems} item) berhasil didistribusikan ke {$boxes->count()} box",
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Gagal mendistribusikan komponen: '.$e->getMessage(),
            ], 500);
        }
    }
}
