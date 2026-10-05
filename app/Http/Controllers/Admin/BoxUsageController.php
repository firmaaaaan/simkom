<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BoxReturnNote;
use App\Models\BoxUsage;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class BoxUsageController extends Controller
{
    /**
     * Aturan validasi peminjaman box manual (dipakai store & update).
     * Waktu pinjam/kembali dikirim dari datetime-local dalam format Y-m-d\TH:i.
     */
    private function rules(): array
    {
        return [
            'box_id'      => 'required|exists:boxes,id',
            'user_name'   => 'required|string|max:255',
            'user_nim'    => 'required|string|max:50',
            'user_kelas'  => 'nullable|string|max:100',
            'status'      => 'required|in:Using,Returned',
            'used_at'     => 'required|date',
            'returned_at' => 'required_if:status,Returned|nullable|date|after_or_equal:used_at',
            'note'        => 'nullable|string|max:500',
        ];
    }

    private function messages(): array
    {
        return [
            'box_id.required'            => 'Box wajib dipilih.',
            'box_id.exists'              => 'Box tidak ditemukan.',
            'user_name.required'         => 'Nama peminjam wajib diisi.',
            'user_nim.required'          => 'NIM wajib diisi.',
            'status.required'            => 'Status wajib dipilih.',
            'status.in'                  => 'Status tidak valid.',
            'used_at.required'           => 'Waktu pinjam wajib diisi.',
            'used_at.date'               => 'Waktu pinjam tidak valid.',
            'returned_at.required_if'    => 'Waktu kembali wajib diisi untuk status Dikembalikan.',
            'returned_at.date'           => 'Waktu kembali tidak valid.',
            'returned_at.after_or_equal' => 'Waktu kembali tidak boleh sebelum waktu pinjam.',
            'note.max'                   => 'Catatan maksimal 500 karakter.',
        ];
    }

    /**
     * Kotak yang sedang dipinjam orang lain tidak boleh dipinjamkan lagi
     * (aturan yang sama dengan scan QR). Hanya berlaku untuk status Using —
     * riwayat yang sudah Returned boleh dicatat meski box kini dipinjam lain.
     * $usage dipakai saat update agar record yang sedang diedit tidak dianggap
     * bentrok dengan dirinya sendiri.
     */
    private function activeConflict(array $data, ?BoxUsage $usage = null): ?BoxUsage
    {
        if (($data['status'] ?? null) !== 'Using') {
            return null;
        }

        return BoxUsage::with('box')
            ->where('box_id', $data['box_id'])
            ->where('status', 'Using')
            ->when($usage, fn ($q) => $q->whereKeyNot($usage->id))
            ->get()
            ->first();
    }

    public function store(Request $request)
    {
        $validated = $request->validate($this->rules(), $this->messages());

        if ($conflict = $this->activeConflict($validated)) {
            return back()->withErrors([
                'box_id' => "Box ini sedang digunakan oleh {$conflict->user_name}. Kembalikan dulu sebelum input manual.",
            ])->withInput();
        }

        $isReturned = $validated['status'] === 'Returned';

        $usage = BoxUsage::create([
            'box_id'      => $validated['box_id'],
            'user_name'   => $validated['user_name'],
            'user_nim'    => $validated['user_nim'],
            'user_kelas'  => $validated['user_kelas'] ?? null,
            'status'      => $validated['status'],
            'used_at'     => Carbon::parse($validated['used_at']),
            'returned_at' => $isReturned ? Carbon::parse($validated['returned_at']) : null,
            'source'      => 'manual',
            'created_by'  => $request->user()->id,
        ]);

        if ($isReturned && filled($validated['note'] ?? null)) {
            BoxReturnNote::create([
                'box_usage_id' => $usage->id,
                'note'         => $validated['note'],
            ]);
        }

        return back()->with(
            'success',
            $isReturned
                ? "Peminjaman box (sudah kembali) untuk {$validated['user_name']} berhasil dicatat."
                : "Peminjaman box untuk {$validated['user_name']} berhasil dicatat."
        );
    }

    public function update(Request $request, BoxUsage $usage)
    {
        $validated = $request->validate($this->rules(), $this->messages());

        if ($conflict = $this->activeConflict($validated, $usage)) {
            return back()->withErrors([
                'box_id' => "Box ini sedang digunakan oleh {$conflict->user_name}.",
            ])->withInput();
        }

        $isReturned = $validated['status'] === 'Returned';

        $usage->update([
            'box_id'      => $validated['box_id'],
            'user_name'   => $validated['user_name'],
            'user_nim'    => $validated['user_nim'],
            'user_kelas'  => $validated['user_kelas'] ?? null,
            'status'      => $validated['status'],
            'used_at'     => Carbon::parse($validated['used_at']),
            'returned_at' => $isReturned ? Carbon::parse($validated['returned_at']) : null,
        ]);

        // Catatan pengembalian hanya berlaku untuk status Returned.
        if ($isReturned) {
            $note = $validated['note'] ?? null;
            if (filled($note)) {
                $usage->returnNote()->updateOrCreate([], ['note' => $note]);
            } else {
                $usage->returnNote()->delete();
            }
        } else {
            $usage->returnNote()->delete();
        }

        return back()->with('success', "Data peminjaman box {$validated['user_name']} berhasil diperbarui.");
    }

    public function destroy(BoxUsage $usage)
    {
        $name = $usage->user_name;
        $usage->delete(); // catatan pengembalian ikut terhapus (cascade)

        return back()->with('success', "Data peminjaman box {$name} berhasil dihapus.");
    }
}
