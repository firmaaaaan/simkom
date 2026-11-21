<?php

namespace App\Http\Controllers;

use App\Models\Alat;
use App\Models\AlatCheck;
use App\Models\Laboratory;
use App\Services\SimpttInventarisService;
use Illuminate\Http\Request;
use RuntimeException;

class AlatController extends Controller
{
    private const VIEW_ALIASES = ['view-alat', 'view-kartu-alat', 'view-alat-laboratorium', 'view-equipment', 'view-kartu-kendali-alat'];

    private const CREATE_ALIASES = ['create-alat', 'create-kartu-alat', 'create-alat-laboratorium', 'create-equipment', 'create-kartu-kendali-alat'];

    private const EDIT_ALIASES = ['edit-alat', 'edit-kartu-alat', 'edit-alat-laboratorium', 'edit-equipment', 'edit-kartu-kendali-alat'];

    private const DELETE_ALIASES = ['delete-alat', 'delete-kartu-alat', 'delete-alat-laboratorium', 'delete-equipment', 'delete-kartu-kendali-alat'];

    private const PRINT_ALIASES = ['print-alat', 'print-kartu-alat', 'print-alat-laboratorium', 'print-equipment', 'print-kartu-kendali-alat'];

    private function canView(): bool
    {
        return auth()->user()->hasAnyPermission(self::VIEW_ALIASES);
    }

    private function canCreate(): bool
    {
        return auth()->user()->hasAnyPermission(self::CREATE_ALIASES);
    }

    private function canEdit(): bool
    {
        return auth()->user()->hasAnyPermission(self::EDIT_ALIASES);
    }

    private function canDelete(): bool
    {
        return auth()->user()->hasAnyPermission(self::DELETE_ALIASES);
    }

    private function canPrint(): bool
    {
        return auth()->user()->hasAnyPermission(self::PRINT_ALIASES);
    }

    public function index(Request $request)
    {
        abort_unless($this->canView(), 403);

        $laboratories = Laboratory::orderBy('name')->get();
        $selectedLabId = $request->input('laboratory_id');

        $alat = Alat::with('laboratory')
            ->when($selectedLabId && $selectedLabId !== 'all', fn ($q) => $q->where('laboratory_id', $selectedLabId))
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('alat.index', compact('alat', 'laboratories', 'selectedLabId'));
    }

    public function create()
    {
        abort_unless($this->canCreate(), 403);

        return view('alat.create', [
            'laboratories' => Laboratory::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        abort_unless($this->canCreate(), 403);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:50|unique:alat,code',
            'merk_type' => 'nullable|string|max:255',
            'laboratory_id' => 'nullable|exists:laboratories,id',
            'location' => 'nullable|string|max:255',
            'status' => 'nullable|string|max:50',
            'description' => 'nullable|string',
        ]);

        $validated['code'] = ($validated['code'] ?? null) ?: Alat::generateCode();

        Alat::create($validated);

        if ($request->input('action') === 'save_another') {
            return redirect()->route('alat.create')
                ->with('success', 'Alat berhasil ditambahkan. Silakan isi form untuk data berikutnya.');
        }

        return redirect()->route('alat.index')->with('success', 'Alat berhasil ditambahkan.');
    }

