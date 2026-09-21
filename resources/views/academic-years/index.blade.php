@extends('layouts.app')

@section('title', 'Tahun Ajaran')
@section('header', 'Data Tahun Ajaran')

@section('content')
<div x-data="{ selected: [] }">
<div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
    <p class="text-sm text-gray-500">Kelola seluruh data tahun ajaran</p>
    <div class="flex items-center gap-2">
        <button x-show="selected.length > 0" x-cloak @click="if(confirm('Yakin ingin menghapus ' + selected.length + ' tahun ajaran?')) $refs.bulkForm.submit()"
            class="inline-flex items-center gap-2 px-4 py-2 bg-red-600 text-white text-sm font-medium rounded-lg hover:bg-red-700 transition-colors">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
            </svg>
            Hapus Terpilih (<span x-text="selected.length"></span>)
        </button>
        <x-export-button route="academic-years.export" />
        <a href="{{ route('academic-years.create') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700 transition-colors">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
            </svg>
            Tambah Tahun Ajaran
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
        <h2 class="text-base font-semibold text-gray-800">Daftar Tahun Ajaran</h2>
        <form action="{{ route('academic-years.index') }}" method="GET" class="relative">
            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
            </svg>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari tahun ajaran..." class="pl-9 pr-4 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent w-full sm:w-64">
        </form>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm table-responsive-cards">
            <thead>
                <tr class="border-b border-gray-100">
                    <th class="text-left px-6 py-3 font-medium text-gray-500 w-10">
                        <input type="checkbox" class="rounded border-gray-300 text-green-600 focus:ring-green-500"
                            @change="selected = $event.target.checked ? @js($academicYears->pluck('id')->toArray()) : []">
                    </th>
                    <th class="text-left px-6 py-3 font-medium text-gray-500">No</th>
                    <th class="text-left px-6 py-3 font-medium text-gray-500">Nama</th>
                    <th class="text-left px-6 py-3 font-medium text-gray-500">Tahun</th>
                    <th class="text-left px-6 py-3 font-medium text-gray-500">Periode</th>
                    <th class="text-left px-6 py-3 font-medium text-gray-500">Status</th>
                    <th class="text-left px-6 py-3 font-medium text-gray-500">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($academicYears as $index => $year)
                <tr class="border-b border-gray-100 hover:bg-gray-50">
                    <td data-label="" class="px-6 py-4">
                        <input type="checkbox" value="{{ $year->id }}" class="rounded border-gray-300 text-green-600 focus:ring-green-500" x-model="selected">
                    </td>
                    <td data-label="No" class="px-6 py-4 text-gray-500">{{ $academicYears->firstItem() + $index }}</td>
                    <td data-label="Nama" class="px-6 py-4 font-medium text-gray-800">{{ $year->name }}</td>
                    <td data-label="Tahun" class="px-6 py-4 text-gray-600">{{ $year->start_year }}/{{ $year->end_year }}</td>
                    <td data-label="Periode" class="px-6 py-4 text-gray-600">{{ $year->start_year }} - {{ $year->end_year }}</td>
                    <td data-label="Status" class="px-6 py-4">
                        @if($year->status === 'Aktif')
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-700">Aktif</span>
                        @else
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-700">Tidak Aktif</span>
                        @endif
                    </td>
                    <td data-label="Aksi" class="px-6 py-4">
                        <div class="flex items-center gap-2">
                            <a href="{{ route('academic-years.edit', $year->id) }}" class="inline-flex items-center px-3 py-1.5 bg-blue-600 text-white text-xs font-medium rounded-lg hover:bg-blue-700 transition-colors">
                                Edit
                            </a>
                            <form action="{{ route('academic-years.destroy', $year->id) }}" method="POST" class="inline" onsubmit="return confirm('Yakin ingin menghapus tahun ajaran ini?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="inline-flex items-center px-3 py-1.5 bg-red-600 text-white text-xs font-medium rounded-lg hover:bg-red-700 transition-colors">
                                    Hapus
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-6 py-12 text-center text-gray-500">
                        <div class="flex flex-col items-center gap-2">
                            <svg class="w-12 h-12 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5m6 4.125l2.25 2.25m0 0l2.25 2.25M12 13.875l2.25-2.25M12 13.875l-2.25 2.25M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z" />
                            </svg>
                            <p class="text-sm">Tidak ada data tahun ajaran</p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($academicYears->hasPages())
        <div class="px-6 py-3 border-t border-gray-100">
            {{ $academicYears->links() }}
        </div>
    @endif
</div>

<form x-ref="bulkForm" action="{{ route('academic-years.bulk-destroy') }}" method="POST" class="hidden">
    @csrf
    @method('DELETE')
    <template x-for="id in selected" :key="id">
        <input type="hidden" name="ids[]" :value="id">
    </template>
</form>
</div>
@endsection
