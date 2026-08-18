<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Print Kartu Kendali - {{ $komputer->nama_komputer }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; font-size: 11px; color: #333; }
        .page { width: 210mm; min-height: 297mm; padding: 15mm; margin: 0 auto; background: #fff; }
        .header { text-align: center; border-bottom: 3px double #000; padding-bottom: 10px; margin-bottom: 15px; }
        .header h1 { font-size: 16px; text-transform: uppercase; letter-spacing: 2px; margin-bottom: 5px; }
        .header p { font-size: 10px; color: #666; }
        .info-table { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
        .info-table th, .info-table td { border: 1px solid #333; padding: 6px 8px; text-align: left; vertical-align: top; }
        .info-table th { width: 35%; background: #f5f5f5; font-weight: bold; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
        th, td { border: 1px solid #333; padding: 6px 8px; text-align: left; }
        th { background: #f5f5f5; font-weight: bold; }
        .text-center { text-align: center; }
        .footer { margin-top: 20px; padding-top: 10px; border-top: 1px solid #333; font-size: 9px; color: #666; text-align: right; }
        .badge { padding: 2px 6px; border-radius: 4px; font-size: 10px; font-weight: bold; }
        .badge-success { background: #d1e7dd; color: #0f5132; }
        .badge-warning { background: #fff3cd; color: #664d03; }
        .badge-danger { background: #f8d7da; color: #842029; }
        .badge-secondary { background: #e2e3e5; color: #41464b; }
        @media print {
            body { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            .page { margin: 0; padding: 10mm; width: 100%; }
            .no-print { display: none !important; }
        }
    </style>
</head>
<body>
    <div class="no-print" style="text-align: center; padding: 10px;">
        <button onclick="window.print()" style="padding: 8px 20px; font-size: 12px;">Print / Save as PDF</button>
        <a href="{{ route('admin.komputer.show', $komputer) }}" style="padding: 8px 20px; font-size: 12px; text-decoration: none; color: #333;">Kembali</a>
    </div>

    <div class="page">
        <div class="header">
            <h1>Riwayat Kartu Kendali Komputer</h1>
            <p>Laporan Pemeriksaan Kondisi Asset</p>
        </div>

        <table class="info-table">
            <tr>
                <th>Kode Komputer</th>
                <td>{{ $komputer->kode_komputer }}</td>
                <th>Nama Komputer</th>
                <td>{{ $komputer->nama_komputer }}</td>
            </tr>
            <tr>
                <th>Laboratorium</th>
                <td>{{ $komputer->laboratorium->nama_laboratorium ?? '-' }}</td>
                <th>Lokasi</th>
                <td>{{ $komputer->laboratorium->gedung ?? '-' }} / {{ $komputer->laboratorium->ruangan ?? '-' }}</td>
            </tr>
            <tr>
                <th>Status</th>
                <td>{{ ucfirst(str_replace('_', ' ', $komputer->status)) }}</td>
                <th>Total Pemeriksaan</th>
                <td>{{ $riwayat->count() }} kali</td>
            </tr>
        </table>

        @if($riwayat->isNotEmpty())
                <table>
                    <thead>
                        <tr>
                            <th class="text-center">No</th>
                            <th>Tanggal</th>
                            <th>Tahun Ajaran</th>
                            <th>Kondisi</th>
                            <th>Catatan</th>
                            <th>Pemeriksa</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($riwayat as $index => $kartu)
                            <tr>
                                <td class="text-center">{{ $riwayat->count() - $index }}</td>
                                <td>{{ \Carbon\Carbon::parse($kartu->tanggal_pemeriksaan)->translatedFormat('d F Y') }}</td>
                                <td>{{ $kartu->tahunAjaran->nama ?? '-' }}</td>
                                <td>
                                    @php
                                        $badgeClass = match($kartu->kondisi_keseluruhan) {
                                            'baik' => 'badge-success',
                                            'cukup' => 'badge-warning',
                                            'rusak' => 'badge-danger',
                                            default => 'badge-secondary',
                                        };
                                    @endphp
                                    <span class="badge {{ $badgeClass }}">{{ ucfirst($kartu->kondisi_keseluruhan) }}</span>
                                </td>
                                {{-- <td>
                                    @if($kartu->items)
                                        @foreach($kartu->items as $item)
                                            @if(!empty($item['']))
                                                <div>{{ $item['nama'] }}: {{ $item[''] ?: '-' }}</div>
                                            @endif
                                        @endforeach
                                    @endif
                                </td> --}}
                                <td>{{ $kartu->catatan ?: '-' }}</td>
                                <td>{{ $kartu->pemeriksa }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
        @else
            <p class="text-center text-muted">Belum ada riwayat pemeriksaan.</p>
        @endif

        <div class="footer">
            <p>Dicetak pada: {{ now()->translatedFormat('d F Y H:i') }}</p>
        </div>
    </div>
</body>
</html>