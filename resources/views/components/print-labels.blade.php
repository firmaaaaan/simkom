<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <title>Cetak Label Box - SimKom</title>
    <script src="https://cdn.jsdelivr.net/npm/qrcodejs@1.0.0/qrcode.min.js"></script>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        @page {
            size: A3 portrait;
            margin: 8mm;
        }

        body {
            font-family: 'Inter', 'Segoe UI', system-ui, -apple-system, sans-serif;
            background: #fff;
        }

        .print-header {
            display: none;
        }

        .labels-container {
            display: grid;
            grid-template-columns: repeat(2, 138mm);
            grid-auto-rows: 60mm;
            gap: 4mm;
            justify-content: center;
        }

        .label {
            width: 138mm;
            height: 60mm;
            border: 1.5px solid #333;
            border-radius: 3mm;
            display: flex;
            overflow: hidden;
            page-break-inside: avoid;
            break-inside: avoid;
        }

        .label-content {
            flex: 1;
            padding: 3mm 3mm 3mm 4mm;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            min-width: 0;
        }

        .label-top {
            display: flex;
            flex-direction: column;
            gap: 0.5mm;
        }

        .label-info {
            display: flex;
            flex-direction: column;
            gap: 0.5mm;
            min-width: 0;
        }

        .label-code {
            font-size: 14pt;
            font-weight: 800;
            color: #111;
            letter-spacing: 0.3mm;
            line-height: 1.1;
            font-family: 'Courier New', monospace;
        }

        .label-name {
            font-size: 8pt;
            font-weight: 600;
            color: #333;
            line-height: 1.2;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .label-location {
            font-size: 6.5pt;
            color: #666;
            display: flex;
            align-items: center;
            gap: 1mm;
        }

        .label-location svg {
            width: 2.5mm;
            height: 2.5mm;
            flex-shrink: 0;
        }

        .label-institution {
            font-size: 5.5pt;
            font-weight: 700;
            color: #222;
            line-height: 1.2;
            margin-bottom: 0.5mm;
        }

        .label-notes {
            border-top: 0.5px solid #ccc;
            padding-top: 1.5mm;
            margin-top: 1mm;
        }

        .label-notes-title {
            font-size: 6.5pt;
            font-weight: 700;
            color: #333;
            margin-bottom: 0.5mm;
        }

        .label-notes-text {
            font-size: 5.5pt;
            color: #555;
            line-height: 1.4;
            white-space: pre-wrap;
        }

        .label-rules {
            border-top: 0.5px solid #ccc;
            padding-top: 1.5mm;
            margin-top: 1mm;
        }

        .label-rules-title {
            font-size: 6.5pt;
            font-weight: 700;
            color: #333;
            margin-bottom: 0.5mm;
        }

        .label-rules ul {
            font-size: 5.5pt;
            color: #555;
            line-height: 1.4;
            padding-left: 2.5mm;
            margin: 0;
        }

        .label-rules ul li {
            margin-bottom: 0.2mm;
        }

        .label-qr {
            width: 38mm;
            height: 60mm;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 1.5mm;
            background: #f9fafb;
            border-left: 1px solid #e5e7eb;
            flex-shrink: 0;
        }

        .label-qr-text {
            font-size: 8pt;
            font-weight: 700;
            color: #333;
            text-transform: uppercase;
            letter-spacing: 0.5mm;
        }

        .label-qr .qr-placeholder {
            width: 32mm;
            height: 32mm;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .label-qr .qr-placeholder img,
        .label-qr .qr-placeholder canvas {
            display: block;
        }

        @media print {
            .print-header {
                display: none !important;
            }

            body {
                background: #fff;
            }

            .labels-container {
                gap: 4mm;
            }
        }

        @media screen {
            .print-header {
                display: flex;
                align-items: center;
                justify-content: space-between;
                padding: 16px 24px;
                background: #f9fafb;
                border-bottom: 1px solid #e5e7eb;
                position: sticky;
                top: 0;
                z-index: 100;
            }

            .print-header-left {
                display: flex;
                align-items: center;
                gap: 16px;
            }

            .print-header h1 {
                font-size: 18px;
                font-weight: 700;
                color: #111;
            }

            .print-header p {
                font-size: 13px;
                color: #666;
                margin-top: 2px;
            }

            .filter-group {
                display: flex;
                align-items: center;
                gap: 8px;
            }

            .filter-group label {
                font-size: 13px;
                font-weight: 500;
                color: #374151;
            }

            .filter-group select {
                padding: 8px 12px;
                border: 1px solid #d1d5db;
                border-radius: 6px;
                font-size: 13px;
                background: #fff;
                color: #111;
                min-width: 200px;
                cursor: pointer;
            }

            .filter-group select:focus {
                outline: none;
                border-color: #16a34a;
                box-shadow: 0 0 0 3px rgba(22, 163, 74, 0.1);
            }

            .print-header button {
                padding: 10px 24px;
                background: #16a34a;
                color: #fff;
                border: none;
                border-radius: 8px;
                font-size: 14px;
                font-weight: 600;
                cursor: pointer;
                transition: background 0.2s;
            }

            .print-header button:hover {
                background: #15803d;
            }

            body {
                background: #e5e7eb;
                padding: 20px;
            }

            .labels-container {
                background: #fff;
                padding: 8mm;
                border-radius: 8px;
                box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            }
        }
    </style>
</head>
<body>
    <div class="print-header">
        <div class="print-header-left">
            <div>
                <h1>Cetak Label Box</h1>
                <p>{{ $boxes->count() }} label siap cetak &middot; Kertas: A3 Portrait &middot; Ukuran label: 13.8 x 6 cm &middot; 2 kolom x 7 baris = 14 label/halaman</p>
            </div>
            <div class="filter-group">
                <label for="groupFilter">Grup Box:</label>
                <select id="groupFilter" onchange="filterGroups(this.value)">
                    <option value="">Semua Grup</option>
                    @foreach($groups as $g)
                        <option value="{{ $g }}" {{ $g === $group ? 'selected' : '' }}>{{ $g }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <button onclick="window.print()">
            <svg style="width:16px;height:16px;display:inline;vertical-align:middle;margin-right:6px" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0110.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0l.229 2.523a1.125 1.125 0 01-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0021 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 00-1.913-.247M6.34 18H5.25A2.25 2.25 0 013 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 011.913-.247m0 0a48.159 48.159 0 018.5 0m-8.5 0V6.75a2 2 0 012-2h4.5a2 2 0 012 2v1.034" />
            </svg>
            Cetak Label
        </button>
    </div>

    <div class="labels-container">
        @foreach($boxes as $box)
            <div class="label">
                <div class="label-content">
                    <div class="label-top">
                        <div class="label-institution">UPT LABORATORIUM TERPADU UNIVERSITAS 'AISYIYAH YOGYAKARTA</div>
                        <div class="label-info">
                            <div class="label-code">{{ $box->code }}</div>
                            <div class="label-name">{{ $box->name }}</div>
                            @if($box->location)
                                <div class="label-location">
                                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z" />
                                    </svg>
                                    {{ $box->location }}
                                </div>
                            @endif
                            <div class="label-location">
                                <span style="font-weight:600;">Nama:</span>&nbsp;_________________________
                            </div>
                            <div class="label-location">
                                <span style="font-weight:600;">Kelas:</span>&nbsp;_________________________
                            </div>
                        </div>
                    </div>
                    @if($box->notes)
                        <div class="label-notes">
                            <div class="label-notes-title">Catatan:</div>
                            <div class="label-notes-text">{{ $box->notes }}</div>
                        </div>
                    @else
                        <div class="label-rules">
                            <div class="label-rules-title">Catatan:</div>
                            <ul>
                                <li>Dilarang memindahkan komponen dari satu box ke box lain.</li>
                                <li>Toleransi kerusakan komponen akibat kelalaian pengguna 1 kali, lebih dari itu silakan membawa komponen sendiri.</li>
                                <li>Dilarang membawa pulang box dan/atau komponen</li>
                            </ul>
                        </div>
                    @endif
                </div>
                <div class="label-qr">
                    <div class="label-qr-text">Scan Disini</div>
                    <div class="qr-placeholder" id="qr-{{ $box->code }}"></div>
                </div>
            </div>
        @endforeach
    </div>

    <script>
        function filterGroups(value) {
            const url = new URL(window.location.href);
            if (value) {
                url.searchParams.set('group', value);
            } else {
                url.searchParams.delete('group');
            }
            window.location.href = url.toString();
        }

        document.addEventListener('DOMContentLoaded', function() {
            @foreach($boxes as $box)
                new QRCode(document.getElementById('qr-{{ $box->code }}'), {
                    text: '{{ url("/box-scan/" . $box->code) }}',
                    width: 128,
                    height: 128,
                    colorDark: '#000000',
                    colorLight: '#ffffff',
                    correctLevel: QRCode.CorrectLevel.M,
                });
            @endforeach
        });
    </script>
</body>
</html>