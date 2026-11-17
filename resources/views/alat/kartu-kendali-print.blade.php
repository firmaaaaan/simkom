<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}?v={{ filemtime(public_path('favicon.svg')) }}">
    <title>Kartu Kendali Alat</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Times New Roman', serif; font-size: 11px; color: #000; }
        .cards { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 8mm; align-items: start; }
        .page { padding: 2mm; break-inside: avoid; page-break-inside: avoid; }
        .header { text-align: center; margin-bottom: 8px; }
        .header h1 { font-size: 12px; font-weight: bold; text-transform: uppercase; margin-bottom: 2px; }
        .header h2 { font-size: 11px; font-weight: bold; text-transform: uppercase; }
        .info { margin-bottom: 8px; font-size: 11px; }
        .info table td { padding: 1px 6px 1px 0; vertical-align: top; }
        .info table td:first-child { font-weight: bold; width: 100px; }
        table.report { width: 100%; border-collapse: collapse; margin-bottom: 8px; table-layout: fixed; }
        table.report th, table.report td { border: 1px solid #000; padding: 2px 4px; font-size: 10px; }
        table.report thead th { background: #000; color: #fff; text-align: center; font-weight: bold; }
        table.report thead th.fungsi { background: #009688; }
        table.report tbody td { text-align: center; height: 14px; }
        table.report tbody td.ket { text-align: left; }
        .btn-print { position: fixed; top: 20px; right: 20px; padding: 10px 20px; background: #16a34a; color: white; border: none; border-radius: 8px; cursor: pointer; font-size: 14px; font-weight: 600; z-index: 100; }
        .btn-print:hover { background: #15803d; }
        @media print {
            .btn-print { display: none !important; }
            body { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            @page { size: A4 landscape; margin: 8mm; }
        }
    </style>
</head>
<body>
    <button class="btn-print" onclick="window.print()">Cetak</button>

    <div class="cards">
    @foreach($groups as $group)
        @foreach($group['items'] as $item)
            <div class="page">
                @include('partials.kop', [
                    'kopTitle' => "LABORATORIUM UNIVERSITAS 'AISYIYAH YOGYAKARTA",
                    'kopLines' => ['Jl. Ringroad Barat No.63 Mlangi, Nogotirto, Gamping, Sleman', 'Telp: (0274)4469199, Fax (0274)4469204'],
                ])

                <div style="text-align: center; margin: 8px 0 10px;">
                    <h2 style="font-size: 13px; font-weight: bold; text-transform: uppercase;">KARTU KENDALI</h2>
                </div>

                <div class="info">
                    <table>
                        <tr>
                            <td>Nama Alat</td>
                            <td>: {{ $item->name }}</td>
                        </tr>
                        <tr>
                            <td>No Inventaris</td>
                            <td>: {{ $item->code ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td>Merk/Type</td>
                            <td>: {{ $item->merk_type ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td>Laboratorium</td>
                            <td>: {{ $group['lab']->name ?? '-' }}</td>
                        </tr>
                    </table>
                </div>

                <p style="font-size: 11px; margin-bottom: 6px; text-align: right;">
                    Terisi {{ min($item->checks->count(), 33) }} dari 33 entri
                </p>

                <table class="report">
                    <thead>
                        <tr>
                            <th style="width: 40px;">No</th>
                            <th style="width: 110px;">Tanggal</th>
                            <th class="fungsi" style="width: 60px;">Baik</th>
                            <th class="fungsi" style="width: 60px;">Rusak</th>
                            <th>Keterangan/Paraf</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $rows = $item->checks->take(33)->values();
                            $total = 33;
                        @endphp
                        @for($i = 1; $i <= $total; $i++)
                            @php $row = $rows[$i - 1] ?? null; @endphp
                            <tr>
                                <td>{{ $i }}</td>
                                <td>{{ $row ? $row->check_date->format('d/m/Y') : '' }}</td>
                                <td>{{ $row && $row->status === 'Baik' ? '✓' : '' }}</td>
                                <td>{{ $row && $row->status === 'Rusak' ? '✓' : '' }}</td>
                                <td class="ket">{{ $row ? ($row->notes ?? '') : '' }}</td>
                            </tr>
                        @endfor
                    </tbody>
                </table>
            </div>
        @endforeach
    @endforeach
    </div>
</body>
</html>
