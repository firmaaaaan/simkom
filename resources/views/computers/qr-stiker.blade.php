<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <title>QR Stiker - SimKom</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/qrcodejs@1.0.0/qrcode.min.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        [x-cloak] { display: none !important; }
        @media print {
            @page { size: A3 portrait; margin: 10mm; }
            .no-print { display: none !important; }
            body { margin: 0; padding: 0; }
            .stiker-grid { gap: 8px; }
            .stiker-item { break-inside: avoid; }
        }
        .stiker-grid {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            gap: 8px;
        }
        .stiker-item {
            border: 1px solid #d1d5db;
            border-radius: 8px;
            padding: 8px;
            text-align: center;
            background: white;
        }
        .stiker-item .qr-container {
            display: flex;
            justify-content: center;
            margin-bottom: 6px;
        }
    </style>
</head>
@php
    $specMap = $computers->mapWithKeys(fn ($c) => [
        $c->id => [
            'code' => $c->code,
            'status' => $c->status,
            'description' => $c->description,
            'laboratory' => $c->laboratory?->name,
            'hardware' => $c->hardware->map(fn ($hw) => [
                'code' => $hw->code,
                'name' => $hw->name,
                'brand' => $hw->brand,
                'model' => $hw->model,
                'category' => $hw->category,
                'description' => $hw->description,
            ])->values(),
            'software' => $c->software->map(fn ($sw) => [
                'code' => $sw->code,
                'name' => $sw->name,
                'version' => $sw->version,
                'license_type' => $sw->license_type,
                'category' => $sw->category,
                'status' => $sw->status,
            ])->values(),
        ],
    ]);
