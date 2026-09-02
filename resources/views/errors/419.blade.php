@extends('errors::layout')

@section('title', 'Session Habis')

@section('content')
<div class="text-center max-w-lg">
    <div class="mb-6">
        <div class="w-24 h-24 mx-auto bg-yellow-50 rounded-full flex items-center justify-center">
            <svg class="w-12 h-12 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        </div>
    </div>
    <h1 class="text-7xl font-extrabold text-yellow-500 mb-2">419</h1>
    <h2 class="text-2xl font-bold text-gray-900 mb-3">Session Habis</h2>
    <p class="text-gray-500 mb-8 leading-relaxed">
        Sesi kamu sudah berakhir karena terlalu lama tidak aktif. Silakan muat ulang halaman dan coba lagi.
    </p>
    <div class="flex items-center justify-center gap-3">
        <a href="{{ url('/') }}" class="bg-primary-600 text-white px-6 py-2.5 rounded-lg hover:bg-primary-700 transition-colors font-medium">
            Kembali ke Beranda
        </a>
        <button onclick="location.reload()" class="text-gray-600 hover:text-gray-900 px-6 py-2.5 transition-colors font-medium">
            Muat Ulang
        </button>
    </div>
</div>
@endsection
