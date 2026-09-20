@extends('layouts.app')

@section('title', 'Kendala Praktikum')
@section('header', 'Kendala Praktikum')

@section('content')
<div class="space-y-6" x-data="{ selected: [] }">
    {{-- Action Bar --}}
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <p class="text-sm text-gray-600">Kelola tiket kendala praktikum laboratorium.</p>
        <div class="flex flex-wrap items-center gap-2">
        <x-export-button route="tickets.export" :params="request()->query()" />
        <a href="{{ route('tickets.create') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700 transition-colors">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
            </svg>
            Buat Tiket
        </a>
        </div>
    </div>

    {{-- Success Message --}}
    @if(session('success'))
        <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg text-sm">
            {{ session('success') }}
        </div>
    @endif

    {{-- Bulk Delete --}}
    <div x-show="selected.length > 0" x-transition class="flex items-center gap-3">
        <span class="text-sm text-gray-600" x-text="selected.length + ' tiket dipilih'"></span>
        <form onsubmit="return confirm('Hapus tiket yang dipilih?')">
            @method('DELETE')
            @csrf
            <template x-for="id in selected" :key="id">
                <input type="hidden" name="ids[]" :value="id">
            </template>
            <button type="submit" formaction="{{ route('tickets.bulk-destroy') }}" class="px-3 py-1.5 bg-red-600 text-white text-xs font-medium rounded-lg hover:bg-red-700 transition-colors">
                Hapus Terpilih
            </button>
        </form>
    </div>

    {{-- Filter --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
        <form action="{{ route('tickets.index') }}" method="GET" class="flex flex-col sm:flex-row gap-3">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari judul/kategori..." class="flex-1 px-4 py-2 text-sm border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
            <select name="laboratory_id" class="px-3 py-2 text-sm border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500">
                <option value="">Semua Lab</option>
                @foreach($laboratories as $lab)
                    <option value="{{ $lab->id }}" {{ request('laboratory_id') == $lab->id ? 'selected' : '' }}>{{ $lab->name }}</option>
                @endforeach
            </select>
            <select name="status" class="px-3 py-2 text-sm border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500">
                <option value="">Semua Status</option>
                <option value="Open" {{ request('status') == 'Open' ? 'selected' : '' }}>Open</option>
                <option value="In Progress" {{ request('status') == 'In Progress' ? 'selected' : '' }}>In Progress</option>
                <option value="Resolved" {{ request('status') == 'Resolved' ? 'selected' : '' }}>Resolved</option>
                <option value="Closed" {{ request('status') == 'Closed' ? 'selected' : '' }}>Closed</option>
            </select>
            <select name="priority" class="px-3 py-2 text-sm border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500">
                <option value="">Semua Prioritas</option>
                <option value="Rendah" {{ request('priority') == 'Rendah' ? 'selected' : '' }}>Rendah</option>
                <option value="Sedang" {{ request('priority') == 'Sedang' ? 'selected' : '' }}>Sedang</option>
                <option value="Tinggi" {{ request('priority') == 'Tinggi' ? 'selected' : '' }}>Tinggi</option>
                <option value="Darurat" {{ request('priority') == 'Darurat' ? 'selected' : '' }}>Darurat</option>
            </select>
            <select name="category" class="px-3 py-2 text-sm border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500">
                <option value="">Semua Kategori</option>
                <option value="Komputer" {{ request('category') == 'Komputer' ? 'selected' : '' }}>Komputer</option>
                <option value="Hardware" {{ request('category') == 'Hardware' ? 'selected' : '' }}>Hardware</option>
                <option value="Software" {{ request('category') == 'Software' ? 'selected' : '' }}>Software</option>
                <option value="Jaringan" {{ request('category') == 'Jaringan' ? 'selected' : '' }}>Jaringan</option>
                <option value="Listrik/UPS" {{ request('category') == 'Listrik/UPS' ? 'selected' : '' }}>Listrik/UPS</option>
            </select>
            <select name="month" class="px-3 py-2 text-sm border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500">
                <option value="">Semua Bulan</option>
                @foreach([1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April', 5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus', 9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'] as $number => $label)
                    <option value="{{ $number }}" {{ (string) request('month') === (string) $number ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>
            <select name="year" class="px-3 py-2 text-sm border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500">
                <option value="">Semua Tahun</option>
                @foreach($years as $option)
                    <option value="{{ $option }}" {{ (string) request('year') === (string) $option ? 'selected' : '' }}>{{ $option }}</option>
                @endforeach
            </select>
            <button type="submit" class="px-4 py-2 bg-gray-600 text-white text-sm font-medium rounded-lg hover:bg-gray-700 transition-colors">
                Filter
            </button>
        </form>

        @if(filled(request('month')) || filled(request('year')))
            <div class="mt-3 flex flex-wrap items-center gap-2 text-xs text-gray-600">
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-green-50 text-green-700 rounded-full font-medium">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" />
                    </svg>
                    Periode:
                    {{ request('month') ? \Illuminate\Support\Carbon::create()->month((int) request('month'))->translatedFormat('F') : 'Semua bulan' }}
                    {{ request('year') ?: 'semua tahun' }}
                </span>
                <span class="text-gray-500">{{ $tickets->total() }} tiket ditemukan</span>
                <a href="{{ route('tickets.index', request()->except(['month', 'year', 'page'])) }}" class="text-gray-500 hover:text-green-600 underline">Hapus filter periode</a>
            </div>
        @endif
    </div>

    {{-- Table --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm table-responsive-cards">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-4 py-3 text-center w-10">
                            <input type="checkbox" class="rounded border-gray-300 text-green-600 focus:ring-green-500" @change="selected = $event.target.checked ? @js($tickets->pluck('id')->toArray()) : []" :checked="selected.length === @js($tickets->count()) && selected.length > 0">
                        </th>
                        <th class="px-4 py-3 text-left font-medium text-gray-600">Judul</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-600">Kategori</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-600">Lab</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-600">Prioritas</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-600">Status</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-600">Pelapor</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-600">Tanggal</th>
                        <th class="px-4 py-3 text-center font-medium text-gray-600">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($tickets as $ticket)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td data-label="" class="px-4 py-3 text-center">
                                <input type="checkbox" class="rounded border-gray-300 text-green-600 focus:ring-green-500" value="{{ $ticket->id }}" x-model="selected">
                            </td>
                            <td data-label="Judul" class="px-4 py-3">
                                <a href="{{ route('tickets.show', $ticket) }}" class="font-medium text-gray-900 hover:text-green-600">{{ $ticket->title }}</a>
                            </td>
                            <td data-label="Kategori" class="px-4 py-3">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-700">{{ $ticket->category }}</span>
                            </td>
                            <td data-label="Lab" class="px-4 py-3 text-gray-600">{{ $ticket->laboratory->name ?? '-' }}</td>
                            <td data-label="Prioritas" class="px-4 py-3">
                                @if($ticket->priority === 'Tinggi')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-700">Tinggi</span>
                                @elseif($ticket->priority === 'Sedang')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-700">Sedang</span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-700">Rendah</span>
                                @endif
                            </td>
                            <td data-label="Status" class="px-4 py-3">
                                @if($ticket->status === 'Open')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-orange-100 text-orange-700">Open</span>
                                @elseif($ticket->status === 'In Progress')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-700">In Progress</span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-700">{{ $ticket->status }}</span>
                                @endif
                            </td>
                            <td data-label="Pelapor" class="px-4 py-3 text-gray-600">{{ $ticket->reporter_name ?? '-' }}</td>
                            <td data-label="Tanggal" class="px-4 py-3 text-gray-500 text-xs">{{ $ticket->created_at->format('d M Y') }}</td>
                            <td data-label="Aksi" class="px-4 py-3 text-center">
                                <div class="flex items-center justify-center gap-1">
                                    <a href="{{ route('tickets.show', $ticket) }}" class="p-1.5 text-blue-600 hover:bg-blue-50 rounded-lg transition-colors" title="Lihat">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        </svg>
                                    </a>
                                    <form onsubmit="return confirm('Hapus tiket ini?')" method="POST" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" formaction="{{ route('tickets.destroy', $ticket) }}" class="p-1.5 text-red-600 hover:bg-red-50 rounded-lg transition-colors" title="Hapus">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                                            </svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="px-4 py-12 text-center text-gray-500">
                                <svg class="w-12 h-12 mx-auto text-gray-300 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 6v.75m0 3v.75m0 3v.75m0 3V18m-9-5.25h5.25M7.5 15h3M3.375 5.25c-.621 0-1.125.504-1.125 1.125v3.026a2.999 2.999 0 010 5.198v3.026c0 .621.504 1.125 1.125 1.125h17.25c.621 0 1.125-.504 1.125-1.125v-3.026a2.999 2.999 0 010-5.198V6.375c0-.621-.504-1.125-1.125-1.125H3.375z" />
                                </svg>
                                <p class="text-sm font-medium">Tidak ada tiket ditemukan</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($tickets->hasPages())
            <div class="px-4 py-3 border-t border-gray-200">
                {{ $tickets->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
