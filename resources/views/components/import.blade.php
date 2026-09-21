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

        <div class="bg-blue-50 border border-blue-200 rounded-xl p-4 mb-6">
            <h4 class="text-sm font-semibold text-blue-800 mb-2">Format Kolom Excel:</h4>
            <p class="text-xs text-blue-700">Kode | Nama | Kategori (IoT/Jaringan/Lain-lain) | Merk | Model | Jumlah | Status (Tersedia/Digunakan/Rusak/Maintenance) | Keterangan</p>
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
