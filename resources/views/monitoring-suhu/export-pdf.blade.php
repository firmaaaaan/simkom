<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Monitoring Suhu Ruangan</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #111827; }
        h1 { font-size: 16px; margin: 0 0 2px; }
        .meta { font-size: 10px; color: #6b7280; margin-bottom: 14px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #d1d5db; padding: 6px 8px; text-align: left; }
        th { background: #16a34a; color: #ffffff; font-weight: 700; }
        tr:nth-child(even) td { background: #f9fafb; }
        .center { text-align: center; }
    </style>
</head>
<body>
    <h1>Laporan Monitoring Suhu Ruangan</h1>
    <p class="meta">
        Dicetak: {{ now()->translatedFormat('d F Y H:i') }}
        @if($from || $to)
            · Periode: {{ $from ? \Illuminate\Support\Carbon::parse($from)->translatedFormat('d F Y') : 'awal' }} — {{ $to ? \Illuminate\Support\Carbon::parse($to)->translatedFormat('d F Y') : 'sekarang' }}
        @endif
        · {{ $readings->count() }} entri
    </p>

    <table>
        <thead>
            <tr>
                <th class="center">No</th>
                <th>Ruangan</th>
                <th>Waktu</th>
                <th class="center">Suhu (°C)</th>
                <th class="center">Status</th>
                <th>Catatan</th>
                <th>Diinput Oleh</th>
            </tr>
        </thead>
        <tbody>
            @forelse($readings as $index => $reading)
                <tr>
                    <td class="center">{{ $index + 1 }}</td>
                    <td>{{ $reading->laboratory->name ?? '-' }}</td>
                    <td>{{ $reading->recorded_at?->format('d/m/Y H:i') ?? '-' }}</td>
                    <td class="center">{{ number_format((float) $reading->temperature, 1) }}</td>
                    <td class="center">{{ $reading->status() ?? '-' }}</td>
                    <td>{{ $reading->notes ?? '-' }}</td>
                    <td>{{ $reading->recordedBy->name ?? '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="center">Tidak ada data monitoring suhu.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
