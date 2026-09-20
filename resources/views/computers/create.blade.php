@extends('layouts.app')

@section('title', 'Tambah Komputer')
@section('header', 'Tambah Komputer')

@section('content')
<div class="max-w-3xl">
    <div class="mb-6">
        <a href="{{ route('computers.index') }}" class="inline-flex items-center gap-1.5 text-sm text-gray-500 hover:text-green-600 transition-colors">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
            </svg>
            Kembali
        </a>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 p-6">
        <h2 class="text-base font-semibold text-gray-800 mb-6">Form Tambah Komputer</h2>

        @if($errors->any())
            <div class="mb-4 px-4 py-3 bg-red-50 border border-red-200 text-red-600 rounded-lg text-sm">
                <ul class="list-disc list-inside">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('computers.store') }}" method="POST">
            @csrf
            <div class="space-y-5">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="code" class="block text-sm font-medium text-gray-700 mb-1">Kode Komputer <span class="text-red-500">*</span></label>
                        <input type="text" name="code" id="code" value="{{ old('code') }}" required
                            class="w-full px-4 py-2.5 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent placeholder-gray-400"
                            placeholder="Contoh: PC-001">
                    </div>
                    <div>
                        <label for="laboratory_id" class="block text-sm font-medium text-gray-700 mb-1">Laboratorium</label>
                        <select name="laboratory_id" id="laboratory_id"
                            class="w-full px-4 py-2.5 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent">
                            <option value="">-- Pilih Lab --</option>
                            @foreach($laboratories as $lab)
                                <option value="{{ $lab->id }}" {{ old('laboratory_id') == $lab->id ? 'selected' : '' }}>{{ $lab->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div>
                    <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Status <span class="text-red-500">*</span></label>
                    <select name="status" id="status" required
                        class="w-full px-4 py-2.5 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent">
                        <option value="Aktif" {{ old('status') === 'Aktif' ? 'selected' : '' }}>Aktif</option>
                        <option value="Tidak Aktif" {{ old('status') === 'Tidak Aktif' ? 'selected' : '' }}>Tidak Aktif</option>
                        <option value="Maintenance" {{ old('status') === 'Maintenance' ? 'selected' : '' }}>Maintenance</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Hardware</label>
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-2">
                        @forelse($hardware as $item)
                            <label class="flex items-center gap-2 px-3 py-2 border border-gray-200 rounded-lg hover:bg-gray-50 cursor-pointer transition-colors">
                                <input type="checkbox" name="hardware[]" value="{{ $item->id }}" {{ in_array($item->id, old('hardware', [])) ? 'checked' : '' }}
                                    class="w-4 h-4 text-green-600 border-gray-300 rounded focus:ring-green-500">
                                <span class="text-sm text-gray-700 truncate">{{ $item->name }}</span>
                            </label>
                        @empty
                            <p class="text-sm text-gray-400 col-span-full">Belum ada data hardware</p>
                        @endforelse
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Software</label>
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-2">
                        @forelse($software as $item)
                            <label class="flex items-center gap-2 px-3 py-2 border border-gray-200 rounded-lg hover:bg-gray-50 cursor-pointer transition-colors">
                                <input type="checkbox" name="software[]" value="{{ $item->id }}" {{ in_array($item->id, old('software', [])) ? 'checked' : '' }}
                                    class="w-4 h-4 text-green-600 border-gray-300 rounded focus:ring-green-500">
                                <span class="text-sm text-gray-700 truncate">{{ $item->name }}</span>
                            </label>
                        @empty
                            <p class="text-sm text-gray-400 col-span-full">Belum ada data software</p>
                        @endforelse
                    </div>
                </div>

                <div>
                    <label for="description" class="block text-sm font-medium text-gray-700 mb-1">Keterangan</label>
                    <textarea name="description" id="description" rows="3"
                        class="w-full px-4 py-2.5 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent placeholder-gray-400 resize-none"
                        placeholder="Keterangan tambahan tentang komputer...">{{ old('description') }}</textarea>
                </div>
            </div>

            <div class="flex items-center gap-3 mt-6 pt-4 border-t border-gray-100">
                <button type="submit" class="px-5 py-2.5 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700 transition-colors">
                    Simpan
                </button>
                <a href="{{ route('computers.index') }}" class="px-5 py-2.5 border border-gray-200 text-gray-600 text-sm font-medium rounded-lg hover:bg-gray-50 transition-colors">
                    Batal
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
