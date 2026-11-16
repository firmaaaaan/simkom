@extends('layouts.app')

@section('title', 'Input Suhu')
@section('header', 'Input Monitoring Suhu')

@section('content')
<div class="max-w-2xl">
    @if($errors->any())
        <div class="px-4 py-3 bg-red-50 border border-red-200 text-red-700 rounded-lg text-sm mb-4 space-y-1">
            @foreach($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
    @endif

    <form action="{{ route('monitoring-suhu.store') }}" method="POST" class="bg-white rounded-xl border border-gray-200 p-6 space-y-4">
        @csrf
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Ruangan</label>
            <select name="laboratory_id" required class="w-full rounded-lg border-gray-300 border px-3 py-2 text-sm">
                <option value="">Pilih Ruangan</option>
                @foreach($laboratories as $lab)
                    <option value="{{ $lab->id }}"
                        {{ old('laboratory_id', $laboratory?->id) == $lab->id ? 'selected' : '' }}>
                        {{ $lab->name }}{{ $lab->code ? ' ('.$lab->code.')' : '' }}
                    </option>
                @endforeach
            </select>
            @error('laboratory_id') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Suhu (°C)</label>
            <input type="number" name="temperature" value="{{ old('temperature') }}" step="0.1" min="0" max="100" required
                placeholder="Mis. 24.5"
                class="w-full rounded-lg border-gray-300 border px-3 py-2 text-sm">
            @error('temperature') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
            @if($laboratory && $laboratory->temp_min !== null && $laboratory->temp_max !== null)
                <p class="text-xs text-gray-400 mt-1">Ambang normal ruangan ini: {{ $laboratory->temp_min + 0 }}–{{ $laboratory->temp_max + 0 }}°C</p>
            @endif
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Waktu Pencatatan</label>
            <input type="datetime-local" name="recorded_at" value="{{ old('recorded_at', $defaultTime) }}" required
                class="w-full rounded-lg border-gray-300 border px-3 py-2 text-sm">
            @error('recorded_at') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Catatan (opsional)</label>
            <textarea name="notes" rows="3" maxlength="255" class="w-full rounded-lg border-gray-300 border px-3 py-2 text-sm">{{ old('notes') }}</textarea>
            @error('notes') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
        </div>
        <div class="flex items-center gap-2">
            <button type="submit" name="action" value="save" class="px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700">Simpan</button>
            <button type="submit" name="action" value="save_another" class="px-4 py-2 border border-green-600 text-green-600 text-sm font-medium rounded-lg hover:bg-green-50">Simpan & Tambah Lagi</button>
            <a href="{{ route('monitoring-suhu.index') }}" class="px-4 py-2 text-sm text-gray-600 hover:underline">Batal</a>
        </div>
    </form>
</div>
@endsection
