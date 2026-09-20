<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\Computer;
use App\Models\Laboratory;
use App\Models\AcademicYear;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PublicTicketController extends Controller
{
    public function index(Request $request)
    {
        $laboratories = Laboratory::orderBy('name')->get();

        $query = Computer::with('laboratory')->orderBy('code');

        if ($labId = $request->laboratory_id) {
            $query->where('laboratory_id', $labId);
        }

        $computers = $query->get();
        $selectedLab = $request->laboratory_id ? Laboratory::find($request->laboratory_id) : null;

        return view('lapor-kendala.index', compact('computers', 'laboratories', 'selectedLab'));
    }

    public function create(Computer $computer)
    {
        $computer->load('laboratory');
        $academicYears = AcademicYear::active()->orderBy('start_year', 'desc')->get();

        return view('lapor-kendala.create', compact('computer', 'academicYears'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'computer_id' => 'required|exists:computers,id',
            'reporter_name' => 'required|string|max:255',
            'reporter_nim' => 'required|string|max:50',
            'reporter_prodi' => 'required|string|max:255',
            'academic_year_id' => 'required|exists:academic_years,id',
            'category' => 'required|in:Komputer,Hardware,Software,Jaringan,Listrik/UPS',
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'images' => 'nullable|array|max:5',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
            'priority' => 'required|in:Rendah,Sedang,Tinggi,Darurat',
        ]);

        $computer = Computer::findOrFail($validated['computer_id']);

        $images = null;
        if ($request->hasFile('images')) {
            $images = [];
            foreach ($request->file('images') as $file) {
                $images[] = $file->store('ticket-images', 'public');
            }
        }

        $ticket = Ticket::create([
            'tracking_code' => Ticket::generateTrackingCode(),
            'laboratory_id' => $computer->laboratory_id,
            'computer_id' => $computer->id,
            'academic_year_id' => $validated['academic_year_id'],
            'reporter_name' => $validated['reporter_name'],
            'reporter_nim' => $validated['reporter_nim'],
            'reporter_prodi' => $validated['reporter_prodi'],
            'category' => $validated['category'],
            'title' => $validated['title'],
            'description' => $validated['description'],
            'images' => $images,
            'priority' => $validated['priority'],
            'status' => 'Open',
        ]);

        $computer->syncStatusFromTickets();

        return redirect()->route('track.search', ['tracking_code' => $ticket->tracking_code])
            ->with('success', 'Tiket berhasil dibuat! Simpan kode tracking Anda.');
    }

    public function trackForm()
    {
        return view('lapor-kendala.track-form');
    }

    public function track($trackingCode)
    {
        $ticket = Ticket::where('tracking_code', $trackingCode)
            ->with(['laboratory', 'computer', 'academicYear', 'assignee', 'comments.user'])
            ->firstOrFail();

        return view('lapor-kendala.show', compact('ticket'));
    }
}
