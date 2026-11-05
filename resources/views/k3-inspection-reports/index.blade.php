@extends('layouts.app')

@section('title', 'Laporan Inspeksi K3')
@section('header', 'Laporan Inspeksi K3')

@section('content')
<div class="space-y-6" x-data="{ selected: [] }">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <p class="text-sm text-gray-600">Berita Acara, Checklist, dan Laporan Temuan Inspeksi K3 (Form 005).</p>
        @if(auth()->user()->hasPermission('create-k3-inspection-reports'))
        <a href="{{ route('k3-inspection-reports.create') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700 transition-colors">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
            </svg>
            Buat Laporan Inspeksi K3
        </a>
        @endif
    </div>

    @if(session('success'))
        <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg text-sm">
            {{ session('success') }}
        </div>
    @endif

    @if(auth()->user()->hasPermission('delete-k3-inspection-reports'))
    <div x-show="selected.length > 0" x-cloak x-transition class="flex items-center gap-3">
        <span class="text-sm text-gray-600" x-text="selected.length + ' laporan dipilih'"></span>
        <form onsubmit="return confirm('Hapus laporan inspeksi K3 yang dipilih?')" method="POST" action="{{ route('k3-inspection-reports.bulk-destroy') }}">
            @method('DELETE')
            @csrf
            <template x-for="id in selected" :key="id">
                <input type="hidden" name="ids[]" :value="id">
            </template>
            <button type="submit" class="px-3 py-1.5 bg-red-600 text-white text-xs font-medium rounded-lg hover:bg-red-700 transition-colors">
                Hapus Terpilih
            </button>
        </form>
    </div>
    @endif

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
        <form action="{{ route('k3-inspection-reports.index') }}" method="GET" class="flex flex-col sm:flex-row gap-3">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari kode, lokasi, pemeriksa, bulan..." class="flex-1 px-4 py-2 text-sm border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
            <input type="text" name="bulan" value="{{ request('bulan') }}" placeholder="Bulan inspeksi" class="px-3 py-2 text-sm border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500">
            <select name="tahun" class="px-3 py-2 text-sm border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500">
                <option value="">Semua Tahun</option>
                @foreach($tahunOptions as $tahun)
                    <option value="{{ $tahun }}" {{ request('tahun') == $tahun ? 'selected' : '' }}>{{ $tahun }}</option>
                @endforeach
            </select>
            <button type="submit" class="px-4 py-2 bg-gray-600 text-white text-sm font-medium rounded-lg hover:bg-gray-700 transition-colors">
                Filter
            </button>
        </form>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm table-responsive-cards">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        @if(auth()->user()->hasPermission('delete-k3-inspection-reports'))
                        <th class="px-4 py-3 text-center w-10">
                            <input type="checkbox" class="rounded border-gray-300 text-green-600 focus:ring-green-500" @change="selected = $event.target.checked ? @js($reports->pluck('id')->toArray()) : []" :checked="selected.length === @js($reports->count()) && selected.length > 0">
                        </th>
                        @endif
                        <th class="px-4 py-3 text-left font-medium text-gray-600">Kode</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-600">Bulan Inspeksi</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-600">Lokasi</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-600">Pemeriksa</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-600">Tanggal BA</th>
                        <th class="px-4 py-3 text-center font-medium text-gray-600">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($reports as $report)
                        <tr class="hover:bg-gray-50 transition-colors">
                            @if(auth()->user()->hasPermission('delete-k3-inspection-reports'))
                            <td data-label="" class="px-4 py-3 text-center">
                                <input type="checkbox" class="rounded border-gray-300 text-green-600 focus:ring-green-500" value="{{ $report->id }}" x-model="selected">
                            </td>
                            @endif
                            <td data-label="Kode" class="px-4 py-3">
                                <a href="{{ route('k3-inspection-reports.show', $report) }}" class="font-medium text-gray-900 hover:text-green-600">{{ $report->kode }}</a>
                            </td>
                            <td data-label="Bulan Inspeksi" class="px-4 py-3 text-gray-900">{{ $report->bulan_inspeksi }}</td>
                            <td data-label="Lokasi" class="px-4 py-3 text-gray-900">{{ $report->lokasi }}</td>
                            <td data-label="Pemeriksa" class="px-4 py-3 text-gray-600 text-xs">{{ $report->pemeriksa_nama }}</td>
                            <td data-label="Tanggal BA" class="px-4 py-3 text-gray-600 text-xs">{{ $report->tanggal_ba->translatedFormat('d F Y') }}</td>
                            <td data-label="Aksi" class="px-4 py-3 text-center">
                                <div class="flex items-center justify-center gap-1 flex-wrap">
                                    <a href="{{ route('k3-inspection-reports.show', $report) }}" class="p-1.5 text-blue-600 hover:bg-blue-50 rounded-lg transition-colors" title="Lihat">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        </svg>
                                    </a>
                                    <a href="{{ route('k3-inspection-reports.print', $report) }}" target="_blank" class="p-1.5 text-gray-600 hover:bg-gray-100 rounded-lg transition-colors" title="Cetak">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0110.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0l.229 2.523a1.125 1.125 0 01-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0021 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 00-1.913-.247M6.34 18H5.25A2.25 2.25 0 013 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 011.913-.247m10.5 0a48.536 48.536 0 00-10.5 0m10.5 0V3.375c0-.621-.504-1.125-1.125-1.125h-8.25c-.621 0-1.125.504-1.125 1.125v3.659" />
                                        </svg>
                                    </a>
                                    @if(auth()->user()->hasPermission('edit-k3-inspection-reports'))
                                    <a href="{{ route('k3-inspection-reports.edit', $report) }}" class="p-1.5 text-amber-600 hover:bg-amber-50 rounded-lg transition-colors" title="Ubah">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125" />
                                        </svg>
                                    </a>
                                    @endif
                                    @if(auth()->user()->hasPermission('delete-k3-inspection-reports'))
                                    <form onsubmit="return confirm('Hapus laporan inspeksi K3 ini?')" method="POST" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" formaction="{{ route('k3-inspection-reports.destroy', $report) }}" class="p-1.5 text-red-600 hover:bg-red-50 rounded-lg transition-colors" title="Hapus">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                                            </svg>
                                        </button>
                                    </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-12 text-center text-gray-500">
                                <svg class="w-12 h-12 mx-auto text-gray-300 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25z" />
                                </svg>
                                <p class="text-sm font-medium">Belum ada laporan inspeksi K3</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($reports->hasPages())
            <div class="px-4 py-3 border-t border-gray-200">
                {{ $reports->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
