<?php

namespace App\Http\Controllers;

use App\Exports\TemperatureReadingExport;
use App\Models\Laboratory;
use App\Models\RoomTemperatureReading;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Maatwebsite\Excel\Facades\Excel;

class RoomTemperatureController extends Controller
{
    /**
     * Daftar ruangan beserta suhu terakhir + status (Normal/Panas/Dingin).
     * Filter search & status dihitung di level koleksi karena status
     * bergantung pada ambang batas masing-masing ruangan.
     */
    public function index(Request $request)
    {
        $search = trim((string) $request->input('search'));
        $statusFilter = $request->input('status');

        $laboratories = Laboratory::with(['latestTemperatureReadingRelation.recordedBy'])
            ->orderBy('name')
            ->get()
            ->map(function (Laboratory $lab) {
                $reading = $lab->latestTemperatureReadingRelation;

                return [
                    'laboratory' => $lab,
                    'reading' => $reading,
                    'status' => $lab->temperatureStatus($reading),
                ];
            })
            ->when($search !== '', function ($items) use ($search) {
                $needle = mb_strtolower($search);

                return $items->filter(fn (array $item) => str_contains(mb_strtolower($item['laboratory']->name), $needle)
                    || str_contains(mb_strtolower((string) $item['laboratory']->code), $needle));
            })
            ->when($statusFilter !== null && $statusFilter !== '', fn ($items) => $items->filter(
                fn (array $item) => ($item['status'] ?? '-') === $statusFilter
            ))
            ->values();

        $page = max(1, (int) $request->integer('page', 1));
        $perPage = 15;
        $total = $laboratories->count();
        $items = $laboratories->slice(($page - 1) * $perPage, $perPage)->values();

        $paginated = new LengthAwarePaginator(
            $items,
            $total,
            $perPage,
            $page,
            ['path' => Paginator::resolveCurrentPath(), 'query' => $request->query()]
        );

        return view('monitoring-suhu.index', [
            'rooms' => $paginated,
            'search' => $search,
            'statusFilter' => $statusFilter,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'laboratory_id' => 'required|exists:laboratories,id',
            'temperature' => 'required|numeric|between:0,100',
            'notes' => 'nullable|string|max:255',
            'recorded_at' => 'required|date',
        ], [
            'laboratory_id.required' => 'Ruangan wajib dipilih.',
            'laboratory_id.exists' => 'Ruangan tidak ditemukan.',
            'temperature.required' => 'Suhu wajib diisi.',
            'temperature.numeric' => 'Suhu harus berupa angka.',
            'temperature.between' => 'Suhu harus antara 0 sampai 100°C.',
            'recorded_at.required' => 'Waktu pencatatan wajib diisi.',
            'recorded_at.date' => 'Waktu pencatatan tidak valid.',
        ]);

        RoomTemperatureReading::create([
            'laboratory_id' => $validated['laboratory_id'],
            'temperature' => $validated['temperature'],
            'notes' => $validated['notes'] ?? null,
            'recorded_by' => auth()->id(),
            'recorded_at' => $validated['recorded_at'],
        ]);

        $laboratory = Laboratory::findOrFail($validated['laboratory_id']);

        if ($request->input('action') === 'save_another') {
            return redirect()->route('monitoring-suhu.show', $laboratory)
                ->with('success', 'Suhu berhasil dicatat. Silakan isi form untuk data berikutnya.');
        }

        return redirect()->route('monitoring-suhu.show', $laboratory)
            ->with('success', "Suhu {$laboratory->name} berhasil dicatat.");
    }

    /**
     * Riwayat monitoring suhu satu ruangan (tabel + filter rentang tanggal).
     */
    public function show(Request $request, Laboratory $laboratory)
    {
        $from = $request->input('from');
        $to = $request->input('to');

        $readings = $laboratory->temperatureReadings()
            ->with(['recordedBy', 'laboratory'])
            ->when($from, fn ($q) => $q->whereDate('recorded_at', '>=', $from))
            ->when($to, fn ($q) => $q->whereDate('recorded_at', '<=', $to))
            ->paginate(15)
            ->withQueryString();

        return view('monitoring-suhu.show', [
            'laboratory' => $laboratory,
            'readings' => $readings,
            'latest' => $laboratory->temperatureReadings()->first(),
            'from' => $from,
            'to' => $to,
            'defaultTime' => now()->format('Y-m-d\TH:i'),
        ]);
    }

    public function destroy(Laboratory $laboratory, RoomTemperatureReading $reading)
    {
        if ($reading->laboratory_id !== $laboratory->id) {
            abort(404);
        }

        $reading->delete();

        return redirect()->route('monitoring-suhu.show', $laboratory)
            ->with('success', 'Entri monitoring suhu berhasil dihapus.');
    }

    /**
     * Endpoint QR Code pada label ruangan.
     * Guest diarahkan ke riwayat publik (read-only), user login ke riwayat ruangan.
     */
    public function scan(Laboratory $laboratory)
    {
        if (auth()->check()) {
            return redirect()->route('monitoring-suhu.show', $laboratory);
        }

        return redirect()->route('monitoring-suhu.riwayat', $laboratory);
    }

    /**
     * Riwayat monitoring suhu (read-only, dapat diakses tanpa login via QR).
     */
    public function riwayat(Laboratory $laboratory)
    {
        $readings = $laboratory->temperatureReadings()
            ->with(['recordedBy', 'laboratory'])
            ->paginate(20);

        return view('monitoring-suhu.riwayat', [
            'laboratory' => $laboratory,
            'readings' => $readings,
            'latest' => $laboratory->temperatureReadings()->first(),
        ]);
    }

    /**
     * Halaman cetak label QR per ruangan. Pilih satu ruangan atau semua.
     */
    public function labels(Request $request)
    {
        $laboratories = Laboratory::orderBy('name')->get();
        $selectedLabId = $request->input('laboratory_id', 'all');

        if ($selectedLabId === 'all') {
            $rooms = $laboratories;
        } else {
            $rooms = $laboratories->where('id', $selectedLabId)->values();
        }

        return view('monitoring-suhu.labels', [
            'laboratories' => $laboratories,
            'selectedLabId' => $selectedLabId,
            'rooms' => $rooms,
        ]);
    }

    /**
     * Query riwayat dengan filter aktif — dipakai export Excel & PDF.
     */
    private function filteredQuery(Request $request)
    {
        $query = RoomTemperatureReading::with(['laboratory', 'recordedBy'])
            ->orderByDesc('recorded_at');

        if ($labId = $request->input('laboratory_id')) {
            $query->where('laboratory_id', $labId);
        }

        if ($from = $request->input('from')) {
            $query->whereDate('recorded_at', '>=', $from);
        }

        if ($to = $request->input('to')) {
            $query->whereDate('recorded_at', '<=', $to);
        }

        return $query;
    }

    public function export(Request $request)
    {
        return Excel::download(
            new TemperatureReadingExport($this->filteredQuery($request)),
            'monitoring-suhu-'.now()->format('Y-m-d').'.xlsx'
        );
    }

    public function exportPdf(Request $request)
    {
        $readings = $this->filteredQuery($request)->get();

        $pdf = Pdf::loadView('monitoring-suhu.export-pdf', [
            'readings' => $readings,
            'from' => $request->input('from'),
            'to' => $request->input('to'),
        ]);

        return $pdf->download('monitoring-suhu-'.now()->format('Y-m-d').'.pdf');
    }
}
