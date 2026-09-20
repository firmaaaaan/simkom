@extends('layouts.app')

@section('title', 'Pemeliharaan')
@section('header', 'Data Pemeliharaan')

@section('content')
<div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
    <p class="text-sm text-gray-500">Kelola data pemeliharaan komputer laboratorium</p>
    <div class="flex flex-wrap items-center gap-2">
        <x-export-button route="maintenance.export" />
        <a href="{{ route('maintenance.create') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700 transition-colors">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
            </svg>
            Input Pemeliharaan
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
    <div class="px-6 py-4 border-b border-gray-100">
        <h2 class="text-base font-semibold text-gray-800">Daftar Pemeliharaan</h2>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm table-responsive-cards">
            <thead>
                <tr class="border-b border-gray-100">
                    <th class="text-left px-6 py-3 font-medium text-gray-500">No</th>
                    <th class="text-left px-6 py-3 font-medium text-gray-500">Tanggal</th>
                    <th class="text-left px-6 py-3 font-medium text-gray-500">Laboratorium</th>
                    <th class="text-left px-6 py-3 font-medium text-gray-500">Tahun Ajaran</th>
                    <th class="text-left px-6 py-3 font-medium text-gray-500">Petugas</th>
                    <th class="text-left px-6 py-3 font-medium text-gray-500">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($checklists as $index => $item)
                    <tr class="border-b border-gray-50 hover:bg-gray-50 transition-colors">
                        <td data-label="No" class="px-6 py-4 text-gray-500">{{ $checklists->firstItem() + $index }}</td>
                        <td data-label="Tanggal" class="px-6 py-4 text-gray-800">{{ \Carbon\Carbon::parse($item->maintenance_date)->format('d M Y') }}</td>
                        <td data-label="Laboratorium" class="px-6 py-4 font-medium text-gray-800">{{ $item->laboratory->name ?? '-' }}</td>
                        <td data-label="Tahun Ajaran" class="px-6 py-4 text-gray-600">{{ $item->academicYear->name ?? '-' }}</td>
                        <td data-label="Petugas" class="px-6 py-4 text-gray-600">{{ $item->inspector_name ?? '-' }}</td>
                        <td data-label="Aksi" class="px-6 py-4">
                            <div class="flex items-center gap-1">
                                <a href="{{ route('maintenance.show', $item) }}" class="p-1.5 rounded-lg hover:bg-gray-100 transition-colors" title="Lihat">
                                    <svg class="w-4 h-4 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    </svg>
                                </a>
                                <a href="{{ route('maintenance.edit', $item) }}" class="p-1.5 rounded-lg hover:bg-gray-100 transition-colors" title="Edit">
                                    <svg class="w-4 h-4 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125" />
                                    </svg>
                                </a>
                                <form action="{{ route('maintenance.destroy', $item) }}" method="POST" class="inline" onsubmit="return confirm('Yakin ingin menghapus data pemeliharaan ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-1.5 rounded-lg hover:bg-red-50 transition-colors" title="Hapus">
                                        <svg class="w-4 h-4 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                                        </svg>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center">
                            <svg class="w-12 h-12 text-gray-300 mx-auto mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M11.42 15.17l-5.1-5.1m0 0L11.42 4.97m-5.1 5.1H21" />
                            </svg>
                            <p class="text-gray-500 text-sm">Belum ada data pemeliharaan</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($checklists->hasPages())
        <div class="px-6 py-3 border-t border-gray-100">
            {{ $checklists->links() }}
        </div>
    @endif
</div>
@endsection
