<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <title>Label Praktikum - SimKom</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/qrcodejs@1.0.0/qrcode.min.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        [x-cloak] { display: none !important; }
        @media print {
            @page { size: A3 portrait; margin: 12mm; }
            .no-print { display: none !important; }
            body { margin: 0; padding: 0; }
            .stiker-grid { gap: 10px; }
            .stiker-item { break-inside: avoid; page-break-inside: avoid; }
        }
        .stiker-grid {
            display: grid;
            grid-template-columns: repeat(3, 9cm);
            gap: 4px;
            justify-content: center;
        }
        .stiker-item {
            border: 1px solid #d1d5db;
            border-radius: 6px;
            padding: 3px;
            background: white;
            width: 8.5cm;
            height: 4cm;
            box-sizing: border-box;
            display: flex;
            flex-direction: row;
            align-items: center;
            justify-content: center;
            gap: 24px;
        }
        .number-section {
            flex: 0 0 auto;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }
        .meja-label {
            font-size: 14px;
            font-weight: 700;
            color: #374151;
            letter-spacing: 1px;
            margin-bottom: -2px;
        }
        .meja-number {
            font-size: 120px;
            font-weight: 800;
            color: #1f2937;
            line-height: 1;
            white-space: nowrap;
        }
        .qr-section {
            flex: 0 0 auto;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 6px;
        }
        .scan-label {
            font-size: 8px;
            color: #16a34a;
            font-weight: 600;
            text-transform: uppercase;
            text-align: center;
            line-height: 1.3;
        }
        .qr-container {
            flex-shrink: 0;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        .lab-name {
            font-size: 11px;
            color: #6b7280;
            font-weight: 500;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: 100%;
            text-align: center;
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
                <h1 class="text-xl font-bold text-gray-800">Label Praktikum</h1>
                <p class="text-sm text-gray-500 mt-1">Cetak label nomor meja dengan QR Code lapor kendala (3 kolom, A3)</p>
            </div>
            <form method="GET" action="{{ route('computers.praktikum-labels') }}" class="p-6">
                <div class="flex items-end gap-4">
                    <div class="flex-1">
                        <label for="laboratory_id" class="block text-sm font-medium text-gray-700 mb-1">Laboratorium</label>
                        <select name="laboratory_id" id="laboratory_id" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                            <option value="">Pilih Laboratorium</option>
                            <option value="all" {{ $selectedLab && $selectedLab->id === 'all' ? 'selected' : '' }}>Semua Laboratorium</option>
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
                            🖨️ Print Label
                        </button>
                    @endif
                </div>
            </form>
        </div>

        @if($selectedLab && $computers->count() > 0)
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 no-print mb-4">
                <p class="text-sm text-gray-600">
                    Menampilkan <span class="font-semibold">{{ $computers->count() }}</span> label meja
                    untuk <span class="font-semibold">{{ $selectedLab->name }}</span>
                </p>
            </div>
        @endif

        @if($computers->count() > 0)
            <div class="stiker-grid">
                @foreach($computers as $computer)
                    <div class="stiker-item">
                        <div class="number-section">
                            <div class="meja-label">MEJA</div>
                            <div class="meja-number">{{ str_pad($computer->nomor_meja, 2, '0', STR_PAD_LEFT) }}</div>
                        </div>
                        <div class="qr-section">
                            <div class="scan-label">LAPOR KENDALA<br>PRAKTIKUM</div>
                            <div class="qr-container">
                                <div id="qr-{{ $computer->id }}"></div>
                            </div>
                            <div class="lab-name">{{ $selectedLab->id === 'all' ? $computer->laboratory->name : $selectedLab->name }}</div>
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

    @if($computers->count() > 0)
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            @foreach($computers as $computer)
                new QRCode(document.getElementById("qr-{{ $computer->id }}"), {
                    text: "{{ route('lapor-kendala.create', $computer->id) }}",
                    width: 95,
                    height: 95,
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