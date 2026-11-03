<?php

namespace App\Http\Controllers;

use App\Models\B3Waste;
use App\Models\Laboratory;
use Illuminate\Http\Request;

class B3WasteController extends Controller
{
    public function index()
    {
        $items = B3Waste::with('laboratory')->latest()->paginate(10);
        return view('k3.b3.index', compact('items'));
    }

    public function create()
    {
        $laboratories = Laboratory::orderBy('name')->get();
        return view('k3.b3.create', compact('laboratories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'laboratory_id' => 'nullable|uuid|exists:laboratories,id',
            'waste_name' => 'required|string|max:255',
            'waste_type' => 'nullable|string|max:255',
            'source' => 'nullable|string|max:255',
            'quantity' => 'nullable|numeric',
            'unit' => 'nullable|string|max:50',
            'storage_method' => 'nullable|string',
            'disposal_method' => 'nullable|string',
            'disposal_date' => 'nullable|date',
            'pic' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);
        B3Waste::create($validated);
        return redirect()->route('k3.b3.index')->with('success', 'Data berhasil disimpan.');
    }

    public function edit(B3Waste $b3Waste)
    {
        $laboratories = Laboratory::orderBy('name')->get();
        return view('k3.b3.edit', compact('b3Waste', 'laboratories'));
    }

    public function update(Request $request, B3Waste $b3Waste)
    {
        $validated = $request->validate([
            'laboratory_id' => 'nullable|uuid|exists:laboratories,id',
            'waste_name' => 'required|string|max:255',
            'waste_type' => 'nullable|string|max:255',
            'source' => 'nullable|string|max:255',
            'quantity' => 'nullable|numeric',
            'unit' => 'nullable|string|max:50',
            'storage_method' => 'nullable|string',
            'disposal_method' => 'nullable|string',
            'disposal_date' => 'nullable|date',
            'pic' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);
        $b3Waste->update($validated);
        return redirect()->route('k3.b3.index')->with('success', 'Data berhasil diperbarui.');
    }

    public function destroy(B3Waste $b3Waste)
    {
        $b3Waste->delete();
        return redirect()->route('k3.b3.index')->with('success', 'Data berhasil dihapus.');
    }
}