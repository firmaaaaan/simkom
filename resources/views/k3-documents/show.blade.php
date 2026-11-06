@extends('layouts.app')

@section('title', 'Detail Dokumen K3')
@section('header', 'Detail Dokumen K3 Laboratorium')

@section('content')
<div class="max-w-5xl mx-auto space-y-6">
    <a href="{{ route('k3-documents.index') }}" class="inline-flex items-center gap-2 text-sm text-gray-600 hover:text-green-600 transition-colors">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"/>
        </svg>
        Kembali
    </a>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-4 mb-6">
            <div>
                <h3 class="text-lg font-semibold text-gray-900">{{ $k3Document->judul }}</h3>
                <p class="text-sm text-gray-500 mt-0.5">{{ $k3Document->kode }} &middot; {{ strtoupper($k3Document->file_extension) }} &middot; {{ $k3Document->file_size_human }}</p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('k3-documents.download', $k3Document) }}" class="px-3 py-1.5 rounded-lg bg-green-50 text-green-700 text-xs font-medium hover:bg-green-100 transition-colors">Unduh</a>
                @if(auth()->user()->hasPermission('edit-k3-documents'))
                <a href="{{ route('k3-documents.edit', $k3Document) }}" class="px-3 py-1.5 rounded-lg bg-amber-50 text-amber-700 text-xs font-medium hover:bg-amber-100 transition-colors">Ubah</a>
                @endif
                @if(auth()->user()->hasPermission('delete-k3-documents'))
                <form method="POST" action="{{ route('k3-documents.destroy', $k3Document) }}" onsubmit="return confirm('Hapus dokumen ini?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="px-3 py-1.5 rounded-lg bg-red-50 text-red-700 text-xs font-medium hover:bg-red-100 transition-colors">Hapus</button>
                </form>
                @endif
            </div>
        </div>

        <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-5 text-sm">
            <div>
                <dt class="text-gray-500">Kategori</dt>
                <dd class="mt-1 font-medium text-gray-900">{{ $k3Document->kategori_label }}</dd>
            </div>
            <div>
                <dt class="text-gray-500">Nomor Dokumen</dt>
                <dd class="mt-1 font-medium text-gray-900">{{ $k3Document->nomor_dokumen ?: '-' }}</dd>
            </div>
            <div>
                <dt class="text-gray-500">Tanggal Berlaku</dt>
                <dd class="mt-1 font-medium text-gray-900">{{ $k3Document->tanggal_berlaku?->translatedFormat('d F Y') ?: '-' }}</dd>
            </div>
            <div>
                <dt class="text-gray-500">Tanggal Revisi</dt>
                <dd class="mt-1 font-medium text-gray-900">{{ $k3Document->tanggal_revisi?->translatedFormat('d F Y') ?: '-' }}</dd>
            </div>
            <div>
                <dt class="text-gray-500">Nama File</dt>
                <dd class="mt-1 font-medium text-gray-900 break-all">{{ $k3Document->file_name }}</dd>
            </div>
            <div>
                <dt class="text-gray-500">Diunggah Oleh</dt>
                <dd class="mt-1 font-medium text-gray-900">{{ $k3Document->uploader->name ?? '-' }} &middot; {{ $k3Document->created_at->translatedFormat('d M Y H:i') }}</dd>
            </div>
            <div class="sm:col-span-2">
                <dt class="text-gray-500">Keterangan</dt>
                <dd class="mt-1 text-gray-900 whitespace-pre-line">{{ $k3Document->keterangan ?: '-' }}</dd>
            </div>
        </dl>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <h3 class="text-base font-semibold text-gray-900 mb-4">Pratinjau Dokumen</h3>
        @php($ext = strtolower($k3Document->file_extension ?? ''))
        @if(! $fileExists)
            <div class="px-4 py-6 text-center text-sm text-gray-500 bg-gray-50 border border-gray-200 rounded-lg">File tidak ditemukan di penyimpanan.</div>
        @elseif($ext === 'pdf')
            <iframe src="{{ asset('storage/'.$k3Document->file_path) }}" type="application/pdf" title="Pratinjau {{ $k3Document->judul }}" class="w-full h-[75vh] rounded-lg border border-gray-200"></iframe>
        @elseif(in_array($ext, ['jpg', 'jpeg', 'png', 'webp'], true))
            <img src="{{ asset('storage/'.$k3Document->file_path) }}" alt="{{ $k3Document->judul }}" class="w-full rounded-lg border border-gray-200">
        @else
            <div class="px-4 py-6 text-center bg-gray-50 border border-gray-200 rounded-lg">
                <p class="text-sm text-gray-500">Pratinjau tidak tersedia untuk tipe file {{ strtoupper($ext) ?: '-' }}.</p>
                <a href="{{ route('k3-documents.download', $k3Document) }}" class="inline-flex items-center gap-2 mt-3 px-4 py-2 rounded-lg bg-green-600 text-white text-sm font-medium hover:bg-green-700 transition-colors">Unduh Dokumen</a>
            </div>
        @endif
    </div>
</div>
@endsection
