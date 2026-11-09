@extends('layouts.app')

@section('title', 'Ubah Jadwal Pemeliharaan')
@section('header', 'Ubah Jadwal Pemeliharaan (FM-02)')

@section('content')
<div class="max-w-6xl mx-auto space-y-6">
    <a href="{{ route('jadwal-pemeliharaan.show', $jadwalPemeliharaan) }}" class="inline-flex items-center gap-2 text-sm text-gray-600 hover:text-green-600 transition-colors">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"/>
        </svg>
        Kembali
    </a>

    @if($errors->any())
        <div class="px-4 py-3 bg-red-50 border border-red-200 text-red-700 rounded-lg text-sm">
            <p class="font-semibold mb-1">Periksa kembali formulir:</p>
            <ul class="list-disc list-inside space-y-0.5">
                @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('jadwal-pemeliharaan.update', $jadwalPemeliharaan) }}" method="POST" class="space-y-6">
        @csrf
        @method('PUT')
        @include('jadwal-pemeliharaan._form')

        <div class="flex flex-wrap items-center gap-3">
            <button type="submit" class="px-6 py-2.5 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700 transition-colors">
                Simpan Perubahan
            </button>
            <a href="{{ route('jadwal-pemeliharaan.show', $jadwalPemeliharaan) }}" class="px-6 py-2.5 bg-gray-100 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-200 transition-colors">
                Batal
            </a>
        </div>
    </form>
</div>
@endsection
