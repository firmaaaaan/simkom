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
            grid-template-columns: repeat(6, 1fr);
            gap: 12px;
        }
        .stiker-item {
            border: 1px solid #d1d5db;
            border-radius: 8px;
            padding: 12px;
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
<body class="bg-gray-100 min-h-screen" x-data="{ previewUrl: null }">
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
                        <p class="text-xs text-gray-500 mb-2">{{ $computer->laboratory->name ?? '-' }}</p>
                        <button type="button" @click="previewUrl = '{{ route('kartu.show', $computer->id) }}'"
                            class="text-xs text-green-600 hover:text-green-700 font-medium no-print">
                            👁️ Preview
                        </button>
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

    @if($computers->count() > 0)
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            @foreach($computers as $computer)
                new QRCode(document.getElementById("qr-{{ $computer->id }}"), {
                    text: "{{ route('kartu.show', $computer->id) }}",
                    width: 120,
                    height: 120,
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
