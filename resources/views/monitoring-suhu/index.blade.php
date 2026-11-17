@extends('layouts.app')

@section('title', 'Monitoring Suhu')
@section('header', 'Monitoring Suhu Ruangan')

@section('content')
<div class="space-y-6">
    @if(session('success'))
        <div class="px-4 py-3 bg-green-50 border border-green-200 text-green-700 rounded-lg text-sm">{{ session('success') }}</div>
    @endif

    <form action="{{ route('monitoring-suhu.index') }}" method="GET" class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 flex flex-col sm:flex-row gap-4 items-end">
        <div class="flex-1 w-full">
            <label class="block text-sm font-medium text-gray-700 mb-1">Cari Ruangan</label>
            <input type="text" name="search" value="{{ $search }}" placeholder="Nama atau kode ruangan..."
                class="w-full rounded-lg border-gray-300 border px-3 py-2 text-sm">
        </div>
        <div class="sm:w-56 w-full">
            <label class="block text-sm font-medium text-gray-700 mb-1">Status Suhu</label>
            <select name="status" class="w-full rounded-lg border-gray-300 border px-3 py-2 text-sm">
                <option value="">Semua Status</option>
                @foreach(['Normal', 'Panas', 'Dingin'] as $statusOption)
                    <option value="{{ $statusOption }}" {{ $statusFilter === $statusOption ? 'selected' : '' }}>{{ $statusOption }}</option>
                @endforeach
            </select>
        </div>
        <div class="flex gap-2 flex-wrap">
            <button type="submit" class="px-6 py-2 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700">Tampilkan</button>
            @can('print-monitoring-suhu')
                <a href="{{ route('monitoring-suhu.labels') }}" class="px-6 py-2 bg-emerald-600 text-white text-sm font-medium rounded-lg hover:bg-emerald-700 transition-colors">Label QR</a>
            @endcan
            @can('export-monitoring-suhu')
                <a href="{{ route('monitoring-suhu.export', request()->query()) }}" class="px-6 py-2 bg-emerald-600 text-white text-sm font-medium rounded-lg hover:bg-emerald-700 transition-colors">Export Excel</a>
                <a href="{{ route('monitoring-suhu.export-pdf', request()->query()) }}" class="px-6 py-2 bg-gray-600 text-white text-sm font-medium rounded-lg hover:bg-gray-700 transition-colors">Export PDF</a>
            @endcan
            @if($search !== '' || $statusFilter)
                <a href="{{ route('monitoring-suhu.index') }}" class="px-6 py-2 text-sm text-gray-600 hover:underline">Reset</a>
            @endif
        </div>
    </form>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="p-6 border-b border-gray-200">
            <h2 class="text-base font-semibold text-gray-800">Daftar Ruangan</h2>
            <p class="text-sm text-gray-500 mt-1">Suhu terakhir setiap ruangan beserta status Normal, Panas, atau Dingin</p>
        </div>
        <table class="w-full text-sm table-responsive-cards">
            <thead>
                <tr class="bg-green-600 text-white">
                    <th class="px-4 py-3 text-center w-12">No</th>
                    <th class="px-4 py-3 text-left">Ruangan</th>
                    <th class="px-4 py-3 text-left">Kode</th>
                    <th class="px-4 py-3 text-left">Suhu Terakhir</th>
                    <th class="px-4 py-3 text-left">Status</th>
                    <th class="px-4 py-3 text-left">Ambang Normal</th>
                    <th class="px-4 py-3 text-center w-28">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($rooms as $index => $row)
                    @php($lab = $row['laboratory'])
                    <tr>
                        <td class="px-4 py-3 text-center">{{ $rooms->firstItem() + $index }}</td>
                        <td class="px-4 py-3 font-medium">
                            {{ $lab->name }}
                            @if($lab->location)
                                <span class="block text-xs font-normal text-gray-400">{{ $lab->location }}</span>
                            @endif
                        </td>
                        <td class="px-4 py-3">{{ $lab->code ?? '-' }}</td>
                        <td class="px-4 py-3">
                            @if($row['reading'])
                                <span class="font-semibold">{{ number_format($row['reading']->temperature, 1) }}°C</span>
                                <span class="block text-xs text-gray-400">{{ $row['reading']->recorded_at->translatedFormat('d F Y H:i') }}</span>
                            @else
                                <span class="text-gray-400">Belum ada data</span>
                            @endif
                        </td>
                        <td class="px-4 py-3" data-label="Status">
                            @php($status = $row['status'])
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ \App\Models\RoomTemperatureReading::statusBadge($status) }}">
                                {{ $status ?? 'Tidak ada ambang' }}
                            </span>
                        </td>
                        <td class="px-4 py-3">
                            @if($lab->temp_min !== null && $lab->temp_max !== null)
                                {{ $lab->temp_min + 0 }}–{{ $lab->temp_max + 0 }}°C
                            @else
                                <span class="text-gray-400">Belum diatur</span>
                            @endif
                        </td>
                        <td class="px-4 py-3" data-label="Aksi">
                            <div class="flex items-center justify-center gap-1 flex-wrap">
                                <a href="{{ route('monitoring-suhu.show', $lab) }}" class="p-1.5 text-blue-600 hover:bg-blue-50 rounded-lg transition-colors" title="Riwayat">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </a>
                                @if(auth()->user()->hasPermission('edit-laboratories'))
                                    <a href="{{ route('monitoring-suhu.threshold.edit', $lab) }}" class="p-1.5 text-amber-600 hover:bg-amber-50 rounded-lg transition-colors" title="Edit Ambang Suhu">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125" />
                                        </svg>
                                    </a>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-4 py-8 text-center text-gray-500">
                            @if($search !== '' || $statusFilter)
                                Tidak ada ruangan yang cocok dengan filter.
                            @else
                                Belum ada data ruangan.
                            @endif
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div>{{ $rooms->links() }}</div>
</div>
@endsection
