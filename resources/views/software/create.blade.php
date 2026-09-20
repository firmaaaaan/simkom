@extends('layouts.app')

@section('title', 'Tambah Software')
@section('header', 'Tambah Software')

@section('content')
<div class="max-w-2xl">
    <div class="mb-6">
        <a href="{{ route('software.index') }}" class="inline-flex items-center gap-1.5 text-sm text-gray-500 hover:text-green-600 transition-colors">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
            </svg>
            Kembali
        </a>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 p-6">
        <h2 class="text-base font-semibold text-gray-800 mb-6">Form Tambah Software</h2>

        @if($errors->any())
            <div class="mb-4 px-4 py-3 bg-red-50 border border-red-200 text-red-600 rounded-lg text-sm">
                <ul class="list-disc list-inside">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('software.store') }}" method="POST">
            @csrf
            <div class="space-y-4">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Nama Software <span class="text-red-500">*</span></label>
                        <input type="text" name="name" id="name" value="{{ old('name') }}" required
                            class="w-full px-4 py-2.5 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent placeholder-gray-400"
                            placeholder="Contoh: Microsoft Office 365">
                    </div>
                    <div>
                        <label for="code" class="block text-sm font-medium text-gray-700 mb-1">Kode <span class="text-red-500">*</span></label>
                        <input type="text" name="code" id="code" value="{{ old('code') }}" required
                            class="w-full px-4 py-2.5 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent placeholder-gray-400"
                            placeholder="Contoh: SW-OFF-001">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label for="version" class="block text-sm font-medium text-gray-700 mb-1">Versi</label>
                        <input type="text" name="version" id="version" value="{{ old('version') }}"
                            class="w-full px-4 py-2.5 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent placeholder-gray-400"
                            placeholder="Contoh: 2024, v3.2">
                    </div>
                    <div>
                        <label for="license_type" class="block text-sm font-medium text-gray-700 mb-1">Jenis Lisensi</label>
                        <select name="license_type" id="license_type"
                            class="w-full px-4 py-2.5 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent">
                            <option value="">-- Pilih --</option>
                            <option value="OEM" {{ old('license_type') === 'OEM' ? 'selected' : '' }}>OEM (Bawaan)</option>
                            <option value="Retail" {{ old('license_type') === 'Retail' ? 'selected' : '' }}>Retail (Eceran)</option>
                            <option value="Volume License" {{ old('license_type') === 'Volume License' ? 'selected' : '' }}>Volume License</option>
                            <option value="Subscription" {{ old('license_type') === 'Subscription' ? 'selected' : '' }}>Subscription (Berlangganan)</option>
                            <option value="Freeware" {{ old('license_type') === 'Freeware' ? 'selected' : '' }}>Freeware (Gratis)</option>
                            <option value="Open Source" {{ old('license_type') === 'Open Source' ? 'selected' : '' }}>Open Source</option>
                            <option value="Trial" {{ old('license_type') === 'Trial' ? 'selected' : '' }}>Trial (Uji Coba)</option>
                        </select>
                    </div>
                    <div>
                        <label for="license_count" class="block text-sm font-medium text-gray-700 mb-1">Jumlah Lisensi <span class="text-red-500">*</span></label>
                        <input type="number" name="license_count" id="license_count" value="{{ old('license_count', 0) }}" required min="0"
                            class="w-full px-4 py-2.5 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent placeholder-gray-400">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="category" class="block text-sm font-medium text-gray-700 mb-1">Kategori <span class="text-red-500">*</span></label>
                        <select name="category" id="category" required
                            class="w-full px-4 py-2.5 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent">
                            <option value="">-- Pilih --</option>
                            <option value="Operating System" {{ old('category') === 'Operating System' ? 'selected' : '' }}>Operating System</option>
                            <option value="Office Suite" {{ old('category') === 'Office Suite' ? 'selected' : '' }}>Office Suite</option>
                            <option value="Antivirus" {{ old('category') === 'Antivirus' ? 'selected' : '' }}>Antivirus</option>
                            <option value="Browser" {{ old('category') === 'Browser' ? 'selected' : '' }}>Browser</option>
                            <option value="IDE/Editor" {{ old('category') === 'IDE/Editor' ? 'selected' : '' }}>IDE/Editor</option>
                            <option value="Design" {{ old('category') === 'Design' ? 'selected' : '' }}>Design (Photoshop, dll)</option>
                            <option value="Multimedia" {{ old('category') === 'Multimedia' ? 'selected' : '' }}>Multimedia (Video, Audio)</option>
                            <option value="Database" {{ old('category') === 'Database' ? 'selected' : '' }}>Database</option>
                            <option value="Networking" {{ old('category') === 'Networking' ? 'selected' : '' }}>Networking</option>
                            <option value="Utilities" {{ old('category') === 'Utilities' ? 'selected' : '' }}>Utilities (Tool)</option>
                            <option value="Lainnya" {{ old('category') === 'Lainnya' ? 'selected' : '' }}>Lainnya</option>
                        </select>
                    </div>
                    <div>
                        <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Status <span class="text-red-500">*</span></label>
                        <select name="status" id="status" required
                            class="w-full px-4 py-2.5 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent">
                            <option value="Aktif" {{ old('status') === 'Aktif' ? 'selected' : '' }}>Aktif</option>
                            <option value="Expired" {{ old('status') === 'Expired' ? 'selected' : '' }}>Expired</option>
                            <option value="Trial" {{ old('status') === 'Trial' ? 'selected' : '' }}>Trial</option>
                            <option value="Non Aktif" {{ old('status') === 'Non Aktif' ? 'selected' : '' }}>Non Aktif</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label for="description" class="block text-sm font-medium text-gray-700 mb-1">Deskripsi</label>
                    <textarea name="description" id="description" rows="3"
                        class="w-full px-4 py-2.5 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent placeholder-gray-400 resize-none"
                        placeholder="Deskripsi software (contoh: Paket lengkap Office untuk perkantoran)...">{{ old('description') }}</textarea>
                </div>
            </div>

            <div class="flex items-center gap-3 mt-6 pt-4 border-t border-gray-100">
                <button type="submit" class="px-5 py-2.5 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700 transition-colors">
                    Simpan
                </button>
                <a href="{{ route('software.index') }}" class="px-5 py-2.5 border border-gray-200 text-gray-600 text-sm font-medium rounded-lg hover:bg-gray-50 transition-colors">
                    Batal
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
