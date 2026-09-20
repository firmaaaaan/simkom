@extends('layouts.app')

@section('title', 'Detail Pengecekan Perangkat')
@section('header', 'Detail Pengecekan Perangkat')

@section('content')
@php
    $itemCount = \App\Models\DeviceCheck::itemColumnCount();
    $totalCells = $computers->count() * $itemCount;
    $checkedCount = collect($checked)->filter()->count();
    $problemCount = $totalCells - $checkedCount;
@endphp

<div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
    <a href="{{ route('device-checks.index') }}" class="inline-flex items-center gap-1.5 text-sm text-gray-500 hover:text-green-600 transition-colors">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
        </svg>
        Kembali
    </a>
    <div class="flex items-center gap-2">
        <a href="{{ route('device-checks.print', $check) }}" target="_blank"
            class="inline-flex items-center gap-2 px-4 py-2 border border-gray-300 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-50 transition-colors">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0110.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0l.229 2.523a1.125 1.125 0 01-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0021 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 00-1.913-.247M6.34 18H5.25A2.25 2.25 0 013 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 011.913-.247m10.5 0a48.536 48.536 0 00-10.5 0m10.5 0V3.375c0-.621-.504-1.125-1.125-1.125h-8.25c-.621 0-1.125.504-1.125 1.125v3.659" />
            </svg>
            Cetak
        </a>
        <a href="{{ route('device-checks.edit', $check) }}"
            class="inline-flex items-center gap-2 px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700 transition-colors">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125" />
            </svg>
            Edit
        </a>
        <form action="{{ route('device-checks.destroy', $check) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus data pengecekan ini?')">
            @csrf
            @method('DELETE')
            <button type="submit" class="inline-flex items-center gap-2 px-4 py-2 border border-red-200 text-red-600 text-sm font-medium rounded-lg hover:bg-red-50 transition-colors">
                Hapus
            </button>
        </form>
    </div>
</div>

<div class="bg-white rounded-xl border border-gray-200 p-6 mb-6">
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 text-sm">
        <div>
            <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Laboratorium</p>
            <p class="mt-1 font-semibold text-gray-900">{{ $check->laboratory->name ?? '-' }}</p>
        </div>
        <div>
            <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Tahun Ajaran</p>
            <p class="mt-1 font-semibold text-gray-900">{{ $check->academicYear->name ?? '-' }}</p>
            @if($check->academicYear)
                <p class="text-xs text-gray-400">{{ $check->academicYear->periodLabel() }}</p>
            @endif
        </div>
        <div>
            <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Tanggal</p>
            <p class="mt-1 font-semibold text-gray-900">{{ $check->check_date?->translatedFormat('d M Y') ?? '-' }}</p>
        </div>
        <div>
            <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Petugas</p>
            <p class="mt-1 font-semibold text-gray-900">{{ $check->officer_name ?: '-' }}</p>
        </div>
    </div>
</div>

<div class="bg-white rounded-xl border border-gray-200 overflow-hidden mb-6">
    <div class="px-6 py-4 border-b border-gray-100">
        <h2 class="text-base font-semibold text-gray-800">Matriks Pengecekan Perangkat</h2>
        <div class="flex flex-wrap gap-4 mt-2 text-xs">
            <span class="text-gray-600">Total: <strong class="text-gray-900">{{ $computers->count() }}</strong> komputer &times; <strong class="text-gray-900">{{ $itemCount }}</strong> item = <strong class="text-gray-900">{{ $totalCells }}</strong> sel</span>
            <span class="text-green-600">Ada &amp; berfungsi: <strong>{{ $checkedCount }}</strong></span>
            <span class="{{ $problemCount > 0 ? 'text-red-600' : 'text-gray-500' }}">Perlu perhatian: <strong>{{ $problemCount }}</strong></span>
        </div>
    </div>

    @include('device-checks.partials.matrix', ['editable' => false, 'computers' => $computers, 'checked' => $checked])
</div>

@if($check->notes)
    <div class="bg-white rounded-xl border border-gray-200 p-6">
        <h2 class="text-base font-semibold text-gray-800 mb-2">Catatan Tambahan</h2>
        <p class="text-sm text-gray-600 whitespace-pre-line">{{ $check->notes }}</p>
    </div>
@endif
@endsection
