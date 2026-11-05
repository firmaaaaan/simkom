<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <title>Inspeksi HSE - {{ $hseInspection->lokasi }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, Helvetica, sans-serif; font-size: 10px; color: #000; padding: 15px; }

        .print-btn {
            display: inline-flex; align-items: center; gap: 6px;
            padding: 8px 20px; background: #16a34a; color: #fff; border: none;
            border-radius: 6px; font-size: 13px; font-weight: 600; cursor: pointer;
            margin-bottom: 12px;
        }
        .print-btn:hover { background: #15803d; }

        .page { max-width: 1080px; margin: 0 auto; }

        .title { text-align: center; margin: 4px 0 2px; }
        .title h1 { font-size: 14px; font-weight: bold; letter-spacing: .5px; }
        .title p { font-size: 10px; }

        .doc-meta { position: absolute; top: 0; right: 0; border-collapse: collapse; font-size: 9px; }
        .doc-meta td { border: 1px solid #000; padding: 2px 5px; }
        .doc-meta td:first-child { background: #f2f2f2; white-space: nowrap; }

        .top { position: relative; min-height: 52px; padding-right: 210px; }

        .fields { width: 100%; border-collapse: collapse; margin-top: 10px; }
        .fields td { border: 1px solid #000; padding: 4px 6px; font-size: 10px; }
        .fields td.label { background: #f2f2f2; font-weight: bold; width: 110px; }

        .sheet { width: 100%; border-collapse: collapse; margin-top: 8px; table-layout: fixed; }
        .sheet th, .sheet td { border: 1px solid #000; padding: 3px 4px; vertical-align: top; font-size: 9px; }
        .sheet thead th { font-weight: bold; text-align: center; background: #f2f2f2; font-size: 9px; }
        .sheet td.center { text-align: center; }
        .sheet tbody tr { height: 58px; }
        .sheet .mark { font-size: 12px; }

        .signatures { display: flex; justify-content: space-between; margin-top: 18px; font-size: 10px; }
        .signature { width: 42%; text-align: center; }
        .signature .role { margin-bottom: 2px; }
        .signature .space { height: 52px; }
        .signature .name { font-weight: bold; text-decoration: underline; }

        @media print {
            .print-btn { display: none !important; }
            body { padding: 0; }
            .page { max-width: none; }
            @page { size: A4 landscape; margin: 10mm; }
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

    <div class="page">
        <div class="top">
            <table class="doc-meta">
                <tr>
                    <td>No Dokumen</td>
                    <td>{{ \App\Models\HseInspection::DOC_NUMBER }}</td>
                </tr>
                <tr>
                    <td>Revisi</td>
                    <td>{{ \App\Models\HseInspection::DOC_REVISION }}</td>
                </tr>
                <tr>
                    <td>Tgl. Berlaku</td>
                    <td>{{ \App\Models\HseInspection::DOC_EFFECTIVE_DATE }}</td>
                </tr>
            </table>

            <div class="title">
                <h1>INSPEKSI HSE</h1>
                <p>(Health, Security, and Environment)</p>
            </div>
        </div>

        <table class="fields">
            <tr>
                <td class="label">Lokasi</td>
                <td colspan="3">{{ $hseInspection->lokasi }}</td>
            </tr>
            <tr>
                <td class="label">Inspeksi Oleh</td>
                <td style="width: 45%;">{{ $hseInspection->diperiksa_oleh }}</td>
                <td class="label" style="width: 110px;">Tanggal Inspeksi</td>
                <td>{{ $hseInspection->tanggal_inspeksi->translatedFormat('d/m/Y') }}</td>
            </tr>
        </table>

        <table class="sheet">
            <colgroup>
                <col style="width: 3.5%;">
                <col style="width: 9.5%;">
                <col style="width: 41%;">
                <col style="width: 4.5%;">
                <col style="width: 4%;">
                <col style="width: 8%;">
                <col style="width: 13.5%;">
                <col style="width: 8.5%;">
                <col style="width: 7.5%;">
            </colgroup>
            <thead>
                <tr>
                    <th>No</th>
                    <th>Lokasi</th>
                    <th>Temuan &amp; Dokumentasi</th>
                    <th>Unsafe Condition</th>
                    <th>Unsafe Action</th>
                    <th>Tanggal Kejadian</th>
                    <th>Tindakan Perbaikan</th>
                    <th>Tanggal Perbaikan</th>
                    <th>PIC</th>
                </tr>
            </thead>
            <tbody>
                @forelse($hseInspection->finding_rows as $row)
                    <tr>
                        <td class="center">{{ $loop->iteration }}</td>
                        <td>{{ $row['lokasi'] }}</td>
                        <td>{!! nl2br(e($row['temuan'])) !!}</td>
                        <td class="center mark">{{ $row['unsafe_condition'] ? '✓' : '' }}</td>
                        <td class="center mark">{{ $row['unsafe_action'] ? '✓' : '' }}</td>
                        <td class="center">{{ $row['tanggal_kejadian_label'] === '-' ? '' : $row['tanggal_kejadian_label'] }}</td>
                        <td>{!! nl2br(e($row['tindakan_perbaikan'])) !!}</td>
                        <td class="center">{{ $row['tanggal_perbaikan_label'] === '-' ? '' : $row['tanggal_perbaikan_label'] }}</td>
                        <td>{{ $row['pic'] }}</td>
                    </tr>
                @empty
                    @for($i = 1; $i <= 3; $i++)
                        <tr>
                            <td class="center">{{ $i }}</td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                        </tr>
                    @endfor
                @endforelse
            </tbody>
        </table>

        <div class="signatures">
            <div class="signature">
                <div class="role">Mengetahui</div>
                <div class="role">{{ \App\Models\HseInspection::KEPALA_ROLE }}</div>
                <div class="space"></div>
                <div class="name">{{ \App\Models\HseInspection::KEPALA_NAME }}</div>
            </div>
            <div class="signature">
                <div class="role">Inspeksi dilakukan oleh</div>
                <div class="role">{{ \App\Models\HseInspection::KOORDINATOR_ROLE }}</div>
                <div class="space"></div>
                <div class="name">{{ \App\Models\HseInspection::KOORDINATOR_NAME }}</div>
            </div>
        </div>
    </div>
</body>
</html>
