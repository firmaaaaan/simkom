@extends('layouts.app')

@section('title', 'Riwayat Suhu - '.$laboratory->name)
@section('header', 'Riwayat Monitoring Suhu')

@section('content')
<div class="space-y-6">
    @if(session('success'))
        <div class="px-4 py-3 bg-green-50 border border-green-200 text-green-700 rounded-lg text-sm">{{ session('success') }}</div>
    @endif

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
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
            @can('print-monitoring-suhu')
                <a href="{{ route('monitoring-suhu.labels', ['laboratory_id' => $laboratory->id]) }}" class="px-6 py-2 bg-emerald-600 text-white text-sm font-medium rounded-lg hover:bg-emerald-700 transition-colors">Label QR</a>
            @endcan
            @can('export-monitoring-suhu')
                <a href="{{ route('monitoring-suhu.export', array_filter(['laboratory_id' => $laboratory->id, 'from' => $from, 'to' => $to])) }}" class="px-6 py-2 bg-emerald-600 text-white text-sm font-medium rounded-lg hover:bg-emerald-700 transition-colors">Export Excel</a>
                <a href="{{ route('monitoring-suhu.export-pdf', array_filter(['laboratory_id' => $laboratory->id, 'from' => $from, 'to' => $to])) }}" class="px-6 py-2 bg-gray-600 text-white text-sm font-medium rounded-lg hover:bg-gray-700 transition-colors">Export PDF</a>
            @endcan
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        @can('create-monitoring-suhu')
            <div class="lg:col-span-1">
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <h2 class="text-base font-semibold text-gray-800 mb-4">Input Suhu Baru</h2>
                    @if($errors->any())
                        <div class="px-4 py-3 bg-red-50 border border-red-200 text-red-700 rounded-lg text-sm mb-4 space-y-1">
                            @foreach($errors->all() as $error)
                                <p>{{ $error }}</p>
                            @endforeach
                        </div>
                    @endif
                    <form action="{{ route('monitoring-suhu.store') }}" method="POST" class="space-y-4">
                        @csrf
                        <input type="hidden" name="laboratory_id" value="{{ $laboratory->id }}">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Suhu (°C)</label>
                            <input type="number" name="temperature" value="{{ old('temperature') }}" step="0.1" min="0" max="100" required
                                placeholder="Mis. 24.5"
                                class="w-full rounded-lg border-gray-300 border px-3 py-2 text-sm">
                            @error('temperature') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                            @if($laboratory->temp_min !== null && $laboratory->temp_max !== null)
                                <p class="text-xs text-gray-400 mt-1">Ambang normal ruangan ini: {{ $laboratory->temp_min + 0 }}–{{ $laboratory->temp_max + 0 }}°C</p>
                            @endif
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Waktu Pencatatan</label>
                            <input type="datetime-local" name="recorded_at" value="{{ old('recorded_at', $defaultTime) }}" required
                                class="w-full rounded-lg border-gray-300 border px-3 py-2 text-sm">
                            @error('recorded_at') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Catatan (opsional)</label>
                            <textarea name="notes" rows="3" maxlength="255" class="w-full rounded-lg border-gray-300 border px-3 py-2 text-sm">{{ old('notes') }}</textarea>
                            @error('notes') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div class="flex items-center gap-2 flex-wrap">
                            <button type="submit" name="action" value="save" class="px-6 py-2 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700">Simpan</button>
                            <button type="submit" name="action" value="save_another" class="px-6 py-2 bg-gray-600 text-white text-sm font-medium rounded-lg hover:bg-gray-700">Simpan & Tambah Lagi</button>
                        </div>
                    </form>
                </div>
            </div>
        @endcan

        <div class="{{ auth()->user()->hasPermission('create-monitoring-suhu') ? 'lg:col-span-2' : 'lg:col-span-3' }} space-y-6">
            <form action="{{ route('monitoring-suhu.show', $laboratory) }}" method="GET" class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 flex flex-col sm:flex-row gap-4 items-end">
                <div class="flex-1">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Dari Tanggal</label>
                    <input type="date" name="from" value="{{ $from }}" class="w-full rounded-lg border-gray-300 border px-3 py-2 text-sm">
                </div>
                <div class="flex-1">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Sampai Tanggal</label>
                    <input type="date" name="to" value="{{ $to }}" class="w-full rounded-lg border-gray-300 border px-3 py-2 text-sm">
                </div>
                <button type="submit" class="px-6 py-2 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700">Tampilkan</button>
                @if($from || $to)
                    <a href="{{ route('monitoring-suhu.show', $laboratory) }}" class="px-6 py-2 text-sm text-gray-600 hover:underline">Reset</a>
                @endif
            </form>

            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
                    <h2 class="text-base font-semibold text-gray-800">Riwayat Monitoring Suhu</h2>
                    <span class="text-xs text-gray-500">{{ $readings->total() }} entri</span>
                </div>
                <table class="w-full text-sm table-responsive-cards">
                    <thead>
                        <tr class="bg-green-600 text-white">
                            <th class="px-4 py-3 text-center w-12">No</th>
                            <th class="px-4 py-3 text-left">Waktu</th>
                            <th class="px-4 py-3 text-left">Suhu</th>
                            <th class="px-4 py-3 text-left">Status</th>
                            <th class="px-4 py-3 text-left">Catatan</th>
                            <th class="px-4 py-3 text-left">Diinput Oleh</th>
                            <th class="px-4 py-3 text-center w-28">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($readings as $index => $reading)
                            <tr>
                                <td class="px-4 py-3 text-center">{{ $readings->firstItem() + $index }}</td>
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
                                    <div class="flex items-center justify-center">
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
                                    </div>
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
    </div>
</div>
@endsection
