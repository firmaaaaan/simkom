@extends('layouts.app')

@section('title', 'Buat Layout Denah Baru')

@section('content')
<div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="mb-6">
        <a href="{{ route('lab-layouts.index') }}" class="inline-flex items-center gap-2 text-sm text-gray-600 hover:text-green-600 transition-colors mb-6">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
            </svg>
            Kembali ke Daftar Layout
        </a>
        <h1 class="text-2xl font-bold text-gray-900">Buat Layout Denah Baru</h1>
        <p class="text-sm text-gray-500 mt-1">Pilih laboratorium untuk layout ini</p>
    </div>

    @if($errors->any())
        <div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-lg text-red-800">
            <ul class="list-disc list-inside space-y-1">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('lab-layouts.store') }}" method="POST" class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6 space-y-6">
        @csrf

        <div>
            <label for="laboratory_id" class="block text-sm font-medium text-gray-700 mb-1">Laboratorium <span class="text-red-500">*</span></label>
            <select id="laboratory_id" name="laboratory_id" required
                    class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-colors">
                <option value="">-- Pilih Laboratorium --</option>
                @foreach($laboratories as $lab)
                    <option value="{{ $lab->id }}" {{ old('laboratory_id') == $lab->id ? 'selected' : '' }}>
                        {{ $lab->name }} ({{ $lab->code }})
                    </option>
                @endforeach
            </select>
        </div>

        <div>
            <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Nama Layout <span class="text-red-500">*</span></label>
            <input type="text" id="name" name="name" required
                   value="{{ old('name') }}"
                   class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-colors"
                   placeholder="Contoh: Layout Ruang A v1">
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div>
                <label for="grid_cols" class="block text-sm font-medium text-gray-700 mb-1">Kolom Grid <span class="text-red-500">*</span></label>
                <input type="number" id="grid_cols" name="grid_cols" required min="4" max="50"
                       value="{{ old('grid_cols', 12) }}"
                       class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-colors">
            </div>
            <div>
                <label for="grid_rows" class="block text-sm font-medium text-gray-700 mb-1">Baris Grid <span class="text-red-500">*</span></label>
                <input type="number" id="grid_rows" name="grid_rows" required min="3" max="50"
                       value="{{ old('grid_rows', 8) }}"
                       class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-colors">
            </div>
            <div>
                <label for="cell_size" class="block text-sm font-medium text-gray-700 mb-1">Ukuran Cell (px) <span class="text-red-500">*</span></label>
                <input type="number" id="cell_size" name="cell_size" required min="50" max="200"
                       value="{{ old('cell_size', 100) }}"
                       class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-colors">
            </div>
        </div>

        <div>
            <label for="background_color" class="block text-sm font-medium text-gray-700 mb-1">Warna Background <span class="text-red-500">*</span></label>
            <div class="flex items-center gap-3">
                <input type="color" id="background_color" name="background_color" required
                       value="{{ old('background_color', '#ffffff') }}"
                       class="w-12 h-10 border border-gray-300 rounded-lg cursor-pointer">
                <span class="text-sm text-gray-500">Default: #ffffff (putih)</span>
            </div>
        </div>

        <div class="pt-4 border-t border-gray-200 flex justify-end gap-3">
            <a href="{{ route('lab-layouts.index') }}"
               class="px-6 py-2.5 bg-gray-100 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-200 transition-colors">
                Batal
            </a>
            <button type="submit"
                    class="inline-flex items-center gap-2 px-6 py-2.5 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700 transition-colors">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
                Buat Layout
            </button>
        </div>
    </form>
</div>
@endsection