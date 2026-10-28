<?php

namespace App\Http\Controllers;

use App\Models\TodoLabel;
use Illuminate\Http\Request;

class TodoLabelController extends Controller
{
    private function ownedLabel(string $id): TodoLabel
    {
        return TodoLabel::where('user_id', auth()->id())->findOrFail($id);
    }

    private function rules(): array
    {
        return [
            'name' => 'required|string|max:50',
            'color' => 'required|string|max:7|regex:/^#[0-9A-Fa-f]{6}$/',
        ];
    }

    public function store(Request $request)
    {
        $validated = $request->validate($this->rules());

        $label = TodoLabel::create([
            'user_id' => auth()->id(),
            ...$validated,
        ]);

        return response()->json([
            'label' => ['id' => $label->id, 'name' => $label->name, 'color' => $label->color],
        ], 201);
    }

    public function update(Request $request, string $id)
    {
        $label = $this->ownedLabel($id);

        $validated = $request->validate($this->rules());

        $label->update($validated);

        return response()->json([
            'label' => ['id' => $label->id, 'name' => $label->name, 'color' => $label->color],
        ]);
    }

    public function destroy(string $id)
    {
        $this->ownedLabel($id)->delete();

        return response()->json(['message' => 'Label berhasil dihapus.']);
    }
}
