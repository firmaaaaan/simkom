<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <title>Cetak - Kartu Kendali {{ $computer->code }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Times New Roman', Times, serif; font-size: 11px; color: #000; padding: 15px; width: 190mm; margin: 0 auto; overflow: hidden; }

        @page { 
            size: A4 portrait !important; 
            margin: 10mm; 
        }

        .print-btn {
            display: inline-flex; align-items: center; gap: 6px;
            padding: 8px 20px; background: #16a34a; color: #fff; border: none;
            border-radius: 6px; font-size: 13px; font-weight: 600; cursor: pointer;
            margin-bottom: 15px;
        }
        .print-btn:hover { background: #15803d; }

        .header { text-align: center; margin-bottom: 14px; border-bottom: 2px solid #000; padding-bottom: 8px; }
        .header h1 { font-size: 14px; font-weight: bold; text-transform: uppercase; margin-bottom: 2px; }
        .header h2 { font-size: 12px; font-weight: bold; text-transform: uppercase; }

        .info-table { width: 100%; border-collapse: collapse; margin: 0 auto 14px auto; page-break-inside: avoid; table-layout: fixed; }
        .info-table td { padding: 4px 8px; font-size: 11px; border: 1px solid #000; vertical-align: top; }
        .info-table td:first-child { font-weight: bold; background: #f0f0f0; }

        .checklist-table { width: 100%; border-collapse: collapse; border: 1px solid #000; font-size: 10px; page-break-inside: avoid; margin: 0 auto; table-layout: fixed; }
        .checklist-table th,
        .checklist-table td { border: 1px solid #000; padding: 4px 6px; text-align: left; vertical-align: middle; }
        .checklist-table th { background: #f0f0f0; font-weight: bold; font-size: 10px; }
        .checklist-table td.status-col { text-align: center; }

        .footer { margin-top: 40px; display: flex; justify-content: flex-end; font-size: 11px; }
        .footer .sign { width: 220px; }

        @media print {
            .print-btn { display: none !important; }
            body { padding: 0; width: 100%; }
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

    <div class="header">
        <h1>Kartu Kendali Komputer</h1>
        <h2>Universitas 'Aisyiyah Yogyakarta</h2>
    </div>

    <table class="info-table">
        <tr>
            <td style="width: 140px;">Kode Komputer</td>
            <td>{{ $computer->code }}</td>
            <td style="width: 120px;">Laboratorium</td>
            <td>{{ $computer->laboratory?->name ?? '-' }}</td>
        </tr>
        <tr>
            <td>Status</td>
            <td>{{ $computer->status }}</td>
            <td>Keterangan</td>
            <td>{{ $computer->description ?? '-' }}</td>
        </tr>
    </table>

    @if($checks->count() > 0)
        <h3 style="margin-bottom: 8px; font-size: 12px; font-weight: bold;">Riwayat Pengecekan</h3>
        <table class="checklist-table">
            <thead>
                <tr>
                    <th style="width: 40px;">No</th>
                    <th>Tanggal</th>
                    <th style="width: 100px;">Status</th>
                    <th style="width: 120px;">Pengecek</th>
                    <th>Catatan</th>
                </tr>
            </thead>
            <tbody>
                @foreach($checks as $index => $check)
                    <tr>
                        <td style="text-align: center;">{{ $index + 1 }}</td>
                        <td>{{ $check->created_at->format('d M Y H:i') }}</td>
                        <td class="status-col" style="font-weight: bold; color: {{ $check->overall_status === 'Baik' ? '#16a34a' : ($check->overall_status === 'Kritis' ? '#dc2626' : '#ca8a04') }};">{{ $check->overall_status }}</td>
                        <td>{{ $check->checkedBy?->name ?? 'System' }}</td>
                        <td>{{ $check->notes ?? '-' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <p style="text-align: center; color: #888; margin-top: 20px;">Belum ada riwayat pengecekan</p>
    @endif

    <div class="footer">
        <div class="sign">
            <p>Yogyakarta, {{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}</p>
            <p>Penanggung jawab</p>
            <p style="margin-top: 50px; padding-top: 4px;">Firmansyah, S.Kom</p>
        </div>
    </div>
</body>
</html>
