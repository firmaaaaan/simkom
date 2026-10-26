<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <title>{{ $title }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, Helvetica, sans-serif; font-size: 10px; color: #000; padding: 15px; }

        .print-btn {
            display: inline-flex; align-items: center; gap: 6px;
            padding: 8px 20px; background: #16a34a; color: #fff; border: none;
            border-radius: 6px; font-size: 13px; font-weight: 600; cursor: pointer;
            margin-bottom: 15px;
        }
        .print-btn:hover { background: #15803d; }

        .empty { font-size: 12px; }
        .sheet + .sheet { page-break-before: always; }

        .header { text-align: center; margin-bottom: 12px; }
        .header h1 { font-size: 13px; font-weight: bold; text-transform: uppercase; margin-bottom: 2px; }
        .header h2 { font-size: 12px; font-weight: bold; text-transform: uppercase; }

        .info { margin-bottom: 10px; }
        .info table { border-collapse: collapse; }
        .info td { padding: 2px 8px 2px 0; font-size: 11px; vertical-align: top; }
        .info td:first-child { font-weight: bold; white-space: nowrap; width: 110px; }

        h3.section { font-size: 11px; margin: 12px 0 5px; text-transform: uppercase; }

        table.data { width: 100%; border-collapse: collapse; }
        table.data th, table.data td { border: 1px solid #000; padding: 3px 5px; font-size: 10px; text-align: left; vertical-align: top; }
        table.data thead th { background: #e8e8e8; font-weight: bold; }
        table.data td.num, table.data th.num { text-align: center; width: 40px; }
        table.data tbody tr:nth-child(even) { background: #f7f7f7; }

        .recap { display: flex; gap: 12px; }
        .recap > div { flex: 1; }
        .muted { color: #555; font-style: italic; }

        @media print {
            .print-btn { display: none !important; }
            body { padding: 0; }
            @page { size: portrait; margin: 10mm; }
        }
    </style>
</head>
<body>
    <button class="print-btn" onclick="window.print()">
        <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0110.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0l.229 2.523a1.125 1.125 0 01-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0021 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 00-1.913-.247M6.34 18H5.25A2.25 2.25 0 013 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 011.913-.247m10.5 0a48.536 48.536 0 00-10.5 0m10.5 0V3.375c0-.621-.504-1.125-1.125-1.125h-8.25c-.621 0-1.125.504-1.125 1.125v3.659" />
        </svg>
        Cetak
    </button>

    @forelse($rows as $row)
        <div class="sheet">
            <div class="header">
                <h1>Laporan Inventaris Laboratorium Komputer</h1>
                <h2>Universitas 'Aisyiyah Yogyakarta</h2>
            </div>

            <div class="info">
                <table>
                    <tr>
                        <td>Laboratorium</td>
                        <td>: {{ $row['laboratory']->name }}{{ $row['laboratory']->code ? ' ('.$row['laboratory']->code.')' : '' }}</td>
                    </tr>
                    <tr>
                        <td>Jumlah Komputer</td>
                        <td>: {{ $row['computers_count'] }}</td>
                    </tr>
                    <tr>
                        <td>Dicetak</td>
                        <td>: {{ \Carbon\Carbon::now()->translatedFormat('d F Y H:i') }} WIB</td>
                    </tr>
                </table>
            </div>

            <div class="recap">
                <div>
                    <h3 class="section">RAM</h3>
                    <table class="data">
                        <thead>
                            <tr>
                                <th>Kapasitas</th>
                                <th class="num">Jumlah</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($row['ram'] as $item)
                                <tr>
                                    <td class="{{ str_starts_with($item['label'], 'Tidak') ? 'muted' : '' }}">{{ $item['label'] }}</td>
                                    <td class="num">{{ $item['count'] }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="2" class="muted">Belum ada data</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div>
                    <h3 class="section">Storage</h3>
                    <table class="data">
                        <thead>
                            <tr>
                                <th>Tipe</th>
                                <th class="num">Jumlah</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($row['storage'] as $item)
                                <tr>
                                    <td class="{{ str_starts_with($item['label'], 'Tidak') ? 'muted' : '' }}">{{ $item['label'] }}</td>
                                    <td class="num">{{ $item['count'] }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="2" class="muted">Belum ada data</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div>
                    <h3 class="section">Processor</h3>
                    <table class="data">
                        <thead>
                            <tr>
                                <th>Jenis</th>
                                <th class="num">Jumlah</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($row['processors'] as $item)
                                <tr>
                                    <td class="{{ str_starts_with($item['label'], 'Tidak') ? 'muted' : '' }}">{{ $item['label'] }}</td>
                                    <td class="num">{{ $item['count'] }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="2" class="muted">Belum ada data</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <h3 class="section">Rincian Spesifikasi per Komputer</h3>
            <table class="data">
                <thead>
                    <tr>
                        <th class="num">No</th>
                        <th>Kode Komputer</th>
                        <th>RAM</th>
                        <th>Storage</th>
                        <th>Processor</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($row['details'] as $index => $detail)
                        <tr>
                            <td class="num">{{ $index + 1 }}</td>
                            <td>{{ $detail['computer']->code }}</td>
                            <td>{{ $detail['ram'] }}</td>
                            <td>{{ $detail['storage'] }}</td>
                            <td>{{ $detail['processor'] }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="muted">Belum ada komputer di laboratorium ini</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    @empty
        <p class="empty">Belum ada laboratorium untuk ditampilkan.</p>
    @endforelse
</body>
</html>
