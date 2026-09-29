@extends('layouts.app')

@section('title', 'Backup & Restore')
@section('header', 'Backup & Restore Data')

@section('content')
<div x-data="{ confirmText: '' }">
    <div class="mb-6">
        <p class="text-sm text-gray-500">Backup seluruh data aplikasi ke file JSON dan pulihkan kembali bila diperlukan</p>
    </div>

    @if(session('success'))
        <div class="mb-4 px-4 py-3 bg-green-50 border border-green-200 text-green-700 rounded-lg text-sm flex items-center gap-2">
            <svg class="w-5 h-5 text-green-600 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="mb-4 px-4 py-3 bg-red-50 border border-red-200 text-red-700 rounded-lg text-sm flex items-center gap-2">
            <svg class="w-5 h-5 text-red-600 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
            </svg>
            {{ session('error') }}
        </div>
    @endif

    @if($errors->any())
        <div class="mb-4 px-4 py-3 bg-red-50 border border-red-200 text-red-700 rounded-lg text-sm">
            <p class="font-semibold mb-1">File tidak valid:</p>
            <ul class="list-disc list-inside space-y-0.5">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- Backup manual --}}
        <div class="bg-white rounded-xl border border-gray-200 p-6">
            <div class="flex items-start justify-between gap-4 mb-4">
                <div>
                    <h2 class="text-base font-semibold text-gray-800">Backup Data</h2>
                    <p class="text-sm text-gray-500 mt-1">Unduh seluruh data aplikasi sebagai file JSON</p>
                </div>
                <a href="{{ route('backups.download') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700 transition-colors whitespace-nowrap">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" />
                    </svg>
                    Unduh Backup JSON
                </a>
            </div>

            <div class="flex items-center gap-6 mb-4 pb-4 border-b border-gray-100">
                <div>
                    <p class="text-2xl font-bold text-gray-900">{{ $summary['tables'] }}</p>
                    <p class="text-xs text-gray-500">Tabel</p>
                </div>
                <div>
                    <p class="text-2xl font-bold text-gray-900">{{ number_format($summary['rows']) }}</p>
                    <p class="text-xs text-gray-500">Baris data</p>
                </div>
            </div>

            <p class="text-xs font-medium text-gray-500 uppercase tracking-wider mb-2">Ringkasan per tabel</p>
            <div class="max-h-64 overflow-y-auto divide-y divide-gray-100 border border-gray-100 rounded-lg">
                @foreach($summary['details'] as $table => $count)
                    <div class="flex items-center justify-between px-3 py-2 text-sm">
                        <span class="text-gray-600 font-mono">{{ $table }}</span>
                        <span class="text-gray-800 font-medium">{{ number_format($count) }}</span>
                    </div>
                @endforeach
            </div>

            <p class="text-xs text-gray-400 mt-3">Tabel sistem (sesi login, cache, antrian job) tidak ikut dibackup.</p>
        </div>

        <div class="space-y-6">
            {{-- Restore --}}
            <div class="bg-white rounded-xl border border-gray-200 p-6">
                <h2 class="text-base font-semibold text-gray-800">Restore Data</h2>
                <p class="text-sm text-gray-500 mt-1 mb-4">Pulihkan data dari file backup JSON</p>

                <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-4">
                    <div class="flex items-start gap-3">
                        <svg class="w-5 h-5 text-red-500 mt-0.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                        </svg>
                        <div class="text-sm text-red-700">
                            <p class="font-semibold">Peringatan</p>
                            <p class="mt-1">Seluruh data saat ini akan <strong>ditimpa</strong> oleh isi file backup. Disarankan mengunduh backup terlebih dahulu. Operasi berjalan dalam satu transaksi — bila gagal, data lama tetap utuh.</p>
                        </div>
                    </div>
                </div>

                <form action="{{ route('backups.restore') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                    @csrf

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">File Backup (.json) <span class="text-red-500">*</span></label>
                        <input type="file" name="file" required accept=".json,.txt"
                            class="w-full px-4 py-2.5 text-sm border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-green-50 file:text-green-700 hover:file:bg-green-100">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Konfirmasi <span class="text-red-500">*</span></label>
                        <input type="text" name="confirmation" x-model="confirmText" placeholder="Ketik RESTORE"
                            autocomplete="off"
                            class="w-full px-4 py-2.5 text-sm border border-gray-200 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500">
                        <p class="text-xs text-gray-500 mt-1">Ketik <span class="font-mono font-semibold">RESTORE</span> (huruf besar) untuk mengaktifkan tombol.</p>
                    </div>

                    <button type="submit" x-bind:disabled="confirmText !== 'RESTORE'"
                        class="inline-flex items-center gap-2 px-5 py-2.5 bg-red-600 text-white text-sm font-semibold rounded-lg hover:bg-red-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99" />
                        </svg>
                        Restore Sekarang
                    </button>
                </form>
            </div>

            {{-- Backup otomatis --}}
            <div class="bg-white rounded-xl border border-gray-200 p-6">
                <div class="flex items-start justify-between gap-4 mb-1">
                    <div>
                        <h2 class="text-base font-semibold text-gray-800">Backup Otomatis (Bulanan)</h2>
                        <p class="text-sm text-gray-500 mt-1">Berjalan otomatis tanggal 1 tiap bulan pukul 00.10</p>
                    </div>
                </div>
                <p class="text-xs text-gray-400 mb-4">File disimpan di <span class="font-mono">storage/app/backups</span> dan tidak dihapus otomatis.</p>

                <div class="divide-y divide-gray-100 border border-gray-100 rounded-lg">
                    @forelse($files as $file)
                        <div class="flex flex-wrap items-center justify-between gap-3 px-3 py-2.5">
                            <div class="min-w-0">
                                <p class="text-sm font-mono text-gray-800 truncate">{{ $file['name'] }}</p>
                                <p class="text-xs text-gray-500">{{ $file['modified_at'] }} &middot; {{ $file['size'] >= 1048576 ? number_format($file['size'] / 1048576, 2).' MB' : number_format($file['size'] / 1024, 1).' KB' }}</p>
                            </div>
                            <div class="flex items-center gap-2">
                                <a href="{{ route('backups.files.download', $file['name']) }}" class="inline-flex items-center px-3 py-1.5 bg-blue-600 text-white text-xs font-medium rounded-lg hover:bg-blue-700 transition-colors">
                                    Unduh
                                </a>
                                <form action="{{ route('backups.files.destroy', $file['name']) }}" method="POST" class="inline" onsubmit="return confirm('Yakin ingin menghapus file backup ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="inline-flex items-center px-3 py-1.5 bg-red-600 text-white text-xs font-medium rounded-lg hover:bg-red-700 transition-colors">
                                        Hapus
                                    </button>
                                </form>
                            </div>
                        </div>
                    @empty
                        <div class="px-3 py-8 text-center text-sm text-gray-500">
                            Belum ada file backup otomatis. Jalankan <span class="font-mono font-medium">php artisan backup:run</span> untuk membuat backup manual via CLI.
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
