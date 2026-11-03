@extends('layouts.app')

@section('title', 'Ubah Laporan Insiden')
@section('header', 'Ubah Laporan Insiden')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    <a href="{{ route('incident-reports.show', $incidentReport) }}" class="inline-flex items-center gap-2 text-sm text-gray-600 hover:text-green-600 transition-colors">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"/>
        </svg>
        Kembali
    </a>

    <form action="{{ route('incident-reports.update', $incidentReport) }}" method="POST" enctype="multipart/form-data" class="space-y-6">
        @csrf
        @method('PUT')
        <div>
            <h2 class="text-lg font-semibold text-gray-900">Form Laporan Insiden K3</h2>
            <p class="text-sm text-gray-500 mt-0.5">{{ $incidentReport->kode }}</p>
        </div>

        @include('incident-reports._form', ['incidentReport' => $incidentReport])

        <div class="flex flex-wrap items-center gap-3">
            <button type="submit" class="px-6 py-2.5 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700 transition-colors">
                Simpan Perubahan
            </button>
            <a href="{{ route('incident-reports.index') }}" class="px-6 py-2.5 bg-gray-100 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-200 transition-colors">
                Batal
            </a>
        </div>
    </form>
</div>
@endsection
