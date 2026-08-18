@extends('layouts.public')

@section('title', 'Peminjaman Berhasil')

@section('content')
<div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="p-8">
            <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            </div>
            <h1 class="text-2xl font-bold text-slate-900 mb-2">Peminjaman Berhasil Dicatat</h1>
            <p class="text-slate-600 mb-6">Silakan tunjukkan bukti peminjaman kepada admin di laboratorium.</p>

            <a href="{{ route('peminjaman-komputer.index') }}" class="bg-primary-600 text-white px-6 py-2.5 rounded-lg hover:bg-primary-700 transition-colors font-medium">
                Kembali ke Daftar Komputer
            </a>
        </div>
    </div>
</div>
@endsection