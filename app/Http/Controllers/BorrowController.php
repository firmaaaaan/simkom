<?php

namespace App\Http\Controllers;

use App\Models\ComputerBorrowing;
use App\Models\Computer;
use Illuminate\Http\Request;

class BorrowController extends Controller
{
    public function create(Computer $computer)
    {
        $computer->load('laboratory');

        $borrowedDates = ComputerBorrowing::where('computer_id', $computer->id)
            ->whereIn('status', ['Pending', 'Approved'])
            ->pluck('borrow_date')
            ->toArray();

        return view('pinjam-komputer.create', compact('computer', 'borrowedDates'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'computer_id' => 'required|exists:computers,id',
            'borrower_name' => 'required|string|max:255',
            'borrower_nim' => 'required|string|max:50',
            'borrower_prodi' => 'required|string|max:255',
            'purpose' => 'required|string|max:255',
            'borrow_date' => 'required|date|after_or_equal:today',
            'borrow_time_start' => 'required',
            'borrow_time_end' => 'required|after:borrow_time_start',
        ]);

        $computer = Computer::findOrFail($validated['computer_id']);

        $existingBorrowing = ComputerBorrowing::where('computer_id', $computer->id)
            ->whereIn('status', ['Pending', 'Approved'])
            ->where('borrow_date', $validated['borrow_date'])
            ->where(function ($q) use ($validated) {
                $q->whereBetween('borrow_time_start', [$validated['borrow_time_start'], $validated['borrow_time_end']])
                  ->orWhereBetween('borrow_time_end', [$validated['borrow_time_start'], $validated['borrow_time_end']])
                  ->orWhere(function ($q2) use ($validated) {
                      $q2->where('borrow_time_start', '<=', $validated['borrow_time_start'])
                         ->where('borrow_time_end', '>=', $validated['borrow_time_end']);
                  });
            })
            ->exists();

        if ($existingBorrowing) {
            return back()->withErrors(['borrow_time_start' => 'Komputer sudah dipinjam pada jam tersebut.'])->withInput();
        }

        $borrowing = ComputerBorrowing::create([
            'tracking_code' => ComputerBorrowing::generateTrackingCode(),
            'computer_id' => $computer->id,
            'laboratory_id' => $computer->laboratory_id,
            'borrower_name' => $validated['borrower_name'],
            'borrower_nim' => $validated['borrower_nim'],
            'borrower_prodi' => $validated['borrower_prodi'],
            'purpose' => $validated['purpose'],
            'borrow_date' => $validated['borrow_date'],
            'borrow_time_start' => $validated['borrow_time_start'],
            'borrow_time_end' => $validated['borrow_time_end'],
            'status' => 'Pending',
        ]);

        return redirect()->route('track.search', ['tracking_code' => $borrowing->tracking_code])
            ->with('success', 'Peminjaman berhasil diajukan! Simpan kode tracking Anda.');
    }

    public function trackForm()
    {
        return view('pinjam-komputer.track-form');
    }

    public function track($trackingCode)
    {
        $borrowing = ComputerBorrowing::where('tracking_code', $trackingCode)
            ->with(['computer', 'laboratory'])
            ->firstOrFail();

        return view('pinjam-komputer.show', compact('borrowing'));
    }
}
