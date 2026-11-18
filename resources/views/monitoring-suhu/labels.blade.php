<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}?v={{ @filemtime(public_path('favicon.svg')) }}">
    <title>Label Monitoring Suhu - SmartLabs</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/qrcodejs@1.0.0/qrcode.min.js"></script>
    <style>
        @media print {
            @page { size: A4 portrait; margin: 8mm; }
            .no-print { display: none !important; }
            body { margin: 0; padding: 0; background: #fff; }
            .label-grid { gap: 4mm; }
            .label-item { break-inside: avoid; page-break-inside: avoid; box-shadow: none !important; }
        }
        body { background: #f3f4f6; }
        .label-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 4mm;
        }
        .label-item {
            display: flex;
            gap: 3mm;
            align-items: center;
            border: 1px solid #9ca3af;
            border-radius: 2mm;
            padding: 3mm;
            background: #fff;
            box-shadow: 0 1px 2px rgba(0,0,0,.06);
            min-height: 28mm;
        }
        .label-item .qr {
            flex: 0 0 auto;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .label-item .qr img,
        .label-item .qr canvas { display: block; }
        .label-info { min-width: 0; flex: 1; }
        .label-info .nama {
            font-weight: 700;
            font-size: 11pt;
            line-height: 1.2;
            color: #111827;
            overflow-wrap: anywhere;
        }
        .label-info .baris {
            font-size: 8.5pt;
            color: #374151;
            line-height: 1.35;
            overflow-wrap: anywhere;
        }
        .label-info .baris b { color: #111827; }
        .label-info .lab {
            display: inline-block;
            margin-top: 1mm;
            font-size: 7.5pt;
            font-weight: 700;
            color: #065f46;
            background: #ecfdf5;
            border: 1px solid #a7f3d0;
            border-radius: 1mm;
            padding: 0.3mm 1.5mm;
        }
        .room-option {
            display: flex;
            align-items: flex-start;
            gap: 0.6rem;
            border: 1px solid #e5e7eb;
            border-radius: 0.5rem;
            padding: 0.6rem 0.75rem;
            background: #fff;
            cursor: pointer;
        }
        .room-option:hover { background: #f0fdf4; }
        .room-option.checked { border-color: #16a34a; background: #f0fdf4; }
        .room-option input { margin-top: 0.15rem; accent-color: #16a34a; }
        .room-option .nama { font-size: 0.875rem; font-weight: 600; color: #111827; }
        .room-option .meta { font-size: 0.75rem; color: #6b7280; }
        @media screen {
            .label-grid { grid-template-columns: repeat(auto-fill, minmax(260px, 1fr)); }
        }
    </style>
</head>
<body class="min-h-screen">
    <div class="px-4 sm:px-6 lg:px-8 py-6">
        {{-- Toolbar (tidak ikut dicetak) --}}
        <div class="no-print mb-5 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <a href="{{ route('monitoring-suhu.index') }}" class="text-green-600 hover:text-green-700 font-medium">&larr; Kembali ke Monitoring Suhu</a>
            <h1 class="text-xl font-bold text-gray-800 sm:hidden">Label Monitoring Suhu</h1>
        </div>

        <div class="no-print bg-white rounded-xl border border-gray-200 mb-6">
            <div class="px-6 py-4 border-b border-gray-200">
                <h1 class="text-xl font-bold text-gray-800">Label Monitoring Suhu Ruangan</h1>
                <p class="text-sm text-gray-500 mt-1">Centang ruangan yang ingin dicetak labelnya — tidak semua ruangan harus dicetak. Scan QR untuk melihat riwayat suhu.</p>
            </div>
            <form method="GET" action="{{ route('monitoring-suhu.labels') }}" class="p-6">
                <input type="hidden" name="filtered" value="1">

                <div class="flex flex-wrap items-end justify-between gap-3 mb-3">
                    <div>
                        <span class="block text-sm font-medium text-gray-700">Pilih Ruangan</span>
                        <span class="block text-xs text-gray-500 mt-0.5">Terpilih: <b id="selected-count">{{ count($selectedIds) }}</b> dari {{ $laboratories->count() }} ruangan</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <button type="button" id="select-all"
                            class="px-3 py-1.5 text-xs font-medium border border-green-600 text-green-700 bg-white rounded-lg hover:bg-green-50">
                            Pilih Semua
                        </button>
                        <button type="button" id="select-none"
                            class="px-3 py-1.5 text-xs font-medium border border-gray-300 text-gray-600 bg-white rounded-lg hover:bg-gray-50">
                            Hapus Pilihan
                        </button>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2 mb-4">
                    @foreach($laboratories as $lab)
                        <label class="room-option {{ in_array($lab->id, $selectedIds) ? 'checked' : '' }}">
                            <input type="checkbox" name="rooms[]" value="{{ $lab->id }}"
                                {{ in_array($lab->id, $selectedIds) ? 'checked' : '' }}>
                            <span class="min-w-0">
                                <span class="nama block truncate">{{ $lab->name }}</span>
                                <span class="meta block">
                                    {{ $lab->code ?? '-' }}
                                </span>
                            </span>
                        </label>
                    @endforeach
                </div>

                <div class="flex flex-wrap items-center gap-3">
                    <button type="submit" class="px-6 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 font-medium">
                        Tampilkan Label
                    </button>
                    @if($rooms->count())
                        <button type="button" onclick="window.print()"
                            class="px-6 py-2 bg-gray-800 text-white rounded-lg hover:bg-gray-900 font-medium">
                            Cetak Label
                        </button>
                    @endif
                    <a href="{{ route('monitoring-suhu.labels') }}" class="px-4 py-2 text-sm text-gray-600 hover:underline">Reset</a>
                </div>
            </form>
        </div>

        @if($rooms->count())
            <div class="no-print bg-white rounded-xl border border-gray-200 px-6 py-4 mb-6">
                <p class="text-sm text-gray-600">
                    Menampilkan <span class="font-semibold">{{ $rooms->count() }}</span> label dari
                    <span class="font-semibold">{{ $laboratories->count() }}</span> ruangan
                    @if($rooms->count() < $laboratories->count())
                        · siap dicetak, ruangan lain tidak ikut tercetak
                    @endif
                </p>
            </div>

            <div class="label-grid">
                @foreach($rooms as $room)
                    <div class="label-item">
                        <div class="qr">
                            <div id="qr-{{ $room->id }}"></div>
                        </div>
                        <div class="label-info">
                            <p class="nama">{{ $room->name }}</p>
                            <p class="baris"><b>Kode:</b> {{ $room->code ?? '-' }}</p>
                            @if($room->location)
                                <p class="baris"><b>Lokasi:</b> {{ $room->location }}</p>
                            @endif
                            <span class="lab">Monitoring Suhu</span>
                        </div>
                    </div>
                @endforeach
            </div>
        @elseif($filtered)
            <div class="no-print bg-white rounded-xl border border-gray-200 px-6 py-10 text-center text-gray-500">
                Tidak ada ruangan yang dicentang. Centang minimal satu ruangan lalu klik <b>Tampilkan Label</b>.
            </div>
        @else
            <div class="no-print bg-white rounded-xl border border-gray-200 px-6 py-10 text-center text-gray-500">
                Tidak ada data ruangan untuk ditampilkan.
            </div>
        @endif
    </div>

    @if($rooms->count())
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                @foreach($rooms as $room)
                    new QRCode(document.getElementById('qr-{{ $room->id }}'), {
                        text: @js(route('monitoring-suhu.scan', $room)),
                        width: 84,
                        height: 84,
                        colorDark: '#000000',
                        colorLight: '#ffffff',
                        correctLevel: QRCode.CorrectLevel.M
                    });
                @endforeach
            });
        </script>
    @endif

    <script>
        (function () {
            var boxes = Array.prototype.slice.call(document.querySelectorAll('input[name="rooms[]"]'));
            var counter = document.getElementById('selected-count');

            function refresh() {
                var total = boxes.filter(function (box) { return box.checked; }).length;
                counter.textContent = total;
                boxes.forEach(function (box) {
                    box.closest('.room-option').classList.toggle('checked', box.checked);
                });
            }

            boxes.forEach(function (box) {
                box.addEventListener('change', refresh);
            });

            document.getElementById('select-all').addEventListener('click', function () {
                boxes.forEach(function (box) { box.checked = true; });
                refresh();
            });

            document.getElementById('select-none').addEventListener('click', function () {
                boxes.forEach(function (box) { box.checked = false; });
                refresh();
            });

            refresh();
        })();
    </script>
</body>
</html>
