@extends('layouts.app')

@section('title', 'Komputer')
@section('header', 'Data Komputer')

@section('content')
<div x-data="{ selected: [] }">
<div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
    <p class="text-sm text-gray-500">Kelola seluruh data komputer laboratorium</p>
    <div class="flex flex-wrap items-center justify-end gap-2">
        <button x-show="selected.length > 0" x-cloak @click="if(confirm('Yakin ingin menghapus ' + selected.length + ' komputer?')) $refs.bulkForm.submit()"
            class="inline-flex items-center gap-2 px-4 py-2 bg-red-600 text-white text-sm font-medium rounded-lg hover:bg-red-700 transition-colors">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
            </svg>
            Hapus Terpilih (<span x-text="selected.length"></span>)
        </button>
        <x-export-button route="computers.export" :params="request()->query()" />
        <a href="{{ route('computers.qr-stiker') }}" class="inline-flex items-center gap-2 px-4 py-2 border border-gray-600 text-gray-600 text-sm font-medium rounded-lg hover:bg-gray-50 transition-colors">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 013.75 9.375v-4.5zM3.75 14.625c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5a1.125 1.125 0 01-1.125-1.125v-4.5zM13.5 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 0113.5 9.375v-4.5z" />
                <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 6.75h.75v.75h-.75v-.75zM6.75 16.5h.75v.75h-.75v-.75zM16.5 6.75h.75v.75h-.75v-.75zM13.5 13.5h.75v.75h-.75v-.75zM13.5 19.5h.75v.75h-.75v-.75zM19.5 13.5h.75v.75h-.75v-.75zM19.5 19.5h.75v.75h-.75v-.75zM16.5 16.5h.75v.75h-.75v-.75z" />
            </svg>
            QR Stiker
        </a>
        <a href="{{ route('computers.praktikum-labels') }}" class="inline-flex items-center gap-2 px-4 py-2 border border-blue-600 text-blue-600 text-sm font-medium rounded-lg hover:bg-blue-50 transition-colors">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5M3.75 6.75v10.5a2.25 2.25 0 002.25 2.25h12a2.25 2.25 0 002.25-2.25V6.75m-16.5 0v-1.5A2.25 2.25 0 016 3h12a2.25 2.25 0 012.25 2.25V6.75" />
            </svg>
            Label Praktikum
        </a>
        <a href="{{ route('computers.generate') }}" class="inline-flex items-center gap-2 px-4 py-2 border border-green-600 text-green-600 text-sm font-medium rounded-lg hover:bg-green-50 transition-colors">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 13.5l10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75z" />
            </svg>
            Generate
        </a>
        <form action="{{ route('settings.public-spec') }}" method="POST">
            @csrf
            @method('PATCH')
            <button type="submit"
                title="Saklar tombol \"Lihat Spesifikasi\" pada halaman publik"
                class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium rounded-lg border transition-colors {{ $publicSpecEnabled ? 'border-amber-500 text-amber-600 hover:bg-amber-50' : 'border-gray-400 text-gray-600 hover:bg-gray-50' }}">
                @if($publicSpecEnabled)
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88" />
                    </svg>
                    Sembunyikan Spesifikasi
                @else
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    Tampilkan Spesifikasi
                @endif
            </button>
        </form>
        <a href="{{ route('computers.create') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700 transition-colors">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
            </svg>
            Tambah Komputer
        </a>
    </div>
</div>

@if(session('success'))
    <div class="mb-4 px-4 py-3 bg-green-50 border border-green-200 text-green-700 rounded-lg text-sm flex items-center gap-2">
        <svg class="w-5 h-5 text-green-600 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        {{ session('success') }}
    </div>
@endif

