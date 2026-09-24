<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LabUsage;
use App\Models\Laboratory;
use Illuminate\Http\Request;

class LabUsageController extends Controller
{
    public function index(Request $request)
    {
        $query = LabUsage::with(['laboratory', 'validatedBy']);

        if ($status = $request->status) {
            $query->where('status', $status);
        }

        if ($labId = $request->laboratory_id) {
            $query->where('laboratory_id', $labId);
        }

        if ($date = $request->date) {
            $query->whereDate('checked_in_at', $date);
        }

        if ($search = $request->search) {
            $query->where(function ($q) use ($search) {
                $q->where('user_name', 'like', "%{$search}%")
                  ->orWhere('user_prodi', 'like', "%{$search}%")
                  ->orWhere('purpose', 'like', "%{$search}%");
            });
        }

        $labUsages = $query->latest('checked_in_at')->paginate(15)->withQueryString();
        $laboratories = Laboratory::orderBy('name')->get(['id', 'name', 'code']);

        $stats = [
            'total' => LabUsage::count(),
            'in'    => LabUsage::where('status', 'In')->count(),
            'out'   => LabUsage::where('status', 'Out')->count(),
            'today' => LabUsage::whereDate('checked_in_at', today())->count(),
        ];

        return view('lab-usages.index', compact('labUsages', 'laboratories', 'stats'));
    }

    public function validateOut(Request $request, LabUsage $usage)
    {
        if ($usage->status !== 'In') {
            return back()->withErrors(['usage' => 'Penggunaan ini sudah divalidasi keluar.']);
        }

        $validated = $request->validate([
            'exit_note' => 'nullable|string|max:500',
        ]);

        $usage->update([
            'status'       => 'Out',
            'validated_at' => now(),
            'validated_by' => $request->user()->id,
            'exit_note'    => $validated['exit_note'] ?? null,
        ]);

        return back()->with('success', "Keluar {$usage->user_name} berhasil divalidasi.");
    }

    public function qrStiker()
    {
        $laboratories = Laboratory::orderBy('name')->get();

        return view('lab-usages.qr-stiker', compact('laboratories'));
    }
}
