<?php

namespace App\Http\Controllers;

use App\Models\K3Document;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class K3DocumentController extends Controller
{
    public function index(Request $request)
    {
        $documents = K3Document::query()
            ->search($request->string('search')->toString())
            ->kategori($request->string('kategori')->toString())
            ->latest()
            ->paginate(12)
            ->withQueryString();

        return view('k3-documents.index', compact('documents'));
    }

    public function create()
    {
        return view('k3-documents.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'judul' => 'required|string|max:255',
            'kategori' => 'required|string|in:form,sop,peraturan,pelatihan,laporan,lain-lain',
            'nomor_dokumen' => 'nullable|string|max:100',
            'tanggal_berlaku' => 'nullable|date',
            'tanggal_revisi' => 'nullable|date',
            'keterangan' => 'nullable|string|max:1000',
            'file' => 'required|file|mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,jpg,jpeg,png,webp,zip|max:20480',
        ], [
            'judul.required' => 'Judul dokumen wajib diisi.',
            'kategori.required' => 'Kategori wajib dipilih.',
            'file.required' => 'File dokumen wajib diunggah.',
            'file.mimes' => 'Format file tidak diizinkan. Gunakan PDF, DOC, DOCX, XLS, XLSX, PPT, PPTX, JPG, PNG, WEBP, atau ZIP.',
            'file.max' => 'Ukuran file maksimal 20 MB.',
        ]);

        $file = $request->file('file');
        $originalName = $file->getClientOriginalName();
        $extension = strtolower($file->getClientOriginalExtension());
        $filename = Str::uuid().'.'.$extension;
        $path = $file->storeAs('k3-documents', $filename, 'public');

        K3Document::create([
            'judul' => $validated['judul'],
            'kategori' => $validated['kategori'],
            'nomor_dokumen' => $validated['nomor_dokumen'] ?? null,
            'tanggal_berlaku' => $validated['tanggal_berlaku'] ?? null,
            'tanggal_revisi' => $validated['tanggal_revisi'] ?? null,
            'keterangan' => $validated['keterangan'] ?? null,
            'file_name' => $originalName,
            'file_path' => $path,
            'file_extension' => $extension,
            'mime_type' => $file->getMimeType(),
            'file_size' => $file->getSize(),
            'uploaded_by' => auth()->id(),
        ]);

        if ($request->input('action') === 'save_another') {
            return redirect()->route('k3-documents.create')
                ->with('success', 'Dokumen K3 berhasil diunggah. Silakan unggah dokumen berikutnya.');
        }

        return redirect()->route('k3-documents.index')
            ->with('success', 'Dokumen K3 berhasil diunggah.');
    }

    public function show(K3Document $k3Document)
    {
        $k3Document->load('uploader');

        return view('k3-documents.show', compact('k3Document'));
    }

    public function download(K3Document $k3Document)
    {
        if (! Storage::disk('public')->exists($k3Document->file_path)) {
            abort(404, 'File tidak ditemukan.');
        }

        return Storage::disk('public')->download($k3Document->file_path, $k3Document->file_name);
    }

    public function edit(K3Document $k3Document)
    {
        return view('k3-documents.edit', compact('k3Document'));
    }

    public function update(Request $request, K3Document $k3Document)
    {
        $validated = $request->validate([
            'judul' => 'required|string|max:255',
            'kategori' => 'required|string|in:form,sop,peraturan,pelatihan,laporan,lain-lain',
            'nomor_dokumen' => 'nullable|string|max:100',
            'tanggal_berlaku' => 'nullable|date',
            'tanggal_revisi' => 'nullable|date',
            'keterangan' => 'nullable|string|max:1000',
        ], [
            'judul.required' => 'Judul dokumen wajib diisi.',
            'kategori.required' => 'Kategori wajib dipilih.',
        ]);

        $k3Document->update($validated);

        return redirect()->route('k3-documents.index')
            ->with('success', 'Data dokumen K3 berhasil diperbarui.');
    }

    public function destroy(K3Document $k3Document)
    {
        if (Storage::disk('public')->exists($k3Document->file_path)) {
            Storage::disk('public')->delete($k3Document->file_path);
        }

        $k3Document->delete();

        return redirect()->route('k3-documents.index')
            ->with('success', 'Dokumen K3 berhasil dihapus.');
    }

    public function bulkDestroy(Request $request)
    {
        $request->validate([
            'ids' => 'required|array|min:1',
            'ids.*' => 'exists:k3_documents,id',
        ]);

        $documents = K3Document::whereIn('id', $request->ids)->get();

        foreach ($documents as $document) {
            if (Storage::disk('public')->exists($document->file_path)) {
                Storage::disk('public')->delete($document->file_path);
            }
            $document->delete();
        }

        return redirect()->route('k3-documents.index')
            ->with('success', count($documents).' dokumen K3 berhasil dihapus.');
    }
}