    /**
     * Simpan alat hasil scan barcode. Dipakai alur "pilih lab lalu scan":
     * setiap scan langsung membuat data alat pada laboratorium terpilih.
     *
     * Payload berupa kode saja dilookup ke API SIMPTT Inventaris, lalu data
     * hasil lookup (no inventaris, nama, merk/type, lokasi, status, keterangan)
     * yang dipakai untuk record lokal. Payload JSON berisi data lengkap tetap
     * didukung tanpa lookup (kompatibilitas lama).
     */
    public function scanStore(Request $request, SimpttInventarisService $inventaris)
    {
        abort_unless($this->canCreate(), 403);

        $validated = $request->validate([
            'laboratory_id' => 'required|exists:laboratories,id',
            'code' => 'required|string|max:50',
            'name' => 'nullable|string|max:255',
            'merk_type' => 'nullable|string|max:255',
            'location' => 'nullable|string|max:255',
            'status' => 'nullable|string|max:50',
            'description' => 'nullable|string',
        ]);

        $existing = Alat::where('code', $validated['code'])->first();

        if ($existing) {
            return response()->json([
                'ok' => false,
                'message' => 'Kode '.$validated['code'].' sudah terdaftar sebagai '.$existing->name.'.',
            ], 422);
        }

        $simptt = null;

        if (empty($validated['name'])) {
            try {
                $simptt = $inventaris->scan($validated['code']);
            } catch (RuntimeException $e) {
                return response()->json([
                    'ok' => false,
                    'message' => 'Gagal menghubungi SIMPTT Inventaris. Coba lagi atau gunakan input manual.',
                ], 502);
            }

            if ($simptt === null) {
                return response()->json([
                    'ok' => false,
                    'message' => 'Kode '.$validated['code'].' tidak ditemukan di SIMPTT Inventaris. '
                        .'Pastikan barcode berisi no inventaris (format 00.00.00.00.00.000) '
                        .'atau hubungi pengembang SIMPTT agar kode barcode didukung.',
                ], 422);
            }
        }

        $code = ($simptt['no_inventaris'] ?? null) ?: $validated['code'];

        if ($code !== $validated['code']) {
            $existing = Alat::where('code', $code)->first();

            if ($existing) {
                return response()->json([
                    'ok' => false,
                    'message' => 'Kode '.$code.' sudah terdaftar sebagai '.$existing->name.'.',
                ], 422);
            }
        }

        $merk = ($simptt['merk_type'] ?? null) ?: ($validated['merk_type'] ?? null);
        $merk = is_string($merk) ? trim((string) preg_replace('/\s+/', ' ', $merk)) : null;
        $merk = $merk ?: null;

        $name = ($simptt['nama_alat'] ?? null) ?: ($validated['name'] ?? null) ?: $code;

        $alat = Alat::create([
            'laboratory_id' => $validated['laboratory_id'],
            'name' => $name,
            'code' => $code,
            'merk_type' => $merk,
            'location' => ($simptt['lokasi'] ?? null) ?: ($validated['location'] ?? null) ?: null,
            'status' => ($simptt['status'] ?? null) ?: ($validated['status'] ?? null) ?: null,
            'description' => ($simptt['keterangan'] ?? null) ?: ($validated['description'] ?? null) ?: null,
        ]);

        $message = $alat->name.' ('.$alat->code.') tersimpan.';

        if (! empty($simptt['laboratorium'])) {
            $message .= ' Lab: '.$simptt['laboratorium'].'.';
        }

        $payload = [
            'ok' => true,
            'message' => $message,
            'alat' => [
                'id' => $alat->id,
                'name' => $alat->name,
                'code' => $alat->code,
                'status' => $alat->status,
                'laboratory' => $alat->laboratory?->name,
            ],
        ];

        if ($simptt !== null) {
            $payload['inventaris'] = $simptt;
        }

        return response()->json($payload, 201);
    }

    /**
     * Daftar alat pada satu laboratorium — untuk panel samping di form tambah,
     * menampilkan alat yang sudah ada sekaligus hasil scan terbaru.
     */
    public function labList(Request $request)
    {
        abort_unless($this->canView() || $this->canCreate(), 403);

        $validated = $request->validate([
            'laboratory_id' => 'required|exists:laboratories,id',
        ]);

        $lab = Laboratory::findOrFail($validated['laboratory_id']);

        $alat = Alat::where('laboratory_id', $lab->id)
            ->orderBy('name')
            ->get()
            ->map(fn (Alat $item) => [
                'id' => $item->id,
                'name' => $item->name,
                'code' => $item->code,
                'status' => $item->status,
            ]);

        return response()->json([
            'ok' => true,
            'laboratory' => [
                'id' => $lab->id,
                'name' => $lab->name,
            ],
            'alat' => $alat,
        ]);
    }

