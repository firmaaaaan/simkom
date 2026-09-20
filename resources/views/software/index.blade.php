@extends('layouts.app')

@section('title', 'Software')
@section('header', 'Data Software')

@section('content')
<div x-data="{ selected: [] }">
<div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
    <p class="text-sm text-gray-500">Kelola seluruh data software laboratorium</p>
    <div class="flex items-center gap-2">
        <button x-show="selected.length > 0" x-cloak @click="if(confirm('Yakin ingin menghapus ' + selected.length + ' software?')) $refs.bulkForm.submit()"
            class="inline-flex items-center gap-2 px-4 py-2 bg-red-600 text-white text-sm font-medium rounded-lg hover:bg-red-700 transition-colors">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
            </svg>
            Hapus Terpilih (<span x-text="selected.length"></span>)
        </button>
        <a href="{{ route('software.import') }}" class="inline-flex items-center gap-2 px-4 py-2 border border-blue-600 text-blue-600 text-sm font-medium rounded-lg hover:bg-blue-50 transition-colors">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5" />
            </svg>
            Import
        </a>
        <a href="{{ route('software.export') }}" class="inline-flex items-center gap-2 px-4 py-2 border border-purple-600 text-purple-600 text-sm font-medium rounded-lg hover:bg-purple-50 transition-colors">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" />
            </svg>
            Export
        </a>
        <a href="{{ route('software.create') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700 transition-colors">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
            </svg>
            Tambah Software
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

<div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
    <div class="px-6 py-4 border-b border-gray-100 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <h2 class="text-base font-semibold text-gray-800">Daftar Software</h2>
        <form action="{{ route('software.index') }}" method="GET" class="relative">
            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
            </svg>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari software..." class="pl-9 pr-4 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent w-full sm:w-64">
        </form>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm table-responsive-cards">
            <thead>
                <tr class="border-b border-gray-100">
                    <th class="text-left px-6 py-3 font-medium text-gray-500 w-10">
                        <input type="checkbox" class="rounded border-gray-300 text-green-600 focus:ring-green-500"
                            @change="selected = $event.target.checked ? @js($software->pluck('id')->toArray()) : []">
                    </th>
                    <th class="text-left px-6 py-3 font-medium text-gray-500">No</th>
                    <th class="text-left px-6 py-3 font-medium text-gray-500">Kode</th>
                    <th class="text-left px-6 py-3 font-medium text-gray-500">Nama</th>
                    <th class="text-left px-6 py-3 font-medium text-gray-500">Versi</th>
                    <th class="text-left px-6 py-3 font-medium text-gray-500">Kategori</th>
                    <th class="text-left px-6 py-3 font-medium text-gray-500">Lisensi</th>
                    <th class="text-left px-6 py-3 font-medium text-gray-500">Status</th>
                    <th class="text-left px-6 py-3 font-medium text-gray-500">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($software as $index => $item)
                    <tr class="border-b border-gray-50 hover:bg-gray-50/50 transition-colors">
                        <td data-label="" class="px-6 py-4">
                            <input type="checkbox" value="{{ $item->id }}" class="rounded border-gray-300 text-green-600 focus:ring-green-500"
                                x-model="selected">
                        </td>
                        <td data-label="No" class="px-6 py-4 text-gray-500">
                            {{ $software->firstItem() + $index }}
                        </td>
                        <td data-label="Kode" class="px-6 py-4">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-xs font-mono font-medium bg-gray-100 text-gray-700">
                                {{ $item->code }}
                            </span>
                        </td>
                        <td data-label="Nama" class="px-6 py-4 font-medium text-gray-900">
                            {{ $item->name }}
                        </td>
                        <td data-label="Versi" class="px-6 py-4 text-gray-600">
                            {{ $item->version }}
                        </td>
                        <td data-label="Kategori" class="px-6 py-4">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-700">
                                {{ $item->category }}
                            </span>
                        </td>
                        <td data-label="Lisensi" class="px-6 py-4 text-gray-600">
                            {{ $item->license_count }}
                        </td>
                        <td data-label="Status" class="px-6 py-4">
                            @if($item->status === 'active')
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-700">
                                    Aktif
                                </span>
                            @elseif($item->status === 'inactive')
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-700">
                                    Tidak Aktif
                                </span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-700">
                                    {{ ucfirst($item->status) }}
                                </span>
                            @endif
                        </td>
                        <td data-label="Aksi" class="px-6 py-4">
                            <div class="flex items-center gap-2">
                                <a href="{{ route('software.edit', $item->id) }}" class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-medium text-blue-600 bg-blue-50 rounded-lg hover:bg-blue-100 transition-colors">
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" />
                                    </svg>
                                    Edit
                                </a>
                                <form action="{{ route('software.destroy', $item->id) }}" method="POST" class="inline"
                                    onsubmit="return confirm('Yakin ingin menghapus software ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-medium text-red-600 bg-red-50 rounded-lg hover:bg-red-100 transition-colors">
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                                        </svg>
                                        Hapus
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="px-6 py-12 text-center">
                            <div class="flex flex-col items-center gap-3">
                                <svg class="w-12 h-12 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5m6 4.125l2.25 2.25m0-2.25l2.25 2.25M12 13.875l2.25-2.25M12 13.875l-2.25 2.25M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z" />
                                </svg>
                                <p class="text-sm text-gray-500">Tidak ada data software</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($software->hasPages())
        <div class="px-6 py-3 border-t border-gray-100">
            {{ $software->links() }}
        </div>
    @endif
</div>

<form x-ref="bulkForm" action="{{ route('software.bulk-destroy') }}" method="POST" class="hidden">
    @csrf
    @method('DELETE')
    <template x-for="id in selected" :key="id">
        <input type="hidden" name="ids[]" :value="id">
    </template>
</form>
</div>
@endsection
