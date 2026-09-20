<?php

namespace App\Http\Controllers\Admin;

use App\Exports\BorrowingExport;
use App\Http\Controllers\Controller;
use App\Models\ComputerBorrowing;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class BorrowingController extends Controller
{
    /**
     * Query daftar peminjaman dengan filter aktif — dipakai halaman & export.
     */
    private function filteredQuery(Request $request)
    {
        $query = ComputerBorrowing::with(['computer', 'laboratory']);

        if ($status = $request->status) {
            $query->where('status', $status);
        }

        if ($search = $request->search) {
            $query->where(function ($q) use ($search) {
                $q->where('tracking_code', 'like', "%{$search}%")
                  ->orWhere('borrower_name', 'like', "%{$search}%")
                  ->orWhere('borrower_nim', 'like', "%{$search}%");
            });
        }

        return $query;
    }

    public function export(Request $request)
    {
        return Excel::download(
            new BorrowingExport($this->filteredQuery($request)->latest()),
            'peminjaman-' . now()->format('Y-m-d') . '.xlsx'
        );
    }

    public function index(Request $request)
    {
        $borrowings = $this->filteredQuery($request)->latest()->paginate(15)->withQueryString();

        $stats = [
            'total' => ComputerBorrowing::count(),
            'pending' => ComputerBorrowing::where('status', 'Pending')->count(),
            'approved' => ComputerBorrowing::where('status', 'Approved')->count(),
            'returned' => ComputerBorrowing::where('status', 'Returned')->count(),
        ];

        return view('admin.borrowings.index', compact('borrowings', 'stats'));
    }

    public function updateStatus(Request $request, ComputerBorrowing $borrowing)
    {
        $validated = $request->validate([
            'status' => 'required|in:Approved,Rejected',
            'admin_notes' => $request->status === 'Rejected' ? 'required|string|max:500' : 'nullable|string|max:500',
        ]);

        $borrowing->update([
            'status' => $validated['status'],
            'admin_notes' => $validated['admin_notes'] ?? null,
        ]);

        $label = $validated['status'] === 'Approved' ? 'disetujui' : 'ditolak';
        $note = filled($validated['admin_notes'] ?? null) ? ' beserta catatannya' : '';

        return back()->with('success', "Peminjaman {$borrowing->tracking_code} berhasil {$label}{$note}.");
    }
}
