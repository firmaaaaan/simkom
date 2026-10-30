@extends('layouts.app')

@section('title', $signature ? 'Ubah TTD Laporan' : 'Tambah TTD Laporan')
@section('header', $signature ? 'Ubah TTD Laporan' : 'Tambah TTD Laporan')

@section('content')
<div class="max-w-3xl">
    <div class="mb-6">
        <a href="{{ route('settings.signatures.index') }}" class="inline-flex items-center gap-1.5 text-sm text-gray-500 hover:text-green-600 transition-colors">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
            </svg>
            Kembali
        </a>
    </div>

    @if($errors->any())
        <div class="mb-4 px-4 py-3 bg-red-50 border border-red-200 text-red-700 rounded-lg text-sm">
            <ul class="list-disc list-inside space-y-1">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ $signature ? route('settings.signatures.update', $signature) : route('settings.signatures.store') }}"
        method="POST" enctype="multipart/form-data"
        x-data="{ preview: @js($signature?->imageSrc()) }">
        @csrf
        @if($signature)
            @method('PUT')
        @endif

        <div class="bg-white rounded-xl border border-gray-200 p-6 mb-4">
            <h2 class="text-base font-semibold text-gray-800 mb-4">Identitas TTD</h2>

            <div class="mb-4">
                <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Nama</label>
                <input type="text" name="name" id="name" value="{{ old('name', $signature?->name) }}" required maxlength="150"
                    placeholder="mis. Dr. Dhesi Ari Astuti, S.SiT., M.Kes"
                    class="w-full px-4 py-2.5 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent placeholder-gray-400">
            </div>

            <div class="mb-4">
                <label for="role" class="block text-sm font-medium text-gray-700 mb-1">Jabatan</label>
                <input type="text" name="role" id="role" value="{{ old('role', $signature?->role) }}" required maxlength="150"
                    placeholder="mis. Kepala UPT Laboratorium Terpadu"
                    class="w-full px-4 py-2.5 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent placeholder-gray-400">
                <p class="text-xs text-gray-400 mt-1">Teks yang dicetak di atas nama pada laporan.</p>
            </div>

            <div>
                <label for="image" class="block text-sm font-medium text-gray-700 mb-1">Gambar TTD (opsional)</label>
                <input type="file" name="image" id="image" accept="image/jpeg,image/png,image/gif,image/webp"
                    class="block w-full text-sm text-gray-600 file:mr-3 file:px-4 file:py-2 file:border-0 file:rounded-lg file:bg-green-50 file:text-green-700 file:text-sm file:font-medium hover:file:bg-green-100"
                    x-on:change="
                        const file = $event.target.files[0];
                        if (file) { const reader = new FileReader(); reader.onload = e => preview = e.target.result; reader.readAsDataURL(file); }
                        else { preview = @js($signature?->imageSrc()); }
                    ">
                <p class="text-xs text-gray-400 mt-1">Scan tanda tangan (PNG transparan disarankan), maks 2 MB. Jika kosong, laman cetak menyisakan ruang tanda tangan basah.</p>

                <div class="mt-3 w-44 h-16 bg-gray-50 border border-gray-200 rounded-lg flex items-center justify-center overflow-hidden">
                    <template x-if="preview">
                        <img :src="preview" alt="Pratinjau TTD" class="max-h-14 max-w-full object-contain">
                    </template>
                    <template x-if="!preview">
                        <span class="text-xs text-gray-400">Belum ada gambar</span>
                    </template>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl border border-gray-200 p-6 mb-4">
            <h2 class="text-base font-semibold text-gray-800 mb-1">Terapkan ke Laporan</h2>
            <p class="text-sm text-gray-500 mb-4">
                Centang laporan yang ingin memakai TTD ini. Satu laporan hanya bisa memakai satu TTD —
                mencentang laporan yang sedang dipakai TTD lain akan memindahkannya ke TTD ini.
            </p>

            <div class="border border-gray-100 rounded-lg divide-y divide-gray-50">
                @foreach($reports as $key => $label)
                    @php($isChecked = in_array($key, old('reports', $signature?->assignments->pluck('report_key')->all() ?? []), true))
                    <label class="flex items-center gap-2.5 px-4 py-3 cursor-pointer select-none hover:bg-gray-50 transition-colors">
                        <input type="checkbox" name="reports[]" value="{{ $key }}" {{ $isChecked ? 'checked' : '' }}
                            class="rounded border-gray-300 text-green-600 focus:ring-green-500">
                        <span class="min-w-0 flex-1">
                            <span class="block text-sm font-medium text-gray-800">{{ $label }}</span>
                            @if(isset($takenReports[$key]))
                                <span class="block text-xs mt-0.5 text-amber-600">Sedang dipakai TTD: {{ $takenReports[$key] }}</span>
                            @endif
                        </span>
                    </label>
                @endforeach
            </div>
            <p class="text-xs text-gray-400 mt-2">Laporan tanpa centang tetap memakai TTD default laporan tersebut.</p>
        </div>

        <div class="flex items-center gap-3">
            <button type="submit" class="px-5 py-2.5 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700 transition-colors">
                Simpan
            </button>
            <a href="{{ route('settings.signatures.index') }}" class="px-5 py-2.5 border border-gray-200 text-gray-600 text-sm font-medium rounded-lg hover:bg-gray-50 transition-colors">
                Batal
            </a>
        </div>
    </form>
</div>
@endsection
