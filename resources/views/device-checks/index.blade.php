@extends('layouts.app')

@section('title', 'Pengecekan Perangkat')
@section('header', 'Pengecekan Perangkat')

@section('content')
<div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
    <p class="text-sm text-gray-500">Matriks pengecekan perangkat per laboratorium &amp; tahun ajaran</p>
    <div class="flex items-center gap-2">
        <x-export-button route="device-checks.export" />
        <a href="{{ route('device-checks.report') }}" class="inline-flex items-center gap-2 px-4 py-2 border border-gray-300 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-50 transition-colors">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25z" />
            </svg>
            Laporan per Tahun Ajaran
        </a>
        <a href="{{ route('device-checks.create') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700 transition-colors">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
            </svg>
            Input Pengecekan
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
        <h2 class="text-base font-semibold text-gray-800">Daftar Pengecekan</h2>
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
                    <th class="text-left px-6 py-3 font-medium text-gray-500">Komputer</th>
                    <th class="text-left px-6 py-3 font-medium text-gray-500">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($checks as $index => $item)
                    <tr class="border-b border-gray-50 hover:bg-gray-50 transition-colors">
                        <td data-label="No" class="px-6 py-4 text-gray-500">{{ $checks->firstItem() + $index }}</td>
                        <td data-label="Tanggal" class="px-6 py-4 text-gray-800">{{ $item->check_date?->format('d M Y') ?? '-' }}</td>
                        <td data-label="Laboratorium" class="px-6 py-4 font-medium text-gray-800">{{ $item->laboratory->name ?? '-' }}</td>
                        <td data-label="Tahun Ajaran" class="px-6 py-4 text-gray-600">{{ $item->academicYear->name ?? '-' }}</td>
                        <td data-label="Petugas" class="px-6 py-4 text-gray-600">{{ $item->officer_name ?: '-' }}</td>
                        <td data-label="Komputer" class="px-6 py-4 text-gray-600">
                            {{ $item->computers_count }} komputer
                            <span class="text-xs text-gray-400 block">{{ $item->items_count }} sel diperiksa</span>
                        </td>
                        <td data-label="Aksi" class="px-6 py-4">
                            <div class="flex items-center gap-1">
                                <a href="{{ route('device-checks.show', $item) }}" class="p-1.5 rounded-lg hover:bg-gray-100 transition-colors" title="Lihat">
                                    <svg class="w-4 h-4 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    </svg>
                                </a>
                                <a href="{{ route('device-checks.print', $item) }}" target="_blank" class="p-1.5 rounded-lg hover:bg-gray-100 transition-colors" title="Cetak">
                                    <svg class="w-4 h-4 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0110.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0l.229 2.523a1.125 1.125 0 01-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0021 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 00-1.913-.247M6.34 18H5.25A2.25 2.25 0 013 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 011.913-.247m10.5 0a48.536 48.536 0 00-10.5 0m10.5 0V3.375c0-.621-.504-1.125-1.125-1.125h-8.25c-.621 0-1.125.504-1.125 1.125v3.659" />
                                    </svg>
                                </a>
                                <a href="{{ route('device-checks.edit', $item) }}" class="p-1.5 rounded-lg hover:bg-gray-100 transition-colors" title="Edit">
                                    <svg class="w-4 h-4 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125" />
                                    </svg>
                                </a>
                                <form action="{{ route('device-checks.destroy', $item) }}" method="POST" class="inline" onsubmit="return confirm('Yakin ingin menghapus data pengecekan ini?')">
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
                        <td colspan="7" class="px-6 py-12 text-center">
                            <svg class="w-12 h-12 text-gray-300 mx-auto mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                            </svg>
                            <p class="text-gray-500 text-sm">Belum ada data pengecekan perangkat</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($checks->hasPages())
        <div class="px-6 py-3 border-t border-gray-100">
            {{ $checks->links() }}
        </div>
    @endif
</div>
@endsection
