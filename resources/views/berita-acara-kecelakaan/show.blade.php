@extends('layouts.app')

@section('title', 'Detail Berita Acara Kecelakaan')
@section('header', 'Detail Berita Acara Kecelakaan')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <a href="{{ route('berita-acara-kecelakaan.index') }}" class="inline-flex items-center gap-2 text-sm text-gray-600 hover:text-green-600 transition-colors">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"/>
        </svg>
        Kembali
    </a>

    @if(session('success'))
        <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg text-sm">
            {{ session('success') }}
        </div>
    @endif

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-4 mb-6">
            <div>
                <h3 class="text-lg font-semibold text-gray-900">{{ $report->nama_korban }}</h3>
                <p class="text-sm text-gray-500 mt-0.5">{{ $report->kode }}</p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('berita-acara-kecelakaan.print', $report) }}" target="_blank" class="px-3 py-1.5 rounded-lg bg-green-50 text-green-700 text-xs font-medium hover:bg-green-100 transition-colors">Cetak</a>
                @if(auth()->user()->hasPermission('edit-accident-reports'))
                <a href="{{ route('berita-acara-kecelakaan.edit', $report) }}" class="px-3 py-1.5 rounded-lg bg-amber-50 text-amber-700 text-xs font-medium hover:bg-amber-100 transition-colors">Ubah</a>
                @endif
                @if(auth()->user()->hasPermission('delete-accident-reports'))
                <form method="POST" action="{{ route('berita-acara-kecelakaan.destroy', $report) }}" onsubmit="return confirm('Hapus berita acara kecelakaan ini?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="px-3 py-1.5 rounded-lg bg-red-50 text-red-700 text-xs font-medium hover:bg-red-100 transition-colors">Hapus</button>
                </form>
                @endif
            </div>
        </div>

        <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-5 text-sm">
            <div>
                <dt class="text-gray-500">Tanggal Berita Acara</dt>
                <dd class="mt-1 font-medium text-gray-900">{{ $report->hari }}, {{ $report->tanggal_tanda_tangan }}</dd>
            </div>
            <div>
                <dt class="text-gray-500">Yang Menerangkan</dt>
                <dd class="mt-1 font-medium text-gray-900">{{ $report->nama_pelapor }} ({{ $report->jabatan_pelapor }})</dd>
            </div>
            <div>
                <dt class="text-gray-500">Nama Korban</dt>
                <dd class="mt-1 font-medium text-gray-900">{{ $report->nama_korban }}</dd>
            </div>
            <div>
                <dt class="text-gray-500">Alamat Korban</dt>
                <dd class="mt-1 font-medium text-gray-900">{{ $report->alamat_korban }}</dd>
            </div>
            <div>
                <dt class="text-gray-500">Tempat Tanggal Lahir</dt>
                <dd class="mt-1 font-medium text-gray-900">{{ $report->ttl_korban }}</dd>
            </div>
            <div>
                <dt class="text-gray-500">Unit/Status</dt>
                <dd class="mt-1 font-medium text-gray-900">{{ $report->unit_status_korban }}</dd>
            </div>
            <div>
                <dt class="text-gray-500">Saksi I / Saksi II</dt>
                <dd class="mt-1 font-medium text-gray-900">{{ $report->saksi_satu }} / {{ $report->saksi_dua }}</dd>
            </div>
            <div>
                <dt class="text-gray-500">Dicatat Sistem Oleh</dt>
                <dd class="mt-1 font-medium text-gray-900">{{ $report->creator->name ?? '-' }}</dd>
            </div>
        </dl>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 space-y-6">
        <div>
            <h3 class="text-sm font-semibold text-gray-900 mb-3">Uraian Kejadian</h3>
            <ol class="list-decimal list-inside space-y-2 text-sm text-gray-700">
                @forelse($report->uraian_rows as $uraian)
                    <li>{{ $uraian }}</li>
                @empty
                    <li class="text-gray-400 list-none">-</li>
                @endforelse
            </ol>
        </div>

        <div class="pt-4 border-t border-gray-100">
            <h3 class="text-sm font-semibold text-gray-900 mb-2">Dampak Kecelakaan</h3>
            <p class="text-sm text-gray-700 whitespace-pre-line">{{ $report->dampak }}</p>
        </div>
    </div>
</div>
@endsection
