@extends('layouts.app')

@section('title', 'Inventaris Lab')
@section('header', 'Laporan Inventaris Laboratorium')

@section('content')
<div class="space-y-6">
    {{-- Filter --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <form action="{{ route('reports.inventory') }}" method="GET" class="flex flex-col sm:flex-row gap-4 items-end">
            <div class="flex-1 w-full">
                <label class="block text-sm font-medium text-gray-700 mb-1">Laboratorium</label>
                <select name="laboratory_id" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-green-500 focus:border-green-500">
                    <option value="all" {{ $selectedLabId === 'all' ? 'selected' : '' }}>Semua Laboratorium</option>
                    @foreach($laboratories as $lab)
                        <option value="{{ $lab->id }}" {{ $selectedLabId === $lab->id ? 'selected' : '' }}>
                            {{ $lab->name }}{{ $lab->code ? ' ('.$lab->code.')' : '' }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="flex flex-col sm:flex-row gap-4 sm:items-end">
                <label class="flex items-center gap-2 text-sm text-gray-700 cursor-pointer whitespace-nowrap">
                    <input type="hidden" name="details" value="0">
                    <input type="checkbox" name="details" value="1" {{ $includeDetails ? 'checked' : '' }}
                        class="rounded border-gray-300 text-green-600 focus:ring-green-500">
                    Sertakan rincian spesifikasi
                </label>
                <div class="flex gap-2">
                    <button type="submit" class="px-6 py-2 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700 transition-colors">
                        Tampilkan
                    </button>
                    @can('print-reports')
                    <button type="submit" formaction="{{ route('reports.inventory-print') }}" formtarget="_blank"
                        class="inline-flex items-center gap-2 px-6 py-2 border border-gray-300 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-50 transition-colors">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0110.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0l.229 2.523a1.125 1.125 0 01-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0021 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 00-1.913-.247M6.34 18H5.25A2.25 2.25 0 013 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 011.913-.247m10.5 0a48.536 48.536 0 00-10.5 0m10.5 0V3.375c0-.621-.504-1.125-1.125-1.125h-8.25c-.621 0-1.125.504-1.125 1.125v3.659" />
                        </svg>
                        Cetak
                    </button>
                    @endcan
                </div>
            </div>
        </form>
        <p class="text-xs text-gray-500 mt-3">
            Spesifikasi diambil dari komponen hardware yang terpasang pada tiap komputer (kategori RAM, Storage, Processor, Monitor).
            Kapasitas RAM dibaca dari nama komponen, mis. "8GB DDR4" &rarr; 8 GB, "2x8GB" &rarr; 16 GB.
            Kapasitas storage juga dibaca dari namanya, mis. "SSD 512GB" &rarr; SSD 512 GB, "HDD 1TB" &rarr; HDD 1 TB.
        </p>
    </div>

    @forelse($rows as $row)
        {{-- Rekap spesifikasi lab --}}
        <div class="bg-white rounded-xl border border-gray-200 p-6">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                <div>
                    <h2 class="text-base font-semibold text-gray-800">{{ $row['laboratory']->name }}</h2>
                    <p class="text-xs text-gray-500 mt-1">{{ $row['computers_count'] }} komputer terdata</p>
                </div>
                @if($row['laboratory']->code)
                    <span class="inline-flex items-center px-2.5 py-1 rounded-full bg-gray-100 text-gray-600 text-xs font-medium">
                        {{ $row['laboratory']->code }}
                    </span>
                @endif
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4 mt-4">
                <div class="border border-gray-200 rounded-lg p-4">
                    <h3 class="text-sm font-semibold text-gray-700 mb-2">RAM</h3>
                    @include('reports.partials.inventory-recap', ['items' => $row['ram'], 'total' => $row['computers_count']])
                </div>
                <div class="border border-gray-200 rounded-lg p-4">
                    <h3 class="text-sm font-semibold text-gray-700 mb-2">Storage (SSD/HDD)</h3>
                    @include('reports.partials.inventory-recap', ['items' => $row['storage'], 'total' => $row['computers_count']])
                </div>
                <div class="border border-gray-200 rounded-lg p-4">
                    <h3 class="text-sm font-semibold text-gray-700 mb-2">Processor</h3>
                    @include('reports.partials.inventory-recap', ['items' => $row['processors'], 'total' => $row['computers_count']])
                </div>
                <div class="border border-gray-200 rounded-lg p-4">
                    <h3 class="text-sm font-semibold text-gray-700 mb-2">Monitor</h3>
                    @include('reports.partials.inventory-recap', ['items' => $row['monitors'], 'total' => $row['computers_count']])
                </div>
            </div>
        </div>

        {{-- Rincian per komputer --}}
        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100">
                <h2 class="text-base font-semibold text-gray-800">Rincian Spesifikasi</h2>
                <p class="text-xs text-gray-500 mt-1">Satu baris per komputer di {{ $row['laboratory']->name }}</p>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm table-responsive-cards">
                    <thead>
                        <tr class="border-b border-gray-100">
                            <th class="text-left px-6 py-3 font-medium text-gray-500 w-10">No</th>
                            <th class="text-left px-6 py-3 font-medium text-gray-500">Kode Komputer</th>
                            <th class="text-left px-6 py-3 font-medium text-gray-500">Kode Inventaris</th>
                            <th class="text-left px-6 py-3 font-medium text-gray-500">RAM</th>
                            <th class="text-left px-6 py-3 font-medium text-gray-500">Storage</th>
                            <th class="text-left px-6 py-3 font-medium text-gray-500">Processor</th>
                            <th class="text-left px-6 py-3 font-medium text-gray-500">Monitor</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($row['details'] as $index => $detail)
                            <tr class="border-b border-gray-50 hover:bg-gray-50 transition-colors">
                                <td data-label="No" class="px-6 py-3 text-gray-500">{{ $index + 1 }}</td>
                                <td data-label="Kode Komputer" class="px-6 py-3 font-medium text-gray-800">{{ $detail['computer']->code }}</td>
                                <td data-label="Kode Inventaris" class="px-6 py-3 text-gray-600">{{ $detail['computer']->barcode ?? '-' }}</td>
                                <td data-label="RAM" class="px-6 py-3 text-gray-600">{{ $detail['ram'] }}</td>
                                <td data-label="Storage" class="px-6 py-3 text-gray-600">{{ $detail['storage'] }}</td>
                                <td data-label="Processor" class="px-6 py-3 text-gray-600">{{ $detail['processor'] }}</td>
                                <td data-label="Monitor" class="px-6 py-3 text-gray-600">{{ $detail['monitor'] }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-10 text-center text-sm text-gray-500">
                                    Belum ada komputer di laboratorium ini
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @empty
        <div class="bg-white rounded-xl border border-gray-200 p-12 text-center">
            <p class="text-sm text-gray-500">Belum ada laboratorium untuk ditampilkan.</p>
        </div>
    @endforelse
</div>
@endsection
