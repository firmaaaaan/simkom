@extends('layouts.app')

@section('title', 'Pengadaan')
@section('header', 'Pengadaan')

@section('content')
<div class="space-y-6" x-data="{ selected: [] }">
    {{-- Action Bar --}}
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <p class="text-sm text-gray-600">Kelola pengadaan software, hardware, dan PC laboratorium.</p>
        @if(auth()->user()->hasPermission('create-pengadaan'))
        <a href="{{ route('pengadaan.create') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700 transition-colors">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
            </svg>
            Buat Pengadaan
        </a>
        @endif
    </div>

    {{-- Success Message --}}
    @if(session('success'))
        <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg text-sm">
            {{ session('success') }}
        </div>
    @endif

    {{-- Bulk Delete --}}
    @if(auth()->user()->hasPermission('delete-pengadaan'))
    <div x-show="selected.length > 0" x-transition class="flex items-center gap-3">
        <span class="text-sm text-gray-600" x-text="selected.length + ' pengadaan dipilih'"></span>
        <form onsubmit="return confirm('Hapus pengadaan yang dipilih?')">
            @method('DELETE')
            @csrf
            <template x-for="id in selected" :key="id">
                <input type="hidden" name="ids[]" :value="id">
            </template>
            <button type="submit" formaction="{{ route('pengadaan.bulk-destroy') }}" class="px-3 py-1.5 bg-red-600 text-white text-xs font-medium rounded-lg hover:bg-red-700 transition-colors">
                Hapus Terpilih
            </button>
        </form>
    </div>
    @endif

    {{-- Filter --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
        <form action="{{ route('pengadaan.index') }}" method="GET" class="flex flex-col sm:flex-row gap-3">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari kode/nama barang..." class="flex-1 px-4 py-2 text-sm border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
            <select name="kategori" class="px-3 py-2 text-sm border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500">
                <option value="">Semua Kategori</option>
                <option value="Software" {{ request('kategori') == 'Software' ? 'selected' : '' }}>Software</option>
                <option value="Hardware" {{ request('kategori') == 'Hardware' ? 'selected' : '' }}>Hardware</option>
                <option value="PC" {{ request('kategori') == 'PC' ? 'selected' : '' }}>PC</option>
            </select>
            <select name="status" class="px-3 py-2 text-sm border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500">
                <option value="">Semua Status</option>
                <option value="Proses" {{ request('status') == 'Proses' ? 'selected' : '' }}>Proses</option>
                <option value="Acc" {{ request('status') == 'Acc' ? 'selected' : '' }}>Acc</option>
                <option value="Ditolak" {{ request('status') == 'Ditolak' ? 'selected' : '' }}>Ditolak</option>
            </select>
            <select name="academic_year_id" class="px-3 py-2 text-sm border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500">
                <option value="">Semua Semester</option>
                @foreach($academicYears as $year)
                    <option value="{{ $year->id }}" {{ request('academic_year_id') == $year->id ? 'selected' : '' }}>{{ $year->name }}</option>
                @endforeach
            </select>
            <button type="submit" class="px-4 py-2 bg-gray-600 text-white text-sm font-medium rounded-lg hover:bg-gray-700 transition-colors">
                Filter
            </button>
        </form>
    </div>

    {{-- Table --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm table-responsive-cards">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        @if(auth()->user()->hasPermission('delete-pengadaan'))
                        <th class="px-4 py-3 text-center w-10">
                            <input type="checkbox" class="rounded border-gray-300 text-green-600 focus:ring-green-500" @change="selected = $event.target.checked ? @js($pengadaans->pluck('id')->toArray()) : []" :checked="selected.length === @js($pengadaans->count()) && selected.length > 0">
                        </th>
                        @endif
                        <th class="px-4 py-3 text-left font-medium text-gray-600">Kode</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-600">Nama Barang</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-600">Kategori</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-600">Jumlah</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-600">Total Harga</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-600">Tanggal</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-600">Semester</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-600">Status</th>
                        <th class="px-4 py-3 text-center font-medium text-gray-600">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @php
                        $statusColors = [
                            'Proses' => 'bg-yellow-100 text-yellow-700',
                            'Acc' => 'bg-green-100 text-green-700',
                            'Ditolak' => 'bg-red-100 text-red-700',
                        ];
                        $kategoriColors = [
                            'Software' => 'bg-purple-100 text-purple-700',
                            'Hardware' => 'bg-blue-100 text-blue-700',
                            'PC' => 'bg-orange-100 text-orange-700',
                        ];
                    @endphp
                    @forelse($pengadaans as $pengadaan)
                        <tr class="hover:bg-gray-50 transition-colors">
                            @if(auth()->user()->hasPermission('delete-pengadaan'))
                            <td data-label="" class="px-4 py-3 text-center">
                                <input type="checkbox" class="rounded border-gray-300 text-green-600 focus:ring-green-500" value="{{ $pengadaan->id }}" x-model="selected">
                            </td>
                            @endif
                            <td data-label="Kode" class="px-4 py-3">
                                <a href="{{ route('pengadaan.show', $pengadaan) }}" class="font-medium text-gray-900 hover:text-green-600">{{ $pengadaan->kode }}</a>
                            </td>
                            <td data-label="Nama Barang" class="px-4 py-3 text-gray-900">{{ $pengadaan->nama_barang }}</td>
                            <td data-label="Kategori" class="px-4 py-3">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $kategoriColors[$pengadaan->kategori] ?? '' }}">{{ $pengadaan->kategori }}</span>
                            </td>
                            <td data-label="Jumlah" class="px-4 py-3 text-gray-600">{{ $pengadaan->jumlah }}</td>
                            <td data-label="Total Harga" class="px-4 py-3 text-gray-600">Rp {{ number_format($pengadaan->total, 0, ',', '.') }}</td>
                            <td data-label="Tanggal" class="px-4 py-3 text-gray-500 text-xs">{{ $pengadaan->tanggal->format('d M Y') }}</td>
                            <td data-label="Semester" class="px-4 py-3 text-gray-600 text-xs">{{ $pengadaan->academicYear->name ?? '-' }}</td>
                            <td data-label="Status" class="px-4 py-3">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $statusColors[$pengadaan->status] ?? '' }}">{{ $pengadaan->status }}</span>
                            </td>
                            <td data-label="Aksi" class="px-4 py-3 text-center">
                                <div class="flex items-center justify-center gap-1 flex-wrap">
                                    @if(auth()->user()->hasPermission('edit-pengadaan') && $pengadaan->status === 'Proses')
                                    <form method="POST" action="{{ route('pengadaan.update-status', $pengadaan) }}" class="inline">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="status" value="Acc">
                                        <button type="submit" class="px-2 py-1 rounded-lg bg-green-50 text-green-600 hover:bg-green-100 text-xs font-medium transition-colors" title="ACC">ACC</button>
                                    </form>
                                    <form method="POST" action="{{ route('pengadaan.update-status', $pengadaan) }}" class="inline">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="status" value="Ditolak">
                                        <button type="submit" class="px-2 py-1 rounded-lg bg-red-50 text-red-600 hover:bg-red-100 text-xs font-medium transition-colors" onclick="return confirm('Tolak pengadaan ini?')" title="Tolak">Tolak</button>
                                    </form>
                                    @endif
                                    <a href="{{ route('pengadaan.show', $pengadaan) }}" class="p-1.5 text-blue-600 hover:bg-blue-50 rounded-lg transition-colors" title="Lihat">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        </svg>
                                    </a>
                                    @if(auth()->user()->hasPermission('edit-pengadaan'))
                                    <a href="{{ route('pengadaan.edit', $pengadaan) }}" class="p-1.5 text-amber-600 hover:bg-amber-50 rounded-lg transition-colors" title="Ubah">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125" />
                                        </svg>
                                    </a>
                                    @endif
                                    @if(auth()->user()->hasPermission('delete-pengadaan'))
                                    <form onsubmit="return confirm('Hapus pengadaan ini?')" method="POST" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" formaction="{{ route('pengadaan.destroy', $pengadaan) }}" class="p-1.5 text-red-600 hover:bg-red-50 rounded-lg transition-colors" title="Hapus">
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
                            <td colspan="10" class="px-4 py-12 text-center text-gray-500">
                                <svg class="w-12 h-12 mx-auto text-gray-300 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 00-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 00-16.536-1.84M7.5 14.25L5.106 5.272M6 20.25a.75.75 0 11-1.5 0 .75.75 0 011.5 0zm12.75 0a.75.75 0 11-1.5 0 .75.75 0 011.5 0z" />
                                </svg>
                                <p class="text-sm font-medium">Tidak ada pengadaan ditemukan</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($pengadaans->hasPages())
            <div class="px-4 py-3 border-t border-gray-200">
                {{ $pengadaans->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
