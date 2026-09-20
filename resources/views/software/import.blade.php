@extends('layouts.app')

@section('title', 'Import Software')
@section('header', 'Import Data Software')

@section('content')
<div class="max-w-2xl">
    <div class="mb-6">
        <a href="{{ route('software.index') }}" class="inline-flex items-center gap-1.5 text-sm text-gray-500 hover:text-green-600 transition-colors">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
            </svg>
            Kembali
        </a>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 p-6">
        <h2 class="text-lg font-bold text-gray-800 mb-2">Import Software dari Excel</h2>
        <p class="text-sm text-gray-500 mb-6">Upload file Excel (.xlsx, .xls, .csv) untuk mengimport data software secara batch.</p>

        <div class="mb-6 p-4 bg-blue-50 border border-blue-200 rounded-lg">
            <div class="flex items-start gap-3">
                <svg class="w-5 h-5 text-blue-600 mt-0.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z" />
                </svg>
                <div>
                    <p class="text-sm font-medium text-blue-800">Format kolom yang diperlukan:</p>
                    <p class="text-xs text-blue-700 mt-1">Kode, Nama, Versi, Kategori, Jenis Lisensi, Jumlah Lisensi, Status, Keterangan</p>
                    <p class="text-xs text-blue-700 mt-1">Kode harus unik. Jika kode sudah ada, data akan diupdate.</p>
                </div>
            </div>
        </div>

        <div class="mb-6">
            <a href="{{ route('software.template') }}" class="inline-flex items-center gap-2 px-4 py-2 border border-green-600 text-green-600 text-sm font-medium rounded-lg hover:bg-green-50 transition-colors">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" />
                </svg>
                Download Template
            </a>
        </div>

        <form action="{{ route('software.store-import') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="mb-6">
                <label for="file" class="block text-sm font-medium text-gray-700 mb-2">Pilih File</label>
                <div class="flex items-center justify-center w-full">
                    <label for="file" class="flex flex-col items-center justify-center w-full h-32 border-2 border-dashed border-gray-300 rounded-lg cursor-pointer hover:border-green-500 hover:bg-green-50 transition-colors">
                        <div class="flex flex-col items-center justify-center pt-5 pb-6">
                            <svg class="w-8 h-8 mb-2 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5" />
                            </svg>
                            <p class="mb-1 text-sm text-gray-500"><span class="font-semibold">Klik untuk upload</span></p>
                            <p class="text-xs text-gray-400">XLSX, XLS, atau CSV (maks. 10MB)</p>
                        </div>
                        <input id="file" name="file" type="file" class="hidden" accept=".xlsx,.xls,.csv" required>
                    </label>
                </div>
                <p id="file-name" class="mt-2 text-sm text-gray-500"></p>
                @error('file')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex items-center gap-3">
                <button type="submit" class="inline-flex items-center gap-2 px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700 transition-colors">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5" />
                    </svg>
                    Import Data
                </button>
                <a href="{{ route('software.index') }}" class="px-4 py-2 text-sm text-gray-600 hover:text-gray-800">Batal</a>
            </div>
        </form>
    </div>
</div>

<script>
    document.getElementById('file').addEventListener('change', function(e) {
        const fileName = e.target.files[0] ? e.target.files[0].name : '';
        document.getElementById('file-name').textContent = fileName ? 'File: ' + fileName : '';
    });
</script>
@endsection
