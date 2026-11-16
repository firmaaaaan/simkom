@extends('layouts.app')

@section('title', 'Riwayat Suhu - '.$laboratory->name)
@section('header', 'Riwayat Monitoring Suhu')

@section('content')
<div class="space-y-4">
    @if(session('success'))
        <div class="px-4 py-3 bg-green-50 border border-green-200 text-green-700 rounded-lg text-sm">{{ session('success') }}</div>
    @endif

    <div class="bg-white rounded-xl border border-gray-200 p-5">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
            <div>
                <a href="{{ route('monitoring-suhu.index') }}" class="inline-flex items-center gap-1.5 text-sm text-gray-500 hover:text-green-600 transition-colors mb-2">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" /></svg>
                    Kembali ke Daftar Ruangan
                </a>
                <h2 class="text-lg font-bold text-gray-800">{{ $laboratory->name }}</h2>
                <p class="text-sm text-gray-500 mt-0.5">
                    {{ $laboratory->code ?? '-' }}{{ $laboratory->location ? ' · '.$laboratory->location : '' }}
                </p>
            </div>
            <div class="flex items-center gap-6">
                <div class="text-center">
                    <p class="text-xs text-gray-500 uppercase tracking-wide">Suhu Terakhir</p>
                    @if($latest)
                        <p class="text-2xl font-bold text-gray-800">{{ number_format($latest->temperature, 1) }}°C</p>
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ \App\Models\RoomTemperatureReading::statusBadge($laboratory->temperatureStatus($latest)) }}">
                            {{ $laboratory->temperatureStatus($latest) ?? 'Tidak ada ambang' }}
                        </span>
                    @else
                        <p class="text-2xl font-bold text-gray-400">—</p>
                        <span class="text-xs text-gray-400">Belum ada data</span>
                    @endif
                </div>
                <div class="text-center">
                    <p class="text-xs text-gray-500 uppercase tracking-wide">Ambang Normal</p>
                    @if($laboratory->temp_min !== null && $laboratory->temp_max !== null)
                        <p class="text-2xl font-bold text-gray-800">{{ $laboratory->temp_min + 0 }}–{{ $laboratory->temp_max + 0 }}°C</p>
                    @else
                        <p class="text-2xl font-bold text-gray-400">—</p>
                        <span class="text-xs text-gray-400">Belum diatur</span>
                    @endif
                </div>
            </div>
        </div>
        <div class="flex items-center gap-2 flex-wrap mt-4 pt-4 border-t border-gray-100">
            @can('create-monitoring-suhu')
                <a href="{{ route('monitoring-suhu.create', ['laboratory_id' => $laboratory->id]) }}" class="inline-flex items-center gap-2 px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700 transition-colors">
                    Input Suhu
                </a>
            @endcan
            @can('print-monitoring-suhu')
                <a href="{{ route('monitoring-suhu.labels', ['laboratory_id' => $laboratory->id]) }}" class="inline-flex items-center gap-2 px-4 py-2 border border-green-600 text-green-600 text-sm font-medium rounded-lg hover:bg-green-50 transition-colors">
                    Label QR
                </a>
            @endcan
            @can('export-monitoring-suhu')
                <a href="{{ route('monitoring-suhu.export', array_filter(['laboratory_id' => $laboratory->id, 'from' => $from, 'to' => $to])) }}" class="inline-flex items-center gap-2 px-4 py-2 border border-emerald-600 text-emerald-600 text-sm font-medium rounded-lg hover:bg-emerald-50 transition-colors">
                    Export Excel
                </a>
                <a href="{{ route('monitoring-suhu.export-pdf', array_filter(['laboratory_id' => $laboratory->id, 'from' => $from, 'to' => $to])) }}" class="inline-flex items-center gap-2 px-4 py-2 border border-emerald-600 text-emerald-600 text-sm font-medium rounded-lg hover:bg-emerald-50 transition-colors">
                    Export PDF
                </a>
            @endcan
        </div>
    </div>

    <form action="{{ route('monitoring-suhu.show', $laboratory) }}" method="GET" class="bg-white rounded-xl border border-gray-200 p-4 flex flex-col sm:flex-row gap-3 items-end">
        <div class="flex-1">
            <label class="block text-sm font-medium text-gray-700 mb-1">Dari Tanggal</label>
            <input type="date" name="from" value="{{ $from }}" class="w-full rounded-lg border-gray-300 border px-3 py-2 text-sm">
        </div>
        <div class="flex-1">
            <label class="block text-sm font-medium text-gray-700 mb-1">Sampai Tanggal</label>
            <input type="date" name="to" value="{{ $to }}" class="w-full rounded-lg border-gray-300 border px-3 py-2 text-sm">
        </div>
        <button type="submit" class="px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700">Tampilkan</button>
        @if($from || $to)
            <a href="{{ route('monitoring-suhu.show', $laboratory) }}" class="px-4 py-2 text-sm text-gray-600 hover:underline">Reset</a>
        @endif
    </form>

    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <div class="px-4 py-3 border-b border-gray-100 flex items-center justify-between">
            <h3 class="text-sm font-semibold text-gray-700">Riwayat Monitoring Suhu</h3>
            <span class="text-xs text-gray-500">{{ $readings->total() }} entri</span>
        </div>
        <table class="w-full text-sm table-responsive-cards">
            <thead>
                <tr class="border-b border-gray-100">
                    <th class="text-left px-4 py-3 font-medium text-gray-500">No</th>
                    <th class="text-left px-4 py-3 font-medium text-gray-500">Waktu</th>
                    <th class="text-left px-4 py-3 font-medium text-gray-500">Suhu</th>
                    <th class="text-left px-4 py-3 font-medium text-gray-500">Status</th>
                    <th class="text-left px-4 py-3 font-medium text-gray-500">Catatan</th>
                    <th class="text-left px-4 py-3 font-medium text-gray-500">Diinput Oleh</th>
                    <th class="text-left px-4 py-3 font-medium text-gray-500">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($readings as $index => $reading)
                    <tr class="border-b border-gray-50 hover:bg-gray-50">
                        <td class="px-4 py-3">{{ $readings->firstItem() + $index }}</td>
                        <td class="px-4 py-3 whitespace-nowrap">{{ $reading->recorded_at->translatedFormat('d F Y H:i') }}</td>
                        <td class="px-4 py-3 font-semibold">{{ number_format($reading->temperature, 1) }}°C</td>
                        <td class="px-4 py-3" data-label="Status">
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ \App\Models\RoomTemperatureReading::statusBadge($reading->status()) }}">
                                {{ $reading->status() ?? '-' }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-gray-600">{{ $reading->notes ?? '-' }}</td>
                        <td class="px-4 py-3">{{ $reading->recordedBy->name ?? '-' }}</td>
                        <td class="px-4 py-3" data-label="Aksi">
                            @can('delete-monitoring-suhu')
                                <form action="{{ route('monitoring-suhu.readings.destroy', [$laboratory, $reading]) }}" method="POST" onsubmit="return confirm('Hapus entri suhu ini?')" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-1.5 text-red-600 hover:bg-red-50 rounded-lg transition-colors" title="Hapus">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                                        </svg>
                                    </button>
                                </form>
                            @endcan
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-4 py-8 text-center text-gray-500">
                            @if($from || $to)
                                Tidak ada riwayat suhu pada rentang tanggal tersebut.
                            @else
                                Belum ada riwayat monitoring suhu untuk ruangan ini.
                            @endif
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div>{{ $readings->links() }}</div>
</div>
@endsection
