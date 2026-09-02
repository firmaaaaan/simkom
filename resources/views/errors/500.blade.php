@extends('errors::layout')

@section('title', 'Kesalahan Server')

@section('content')
<div class="text-center max-w-lg">
    <div class="mb-6">
        <div class="w-24 h-24 mx-auto bg-red-50 rounded-full flex items-center justify-center">
            <svg class="w-12 h-12 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4.5c-.77-.833-2.694-.833-3.464 0L3.34 16.5c-.77.833.192 2.5 1.732 2.5z"/>
            </svg>
        </div>
    </div>
    <h1 class="text-7xl font-extrabold text-red-500 mb-2">500</h1>
    <h2 class="text-2xl font-bold text-gray-900 mb-3">Kesalahan Server</h2>
    <p class="text-gray-500 mb-8 leading-relaxed">
        Terjadi kesalahan tak terduga di server. Tim kami sudah diberitahu dan sedang memperbaikinya. Coba lagi dalam beberapa saat.
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