    public function show(Alat $alat)
    {
        abort_unless($this->canView(), 403);

        return view('alat.show', compact('alat'));
    }

    public function edit(Alat $alat)
    {
        abort_unless($this->canEdit(), 403);

        return view('alat.edit', [
            'alat' => $alat,
            'laboratories' => Laboratory::orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Alat $alat)
    {
        abort_unless($this->canEdit(), 403);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:alat,code,'.$alat->id,
            'merk_type' => 'nullable|string|max:255',
            'laboratory_id' => 'nullable|exists:laboratories,id',
            'location' => 'nullable|string|max:255',
            'status' => 'nullable|string|max:50',
            'description' => 'nullable|string',
        ]);

        $alat->update($validated);

        return redirect()->route('alat.index')->with('success', 'Alat berhasil diperbarui.');
    }

    /**
     * Pindahkan alat ke laboratorium/ruangan lain tanpa mengubah kode
     * (No Inventaris) maupun QR code yang sudah tercetak.
     */
    public function pindah(Request $request, Alat $alat)
    {
        abort_unless($this->canEdit(), 403);

        $validated = $request->validate([
            'laboratory_id' => 'required|exists:laboratories,id',
            'location' => 'nullable|string|max:255',
        ]);

        $lab = Laboratory::findOrFail($validated['laboratory_id']);

        $data = ['laboratory_id' => $lab->id];

        if (array_key_exists('location', $validated)) {
            $data['location'] = $validated['location'];
        }

        $alat->update($data);

        return redirect()->route('alat.show', $alat)
            ->with('success', 'Alat dipindahkan ke '.$lab->name.'. QR code tetap sama.');
    }

    public function destroy(Alat $alat)
    {
        abort_unless($this->canDelete(), 403);

        $alat->delete();

        return redirect()->route('alat.index')->with('success', 'Alat berhasil dihapus.');
    }

    /**
     * Endpoint yang dipakai QR Code pada label alat.
     * Guest diarahkan ke riwayat (read-only), user login diarahkan ke input kartu kendali.
     */
    public function scan(Alat $alat)
    {
        if (auth()->check()) {
            return redirect()->route('alat.checks', $alat);
        }

        return redirect()->route('alat.riwayat', $alat);
    }

    /**
     * Riwayat kartu kendali alat (read-only, dapat diakses tanpa login via QR).
     */
    public function riwayat(Alat $alat)
    {
        $alat->load(['laboratory', 'checks.checkedBy']);

        return view('alat.riwayat', [
            'alat' => $alat,
            'checks' => $alat->checks,
        ]);
    }

    public function kartu(Request $request)
    {
        abort_unless($this->canView(), 403);

        $laboratories = Laboratory::orderBy('name')->get();
        $selectedLabId = $request->input('laboratory_id');

        $alat = collect();
        if ($selectedLabId && $selectedLabId !== 'all') {
            $alat = Alat::with(['laboratory', 'checks'])->where('laboratory_id', $selectedLabId)->orderBy('name')->get();
        } elseif ($selectedLabId === 'all') {
            $alat = Alat::with(['laboratory', 'checks'])->orderBy('name')->get();
        }

        return view('alat.kartu-kendali', compact('laboratories', 'selectedLabId', 'alat'));
    }

    /**
     * Halaman input + riwayat kartu kendali satu alat.
     * Menampilkan form entri baru dan tabel riwayat (maks. 33 baris kartu).
     */
    public function checks(Alat $alat)
    {
        abort_unless($this->canView(), 403);

        $alat->load(['laboratory', 'checks.checkedBy']);

        return view('alat.checks', [
            'alat' => $alat,
            'checks' => $alat->checks,
            'statusList' => AlatCheck::STATUSES,
            'defaultDate' => now()->toDateString(),
        ]);
    }

