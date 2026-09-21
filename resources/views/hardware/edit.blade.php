@extends('layouts.app')

@section('title', 'Edit Hardware')
@section('header', 'Edit Hardware')

@section('content')
<div class="max-w-2xl">
    <div class="mb-6">
        <a href="{{ route('hardware.index') }}" class="inline-flex items-center gap-1.5 text-sm text-gray-500 hover:text-green-600 transition-colors">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
            </svg>
            Kembali
        </a>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 p-6">
        <h2 class="text-base font-semibold text-gray-800 mb-6">Form Edit Hardware</h2>

        @if($errors->any())
            <div class="mb-4 px-4 py-3 bg-red-50 border border-red-200 text-red-600 rounded-lg text-sm">
                <ul class="list-disc list-inside">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('hardware.update', $hardware) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="space-y-4">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Nama Komponen <span class="text-red-500">*</span></label>
                        <input type="text" name="name" id="name" value="{{ old('name', $hardware->name) }}" required
                            class="w-full px-4 py-2.5 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent placeholder-gray-400"
                            placeholder="Contoh: Processor Intel Core i7">
                    </div>
                    <div>
                        <label for="code" class="block text-sm font-medium text-gray-700 mb-1">Kode <span class="text-red-500">*</span></label>
                        <input type="text" name="code" id="code" value="{{ old('code', $hardware->code) }}" required
                            class="w-full px-4 py-2.5 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent placeholder-gray-400"
                            placeholder="Contoh: HW-CPU-001">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="brand" class="block text-sm font-medium text-gray-700 mb-1">Merk/Brand</label>
                        <input type="text" name="brand" id="brand" value="{{ old('brand', $hardware->brand) }}"
                            class="w-full px-4 py-2.5 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent placeholder-gray-400"
                            placeholder="Contoh: Intel, AMD, Samsung">
                    </div>
                    <div>
                        <label for="model" class="block text-sm font-medium text-gray-700 mb-1">Model/Tipe</label>
                        <input type="text" name="model" id="model" value="{{ old('model', $hardware->model) }}"
                            class="w-full px-4 py-2.5 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent placeholder-gray-400"
                            placeholder="Contoh: Core i7-13700K">
                    </div>
                </div>

                <div>
                    <label for="category" class="block text-sm font-medium text-gray-700 mb-1">Kategori <span class="text-red-500">*</span></label>
                    <select name="category" id="category" required
                        class="w-full px-4 py-2.5 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent">
                        <option value="">-- Pilih --</option>
                        <option value="Processor" {{ old('category', $hardware->category) === 'Processor' ? 'selected' : '' }}>Processor (CPU)</option>
                        <option value="RAM" {{ old('category', $hardware->category) === 'RAM' ? 'selected' : '' }}>Memory (RAM)</option>
                        <option value="Storage" {{ old('category', $hardware->category) === 'Storage' ? 'selected' : '' }}>Storage (HDD/SSD)</option>
                        <option value="Motherboard" {{ old('category', $hardware->category) === 'Motherboard' ? 'selected' : '' }}>Motherboard</option>
                        <option value="Power Supply" {{ old('category', $hardware->category) === 'Power Supply' ? 'selected' : '' }}>Power Supply (PSU)</option>
                        <option value="VGA" {{ old('category', $hardware->category) === 'VGA' ? 'selected' : '' }}>VGA (GPU)</option>
                        <option value="Monitor" {{ old('category', $hardware->category) === 'Monitor' ? 'selected' : '' }}>Monitor</option>
                        <option value="Keyboard" {{ old('category', $hardware->category) === 'Keyboard' ? 'selected' : '' }}>Keyboard</option>
                        <option value="Mouse" {{ old('category', $hardware->category) === 'Mouse' ? 'selected' : '' }}>Mouse</option>
                        <option value="Printer" {{ old('category', $hardware->category) === 'Printer' ? 'selected' : '' }}>Printer</option>
                        <option value="Scanner" {{ old('category', $hardware->category) === 'Scanner' ? 'selected' : '' }}>Scanner</option>
                        <option value="Headset" {{ old('category', $hardware->category) === 'Headset' ? 'selected' : '' }}>Headset/Microphone</option>
                        <option value="Kabel" {{ old('category', $hardware->category) === 'Kabel' ? 'selected' : '' }}>Kabel/Adapter</option>
                        <option value="Lainnya" {{ old('category', $hardware->category) === 'Lainnya' ? 'selected' : '' }}>Lainnya</option>
                    </select>
                </div>

                <div>
                    <label for="description" class="block text-sm font-medium text-gray-700 mb-1">Speskipsi/Deskripsi</label>
                    <textarea name="description" id="description" rows="3"
                        class="w-full px-4 py-2.5 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent placeholder-gray-400 resize-none"
                        placeholder="Spesifikasi komponen (contoh: Intel Core i7-13700K, 16 Core, 3.4GHz, LGA 1700)...">{{ old('description', $hardware->description) }}</textarea>
                </div>
            </div>

            <div class="flex items-center gap-3 mt-6 pt-4 border-t border-gray-100">
                <button type="submit" class="px-5 py-2.5 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700 transition-colors">
                    Perbarui
                </button>
                <a href="{{ route('hardware.index') }}" class="px-5 py-2.5 border border-gray-200 text-gray-600 text-sm font-medium rounded-lg hover:bg-gray-50 transition-colors">
                    Batal
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
