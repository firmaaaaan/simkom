<?php

namespace App\Http\Controllers\Admin;

use App\Exports\LabUsageExport;
use App\Http\Controllers\Controller;
use App\Models\LabUsage;
use App\Models\Laboratory;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class LabUsageController extends Controller
{
    /**
     * Query daftar penggunaan lab dengan filter aktif — dipakai halaman daftar & export.
     */
    private function filteredQuery(Request $request)
    {
        $query = LabUsage::with(['laboratory', 'validatedBy', 'createdBy']);

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

        return $query;
    }

    public function index(Request $request)
    {
        $labUsages = $this->filteredQuery($request)->latest('checked_in_at')->paginate(15)->withQueryString();
        $laboratories = Laboratory::orderBy('name')->get(['id', 'name', 'code']);

        $stats = [
            'total' => LabUsage::count(),
            'in'    => LabUsage::where('status', 'In')->count(),
            'out'   => LabUsage::where('status', 'Out')->count(),
            'today' => LabUsage::whereDate('checked_in_at', today())->count(),
        ];

        return view('lab-usages.index', compact('labUsages', 'laboratories', 'stats'));
    }

    public function export(Request $request)
    {
        return Excel::download(
            new LabUsageExport($this->filteredQuery($request)),
            'penggunaan-lab-' . now()->format('Y-m-d') . '.xlsx'
        );
    }

    /**
     * Aturan validasi input manual (dipakai store & update).
     * Waktu masuk/waktu keluar dikirim dari datetime-local dalam format Y-m-d\TH:i.
     */
    private function rules(): array
    {
        return [
            'laboratory_id' => 'required|exists:laboratories,id',
            'user_name'     => 'required|string|max:255',
            'user_prodi'    => 'required|string|max:255',
            'purpose'       => 'required|string|max:255',
            'status'        => 'required|in:In,Out',
            'checked_in_at' => 'required|date',
            'validated_at'  => 'required_if:status,Out|nullable|date|after_or_equal:checked_in_at',
            'exit_note'     => 'nullable|string|max:500',
        ];
    }

    private function messages(): array
    {
        return [
            'laboratory_id.required'    => 'Laboratorium wajib dipilih.',
            'laboratory_id.exists'      => 'Laboratorium tidak ditemukan.',
            'user_name.required'        => 'Nama lengkap wajib diisi.',
            'user_prodi.required'       => 'Prodi wajib diisi.',
            'purpose.required'          => 'Keperluan wajib diisi.',
            'status.required'           => 'Status wajib dipilih.',
            'status.in'                 => 'Status tidak valid.',
            'checked_in_at.required'    => 'Waktu masuk wajib diisi.',
            'checked_in_at.date'        => 'Waktu masuk tidak valid.',
            'validated_at.required_if'  => 'Waktu keluar wajib diisi untuk status Out.',
            'validated_at.date'         => 'Waktu keluar tidak valid.',
            'validated_at.after_or_equal' => 'Waktu keluar tidak boleh sebelum waktu masuk.',
            'exit_note.max'             => 'Catatan keluar maksimal 500 karakter.',
        ];
    }

    /**
     * Orang yang sama belum boleh berstatus In dua kali di lab yang sama
     * (aturan yang sama dengan check-in via QR). $usage dipakai saat update
     * agar record yang sedang diedit tidak dianggap duplikat dirinya sendiri.
     */
    private function hasActiveDuplicate(array $data, ?LabUsage $usage = null): bool
    {
        if (($data['status'] ?? null) !== 'In') {
            return false;
        }

        return LabUsage::where('laboratory_id', $data['laboratory_id'])
            ->where('status', 'In')
            ->when($usage, fn ($q) => $q->whereKeyNot($usage->id))
            ->get()
            ->contains(fn (LabUsage $active) => strcasecmp(trim($active->user_name), trim($data['user_name'])) === 0
                && strcasecmp(trim($active->user_prodi), trim($data['user_prodi'])) === 0);
    }

    public function store(Request $request)
    {
        $validated = $request->validate($this->rules(), $this->messages());

        if ($this->hasActiveDuplicate($validated)) {
            return back()->withErrors([
                'user_name' => "{$validated['user_name']} masih berstatus In di laboratorium tersebut. Validasi keluar dulu sebelum input ulang.",
            ])->withInput();
        }

        $checkedIn = \Illuminate\Support\Carbon::parse($validated['checked_in_at']);
        $isOut = $validated['status'] === 'Out';

        LabUsage::create([
            'laboratory_id' => $validated['laboratory_id'],
            'user_name'     => $validated['user_name'],
            'user_prodi'    => $validated['user_prodi'],
            'purpose'       => $validated['purpose'],
            'day'           => LabUsage::dayLabel($checkedIn),
            'status'        => $validated['status'],
            'checked_in_at' => $checkedIn,
            'validated_at'  => $isOut ? \Illuminate\Support\Carbon::parse($validated['validated_at']) : null,
            'validated_by'  => $isOut ? $request->user()->id : null,
            'exit_note'     => $validated['exit_note'] ?? null,
            'source'        => 'manual',
            'created_by'    => $request->user()->id,
        ]);

        return back()->with(
            'success',
            $isOut
                ? "Penggunaan lab (sudah keluar) untuk {$validated['user_name']} berhasil dicatat."
                : "Penggunaan lab untuk {$validated['user_name']} berhasil dicatat."
        );
    }

    public function update(Request $request, LabUsage $usage)
    {
        $validated = $request->validate($this->rules(), $this->messages());

        if ($this->hasActiveDuplicate($validated, $usage)) {
            return back()->withErrors([
                'user_name' => "{$validated['user_name']} masih berstatus In di laboratorium tersebut.",
            ])->withInput();
        }

        $checkedIn = \Illuminate\Support\Carbon::parse($validated['checked_in_at']);
        $isOut = $validated['status'] === 'Out';

        $usage->update([
            'laboratory_id' => $validated['laboratory_id'],
            'user_name'     => $validated['user_name'],
            'user_prodi'    => $validated['user_prodi'],
            'purpose'       => $validated['purpose'],
            'day'           => LabUsage::dayLabel($checkedIn),
            'status'        => $validated['status'],
            // Assignment eksplisit: sama seperti validateOut, mencegah MySQL
            // menimpa checked_in_at lewat mekanisme ON UPDATE timestamp.
            'checked_in_at' => $checkedIn,
            'validated_at'  => $isOut ? \Illuminate\Support\Carbon::parse($validated['validated_at']) : null,
            'validated_by'  => $isOut ? ($usage->validated_by ?? $request->user()->id) : null,
            'exit_note'     => $validated['exit_note'] ?? null,
        ]);

        return back()->with('success', "Data penggunaan lab {$validated['user_name']} berhasil diperbarui.");
    }

    public function destroy(LabUsage $usage)
    {
        $name = $usage->user_name;
        $usage->delete();

        return back()->with('success', "Data penggunaan lab {$name} berhasil dihapus.");
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
            // Assignment eksplisit: MySQL tidak auto-update kolom TIMESTAMP yang
            // di-ASSIGN dalam UPDATE, sehingga checked_in_at tetap aman meski
            // migration fix timestamp auto-update belum dijalankan.
            'checked_in_at' => $usage->checked_in_at,
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
