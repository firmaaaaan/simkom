<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Monitoring Suhu - {{ $laboratory->name }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @media print {
            @page { size: A4 portrait; margin: 10mm; }
            .no-print { display: none !important; }
        }
    </style>
</head>
<body class="bg-gray-100 min-h-screen">
    <div class="max-w-4xl mx-auto px-4 py-6">
        <div class="no-print mb-4 flex items-center justify-between gap-3">
            <a href="{{ route('login') }}" class="text-green-600 hover:text-green-700 font-medium text-sm">&larr; Masuk untuk input</a>
        </div>

        <div class="bg-white rounded-xl border border-gray-200 p-6 mb-4">
            <h1 class="text-xl font-bold text-gray-800 mb-4">Monitoring Suhu: {{ $laboratory->name }}</h1>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-8 gap-y-2 text-sm">
                <div><span class="font-semibold text-gray-700">Nama Ruangan</span><span class="text-gray-500 mx-2">:</span>{{ $laboratory->name }}</div>
                <div><span class="font-semibold text-gray-700">Kode</span><span class="text-gray-500 mx-2">:</span>{{ $laboratory->code ?? '-' }}</div>
                @if($laboratory->location)
                    <div><span class="font-semibold text-gray-700">Lokasi</span><span class="text-gray-500 mx-2">:</span>{{ $laboratory->location }}</div>
                @endif
                <div>
                    <span class="font-semibold text-gray-700">Ambang Normal</span><span class="text-gray-500 mx-2">:</span>
                    {{ $laboratory->temp_min !== null && $laboratory->temp_max !== null ? ($laboratory->temp_min + 0).'–'.($laboratory->temp_max + 0).'°C' : 'Belum diatur' }}
                </div>
                @if($latest)
                    <div>
                        <span class="font-semibold text-gray-700">Suhu Terakhir</span><span class="text-gray-500 mx-2">:</span>
                        {{ number_format($latest->temperature, 1) }}°C
                        <span class="ml-1 inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ \App\Models\RoomTemperatureReading::statusBadge($laboratory->temperatureStatus($latest)) }}">
                            {{ $laboratory->temperatureStatus($latest) ?? '-' }}
                        </span>
                    </div>
                    <div><span class="font-semibold text-gray-700">Waktu Terakhir</span><span class="text-gray-500 mx-2">:</span>{{ $latest->recorded_at->translatedFormat('d F Y H:i') }}</div>
                @endif
            </div>
        </div>

        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-base font-semibold text-gray-800">Riwayat Monitoring Suhu</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-green-600 text-white">
                            <th class="px-4 py-3 text-center w-12">No</th>
                            <th class="px-4 py-3 text-left">Waktu</th>
                            <th class="px-4 py-3 text-center">Suhu</th>
                            <th class="px-4 py-3 text-center">Kelembapan</th>
                            <th class="px-4 py-3 text-center">Status</th>
                            <th class="px-4 py-3 text-left">Catatan</th>
                            <th class="px-4 py-3 text-left">Petugas</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($readings as $index => $reading)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3 text-center text-gray-600">{{ $readings->firstItem() + $index }}</td>
                                <td class="px-4 py-3 text-gray-900 whitespace-nowrap">{{ $reading->recorded_at->translatedFormat('d F Y H:i') }}</td>
                                <td class="px-4 py-3 text-center font-semibold text-gray-900">{{ number_format($reading->temperature, 1) }}°C</td>
                                <td class="px-4 py-3 text-center">{{ $reading->kelembapan !== null ? number_format($reading->kelembapan, 1).'%' : '-' }}</td>
                                <td class="px-4 py-3 text-center">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ \App\Models\RoomTemperatureReading::statusBadge($reading->status()) }}">
                                        {{ $reading->status() ?? '-' }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-gray-600">{{ $reading->notes ?? '-' }}</td>
                                <td class="px-4 py-3 text-gray-900">{{ $reading->recordedBy->name ?? '-' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-4 py-10 text-center text-gray-500">
                                    Belum ada riwayat monitoring suhu untuk ruangan ini
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($readings->hasPages())
                <div class="no-print px-6 py-4 border-t border-gray-100">{{ $readings->links() }}</div>
            @endif
        </div>

        <p class="no-print text-center text-xs text-gray-400 mt-4">
            Scan QR pada label ruangan. Masuk ke sistem untuk menambah input monitoring suhu.
        </p>
    </div>
</body>
</html>
