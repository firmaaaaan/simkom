<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Kartu Kendali - {{ $alat->name }}</title>
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
            <button type="button" onclick="window.print()" class="px-4 py-2 bg-gray-800 text-white text-sm rounded-lg hover:bg-gray-900">Cetak</button>
        </div>

        <div class="bg-white rounded-xl border border-gray-200 p-6 mb-4">
            <h1 class="text-xl font-bold text-gray-800 mb-4">Kartu Kendali: {{ $alat->name }}</h1>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-8 gap-y-2 text-sm">
                <div><span class="font-semibold text-gray-700">Nama Alat</span><span class="text-gray-500 mx-2">:</span>{{ $alat->name }}</div>
                <div><span class="font-semibold text-gray-700">No Inventaris</span><span class="text-gray-500 mx-2">:</span>{{ $alat->code ?? '-' }}</div>
                <div><span class="font-semibold text-gray-700">Merk/Type</span><span class="text-gray-500 mx-2">:</span>{{ $alat->merk_type ?? '-' }}</div>
                <div><span class="font-semibold text-gray-700">Laboratorium</span><span class="text-gray-500 mx-2">:</span>{{ $alat->laboratory->name ?? '-' }}</div>
                @if($alat->location)
                    <div><span class="font-semibold text-gray-700">Lokasi</span><span class="text-gray-500 mx-2">:</span>{{ $alat->location }}</div>
                @endif
            </div>
        </div>

        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-base font-semibold text-gray-800">Riwayat Kartu Kendali</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-green-600 text-white">
                            <th class="px-4 py-3 text-center w-12">No</th>
                            <th class="px-4 py-3 text-left">Tanggal</th>
                            <th class="px-4 py-3 text-center">Baik</th>
                            <th class="px-4 py-3 text-center">Rusak</th>
                            <th class="px-4 py-3 text-left">Keterangan/Paraf</th>
                            <th class="px-4 py-3 text-left">Petugas</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($checks as $index => $check)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3 text-center text-gray-600">{{ $index + 1 }}</td>
                                <td class="px-4 py-3 text-gray-900 whitespace-nowrap">{{ $check->check_date->translatedFormat('d F Y') }}</td>
                                <td class="px-4 py-3 text-center">
                                    @if($check->status === 'Baik')
                                        <span class="inline-flex items-center justify-center w-5 h-5 rounded border-2 border-green-500 bg-green-500">
                                            <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                                        </span>
                                    @else
                                        <span class="inline-flex items-center justify-center w-5 h-5 rounded border border-gray-300 bg-white"></span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-center">
                                    @if($check->status === 'Rusak')
                                        <span class="inline-flex items-center justify-center w-5 h-5 rounded border-2 border-red-500 bg-red-500">
                                            <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                                        </span>
                                    @else
                                        <span class="inline-flex items-center justify-center w-5 h-5 rounded border border-gray-300 bg-white"></span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-gray-600">{{ $check->notes ?? '-' }}</td>
                                <td class="px-4 py-3 text-gray-900">{{ $check->checkedBy->name ?? '-' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-10 text-center text-gray-500">
                                    Belum ada riwayat kartu kendali untuk alat ini
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <p class="no-print text-center text-xs text-gray-400 mt-4">
            Scan QR pada label alat. Masuk ke sistem untuk menambah entri kartu kendali.
        </p>
    </div>
</body>
</html>
