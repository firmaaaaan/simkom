@extends('errors::layout')

@section('title', 'Halaman Tidak Ditemukan')

@section('content')
<div class="text-center max-w-lg">
    <div class="mb-6">
        <div class="w-24 h-24 mx-auto bg-primary-50 rounded-full flex items-center justify-center">
            <svg class="w-12 h-12 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
        </div>
    </div>
    <h1 class="text-7xl font-extrabold text-primary-600 mb-2">404</h1>
    <h2 class="text-2xl font-bold text-gray-900 mb-3">Halaman Tidak Ditemukan</h2>
    <p class="text-gray-500 mb-8 leading-relaxed">
        Sepertinya halaman yang kamu cari sudah dipindahkan, dihapus, atau tidak pernah ada.
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
