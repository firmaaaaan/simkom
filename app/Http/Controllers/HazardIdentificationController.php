<?php

namespace App\Http\Controllers;

use App\Models\HazardIdentification;
use App\Models\Laboratory;
use Illuminate\Http\Request;

class HazardIdentificationController extends Controller
{
    public function index()
    {
        $items = HazardIdentification::with('laboratory')->latest()->paginate(10);
        return view('k3.hazard.index', compact('items'));
    }

    public function create()
    {
        $laboratories = Laboratory::orderBy('name')->get();
        return view('k3.hazard.create', compact('laboratories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'laboratory_id' => 'nullable|uuid|exists:laboratories,id',
            'area_activity' => 'required|string|max:255',
            'hazard' => 'nullable|string',
            'risk' => 'nullable|string',
            'likelihood_before' => 'nullable|string|max:10',
            'impact_before' => 'nullable|string|max:10',
            'risk_level_before' => 'nullable|string|max:10',
            'control_before' => 'nullable|string',
            'schedule' => 'nullable|string|max:255',
            'likelihood_after' => 'nullable|string|max:10',
            'impact_after' => 'nullable|string|max:10',
            'risk_level_after' => 'nullable|string|max:10',
            'control_after' => 'nullable|string',
            'recommendation' => 'nullable|string',
            'authority' => 'nullable|string|max:255',
        ]);
        HazardIdentification::create($validated);
        return redirect()->route('k3.hazard.index')->with('success', 'Data berhasil disimpan.');
    }

    public function edit(HazardIdentification $hazard)
    {
        $laboratories = Laboratory::orderBy('name')->get();
        return view('k3.hazard.edit', compact('hazard', 'laboratories'));
    }

    public function update(Request $request, HazardIdentification $hazard)
    {
        $validated = $request->validate([
            'laboratory_id' => 'nullable|uuid|exists:laboratories,id',
            'area_activity' => 'required|string|max:255',
            'hazard' => 'nullable|string',
            'risk' => 'nullable|string',
            'likelihood_before' => 'nullable|string|max:10',
            'impact_before' => 'nullable|string|max:10',
            'risk_level_before' => 'nullable|string|max:10',
            'control_before' => 'nullable|string',
            'schedule' => 'nullable|string|max:255',
            'likelihood_after' => 'nullable|string|max:10',
            'impact_after' => 'nullable|string|max:10',
            'risk_level_after' => 'nullable|string|max:10',
            'control_after' => 'nullable|string',
            'recommendation' => 'nullable|string',
            'authority' => 'nullable|string|max:255',
        ]);
        $hazard->update($validated);
        return redirect()->route('k3.hazard.index')->with('success', 'Data berhasil diperbarui.');
    }

    public function destroy(HazardIdentification $hazard)
    {
        $hazard->delete();
        return redirect()->route('k3.hazard.index')->with('success', 'Data berhasil dihapus.');
    }
}