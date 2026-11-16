@extends('layouts.app')

@section('title', 'Kartu Kendali Alat')
@section('header', 'Kartu Kendali Alat Laboratorium')

@section('content')
<div class="space-y-6">
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <form action="{{ route('alat.kartu') }}" method="GET" class="flex flex-col sm:flex-row gap-4 items-end">
            <div class="flex-1 w-full">
                <label class="block text-sm font-medium text-gray-700 mb-1">Laboratorium</label>
                <select name="laboratory_id" required class="w-full rounded-lg border-gray-300 border px-3 py-2 text-sm">
                    <option value="">Pilih Laboratorium</option>
                    <option value="all" {{ $selectedLabId === 'all' ? 'selected' : '' }}>Semua Laboratorium</option>
                    @foreach($laboratories as $lab)
                        <option value="{{ $lab->id }}" {{ $selectedLabId == $lab->id ? 'selected' : '' }}>{{ $lab->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex gap-2">
                <button type="submit" class="px-6 py-2 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700">Tampilkan</button>
                @if($selectedLabId)
                    <a href="{{ route('alat.kartu.print', ['laboratory_id' => $selectedLabId]) }}" target="_blank" class="px-6 py-2 bg-gray-600 text-white text-sm font-medium rounded-lg hover:bg-gray-700">Cetak</a>
                    <a href="{{ route('alat.labels', ['laboratory_id' => $selectedLabId]) }}" class="px-6 py-2 bg-emerald-600 text-white text-sm font-medium rounded-lg hover:bg-emerald-700">Label QR</a>
                @endif
            </div>
        </form>
    </div>

    @if($selectedLabId)
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="p-6 border-b border-gray-200">
                <h2 class="text-base font-semibold text-gray-800">Daftar Alat</h2>
            </div>
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-green-600 text-white">
                        <th class="px-4 py-3 text-center w-12">No</th>
                        <th class="px-4 py-3 text-left">Nama Alat</th>
                        <th class="px-4 py-3 text-left">No Inventaris</th>
                        <th class="px-4 py-3 text-left">Merk/Type</th>
                        <th class="px-4 py-3 text-left">Laboratorium</th>
                        <th class="px-4 py-3 text-center w-28">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($alat as $index => $item)
                        <tr>
                            <td class="px-4 py-3 text-center">{{ $index + 1 }}</td>
                            <td class="px-4 py-3">{{ $item->name }}</td>
                            <td class="px-4 py-3">{{ $item->code ?? '-' }}</td>
                            <td class="px-4 py-3">{{ $item->merk_type ?? '-' }}</td>
                            <td class="px-4 py-3">{{ $item->laboratory->name ?? '-' }}</td>
                            <td class="px-4 py-3" data-label="Aksi">
                                <div class="flex items-center justify-center gap-1 flex-wrap">
                                    <a href="{{ route('alat.checks', $item) }}"
                                        class="relative p-1.5 text-amber-600 hover:bg-amber-50 rounded-lg transition-colors"
                                        title="Input & lihat riwayat kartu kendali">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125" />
                                        </svg>
                                        @if($item->checks->count())
                                            <span class="absolute -top-1 -right-1 min-w-[16px] h-4 px-1 rounded-full bg-amber-600 text-white text-[10px] leading-4 text-center font-semibold">
                                                {{ $item->checks->count() }}
                                            </span>
                                        @endif
                                    </a>
                                    <a href="{{ route('alat.print-one', $item) }}" target="_blank"
                                        class="p-1.5 text-gray-600 hover:bg-gray-100 rounded-lg transition-colors"
                                        title="Cetak kartu kendali">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <polyline points="6 9 6 2 18 2 18 9" />
                                            <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2" />
                                            <rect x="6" y="14" width="12" height="8" />
                                        </svg>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-8 text-center text-gray-500">Tidak ada data alat.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    @endif
</div>
@endsection
