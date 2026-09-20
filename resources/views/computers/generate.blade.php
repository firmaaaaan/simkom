@extends('layouts.app')

@section('title', 'Generate Komputer')
@section('header', 'Generate Komputer')

@section('content')
<div class="max-w-xl">
    <div class="mb-6">
        <a href="{{ route('computers.index') }}" class="inline-flex items-center gap-1.5 text-sm text-gray-500 hover:text-green-600 transition-colors">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
            </svg>
            Kembali
        </a>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 p-6">
        <h2 class="text-base font-semibold text-gray-800 mb-6">Generate Komputer Massal</h2>

        <p class="text-sm text-gray-500 mb-4">Buat data komputer secara otomatis sesuai jumlah yang dibutuhkan. Hardware dan software bisa diisi nanti melalui form edit.</p>

        @if($errors->any())
            <div class="mb-4 px-4 py-3 bg-red-50 border border-red-200 text-red-600 rounded-lg text-sm">
                <ul class="list-disc list-inside">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('computers.store-generate') }}" method="POST">
            @csrf
            <div class="space-y-4">
                <div>
                    <label for="laboratory_id" class="block text-sm font-medium text-gray-700 mb-1">Laboratorium <span class="text-red-500">*</span></label>
                    <select name="laboratory_id" id="laboratory_id" required
                        class="w-full px-4 py-2.5 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent">
                        <option value="">-- Pilih Lab --</option>
                        @foreach($laboratories as $lab)
                            <option value="{{ $lab->id }}" {{ old('laboratory_id', request('laboratory_id')) == $lab->id ? 'selected' : '' }}>{{ $lab->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label for="prefix" class="block text-sm font-medium text-gray-700 mb-1">Prefix <span class="text-red-500">*</span></label>
                        <input type="text" name="prefix" id="prefix" value="{{ old('prefix', 'PC') }}" required maxlength="10"
                            class="w-full px-4 py-2.5 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent placeholder-gray-400 uppercase"
                            placeholder="PC">
                    </div>
                    <div>
                        <label for="start_number" class="block text-sm font-medium text-gray-700 mb-1">Mulai Dari <span class="text-red-500">*</span></label>
                        <input type="number" name="start_number" id="start_number" value="{{ old('start_number', 1) }}" required min="1"
                            class="w-full px-4 py-2.5 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent placeholder-gray-400">
                    </div>
                    <div>
                        <label for="count" class="block text-sm font-medium text-gray-700 mb-1">Jumlah <span class="text-red-500">*</span></label>
                        <input type="number" name="count" id="count" value="{{ old('count', 1) }}" required min="1" max="500"
                            class="w-full px-4 py-2.5 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent placeholder-gray-400">
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

                <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                    <p class="text-sm text-gray-600 mb-1">Preview kode yang akan dibuat:</p>
                    <div class="flex flex-wrap gap-1" id="preview">
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800">PC-001</span>
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800">PC-002</span>
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800">PC-003</span>
                        <span class="text-xs text-gray-400 self-center">...</span>
                    </div>
                </div>
            </div>

            <div class="flex items-center gap-3 mt-6 pt-4 border-t border-gray-100">
                <button type="submit" class="px-5 py-2.5 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700 transition-colors">
                    Generate
                </button>
                <a href="{{ route('computers.index') }}" class="px-5 py-2.5 border border-gray-200 text-gray-600 text-sm font-medium rounded-lg hover:bg-gray-50 transition-colors">
                    Batal
                </a>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    const prefixEl = document.getElementById('prefix');
    const startEl = document.getElementById('start_number');
    const countEl = document.getElementById('count');
    const previewEl = document.getElementById('preview');

    function updatePreview() {
        const prefix = prefixEl.value.toUpperCase() || 'PC';
        const start = parseInt(startEl.value) || 1;
        const count = parseInt(countEl.value) || 1;
        const max = Math.min(count, 5);
        let html = '';

        for (let i = 0; i < max; i++) {
            const num = start + i;
            const code = prefix + '-' + String(num).padStart(3, '0');
            html += '<span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800">' + code + '</span>';
        }

        if (count > 5) {
            html += '<span class="text-xs text-gray-400 self-center">...+' + (count - 5) + ' lagi</span>';
        }

        previewEl.innerHTML = html;
    }

    prefixEl.addEventListener('input', updatePreview);
    startEl.addEventListener('input', updatePreview);
    countEl.addEventListener('input', updatePreview);
</script>
@endpush
@endsection