<div class="mb-4 flex items-center justify-between">
    <div class="flex items-center gap-3">
        <h2 class="text-base font-semibold text-gray-800">Daftar Komputer</h2>
        <label class="flex items-center gap-1.5 text-sm text-gray-500 cursor-pointer">
            <input type="checkbox" class="rounded border-gray-300 text-green-600 focus:ring-green-500"
                @change="selected = $event.target.checked ? @js($computers->pluck('id')->toArray()) : []">
            Pilih Semua
        </label>
    </div>
    <form action="{{ route('computers.index') }}" method="GET" class="flex items-center gap-2 flex-wrap">
        <select name="laboratory_id" onchange="this.form.submit()"
            class="text-sm border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent py-2">
            <option value="">Semua Lab</option>
            @foreach($laboratories as $lab)
                <option value="{{ $lab->id }}" @selected(request('laboratory_id') === $lab->id)>{{ $lab->name }}</option>
            @endforeach
        </select>
        <div class="relative">
            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
            </svg>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari komputer..." class="pl-9 pr-4 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent w-full sm:w-52">
        </div>
        <select name="per_page" onchange="this.form.submit()"
            class="text-sm border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent py-2">
            @foreach([10, 25, 50, 100] as $size)
                <option value="{{ $size }}" @selected(request('per_page', 10) == $size)>{{ $size }} / halaman</option>
            @endforeach
        </select>
        @if(request('laboratory_id') || request('search'))
            <a href="{{ route('computers.index') }}" class="text-sm text-gray-500 hover:text-red-600 transition-colors whitespace-nowrap">Reset</a>
        @endif
    </form>
</div>

