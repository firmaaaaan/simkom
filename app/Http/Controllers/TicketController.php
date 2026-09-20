<?php

namespace App\Http\Controllers;

use App\Exports\TicketExport;
use App\Models\Ticket;
use App\Models\TicketComment;
use App\Models\Laboratory;
use App\Models\Computer;
use App\Models\AcademicYear;
use App\Models\User;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class TicketController extends Controller
{
    /**
     * Query daftar tiket dengan filter aktif — dipakai halaman daftar & export.
     */
    private function filteredQuery(Request $request)
    {
        $query = Ticket::with(['laboratory', 'computer', 'academicYear', 'reporter', 'assignee']);

        if ($search = $request->search) {
            $query->where('title', 'like', "%{$search}%")
                  ->orWhere('category', 'like', "%{$search}%");
        }

        if ($labId = $request->laboratory_id) {
            $query->where('laboratory_id', $labId);
        }

        if ($status = $request->status) {
            $query->where('status', $status);
        }

        if ($priority = $request->priority) {
            $query->where('priority', $priority);
        }

        if ($category = $request->category) {
            $query->where('category', $category);
        }

        // Filter periode (dipakai juga saat batang grafik dashboard diklik).
        $query->forPeriod($request->integer('month') ?: null, $request->integer('year') ?: null);

        return $query;
    }

    public function export(Request $request)
    {
        return Excel::download(
            new TicketExport($this->filteredQuery($request)->latest()),
            'kendala-' . now()->format('Y-m-d') . '.xlsx'
        );
    }

    public function index(Request $request)
    {
        $tickets = $this->filteredQuery($request)->latest()->paginate(10)->withQueryString();

        $month = $request->integer('month') ?: null;
        $year = $request->integer('year') ?: null;
        $laboratories = Laboratory::orderBy('name')->get();
        $years = Ticket::availableYears();

        return view('tickets.index', compact('tickets', 'laboratories', 'years', 'month', 'year'));
    }

    public function create(Request $request)
    {
        $laboratories = Laboratory::orderBy('name')->get();
        $academicYears = AcademicYear::active()->orderBy('start_year', 'desc')->get();
        $users = User::orderBy('name')->get();
        $computers = collect();

        if ($labId = $request->laboratory_id) {
            $computers = Computer::where('laboratory_id', $labId)
                ->orderBy('code')
                ->get();
        }

        return view('tickets.create', compact('laboratories', 'academicYears', 'users', 'computers'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'laboratory_id' => 'required|exists:laboratories,id',
            'computer_id' => 'nullable|exists:computers,id',
            'academic_year_id' => 'required|exists:academic_years,id',
            'assigned_to' => 'nullable|exists:users,id',
            'category' => 'required|in:Komputer,Hardware,Software,Jaringan,Listrik/UPS',
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'priority' => 'required|in:Rendah,Sedang,Tinggi,Darurat',
        ]);

        $validated['reported_by'] = auth()->id();
        $validated['status'] = 'Open';

        Ticket::create($validated);

        return redirect()->route('tickets.index')->with('success', 'Tiket kendala berhasil dibuat.');
    }

    public function show(Ticket $ticket)
    {
        $ticket->load([
            'laboratory',
            'computer',
            'academicYear',
            'reporter',
            'assignee',
            'comments.user',
        ]);

        $users = User::orderBy('name')->get();

        return view('tickets.show', compact('ticket', 'users'));
    }

    public function edit(Ticket $ticket)
    {
        $laboratories = Laboratory::orderBy('name')->get();
        $academicYears = AcademicYear::active()->orderBy('start_year', 'desc')->get();
        $users = User::orderBy('name')->get();
        $computers = Computer::where('laboratory_id', $ticket->laboratory_id)
            ->orderBy('code')
            ->get();

        return view('tickets.edit', compact('ticket', 'laboratories', 'academicYears', 'users', 'computers'));
    }

    public function update(Request $request, Ticket $ticket)
    {
        $validated = $request->validate([
            'laboratory_id' => 'required|exists:laboratories,id',
            'computer_id' => 'nullable|exists:computers,id',
            'academic_year_id' => 'required|exists:academic_years,id',
            'assigned_to' => 'nullable|exists:users,id',
            'category' => 'required|in:Komputer,Hardware,Software,Jaringan,Listrik/UPS',
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'priority' => 'required|in:Rendah,Sedang,Tinggi,Darurat',
            'status' => 'required|in:Open,In Progress,Resolved,Closed',
        ]);

        $ticket->update($validated);

        if ($ticket->computer) {
            $ticket->computer->syncStatusFromTickets();
        }

        return redirect()->route('tickets.index')->with('success', 'Tiket kendala berhasil diperbarui.');
    }

    public function destroy(Ticket $ticket)
    {
        $computer = $ticket->computer;
        $ticket->comments()->delete();
        $ticket->delete();

        if ($computer) {
            $computer->syncStatusFromTickets();
        }

        return redirect()->route('tickets.index')->with('success', 'Tiket kendala berhasil dihapus.');
    }

    public function bulkDestroy(Request $request)
    {
        $request->validate([
            'ids' => 'required|array|min:1',
            'ids.*' => 'exists:tickets,id',
        ]);

        $tickets = Ticket::whereIn('id', $request->ids)->get();
        foreach ($tickets as $ticket) {
            $ticket->comments()->delete();
            $ticket->delete();
        }

        return redirect()->route('tickets.index')->with('success', count($request->ids) . ' tiket berhasil dihapus.');
    }

    public function updateStatus(Request $request, Ticket $ticket)
    {
        $validated = $request->validate([
            'status' => 'required|in:Open,In Progress,Resolved,Closed',
        ]);

        $ticket->update($validated);

        if ($ticket->computer) {
            $ticket->computer->syncStatusFromTickets();
        }

        return redirect()->route('tickets.show', $ticket)->with('success', 'Status tiket berhasil diperbarui.');
    }

    public function addComment(Request $request, Ticket $ticket)
    {
        $validated = $request->validate([
            'message' => 'required|string',
        ]);

        $ticket->comments()->create([
            'user_id' => auth()->id(),
            'message' => $validated['message'],
        ]);

        return redirect()->route('tickets.show', $ticket)->with('success', 'Komentar berhasil ditambahkan.');
    }

    public function deleteComment(Ticket $ticket, TicketComment $comment)
    {
        $comment->delete();

        return redirect()->route('tickets.show', $ticket)->with('success', 'Komentar berhasil dihapus.');
    }
}