    /**
     * Simpan satu baris entri kartu kendali (tanggal + Baik/Rusak + keterangan).
     */
    public function storeCheck(Request $request, Alat $alat)
    {
        abort_unless($this->canCreate(), 403);

        $validated = $request->validate([
            'check_date' => 'required|date',
            'status' => 'required|in:Baik,Rusak',
            'notes' => 'nullable|string|max:255',
        ]);

        AlatCheck::create([
            'alat_id' => $alat->id,
            'check_date' => $validated['check_date'],
            'status' => $validated['status'],
            'notes' => $validated['notes'] ?? null,
            'checked_by' => auth()->id(),
        ]);

        return redirect()->route('alat.checks', $alat)
            ->with('success', 'Entri kartu kendali berhasil disimpan.');
    }

    public function destroyCheck(Alat $alat, AlatCheck $check)
    {
        abort_unless($this->canDelete(), 403);

        if ($check->alat_id !== $alat->id) {
            abort(404);
        }

        $check->delete();

        return redirect()->route('alat.checks', $alat)
            ->with('success', 'Entri kartu kendali berhasil dihapus.');
    }

    /**
     * Halaman cetak label alat (stiker ber-QR Code) per laboratorium.
     * Pilih satu lab atau "Semua Laboratorium" lalu cetak.
     */
    public function labels(Request $request)
    {
        abort_unless($this->canPrint(), 403);

        $laboratories = Laboratory::orderBy('name')->get();
        $selectedLabId = $request->input('laboratory_id');
        $selectedLab = null;
        $alat = collect();

        if ($selectedLabId === 'all') {
            $alat = Alat::with('laboratory')->orderBy('laboratory_id')->orderBy('name')->get();
            $selectedLab = (object) ['id' => 'all', 'name' => 'Semua Laboratorium'];
        } elseif ($selectedLabId) {
            $selectedLab = Laboratory::find($selectedLabId);
            if ($selectedLab) {
                $alat = Alat::with('laboratory')
                    ->where('laboratory_id', $selectedLab->id)
                    ->orderBy('name')
                    ->get();
            }
        }

        return view('alat.labels', compact('laboratories', 'selectedLabId', 'selectedLab', 'alat'));
    }

    public function kartuPrint(Request $request)
    {
        abort_unless($this->canPrint(), 403);

        $request->validate([
            'laboratory_id' => 'required',
        ]);

        $selectedLabId = $request->input('laboratory_id');

        if ($selectedLabId === 'all') {
            $laboratories = Laboratory::orderBy('name')->get();
            $groups = [];
            foreach ($laboratories as $lab) {
                $items = Alat::where('laboratory_id', $lab->id)->with('checks')->orderBy('name')->get();
                if ($items->isNotEmpty()) {
                    $groups[] = ['lab' => $lab, 'items' => $items];
                }
            }
            $selectedLab = null;
        } else {
            $selectedLab = Laboratory::findOrFail($selectedLabId);
            $groups = [['lab' => $selectedLab, 'items' => Alat::where('laboratory_id', $selectedLab->id)->with('checks')->orderBy('name')->get()]];
        }

        return view('alat.kartu-kendali-print', compact('groups', 'selectedLab'));
    }

    /**
     * Cetak kartu kendali satu alat (sudah terisi riwayat entri).
     */
    public function printOne(Alat $alat)
    {
        abort_unless($this->canPrint(), 403);

        $alat->load(['laboratory', 'checks']);

        $groups = [
            'lab' => $alat->laboratory,
            'items' => [$alat],
        ];

        return view('alat.kartu-kendali-print', [
            'groups' => [$groups],
            'selectedLab' => $alat->laboratory,
        ]);
    }
}
