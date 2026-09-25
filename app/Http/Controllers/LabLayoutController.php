<?php

namespace App\Http\Controllers;

use App\Models\LabLayout;
use App\Models\Laboratory;
use App\Models\Computer;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class LabLayoutController extends Controller
{
    public function index(): View
    {
        $layouts = LabLayout::with('laboratory', 'creator')
            ->orderByDesc('created_at')
            ->paginate(20);

        return view('lab-layouts.index', compact('layouts'));
    }

    public function create(): View
    {
        $laboratories = Laboratory::orderBy('name')->get();
        return view('lab-layouts.create', compact('laboratories'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'laboratory_id' => 'required|exists:laboratories,id',
            'name' => 'required|string|max:255',
            'grid_cols' => 'required|integer|min:4|max:50',
            'grid_rows' => 'required|integer|min:3|max:50',
            'cell_size' => 'required|integer|min:50|max:200',
            'background_color' => 'required|string|regex:/^#[0-9a-fA-F]{6}$/',
        ]);

        $layout = LabLayout::create([
            ...$validated,
            'layout_data' => [],
            'is_draft' => true,
            'is_published' => false,
            'created_by' => auth()->id(),
        ]);

        return redirect()->route('lab-layouts.edit', $layout)
            ->with('success', 'Layout dibuat. Silakan edit denah.');
    }

    public function show(LabLayout $labLayout): View
    {
        $labLayout->load('laboratory');
        return view('lab-layouts.show', compact('labLayout'));
    }

    public function edit(LabLayout $labLayout): View
    {
        $labLayout->load('laboratory');
        $computers = Computer::where('laboratory_id', $labLayout->laboratory_id)
            ->orderBy('code')
            ->get(['id', 'code', 'name']);

        return view('lab-layouts.editor', compact('labLayout', 'computers'));
    }

    public function update(Request $request, LabLayout $labLayout): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'layout_data' => 'sometimes|array',
            'layout_data.*.type' => 'required_with:layout_data|in:computer,door',
            'layout_data.*.id' => 'required_with:layout_data|string',
            'layout_data.*.grid_x' => 'required_with:layout_data|integer|min:0',
            'layout_data.*.grid_y' => 'required_with:layout_data|integer|min:0',
            'layout_data.*.rotation' => 'sometimes|integer|in:0,90,180,270',
            'layout_data.*.computer_id' => 'sometimes|nullable|exists:computers,id',
            'layout_data.*.label' => 'required_with:layout_data|string|max:100',
            'background_color' => 'sometimes|required|string|regex:/^#[0-9a-fA-F]{6}$/',
            'grid_cols' => 'sometimes|required|integer|min:4|max:50',
            'grid_rows' => 'sometimes|required|integer|min:3|max:50',
            'cell_size' => 'sometimes|required|integer|min:50|max:200',
        ]);

        $labLayout->update($validated);

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'layout' => $labLayout->fresh()]);
        }

        return redirect()->route('lab-layouts.edit', $labLayout)
            ->with('success', 'Layout disimpan sebagai draft.');
    }

    public function publish(LabLayout $labLayout): RedirectResponse
    {
        $labLayout->update([
            'is_published' => true,
            'is_draft' => false,
        ]);

        return redirect()->route('lab-layouts.index')
            ->with('success', 'Layout dipublikasikan. Layout lain di laboratorium yang sama dinonaktifkan.');
    }

    public function duplicate(LabLayout $labLayout): RedirectResponse
    {
        $newLayout = $labLayout->replicate();
        $newLayout->name = $labLayout->name . ' (Copy)';
        $newLayout->is_published = false;
        $newLayout->is_draft = true;
        $newLayout->created_by = auth()->id();
        $newLayout->save();

        return redirect()->route('lab-layouts.edit', $newLayout)
            ->with('success', 'Layout diduplikasi sebagai draft baru.');
    }

    public function destroy(LabLayout $labLayout): RedirectResponse
    {
        if ($labLayout->is_published) {
            return redirect()->route('lab-layouts.index')
                ->with('error', 'Layout yang dipublikasikan tidak bisa dihapus. Nonaktifkan dulu.');
        }

        $labLayout->delete();

        return redirect()->route('lab-layouts.index')
            ->with('success', 'Layout dihapus.');
    }
}