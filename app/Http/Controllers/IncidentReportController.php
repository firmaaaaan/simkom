<?php

namespace App\Http\Controllers;

use App\Models\IncidentReport;
use App\Models\Laboratory;
use Illuminate\Http\Request;

class IncidentReportController extends Controller
{
    public function index()
    {
        $items = IncidentReport::with('laboratory')->latest()->paginate(10);
        return view('k3.incident.index', compact('items'));
    }

    public function create()
    {
        $laboratories = Laboratory::orderBy('name')->get();
        return view('k3.incident.create', compact('laboratories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'laboratory_id' => 'nullable|uuid|exists:laboratories,id',
            'incident_date' => 'required|date',
            'reporter_name' => 'nullable|string|max:255',
            'incident_type' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'impact' => 'nullable|string',
            'immediate_action' => 'nullable|string',
            'follow_up' => 'nullable|string',
            'status' => 'nullable|string|max:50',
        ]);
        IncidentReport::create($validated);
        return redirect()->route('k3.incident.index')->with('success', 'Data berhasil disimpan.');
    }

    public function edit(IncidentReport $incident)
    {
        $laboratories = Laboratory::orderBy('name')->get();
        return view('k3.incident.edit', compact('incident', 'laboratories'));
    }

    public function update(Request $request, IncidentReport $incident)
    {
        $validated = $request->validate([
            'laboratory_id' => 'nullable|uuid|exists:laboratories,id',
            'incident_date' => 'required|date',
            'reporter_name' => 'nullable|string|max:255',
            'incident_type' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'impact' => 'nullable|string',
            'immediate_action' => 'nullable|string',
            'follow_up' => 'nullable|string',
            'status' => 'nullable|string|max:50',
        ]);
        $incident->update($validated);
        return redirect()->route('k3.incident.index')->with('success', 'Data berhasil diperbarui.');
    }

    public function destroy(IncidentReport $incident)
    {
        $incident->delete();
        return redirect()->route('k3.incident.index')->with('success', 'Data berhasil dihapus.');
    }
}