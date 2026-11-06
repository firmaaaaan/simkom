<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}?v={{ filemtime(public_path('favicon.svg')) }}">
    <title>Lembar Periksa APAR - {{ $aparInspection->nomor_registrasi }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, Helvetica, sans-serif; font-size: 11px; color: #000; padding: 15px; }

        .print-btn {
            display: inline-flex; align-items: center; gap: 6px;
            padding: 8px 20px; background: #16a34a; color: #fff; border: none;
            border-radius: 6px; font-size: 13px; font-weight: 600; cursor: pointer;
            margin-bottom: 15px;
        }
        .print-btn:hover { background: #15803d; }

        .doc-meta { display: flex; justify-content: space-between; font-size: 10px; margin-bottom: 10px; }

        .title { text-align: center; margin: 12px 0 6px; }
        .title h1 { font-size: 12px; font-weight: bold; text-transform: uppercase; letter-spacing: .3px; }
        .title h2 { font-size: 11px; font-weight: bold; text-transform: uppercase; }

        .info { margin: 14px 0 8px; }
        .info table { border-collapse: collapse; }
        .info td { padding: 2px 8px 2px 0; font-size: 11px; vertical-align: top; }
        .info td:first-child { font-weight: bold; white-space: nowrap; width: 100px; }

        .sheet { width: 100%; border-collapse: collapse; table-layout: fixed; }
        .sheet th, .sheet td { border: 1px solid #000; padding: 4px 5px; vertical-align: middle; }
        .sheet thead th { font-weight: bold; font-size: 10px; text-align: center; background: #f2f2f2; }
        .sheet td.no { text-align: center; width: 28px; }
        .sheet td.item-en { font-weight: bold; font-size: 10px; }
        .sheet td.item-id { font-size: 9px; }
        .sheet th.col-status { width: 60px; }
        .sheet td.col-status { text-align: center; font-size: 13px; }

        .note { margin-top: 10px; font-size: 10px; }

        .footer-reg { margin-top: 16px; width: 100%; border-collapse: collapse; }
        .footer-reg td { font-size: 11px; padding: 2px 0; vertical-align: top; }
        .footer-reg td:first-child { width: 50%; }

        .signature { margin-top: 40px; display: flex; justify-content: flex-end; font-size: 11px; }
        .signature-block { width: 240px; text-align: center; }
        .signature-space { height: 55px; }
        .signature-name { font-weight: bold; text-decoration: underline; }

        @media print {
            .print-btn { display: none !important; }
            body { padding: 0; }
            @page { size: portrait; margin: 12mm; }
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

    @include('partials.kop', [
        'kopTitle' => 'LEMBAR PERIKSA ALAT PEMADAM API RINGAN (APAR)',
        'kopLines' => ['UPT LABORATORIUM TERPADU', "UNIVERSITAS 'AISYIYAH YOGYAKARTA"],
    ])

    <div class="doc-meta">
        <span>No. Doc : {{ \App\Models\AparInspection::DOC_NUMBER }}</span>
        <span>Date : {{ \App\Models\AparInspection::DOC_DATE }}</span>
        <span>Page : 1 of 1</span>
    </div>

    <div class="title">
        <h1>Lembar Periksa Alat Pemadam Api Ringan (APAR)</h1>
    </div>

    <div class="info">
        <table>
            <tr>
                <td>Hari/Tanggal</td>
                <td>: {{ $aparInspection->hari }}, {{ $aparInspection->tanggal_inspeksi->translatedFormat('d F Y') }}</td>
            </tr>
            <tr>
                <td>Lokasi</td>
                <td>: {{ $aparInspection->lokasi }}</td>
            </tr>
        </table>
    </div>

    <table class="sheet">
        <thead>
            <tr>
                <th class="col-status" style="width: 28px;">No</th>
                <th>Item yang akan Diinspeksi</th>
                <th class="col-status">Baik</th>
                <th class="col-status">Tidak</th>
                <th style="width: 34%;">Keterangan</th>
            </tr>
        </thead>
        <tbody>
            @foreach($aparInspection->item_rows as $row)
                <tr>
                    <td class="no">{{ $loop->iteration }}.</td>
                    <td>
                        <div class="item-en">{{ $row['label'] }}</div>
                        <div class="item-id">{{ $row['label_id'] }}</div>
                    </td>
                    <td class="col-status">{{ $row['status'] === 'baik' ? '✓' : '' }}</td>
                    <td class="col-status">{{ $row['status'] === 'tidak' ? '✓' : '' }}</td>
                    <td>{{ $row['keterangan'] }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <p class="note">
        Sebelum dipasang pada tempatnya, Box Fire Extinguisher harus diberi "Tag" terlebih dahulu.
    </p>

    <table class="footer-reg">
        <tr>
            <td>No. Reg. APAR : {{ $aparInspection->nomor_registrasi }}</td>
            <td>Tgl. Inspeksi / Oleh : {{ $aparInspection->tanggal_inspeksi->translatedFormat('d/m/Y') }} / {{ $aparInspection->diperiksa_oleh }}</td>
        </tr>
    </table>

    <div class="signature">
        <div class="signature-block">
            <div class="signature-space"></div>
            <div class="signature-name">{{ \App\Models\AparInspection::KOORDINATOR_NAME }}</div>
            <div>{{ \App\Models\AparInspection::KOORDINATOR_ROLE }}</div>
        </div>
    </div>
</body>
</html>