<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
    @forelse($computers as $item)
        <div class="bg-white rounded-xl border border-gray-200 p-5 hover:shadow-md transition-shadow">
            <div class="flex items-start justify-between mb-4">
                <div class="flex items-center gap-2">
                    <div class="w-10 h-10 rounded-lg bg-green-100 flex items-center justify-center">
                        <svg class="w-5 h-5 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 17.25v1.007a3 3 0 01-.879 2.122L7.5 21h9l-.621-.621A3 3 0 0115 18.257V17.25m6-12V15a2.25 2.25 0 01-2.25 2.25H5.25A2.25 2.25 0 013 15V5.25m18 0A2.25 2.25 0 0018.75 3H5.25A2.25 2.25 0 003 5.25m18 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 7.41A2.25 2.25 0 012.25 5.496V5.25" />
                        </svg>
                    </div>
                    <div>
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-green-100 text-green-800">
                            {{ $item->code }}
                        </span>
                    </div>
                </div>
                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium
                    {{ $item->status === 'Aktif' ? 'bg-green-100 text-green-800' : ($item->status === 'Maintenance' ? 'bg-yellow-100 text-yellow-800' : 'bg-gray-100 text-gray-600') }}">
                    {{ $item->status }}
                </span>
            </div>

            <div class="mb-3">
                <p class="text-xs text-gray-400 mb-1">Laboratorium</p>
                <p class="text-sm text-gray-700 font-medium">{{ $item->laboratory?->name ?? '-' }}</p>
            </div>

            @if($item->hardware->count() > 0)
                <div class="mb-3">
                    <p class="text-xs text-gray-400 mb-1">Hardware ({{ $item->hardware->count() }})</p>
                    <div class="flex flex-wrap gap-1">
                        @foreach($item->hardware->take(3) as $hw)
                            <span class="inline-flex items-center px-1.5 py-0.5 rounded text-xs bg-blue-50 text-blue-700">{{ $hw->name }}</span>
                        @endforeach
                        @if($item->hardware->count() > 3)
                            <span class="text-xs text-gray-400 self-center">+{{ $item->hardware->count() - 3 }}</span>
                        @endif
                    </div>
                </div>
            @endif

            @if($item->software->count() > 0)
                <div class="mb-4">
                    <p class="text-xs text-gray-400 mb-1">Software ({{ $item->software->count() }})</p>
                    <div class="flex flex-wrap gap-1">
                        @foreach($item->software->take(3) as $sw)
                            <span class="inline-flex items-center px-1.5 py-0.5 rounded text-xs bg-purple-50 text-purple-700">{{ $sw->name }}</span>
                        @endforeach
                        @if($item->software->count() > 3)
                            <span class="text-xs text-gray-400 self-center">+{{ $item->software->count() - 3 }}</span>
                        @endif
                    </div>
                </div>
            @endif

            <div class="flex items-center justify-between pt-3 border-t border-gray-100">
                <label class="flex items-center gap-1.5 text-sm text-gray-500 cursor-pointer">
                    <input type="checkbox" value="{{ $item->id }}" class="rounded border-gray-300 text-green-600 focus:ring-green-500"
                        x-model="selected">
                    Pilih
                </label>
                <div class="relative" x-data="{ open: false }" @click.away="open = false">
                    <button @click="open = !open" class="p-1.5 rounded-lg hover:bg-gray-100 transition-colors">
                        <svg class="w-5 h-5 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 12a.75.75 0 11-1.5 0 .75.75 0 011.5 0zM12.75 12a.75.75 0 11-1.5 0 .75.75 0 011.5 0zM18.75 12a.75.75 0 11-1.5 0 .75.75 0 011.5 0z" />
                        </svg>
                    </button>
                    <div x-show="open" x-transition:enter="transition ease-out duration-100" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100" x-transition:leave="transition ease-in duration-75" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
                        class="absolute right-0 mt-1 w-40 bg-white rounded-lg shadow-lg border border-gray-200 py-1 z-10">
                        <a href="{{ route('computers.show', $item) }}" class="flex items-center gap-2 px-3 py-2 text-sm text-gray-700 hover:bg-gray-50">
                            <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                            Detail
                        </a>
                        <a href="{{ route('computers.card', $item) }}" class="flex items-center gap-2 px-3 py-2 text-sm text-gray-700 hover:bg-gray-50">
                            <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0110.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0l.229 2.523a1.125 1.125 0 01-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0021 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 00-1.913-.247M6.34 18H5.25A2.25 2.25 0 013 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 011.913-.247m10.5 0a48.536 48.536 0 00-10.5 0m10.5 0V3.375c0-.621-.504-1.125-1.125-1.125h-8.25c-.621 0-1.125.504-1.125 1.125v3.659" />
                            </svg>
                            Kartu Kendali
                        </a>
                        <a href="{{ route('computers.edit', $item) }}" class="flex items-center gap-2 px-3 py-2 text-sm text-gray-700 hover:bg-gray-50">
                            <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125" />
                            </svg>
                            Edit
                        </a>
                        <hr class="my-1 border-gray-100">
                        <form action="{{ route('computers.destroy', $item) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus komputer ini?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="flex items-center gap-2 w-full px-3 py-2 text-sm text-red-600 hover:bg-red-50">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                                </svg>
                                Hapus
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @empty
        <div class="col-span-full">
            <div class="bg-white rounded-xl border border-gray-200 px-6 py-12 text-center">
                <svg class="w-12 h-12 text-gray-300 mx-auto mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 17.25v1.007a3 3 0 01-.879 2.122L7.5 21h9l-.621-.621A3 3 0 0115 18.257V17.25m6-12V15a2.25 2.25 0 01-2.25 2.25H5.25A2.25 2.25 0 013 15V5.25m18 0A2.25 2.25 0 0018.75 3H5.25A2.25 2.25 0 003 5.25m18 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 7.41A2.25 2.25 0 012.25 5.496V5.25" />
                </svg>
                <p class="text-gray-500 text-sm">Belum ada data komputer</p>
            </div>
        </div>
    @endforelse
</div>

@if($computers->hasPages())
    <div class="mt-4">
        {{ $computers->links() }}
    </div>
@endif

<form x-ref="bulkForm" action="{{ route('computers.bulk-destroy') }}" method="POST" class="hidden">
    @csrf
    @method('DELETE')
    <template x-for="id in selected" :key="id">
        <input type="hidden" name="ids[]" :value="id">
    </template>
</form>
</div>
@endsection
