@extends('layouts.app')

@section('title', 'Buat Lembar Periksa APAR')
@section('header', 'Buat Lembar Periksa APAR')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <a href="{{ route('apar-inspections.index') }}" class="inline-flex items-center gap-2 text-sm text-gray-600 hover:text-green-600 transition-colors">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"/>
        </svg>
        Kembali
    </a>

    @if(session('success'))
        <div class="px-4 py-3 bg-green-50 border border-green-200 text-green-700 rounded-lg text-sm flex items-center gap-2">
            <svg class="w-5 h-5 text-green-600 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            {{ session('success') }}
        </div>
    @endif

    <form action="{{ route('apar-inspections.store') }}" method="POST" class="space-y-6">
        @csrf
        @include('apar-inspections._form', ['defaultDiperiksaOleh' => $defaultDiperiksaOleh])

        <div class="flex flex-wrap items-center gap-3">
            <button type="submit" class="px-6 py-2.5 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700 transition-colors">
                Simpan Lembar Periksa
            </button>
            <button type="submit" name="action" value="save_another" class="px-6 py-2.5 border border-green-600 text-green-700 bg-white text-sm font-medium rounded-lg hover:bg-green-50 transition-colors">
                Simpan &amp; Buat Ulang
            </button>
            <a href="{{ route('apar-inspections.index') }}" class="px-6 py-2.5 bg-gray-100 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-200 transition-colors">
                Batal
            </a>
        </div>
    </form>
</div>
@endsection
