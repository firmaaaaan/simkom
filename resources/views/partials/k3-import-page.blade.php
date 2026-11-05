{{-- Halaman import Excel untuk modul K3.
     Parameter: $title, $subtitle, $indexRoute, $templateRoute, $storeRoute,
                $accept ('.xlsx,.xls' dsb), $formatLines (array deskripsi kolom per sheet). --}}
@extends('layouts.app')

@section('title', 'Import '.$title)
@section('header', 'Import '.$title)

@section('content')
<div class="max-w-2xl">
    <div class="mb-6">
        <a href="{{ route($indexRoute) }}" class="inline-flex items-center gap-1.5 text-sm text-gray-500 hover:text-green-600 transition-colors">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
            </svg>
            Kembali
        </a>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 p-6">
        <h2 class="text-lg font-bold text-gray-800 mb-2">Import {{ $title }} dari Excel</h2>
        <p class="text-sm text-gray-500 mb-6">{{ $subtitle }}</p>

        @if(session('import_error'))
            <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-lg">
                <p class="text-sm font-semibold text-red-800 mb-1 flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                    </svg>
                    Import Gagal
                </p>
                <p class="text-sm text-red-700">{{ session('import_error') }}</p>
            </div>
        @endif

        @if(session('success'))
            <div class="mb-6 p-4 bg-green-50 border border-green-200 rounded-lg">
                <p class="text-sm font-semibold text-green-800 mb-1 flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Import Berhasil
                </p>
                <p class="text-sm text-green-700">{{ session('success') }}</p>
            </div>
        @endif

        @if(session('import_errors') && count(session('import_errors')) > 0)
            <div class="mb-6 p-4 bg-amber-50 border border-amber-200 rounded-lg">
                <p class="text-sm font-semibold text-amber-800">{{ count(session('import_errors')) }} Baris Gagal</p>
                <ul class="mt-1 text-sm text-amber-700 list-disc list-inside space-y-0.5">
                    @foreach(session('import_errors') as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="mb-6 p-4 bg-blue-50 border border-blue-200 rounded-lg">
            <p class="text-sm font-semibold text-blue-800 mb-2">Format kolom yang diperlukan:</p>
            <div class="text-sm text-blue-700 space-y-1">
                @foreach($formatLines as $line)
                    <p><span class="font-medium">{{ $line['sheet'] }}:</span> {{ $line['columns'] }}</p>
                @endforeach
            </div>
        </div>

        <div class="mb-6">
            <a href="{{ route($templateRoute) }}" class="inline-flex items-center gap-2 px-4 py-2 border border-green-600 text-green-600 text-sm font-medium rounded-lg hover:bg-green-50 transition-colors">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" />
                </svg>
                Download Template
            </a>
        </div>

        <form action="{{ route($storeRoute) }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="mb-6">
                <label for="file" class="block text-sm font-medium text-gray-700 mb-2">Pilih File</label>
                <label for="file" class="flex flex-col items-center justify-center px-6 py-10 border-2 border-dashed border-gray-300 rounded-xl cursor-pointer hover:border-green-500 hover:bg-green-50/50 transition-colors">
                    <svg class="w-10 h-10 text-gray-400 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 16.5V9.75m0 0l3 3m-3-3l-3 3M6.75 19.5a4.5 4.5 0 01-1.41-8.775 5.25 5.25 0 0110.233-2.33 3 3 0 013.758 3.848A3.752 3.752 0 0118 19.5H6.75z" />
                    </svg>
                    <span class="text-sm text-gray-600">Klik untuk pilih file ({{ strtoupper(str_replace('.', '', $accept)) }})</span>
                    <input id="file" name="file" type="file" class="hidden" accept="{{ $accept }}" required>
                </label>
                <p id="file-name" class="mt-2 text-sm text-gray-500"></p>
                @error('file')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
            <div class="flex items-center gap-3">
                <button type="submit" class="px-6 py-2.5 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700 transition-colors">
                    Import Data
                </button>
                <a href="{{ route($indexRoute) }}" class="px-6 py-2.5 bg-gray-100 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-200 transition-colors">
                    Batal
                </a>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    document.getElementById('file').addEventListener('change', function (e) {
        const fileName = e.target.files[0] ? e.target.files[0].name : '';
        document.getElementById('file-name').textContent = fileName ? 'File: ' + fileName : '';
    });
</script>
@endpush
@endsection
