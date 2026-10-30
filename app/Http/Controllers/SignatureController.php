<?php

namespace App\Http\Controllers;

use App\Models\ReportSignature;
use App\Models\Signature;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class SignatureController extends Controller
{
    /**
     * Daftar TTD laporan beserta laporan yang sedang ditugaskan.
     */
    public function index()
    {
        $signatures = Signature::with('assignments')->orderBy('name')->get();

        return view('settings.signatures.index', [
            'signatures' => $signatures,
            'reports' => Signature::REPORTS,
        ]);
    }

    public function create()
    {
        return view('settings.signatures.form', [
            'signature' => null,
            'reports' => Signature::REPORTS,
            'takenReports' => $this->takenReports(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $this->validateSignature($request);

        $signature = Signature::create([
            'name' => $validated['name'],
            'role' => $validated['role'],
            'image' => $request->hasFile('image')
                ? $request->file('image')->store('signatures', 'public')
                : null,
        ]);

        $this->syncReports($signature, $validated['reports'] ?? []);

        return redirect()
            ->route('settings.signatures.index')
            ->with('success', 'TTD berhasil ditambahkan.');
    }

    public function edit(Signature $signature)
    {
        $signature->load('assignments');

        return view('settings.signatures.form', [
            'signature' => $signature,
            'reports' => Signature::REPORTS,
            'takenReports' => $this->takenReports($signature),
        ]);
    }

    public function update(Request $request, Signature $signature)
    {
        $validated = $this->validateSignature($request);

        $image = $signature->image;

        if ($request->hasFile('image')) {
            if ($image) {
                Storage::disk('public')->delete($image);
            }

            $image = $request->file('image')->store('signatures', 'public');
        }

        $signature->update([
            'name' => $validated['name'],
            'role' => $validated['role'],
            'image' => $image,
        ]);

        $this->syncReports($signature, $validated['reports'] ?? []);

        return redirect()
            ->route('settings.signatures.index')
            ->with('success', 'TTD berhasil diperbarui.');
    }

    public function destroy(Signature $signature)
    {
        if ($signature->image) {
            Storage::disk('public')->delete($signature->image);
        }

        // Penugasan ikut terhapus (FK cascade) — laporan kembali ke TTD default.
        $signature->delete();

        return redirect()
            ->route('settings.signatures.index')
            ->with('success', 'TTD dihapus. Laporan terkait kembali memakai TTD default.');
    }

    private function validateSignature(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'role' => ['required', 'string', 'max:150'],
            'image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:2048'],
            'reports' => ['nullable', 'array'],
            'reports.*' => [Rule::in(array_keys(Signature::REPORTS))],
        ]);
    }

    /**
     * Sinkronisasi penugasan: pilihan terlepas dari TTD lain (report_key
     * unique), lalu assignment TTD ini disamakan dengan pilihan form.
     */
    private function syncReports(Signature $signature, array $reportKeys): void
    {
        $reportKeys = array_values(array_unique($reportKeys));

        if ($reportKeys !== []) {
            ReportSignature::whereIn('report_key', $reportKeys)
                ->where('signature_id', '!=', $signature->id)
                ->delete();
        }

        $signature->assignments()->whereNotIn('report_key', $reportKeys)->delete();

        foreach ($reportKeys as $reportKey) {
            ReportSignature::firstOrCreate(
                ['report_key' => $reportKey],
                ['signature_id' => $signature->id],
            );
        }
    }

    /**
     * Map report_key => nama TTD lain yang sedang memegang laporan tersebut
     * (dipakai form untuk menampilkan indikator "dipakai TTD lain").
     */
    private function takenReports(?Signature $current = null): array
    {
        return ReportSignature::with('signature')
            ->when($current, fn ($query) => $query->where('signature_id', '!=', $current->id))
            ->get()
            ->filter(fn (ReportSignature $assignment) => $assignment->signature)
            ->mapWithKeys(fn (ReportSignature $assignment) => [
                $assignment->report_key => $assignment->signature->name,
            ])
            ->all();
    }
}
