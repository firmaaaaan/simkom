<?php

namespace App\Http\Controllers;

use App\Models\EmergencyResponse;
use App\Models\Laboratory;
use Illuminate\Http\Request;

class EmergencyResponseController extends Controller
{
    public function index()
    {
        $items = EmergencyResponse::with('laboratory')->latest()->paginate(10);
        return view('k3.emergency.index', compact('items'));
    }

    public function create()
    {
        $laboratories = Laboratory::orderBy('name')->get();
        return view('k3.emergency.create', compact('laboratories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'laboratory_id' => 'nullable|uuid|exists:laboratories,id',
            'scenario' => 'required|string|max:255',
            'procedure' => 'nullable|string',
            'responsible_person' => 'nullable|string|max:255',
            'contact' => 'nullable|string|max:255',
            'equipment' => 'nullable|string',
            'drill_date' => 'nullable|date',
            'notes' => 'nullable|string',
        ]);
        EmergencyResponse::create($validated);
        return redirect()->route('k3.emergency.index')->with('success', 'Data berhasil disimpan.');
    }

    public function edit(EmergencyResponse $emergency)
    {
        $laboratories = Laboratory::orderBy('name')->get();
        return view('k3.emergency.edit', compact('emergency', 'laboratories'));
    }

    public function update(Request $request, EmergencyResponse $emergency)
    {
        $validated = $request->validate([
            'laboratory_id' => 'nullable|uuid|exists:laboratories,id',
            'scenario' => 'required|string|max:255',
            'procedure' => 'nullable|string',
            'responsible_person' => 'nullable|string|max:255',
            'contact' => 'nullable|string|max:255',
            'equipment' => 'nullable|string',
            'drill_date' => 'nullable|date',
            'notes' => 'nullable|string',
        ]);
        $emergency->update($validated);
        return redirect()->route('k3.emergency.index')->with('success', 'Data berhasil diperbarui.');
    }

    public function destroy(EmergencyResponse $emergency)
    {
        $emergency->delete();
        return redirect()->route('k3.emergency.index')->with('success', 'Data berhasil dihapus.');
    }
}