<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\ComputerBorrowing;
use Illuminate\Http\Request;

class PublicTrackerController extends Controller
{
    public function index()
    {
        return view('track.index');
    }

    public function search(Request $request)
    {
        $request->validate([
            'tracking_code' => 'required|string|max:20',
        ]);

        $code = strtoupper(trim($request->tracking_code));

        if (str_starts_with($code, 'TKT-')) {
            $ticket = Ticket::where('tracking_code', $code)
                ->with(['laboratory', 'computer', 'academicYear', 'assignee', 'comments.user'])
                ->first();

            if ($ticket) {
                return view('track.show-ticket', compact('ticket'));
            }
        } elseif (str_starts_with($code, 'BMJ-')) {
            $borrowing = ComputerBorrowing::where('tracking_code', $code)
                ->with(['computer', 'laboratory'])
                ->first();

            if ($borrowing) {
                return view('track.show-borrowing', compact('borrowing'));
            }
        }

        return back()->withErrors(['tracking_code' => 'Kode tracking tidak ditemukan. Pastikan kode sudah benar.'])->withInput();
    }
}
