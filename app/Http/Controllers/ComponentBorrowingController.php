<?php

namespace App\Http\Controllers;

use App\Models\Component;
use App\Models\ComponentBorrowing;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ComponentBorrowingController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'component_id' => 'required|uuid|exists:components,id',
            'quantity' => 'required|integer|min:1',
            'user_nim' => 'required|string|max:50',
            'user_name' => 'required|string|max:255',
            'borrowed_at' => 'required|date',
        ]);

        try {
            $borrowing = DB::transaction(function () use ($validated) {
                $component = Component::lockForUpdate()->findOrFail($validated['component_id']);

                if ($component->quantity < $validated['quantity']) {
                    throw new \RuntimeException('Stok tidak mencukupi. Stok tersisa: ' . $component->quantity);
                }

                $component->decrement('quantity', $validated['quantity']);

                return ComponentBorrowing::create([
                    'component_id' => $component->id,
                    'quantity' => $validated['quantity'],
                    'user_nim' => $validated['user_nim'],
                    'user_name' => $validated['user_name'],
                    'status' => 'Using',
                    'borrowed_at' => $validated['borrowed_at'],
                ]);
            });
        } catch (\RuntimeException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mencatat peminjaman',
            ], 500);
        }

        return response()->json([
            'success' => true,
            'message' => 'Peminjaman komponen berhasil dicatat',
            'borrowing' => $borrowing->load('component'),
        ]);
    }

    public function returnBorrowing(Request $request, ComponentBorrowing $borrowing)
    {
        if ($borrowing->status !== 'Using') {
            return response()->json([
                'success' => false,
                'message' => 'Peminjaman ini sudah dikembalikan',
            ], 422);
        }

        $validated = $request->validate([
            'note' => 'nullable|string|max:500',
        ]);

        try {
            DB::transaction(function () use ($borrowing, $validated) {
                $borrowing->component()->lockForUpdate()->first()?->increment('quantity', $borrowing->quantity);

                $borrowing->update([
                    'status' => 'Returned',
                    'returned_at' => now(),
                    'return_note' => $validated['note'] ?? null,
                ]);
            });
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memproses pengembalian',
            ], 500);
        }

        return response()->json([
            'success' => true,
            'message' => 'Komponen berhasil dikembalikan',
            'borrowing' => $borrowing->fresh('component'),
        ]);
    }
}
