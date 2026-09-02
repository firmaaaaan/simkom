@extends('errors::layout')

@section('title', 'Terlalu Banyak Permintaan')

@section('content')
<div class="text-center max-w-lg">
    <div class="mb-6">
        <div class="w-24 h-24 mx-auto bg-orange-50 rounded-full flex items-center justify-center">
            <svg class="w-12 h-12 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13 10V3L4 14h7v7l9-11h-7z"/>
            </svg>
        </div>
    </div>
    <h1 class="text-7xl font-extrabold text-orange-500 mb-2">429</h1>
    <h2 class="text-2xl font-bold text-gray-900 mb-3">Terlalu Banyak Permintaan</h2>
    <p class="text-gray-500 mb-8 leading-relaxed">
        Kamu melakukan terlalu banyak permintaan dalam waktu singkat. Tunggu beberapa saat lalu coba lagi.
    </p>
    <div class="flex items-center justify-center gap-3">
        <a href="{{ url('/') }}" class="bg-primary-600 text-white px-6 py-2.5 rounded-lg hover:bg-primary-700 transition-colors font-medium">
            Kembali ke Beranda
        </a>
        <button onclick="history.back()" class="text-gray-600 hover:text-gray-900 px-6 py-2.5 transition-colors font-medium">
            Halaman Sebelumnya
        </button>
    </div>
</div>
@endsection
