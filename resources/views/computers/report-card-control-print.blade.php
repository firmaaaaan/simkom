<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <title>Kartu Kendali - {{ $selectedLab->name ?? '' }} - {{ $selectedYear->name ?? '' }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Times New Roman', serif; font-size: 12px; color: #000; }
        .page { padding: 15mm; }
        .header { text-align: center; margin-bottom: 20px; }
        .header h1 { font-size: 16px; font-weight: bold; text-transform: uppercase; margin-bottom: 4px; }
        .header h2 { font-size: 14px; font-weight: bold; text-transform: uppercase; margin-bottom: 8px; }
        .info { margin-bottom: 15px; font-size: 12px; }
        .info table td { padding: 2px 8px 2px 0; vertical-align: top; }
        .info table td:first-child { font-weight: bold; width: 100px; }
        table.report { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
        table.report th, table.report td { border: 1px solid #000; padding: 5px 8px; text-align: left; font-size: 11px; }
        table.report thead th { background: #000; color: #fff; text-align: center; font-weight: bold; }
        table.report thead th.fungsi-header { background: #009688; }
        table.report tbody td { text-align: center; }
        table.report tbody td.text-left { text-align: left; }
        table.report tbody tr:nth-child(even) { background: #f5f5f5; }
        .checkbox { display: inline-block; width: 14px; height: 14px; border: 1.5px solid #000; vertical-align: middle; }
        .checkbox.checked { position: relative; }
        .checkbox.checked::after { content: '✓'; position: absolute; top: -2px; left: 1px; font-size: 12px; font-weight: bold; }
        .signature { margin-top: 40px; display: flex; justify-content: flex-end; font-size: 11px; }
        .signature-block { width: 220px; }
        .signature-block .line { border-top: 1px solid #000; margin-top: 60px; margin-bottom: 4px; }
        .btn-print { position: fixed; top: 20px; right: 20px; padding: 10px 20px; background: #16a34a; color: white; border: none; border-radius: 8px; cursor: pointer; font-size: 14px; font-weight: 600; z-index: 100; box-shadow: 0 2px 8px rgba(0,0,0,0.2); }
        .btn-print:hover { background: #15803d; }
        @media print {
            .btn-print { display: none !important; }
            body { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            .page { padding: 10mm; }
            .checkbox.checked::after { color: #000; }
        }
        @page { size: A4 portrait; margin: 10mm; }
    </style>
</head>
<body>
    <button class="btn-print" onclick="window.print()">Cetak</button>

    <div class="page">
        <div class="header">
            <h1>Kartu Kendali</h1>
            <h2>Universitas 'Aisyiyah Yogyakarta</h2>
        </div>

        <div class="info">
            <table>
                <tr>
                    <td>Nama Lab</td>
                    <td>: {{ $selectedLab->name ?? '-' }}</td>
                </tr>
                <tr>
                    <td>Periode</td>
                    <td>: {{ $selectedYear->name ?? '-' }}{{ $selectedYear ? ' (' . $selectedYear->periodLabel() . ')' : '' }}</td>
                </tr>
            </table>
        </div>

        <table class="report">
            <thead>
                <tr>
                    <th style="width: 40px;">No</th>
                    @if($selectedLab->id === 'all')
                        <th style="width: 120px;">Lab</th>
                    @endif
                    <th style="width: 130px;">Kode Komputer</th>
                    <th style="width: 85px;">Tanggal</th>
                    <th class="fungsi-header" style="width: 60px;">Baik</th>
                    <th class="fungsi-header" style="width: 60px;">Tidak</th>
                    <th>Keterangan</th>
                    <th style="width: 120px;">PJ</th>
                </tr>
            </thead>
            <tbody>
                @forelse($computers as $index => $computer)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        @if($selectedLab->id === 'all')
                            <td class="text-left">{{ $computer->laboratory->name ?? '-' }}</td>
                        @endif
                        <td class="text-left" style="font-weight: bold;">{{ $computer->code }}</td>
                        <td>{{ $computer->latestCheck ? $computer->latestCheck->created_at->translatedFormat('d M Y') : '-' }}</td>
                        <td>
                            @if($computer->is_baik)
                                <span class="checkbox checked"></span>
                            @elseif($computer->latestCheck)
                                <span class="checkbox"></span>
                            @else
                                &ndash;
                            @endif
                        </td>
                        <td>
                            @if($computer->latestCheck && !$computer->is_baik)
                                <span class="checkbox checked"></span>
                            @elseif($computer->latestCheck)
                                <span class="checkbox"></span>
                            @else
                                &ndash;
                            @endif
                        </td>
                        <td class="text-left">{{ $computer->latestCheck->notes ?? '-' }}</td>
                        <td class="text-left">{{ $computer->latestCheck->checkedBy->name ?? '-' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ $selectedLab->id === 'all' ? 8 : 7 }}" style="text-align: center; padding: 20px;">Tidak ada data komputer</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div class="signature">
            <div class="signature-block">
                <p>Yogyakarta, {{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}</p>
                <p>Penanggung jawab</p>
                <p style="margin-top: 50px; padding-top: 4px;">Firmansyah, S.Kom</p>
            </div>
        </div>
    </div>
</body>
</html>
