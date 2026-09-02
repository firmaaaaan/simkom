@extends('errors::layout')

@section('title', 'Akses Ditolak')

@section('content')
<div class="text-center max-w-lg">
    <div class="mb-6">
        <div class="w-24 h-24 mx-auto bg-red-50 rounded-full flex items-center justify-center">
            <svg class="w-12 h-12 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
            </svg>
        </div>
    </div>
    <h1 class="text-7xl font-extrabold text-red-500 mb-2">403</h1>
    <h2 class="text-2xl font-bold text-gray-900 mb-3">Akses Ditolak</h2>
    <p class="text-gray-500 mb-8 leading-relaxed">
        Kamu tidak memiliki izin untuk mengakses halaman ini. Hubungi administrator jika kamu merasa ini adalah kesalahan.
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
