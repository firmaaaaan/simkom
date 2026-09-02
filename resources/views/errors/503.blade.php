@extends('errors::layout')

@section('title', 'Layanan Tidak Tersedia')

@section('content')
<div class="text-center max-w-lg">
    <div class="mb-6">
        <div class="w-24 h-24 mx-auto bg-purple-50 rounded-full flex items-center justify-center">
            <svg class="w-12 h-12 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/>
            </svg>
        </div>
    </div>
    <h1 class="text-7xl font-extrabold text-purple-500 mb-2">503</h1>
    <h2 class="text-2xl font-bold text-gray-900 mb-3">Layanan Tidak Tersedia</h2>
    <p class="text-gray-500 mb-8 leading-relaxed">
        Sistem sedang dalam pemeliharaan atau sedang mengalami beban berlebih. Silakan coba lagi beberapa menit kemudian.
    </p>
    <div class="flex items-center justify-center gap-3">
        <a href="{{ url('/') }}" class="bg-primary-600 text-white px-6 py-2.5 rounded-lg hover:bg-primary-700 transition-colors font-medium">
            Kembali ke Beranda
        </a>
        <button onclick="location.reload()" class="text-gray-600 hover:text-gray-900 px-6 py-2.5 transition-colors font-medium">
            Coba Lagi
        </button>
    </div>
</div>
@endsection
