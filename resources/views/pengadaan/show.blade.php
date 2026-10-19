@extends('layouts.app')

@section('title', 'Detail Pengadaan')
@section('header', 'Detail Pengadaan')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    {{-- Back Link --}}
    <a href="{{ route('pengadaan.index') }}" class="inline-flex items-center gap-2 text-sm text-gray-600 hover:text-green-600 transition-colors">
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
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
            <div>
                <h3 class="text-lg font-semibold text-gray-900">{{ $pengadaan->nama_barang }}</h3>
                <p class="text-sm text-gray-500 mt-0.5">{{ $pengadaan->kode }}</p>
            </div>
            <div class="flex items-center gap-2">
                @php
                    $statusColors = [
                        'Proses' => 'bg-yellow-100 text-yellow-700',
                        'Acc' => 'bg-green-100 text-green-700',
                        'Ditolak' => 'bg-red-100 text-red-700',
                    ];
                @endphp
                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium {{ $statusColors[$pengadaan->status] ?? '' }}">{{ $pengadaan->status }}</span>
                @if(auth()->user()->hasPermission('edit-pengadaan') && $pengadaan->status === 'Proses')
                <form method="POST" action="{{ route('pengadaan.update-status', $pengadaan) }}">
                    @csrf
                    @method('PATCH')
                    <input type="hidden" name="status" value="Acc">
                    <button type="submit" class="px-3 py-1.5 rounded-lg bg-green-600 text-white text-xs font-medium hover:bg-green-700 transition-colors">ACC</button>
                </form>
                <form method="POST" action="{{ route('pengadaan.update-status', $pengadaan) }}">
                    @csrf
                    @method('PATCH')
                    <input type="hidden" name="status" value="Ditolak">
                    <button type="submit" class="px-3 py-1.5 rounded-lg bg-red-600 text-white text-xs font-medium hover:bg-red-700 transition-colors" onclick="return confirm('Tolak pengadaan ini?')">Tolak</button>
                </form>
                @endif
                @if(auth()->user()->hasPermission('edit-pengadaan'))
                <a href="{{ route('pengadaan.edit', $pengadaan) }}" class="px-3 py-1.5 rounded-lg bg-amber-50 text-amber-700 text-xs font-medium hover:bg-amber-100 transition-colors">Ubah</a>
                @endif
            </div>
        </div>

        <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-4 text-sm">
            <div>
                <dt class="text-gray-500">Kategori</dt>
                <dd class="mt-1 font-medium text-gray-900">{{ $pengadaan->kategori }}</dd>
            </div>
            <div>
                <dt class="text-gray-500">Tanggal Pengadaan</dt>
                <dd class="mt-1 font-medium text-gray-900">{{ $pengadaan->tanggal->translatedFormat('d F Y') }}</dd>
            </div>
            <div>
                <dt class="text-gray-500">Jumlah</dt>
                <dd class="mt-1 font-medium text-gray-900">{{ $pengadaan->jumlah }} unit</dd>
            </div>
            <div>
                <dt class="text-gray-500">Harga Satuan</dt>
                <dd class="mt-1 font-medium text-gray-900">Rp {{ number_format($pengadaan->harga, 0, ',', '.') }}</dd>
            </div>
            <div>
                <dt class="text-gray-500">Total Harga</dt>
                <dd class="mt-1 font-medium text-gray-900">Rp {{ number_format($pengadaan->total, 0, ',', '.') }}</dd>
            </div>
            <div>
                <dt class="text-gray-500">Diajukan Oleh</dt>
                <dd class="mt-1 font-medium text-gray-900">{{ $pengadaan->creator->name ?? '-' }}</dd>
            </div>
            <div>
                <dt class="text-gray-500">Semester</dt>
                <dd class="mt-1 font-medium text-gray-900">{{ $pengadaan->academicYear->name ?? '-' }}</dd>
            </div>
            @if($pengadaan->keterangan)
            <div class="sm:col-span-2">
                <dt class="text-gray-500">Keterangan</dt>
                <dd class="mt-1 text-gray-900 whitespace-pre-line">{{ $pengadaan->keterangan }}</dd>
            </div>
            @endif
        </dl>
    </div>
</div>
@endsection
