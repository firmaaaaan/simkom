@extends('errors::layout')

@section('title', 'Tidak Terotentikasi')

@section('content')
<div class="text-center max-w-lg">
    <div class="mb-6">
        <div class="w-24 h-24 mx-auto bg-primary-50 rounded-full flex items-center justify-center">
            <svg class="w-12 h-12 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
            </svg>
        </div>
    </div>
    <h1 class="text-7xl font-extrabold text-primary-600 mb-2">401</h1>
    <h2 class="text-2xl font-bold text-gray-900 mb-3">Tidak Terotentikasi</h2>
    <p class="text-gray-500 mb-8 leading-relaxed">
        Kamu harus login terlebih dahulu untuk mengakses halaman ini.
    </p>
    <div class="flex items-center justify-center gap-3">
        <a href="{{ url('/') }}" class="bg-primary-600 text-white px-6 py-2.5 rounded-lg hover:bg-primary-700 transition-colors font-medium">
            Kembali ke Beranda
        </a>
    </div>
</div>
@endsection
