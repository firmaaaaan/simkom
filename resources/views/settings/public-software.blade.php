@extends('layouts.app')

@section('title', 'Filter Software Publik')
@section('header', 'Filter Software Publik')

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

    @if(session('success'))
        <div class="mb-4 px-4 py-3 bg-green-50 border border-green-200 text-green-700 rounded-lg text-sm flex items-center gap-2">
            <svg class="w-5 h-5 text-green-600 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            {{ session('success') }}
        </div>
    @endif

    <div class="bg-white rounded-xl border border-gray-200 p-6 mb-4">
        <h2 class="text-base font-semibold text-gray-800 mb-1">Daftar Software</h2>
        <p class="text-sm text-gray-500 mb-4">
            Hanya software yang <span class="font-medium text-gray-700">diaktifkan</span> yang tampil di halaman publik daftar komputer.
            Software baru dari agent otomatis nonaktif sampai diaktifkan di sini.
        </p>

        <form action="{{ route('settings.public-software.update') }}" method="POST" x-data="{
            search: '',
            enabled: @js($names->filter(fn ($n) => $whitelistLower->contains(strtolower($n)))->values()->all()),
            pcCounts: @js($pcCounts->toArray()),
            namesList: @js($names->values()->all()),
            toggle(name) {
                const i = this.enabled.indexOf(name);
                if (i === -1) this.enabled.push(name);
                else this.enabled.splice(i, 1);
            },
            isActive(name) {
                return this.enabled.includes(name);
            },
            get visibleNames() {
                const q = this.search.toLowerCase();
                return this.namesList.filter(n => !q || n.toLowerCase().includes(q));
            }
        }">
            @csrf
            @method('PUT')

            <template x-for="name in enabled" :key="name">
                <input type="hidden" name="names[]" :value="name">
            </template>

            <div class="relative mb-4">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                </svg>
                <input type="text" x-model="search" placeholder="Cari nama software..."
                    class="w-full pl-9 pr-4 py-2.5 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent placeholder-gray-400">
            </div>

            <div class="border border-gray-100 rounded-lg divide-y divide-gray-50 max-h-96 overflow-y-auto">
                <template x-for="name in visibleNames" :key="name">
                    <div class="flex items-center justify-between gap-4 px-4 py-3">
                        <div class="min-w-0">
                            <p class="text-sm font-medium text-gray-800 truncate" x-text="name"></p>
                            <p class="text-xs mt-0.5" :class="pcCounts[name] ? 'text-gray-400' : 'text-amber-500'"
                                x-text="pcCounts[name] ? 'terdeteksi di ' + pcCounts[name] + ' PC' : 'tidak terdeteksi di agent'"></p>
                        </div>
                        <button type="button" role="switch" :aria-checked="isActive(name).toString()" @click="toggle(name)"
                            :aria-label="'Tampilkan ' + name"
                            :class="isActive(name) ? 'bg-green-600' : 'bg-gray-300'"
                            class="relative inline-flex h-6 w-11 flex-shrink-0 items-center rounded-full transition-colors focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2">
                            <span :class="isActive(name) ? 'translate-x-5' : 'translate-x-0.5'"
                                class="inline-block h-5 w-5 transform rounded-full bg-white shadow transition-transform"></span>
                        </button>
                    </div>
                </template>
                <div x-show="visibleNames.length === 0" class="px-4 py-10 text-center text-sm text-gray-400">
                    Tidak ada software yang cocok dengan pencarian.
                </div>
            </div>

            <div class="mt-4">
                <label for="manual_names" class="block text-sm font-medium text-gray-700 mb-1">Tambah nama manual</label>
                <textarea name="manual_names" id="manual_names" rows="2"
                    class="w-full px-4 py-2.5 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent placeholder-gray-400 resize-none"
                    placeholder="Nama software tambahan, satu per baris (misal: Microsoft Office)..."></textarea>
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
