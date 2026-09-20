<?php

namespace App\Http\Controllers;

use App\Models\Box;
use App\Models\BoxComponent;
use App\Models\Component;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BoxComponentController extends Controller
{
    public function store(Request $request, Box $box)
    {
        $validated = $request->validate([
            'component_id' => 'required|exists:components,id',
            'quantity'     => 'required|integer|min:1',
        ]);

        $component = Component::findOrFail($validated['component_id']);

        if ($component->quantity < $validated['quantity']) {
            return response()->json([
                'success' => false,
                'message' => 'Stok tidak cukup. Stok tersedia: ' . $component->quantity,
            ], 422);
        }

        DB::beginTransaction();

        try {
            $boxComponent = BoxComponent::where('box_id', $box->id)
                ->where('component_id', $validated['component_id'])
                ->first();

            if ($boxComponent) {
                $boxComponent->increment('quantity', $validated['quantity']);
            } else {
                BoxComponent::create([
                    'box_id'       => $box->id,
                    'component_id' => $validated['component_id'],
                    'quantity'     => $validated['quantity'],
                ]);
            }

            $component->decrement('quantity', $validated['quantity']);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Komponen berhasil ditambahkan ke box',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal menambahkan komponen: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function destroy(Box $box, BoxComponent $boxComponent)
    {
        if ($boxComponent->box_id !== $box->id) {
            return response()->json([
                'success' => false,
                'message' => 'Data tidak valid',
            ], 404);
        }

        DB::beginTransaction();

        try {
            $component = Component::findOrFail($boxComponent->component_id);
            $component->increment('quantity', $boxComponent->quantity);

            $boxComponent->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Komponen berhasil dikeluarkan dari box. Stok telah dikembalikan.',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengeluarkan komponen: ' . $e->getMessage(),
            ], 500);
        }
    }
}
