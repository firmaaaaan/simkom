@extends('layouts.app')

@section('title', 'Tambah Alat')
@section('header', 'Tambah Alat Laboratorium')

@section('content')
<div class="max-w-2xl">
    <form action="{{ route('alat.store') }}" method="POST" class="bg-white rounded-xl border border-gray-200 p-6 space-y-4">
        @csrf
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Nama Alat</label>
            <input type="text" name="name" value="{{ old('name') }}" required class="w-full rounded-lg border-gray-300 border px-3 py-2 text-sm">
            @error('name') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">No Inventaris</label>
            <input type="text" name="code" value="{{ old('code') }}" class="w-full rounded-lg border-gray-300 border px-3 py-2 text-sm" placeholder="Kosongkan untuk otomatis">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Merk/Type</label>
            <input type="text" name="merk_type" value="{{ old('merk_type') }}" class="w-full rounded-lg border-gray-300 border px-3 py-2 text-sm">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Laboratorium</label>
            <select name="laboratory_id" class="w-full rounded-lg border-gray-300 border px-3 py-2 text-sm">
                <option value="">-</option>
                @foreach($laboratories as $lab)
                    <option value="{{ $lab->id }}" {{ old('laboratory_id') == $lab->id ? 'selected' : '' }}>{{ $lab->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Lokasi/Ruangan</label>
            <input type="text" name="location" value="{{ old('location') }}" class="w-full rounded-lg border-gray-300 border px-3 py-2 text-sm">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
            <input type="text" name="status" value="{{ old('status') }}" class="w-full rounded-lg border-gray-300 border px-3 py-2 text-sm">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Keterangan</label>
            <textarea name="description" rows="3" class="w-full rounded-lg border-gray-300 border px-3 py-2 text-sm">{{ old('description') }}</textarea>
        </div>
        <div class="flex items-center gap-2">
            <button type="submit" name="action" value="save" class="px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700">Simpan</button>
            <button type="submit" name="action" value="save_another" class="px-4 py-2 border border-green-600 text-green-600 text-sm font-medium rounded-lg hover:bg-green-50">Simpan & Tambah Lagi</button>
            <a href="{{ route('alat.index') }}" class="px-4 py-2 text-sm text-gray-600 hover:underline">Batal</a>
        </div>
    </form>
</div>
@endsection
