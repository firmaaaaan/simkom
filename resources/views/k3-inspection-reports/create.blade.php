@extends('layouts.app')

@section('title', 'Buat Laporan Inspeksi K3')
@section('header', 'Buat Laporan Inspeksi K3')

@section('content')
<div class="max-w-6xl mx-auto space-y-6">
    <a href="{{ route('k3-inspection-reports.index') }}" class="inline-flex items-center gap-2 text-sm text-gray-600 hover:text-green-600 transition-colors">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"/>
        </svg>
        Kembali
    </a>

    <form action="{{ route('k3-inspection-reports.store') }}" method="POST" class="space-y-6">
        @csrf
        @include('k3-inspection-reports._form', ['pemeriksaNama' => $pemeriksaNama, 'pemeriksaJabatan' => $pemeriksaJabatan])

        <div class="flex flex-wrap items-center gap-3">
            <button type="submit" class="px-6 py-2.5 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700 transition-colors">
                Simpan Laporan
            </button>
            <button type="submit" name="action" value="save_another" class="px-6 py-2.5 border border-green-600 text-green-700 bg-white text-sm font-medium rounded-lg hover:bg-green-50 transition-colors">
                Simpan &amp; Buat Ulang
            </button>
            <a href="{{ route('k3-inspection-reports.index') }}" class="px-6 py-2.5 bg-gray-100 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-200 transition-colors">
                Batal
            </a>
        </div>
    </form>
</div>
@endsection
