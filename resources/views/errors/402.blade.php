@extends('errors::layout')

@section('title', 'Pembayaran Diperlukan')

@section('content')
<div class="text-center max-w-lg">
    <div class="mb-6">
        <div class="w-24 h-24 mx-auto bg-yellow-50 rounded-full flex items-center justify-center">
            <svg class="w-12 h-12 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
            </svg>
        </div>
    </div>
    <h1 class="text-7xl font-extrabold text-yellow-500 mb-2">402</h1>
    <h2 class="text-2xl font-bold text-gray-900 mb-3">Pembayaran Diperlukan</h2>
    <p class="text-gray-500 mb-8 leading-relaxed">
        Akses ke layanan ini memerlukan pembayaran. Silakan periksa status langganan kamu.
    </p>
    <div class="flex items-center justify-center gap-3">
        <a href="{{ url('/') }}" class="bg-primary-600 text-white px-6 py-2.5 rounded-lg hover:bg-primary-700 transition-colors font-medium">
            Kembali ke Beranda
        </a>
    </div>
</div>
@endsection