@endphp
<body class="bg-gray-100 min-h-screen" x-data="{
    previewUrl: null,
    spec: null,
    specs: @js($specMap),
    statusClass(s) {
        return s === 'Aktif' ? 'bg-green-100 text-green-800'
            : (s === 'Maintenance' ? 'bg-yellow-100 text-yellow-800' : 'bg-gray-100 text-gray-600');
    }
}">
    <div class="max-w-7xl mx-auto px-4 py-8">
        <div class="mb-6 no-print">
            <a href="{{ route('computers.index') }}" class="text-green-600 hover:text-green-700 font-medium">&larr; Kembali</a>
        </div>

        <div class="bg-white rounded-lg shadow-sm border border-gray-200 mb-8 no-print">
            <div class="px-6 py-4 border-b border-gray-200">
                <h1 class="text-xl font-bold text-gray-800">QR Stiker</h1>
                <p class="text-sm text-gray-500 mt-1">Cetak stiker QR Code untuk komputer</p>
            </div>
            <form method="GET" action="{{ route('computers.qr-stiker') }}" class="p-6">
                <div class="flex items-end gap-4">
                    <div class="flex-1">
                        <label for="laboratory_id" class="block text-sm font-medium text-gray-700 mb-1">Laboratorium</label>
                        <select name="laboratory_id" id="laboratory_id" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                            <option value="">Pilih Laboratorium</option>
                            <option value="all" {{ request('laboratory_id') === 'all' ? 'selected' : '' }}>Semua Laboratorium</option>
                            @foreach($laboratories as $lab)
                                <option value="{{ $lab->id }}" {{ $selectedLab && $selectedLab->id === $lab->id ? 'selected' : '' }}>
                                    {{ $lab->name }} ({{ $lab->code }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit" class="px-6 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 font-medium">
                        Tampilkan
                    </button>
                    @if($computers->count() > 0)
                        <button type="button" onclick="window.print()" class="px-6 py-2 bg-gray-800 text-white rounded-lg hover:bg-gray-900 font-medium">
                            🖨️ Print Stiker
                        </button>
                    @endif
                </div>
            </form>
        </div>

        @if($selectedLab && $computers->count() > 0)
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 no-print mb-4">
                <p class="text-sm text-gray-600">
                    Menampilkan <span class="font-semibold">{{ $computers->count() }}</span> stiker QR Code
                    untuk <span class="font-semibold">{{ $selectedLab->name }}</span>
                </p>
            </div>
        @endif

        @if($computers->count() > 0)
            <div class="stiker-grid">
                @foreach($computers as $computer)
                    <div class="stiker-item">
                        <div class="qr-container cursor-pointer" @click="previewUrl = '{{ route('kartu.show', $computer->id) }}'">
                            <div id="qr-{{ $computer->id }}"></div>
                        </div>
                        <p class="font-bold text-gray-800 text-sm">Kartu Kendali</p>
                        <p class="font-bold text-gray-800 text-sm">{{ $computer->code }}</p>
                        <div class="flex items-center justify-center gap-3 no-print">
                            <button type="button" @click="previewUrl = '{{ route('kartu.show', $computer->id) }}'"
                                class="text-xs text-green-600 hover:text-green-700 font-medium">
                                👁️ Preview
                            </button>
                            <button type="button" @click="spec = specs['{{ $computer->id }}']"
                                class="text-xs text-green-600 hover:text-green-700 font-medium">
                                🧾 Spesifikasi
                            </button>
                        </div>
                    </div>
                @endforeach
            </div>

        @elseif($selectedLab)
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-12 text-center">
                <p class="text-gray-500">Tidak ada komputer di laboratorium ini.</p>
            </div>
        @endif
    </div>

    {{-- Preview Modal --}}
    <div x-show="previewUrl !== null" x-cloak
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 no-print"
        @click="previewUrl = null"
        @keydown.escape.window="previewUrl = null">
        <div class="bg-white rounded-xl shadow-2xl w-full max-w-3xl max-h-[90vh] flex flex-col mx-4" @click.stop>
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200">
                <h3 class="font-bold text-gray-800">Preview Kartu Kendali (tampilan publik)</h3>
                <button @click="previewUrl = null" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <div class="flex-1 overflow-auto p-2">
                <iframe x-bind:src="previewUrl" class="w-full h-[75vh] border-0 rounded-lg"></iframe>
            </div>
        </div>
    </div>

    {{-- Spesifikasi Modal --}}
    <div x-show="spec !== null" x-cloak
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 no-print p-4"
        @click="spec = null"
        @keydown.escape.window="spec = null">
        <div class="bg-white rounded-xl shadow-2xl w-full max-w-2xl max-h-[90vh] flex flex-col" @click.stop>
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200">
                <div>
                    <h3 class="font-bold text-gray-800">Spesifikasi Komputer</h3>
                    <p class="text-sm text-gray-500 mt-0.5" x-text="spec?.code"></p>
                </div>
                <button @click="spec = null" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <div class="flex-1 overflow-auto p-6 space-y-5">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <p class="text-xs text-gray-500 mb-1">Laboratorium</p>
                        <p class="text-sm font-medium text-gray-800" x-text="spec?.laboratory ?? '-'"></p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 mb-1">Status</p>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                            :class="statusClass(spec?.status)" x-text="spec?.status"></span>
                    </div>
                </div>

                <div>
                    <p class="text-xs text-gray-500 mb-1">Keterangan</p>
                    <p class="text-sm text-gray-800" x-text="spec?.description || '-'"></p>
                </div>

                <div>
                    <p class="text-sm font-semibold text-gray-800 mb-2">
                        Hardware <span class="font-normal text-gray-400" x-text="'(' + (spec?.hardware ?? []).length + ')'"></span>
                    </p>
                    <template x-if="(spec?.hardware ?? []).length > 0">
                        <div class="overflow-x-auto border border-gray-200 rounded-lg">
                            <table class="w-full text-sm text-left">
                                <thead class="bg-gray-50 text-xs uppercase text-gray-500">
                                    <tr>
                                        <th class="px-3 py-2 font-medium">Kode</th>
                                        <th class="px-3 py-2 font-medium">Nama</th>
                                        <th class="px-3 py-2 font-medium">Brand</th>
                                        <th class="px-3 py-2 font-medium">Model</th>
                                        <th class="px-3 py-2 font-medium">Kategori</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100">
                                    <template x-for="hw in (spec?.hardware ?? [])" :key="hw.code">
                                        <tr>
                                            <td class="px-3 py-2 font-mono text-xs text-gray-600" x-text="hw.code"></td>
                                            <td class="px-3 py-2 text-gray-800" x-text="hw.name"></td>
                                            <td class="px-3 py-2 text-gray-600" x-text="hw.brand ?? '-'"></td>
                                            <td class="px-3 py-2 text-gray-600" x-text="hw.model ?? '-'"></td>
                                            <td class="px-3 py-2 text-gray-600" x-text="hw.category"></td>
                                        </tr>
                                    </template>
                                </tbody>
                            </table>
                        </div>
                    </template>
                    <template x-if="(spec?.hardware ?? []).length === 0">
                        <p class="text-sm text-gray-400 italic">Belum ada hardware</p>
                    </template>
                </div>

                <div>
                    <p class="text-sm font-semibold text-gray-800 mb-2">
                        Software <span class="font-normal text-gray-400" x-text="'(' + (spec?.software ?? []).length + ')'"></span>
                    </p>
                    <template x-if="(spec?.software ?? []).length > 0">
                        <ul class="space-y-2">
                            <template x-for="sw in (spec?.software ?? [])" :key="sw.code">
                                <li class="flex items-center justify-between gap-3 px-3 py-2 border border-gray-200 rounded-lg">
                                    <div>
                                        <p class="text-sm font-medium text-gray-800" x-text="sw.name"></p>
                                        <p class="text-xs text-gray-500" x-text="[sw.version, sw.category].filter(Boolean).join(' • ')"></p>
                                    </div>
                                    <span class="shrink-0 px-2 py-0.5 rounded-full text-xs font-medium"
                                        :class="sw.status === 'Aktif' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-600'"
                                        x-text="sw.status ?? sw.license_type ?? '-'"></span>
                                </li>
                            </template>
                        </ul>
                    </template>
                    <template x-if="(spec?.software ?? []).length === 0">
                        <p class="text-sm text-gray-400 italic">Belum ada software</p>
                    </template>
                </div>
            </div>
        </div>
    </div>

    @if($computers->count() > 0)
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            @foreach($computers as $computer)
                new QRCode(document.getElementById("qr-{{ $computer->id }}"), {
                    text: "{{ route('computers.card', $computer->id) }}",
                    width: 100,
                    height: 100,
                    colorDark: "#000000",
                    colorLight: "#ffffff",
                    correctLevel: QRCode.CorrectLevel.M
                });
            @endforeach
        });
    </script>
    @endif
</body>
</html>
