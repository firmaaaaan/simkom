@extends('layouts.app')

@section('title', 'Ubah Dokumen K3')
@section('header', 'Ubah Dokumen K3 Laboratorium')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">
    <a href="{{ route('k3-documents.index') }}" class="inline-flex items-center gap-2 text-sm text-gray-600 hover:text-green-600 transition-colors">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"/>
        </svg>
        Kembali
    </a>

    @if($errors->any())
        <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg text-sm">
            <ul class="list-disc list-inside space-y-1">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('k3-documents.update', $k3Document) }}" method="POST" class="space-y-6">
        @csrf
        @method('PUT')
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 space-y-5">
            <h3 class="text-sm font-semibold text-gray-900">Informasi Dokumen</h3>
            <p class="text-xs text-gray-500">File tidak dapat diganti. Untuk mengganti file, hapus dokumen ini lalu unggah kembali.</p>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Judul Dokumen <span class="text-red-500">*</span></label>
                <input type="text" name="judul" value="{{ old('judul', $k3Document->judul) }}" required class="w-full px-4 py-2.5 text-sm border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Kategori <span class="text-red-500">*</span></label>
                    <select name="kategori" required class="w-full px-4 py-2.5 text-sm border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                        @foreach(\App\Models\K3Document::KATEGORI as $key => $label)
                            <option value="{{ $key }}" {{ old('kategori', $k3Document->kategori) == $key ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nomor Dokumen</label>
                    <input type="text" name="nomor_dokumen" value="{{ old('nomor_dokumen', $k3Document->nomor_dokumen) }}" class="w-full px-4 py-2.5 text-sm border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Berlaku</label>
                    <input type="date" name="tanggal_berlaku" value="{{ old('tanggal_berlaku', $k3Document->tanggal_berlaku?->format('Y-m-d')) }}" class="w-full px-4 py-2.5 text-sm border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Revisi</label>
                    <input type="date" name="tanggal_revisi" value="{{ old('tanggal_revisi', $k3Document->tanggal_revisi?->format('Y-m-d')) }}" class="w-full px-4 py-2.5 text-sm border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Keterangan</label>
                <textarea name="keterangan" rows="3" class="w-full px-4 py-2.5 text-sm border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">{{ old('keterangan', $k3Document->keterangan) }}</textarea>
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-3">
            <button type="submit" class="px-6 py-2.5 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700 transition-colors">
                Simpan Perubahan
            </button>
            <a href="{{ route('k3-documents.index') }}" class="px-6 py-2.5 bg-gray-100 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-200 transition-colors">
                Batal
            </a>
        </div>
    </form>
</div>
@endsection
