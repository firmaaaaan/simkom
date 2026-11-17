@extends('layouts.app')

@section('title', 'Edit Ambang Suhu - '.$laboratory->name)
@section('header', 'Edit Ambang Suhu Ruangan')

@section('content')
<div class="max-w-2xl space-y-4">
    <a href="{{ route('monitoring-suhu.index') }}" class="inline-flex items-center gap-1.5 text-sm text-gray-500 hover:text-green-600 transition-colors">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" /></svg>
        Kembali ke Daftar Ruangan
    </a>

    @if($errors->any())
        <div class="px-4 py-3 bg-red-50 border border-red-200 text-red-700 rounded-lg text-sm space-y-1">
            @foreach($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
    @endif

    <form action="{{ route('monitoring-suhu.threshold.update', $laboratory) }}" method="POST" class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 space-y-4">
        @csrf
        @method('PUT')

        <div>
            <h2 class="text-base font-semibold text-gray-800">{{ $laboratory->name }}</h2>
            <p class="text-sm text-gray-500 mt-0.5">{{ $laboratory->code ?? '-' }}{{ $laboratory->location ? ' · '.$laboratory->location : '' }}</p>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Suhu Minimum (°C)</label>
                <input type="number" name="temp_min" value="{{ old('temp_min', $laboratory->temp_min) }}" step="0.1" min="0" max="100"
                    placeholder="Mis. 18"
                    class="w-full rounded-lg border-gray-300 border px-3 py-2 text-sm">
                @error('temp_min') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Suhu Maksimum (°C)</label>
                <input type="number" name="temp_max" value="{{ old('temp_max', $laboratory->temp_max) }}" step="0.1" min="0" max="100"
                    placeholder="Mis. 26"
                    class="w-full rounded-lg border-gray-300 border px-3 py-2 text-sm">
                @error('temp_max') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
        </div>

        <p class="text-xs text-gray-500">Kosongkan kedua kolom untuk menghapus ambang. Status suhu hanya muncul jika kedua ambang terisi.</p>

        <div class="flex items-center gap-2 flex-wrap">
            <button type="submit" class="px-6 py-2 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700">Simpan</button>
            <a href="{{ route('monitoring-suhu.index') }}" class="px-6 py-2 text-sm text-gray-600 hover:underline">Batal</a>
        </div>
    </form>
</div>
@endsection
