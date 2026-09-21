@extends('layouts.app')

@section('title', 'Import Komponen')
@section('header', 'Import Komponen')

@section('content')
<div class="max-w-2xl">
    <a href="{{ route('components.index') }}" class="inline-flex items-center gap-2 text-sm text-gray-600 hover:text-green-600 transition-colors mb-6">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"/>
        </svg>
        Kembali ke Daftar Komponen
    </a>

    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6">
        <h3 class="text-lg font-bold text-gray-900 mb-1">Import Data Komponen</h3>
        <p class="text-sm text-gray-500 mb-6">Upload file Excel untuk import data komponen</p>

        @if(session('import_error'))
            <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-lg">
                <div class="flex items-start gap-3">
                    <svg class="w-5 h-5 text-red-500 mt-0.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z"/></svg>
                    <div>
                        <p class="text-sm font-semibold text-red-800">Import Gagal</p>
                        <p class="text-sm text-red-700 mt-1">{{ session('import_error') }}</p>
                    </div>
                </div>
            </div>
        @endif

        @if(session('success'))
            <div class="mb-6 p-4 bg-green-50 border border-green-200 rounded-lg">
                <div class="flex items-start gap-3">
                    <svg class="w-5 h-5 text-green-500 mt-0.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <div>
                        <p class="text-sm font-semibold text-green-800">Import Berhasil</p>
                        <p class="text-sm text-green-700 mt-1">{{ session('success') }}</p>
                    </div>
                </div>
            </div>
        @endif

        @if(session('import_errors') && count(session('import_errors')) > 0)
            <div class="mb-6 p-4 bg-amber-50 border border-amber-200 rounded-lg">
                <div class="flex items-start gap-3">
                    <svg class="w-5 h-5 text-amber-500 mt-0.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/></svg>
                    <div>
                        <p class="text-sm font-semibold text-amber-800">{{ count(session('import_errors')) }} Baris Gagal</p>
                        <ul class="mt-1 text-sm text-amber-700 list-disc list-inside space-y-0.5">
                            @foreach(session('import_errors') as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        @endif

        <div class="bg-blue-50 border border-blue-200 rounded-xl p-4 mb-6">
            <h4 class="text-sm font-semibold text-blue-800 mb-2">Format Kolom Excel:</h4>
            <p class="text-xs text-blue-700">Kode (opsional, auto-generate jika kosong) | Nama | Kategori (IoT/Jaringan/Lain-lain) | Merk | Model | Jumlah | Status (Tersedia/Digunakan/Rusak/Maintenance) | Keterangan</p>
            <a href="{{ route('components.template') }}" class="inline-flex items-center gap-2 mt-3 text-sm font-medium text-blue-600 hover:text-blue-700">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" />
                </svg>
                Download Template
            </a>
        </div>

        <form action="{{ route('components.store-import') }}" method="POST" enctype="multipart/form-data" class="space-y-5">
            @csrf

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">File Excel <span class="text-red-500">*</span></label>
                <input type="file" name="file" required accept=".xlsx,.xls,.csv"
                    class="w-full px-4 py-2.5 text-sm border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-green-50 file:text-green-700 hover:file:bg-green-100">
            </div>

            <div class="flex items-center gap-3 pt-2">
                <button type="submit" class="px-6 py-2.5 bg-green-600 text-white text-sm font-semibold rounded-lg hover:bg-green-700 transition-colors">
                    Import Data
                </button>
                <a href="{{ route('components.index') }}" class="px-6 py-2.5 bg-gray-100 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-200 transition-colors">
                    Batal
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
