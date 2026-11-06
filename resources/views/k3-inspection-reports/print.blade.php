<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}?v={{ filemtime(public_path('favicon.svg')) }}">
    <title>Laporan Inspeksi K3 - {{ $k3InspectionReport->kode }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, Helvetica, sans-serif; font-size: 10px; color: #000; padding: 12px; }

        .print-btn {
            display: inline-flex; align-items: center; gap: 6px;
            padding: 8px 18px; background: #16a34a; color: #fff; border: none;
            border-radius: 6px; font-size: 12px; font-weight: 600; cursor: pointer;
            margin-bottom: 10px;
        }
        .print-btn:hover { background: #15803d; }

        .page { max-width: 950px; margin: 0 auto 20px; page-break-after: always; }
        .page:last-child { page-break-after: auto; }

        .doc-header { text-align: center; position: relative; margin-bottom: 10px; }
        .doc-header h1 { font-size: 13px; letter-spacing: 1px; text-transform: uppercase; }
        .doc-header h2 { font-size: 11px; text-transform: uppercase; }
        .doc-meta { position: absolute; right: 0; top: 0; font-size: 9px; text-align: right; }

        .ba-title { text-align: center; font-weight: bold; font-size: 12px; margin: 16px 0 12px; letter-spacing: 2px; }
        .ba-text { text-align: justify; line-height: 1.6; margin: 8px 0; }
        .ba-fill { border-bottom: 1px dotted #000; display: inline-block; min-width: 90px; text-align: center; }

        .signature-row { display: flex; justify-content: space-between; margin-top: 40px; font-size: 10px; }
        .sig-block { width: 38%; text-align: center; }
        .sig-space { height: 60px; }
        .sig-name { font-weight: bold; text-decoration: underline; }

        .section-title { text-align: center; font-weight: bold; margin: 10px 0; letter-spacing: 1px; }
        .info-row { display: flex; gap: 12px; margin-bottom: 4px; font-size: 10px; }

        .checklist { width: 100%; border-collapse: collapse; margin-top: 10px; }
        .checklist th, .checklist td { border: 1px solid #000; padding: 3px 4px; font-size: 9px; }
        .checklist thead th { background: #f2f2f2; text-align: center; font-size: 8px; }
        .checklist td.center { text-align: center; }
        .checklist td.item { text-align: left; }

        .findings { width: 100%; border-collapse: collapse; }
        .findings th, .findings td { border: 1px solid #000; padding: 3px 4px; font-size: 9px; vertical-align: top; }
        .findings thead th { background: #f2f2f2; text-align: center; font-size: 8px; }
        .findings tbody tr { min-height: 48px; }

        @media print {
            .print-btn { display: none !important; }
            body { padding: 0; }
            @page { size: A4; margin: 10mm; }
        }
    </style>
</head>
<body>
    <button class="print-btn" onclick="window.print()">
        <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0110.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0l.229 2.523a1.125 1.125 0 01-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0021 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 00-1.913-.247M6.34 18H5.25A2.25 2.25 0 013 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 011.913-.247m10.5 0a48.536 48.536 0 00-10.5 0m10.5 0V3.375c0-.621-.504-1.125-1.125-1.125h-8.25c-.621 0-1.125.504-1.125 1.125v3.659" />
        </svg>
        Cetak (Semua Halaman)
    </button>

    {{-- HALAMAN 1 - BERITA ACARA --}}
    <div class="page">
        <div class="doc-header">
            <div class="doc-meta">
                <div>No. Doc : {{ \App\Models\K3InspectionReport::DOC_NUMBER }}</div>
                <div>Date : {{ \App\Models\K3InspectionReport::DOC_DATE }}</div>
                <div>Page : 1 of 5</div>
            </div>
            <h1>INSPEKSI</h1>
            <h1>KESELAMATAN DAN KESEHATAN KERJA</h1>
            <h2>(K3)</h2>
        </div>

        <div class="ba-title">BERITA ACARA</div>

        <p class="ba-text">
            Pada hari <span class="ba-fill" style="min-width: 90px;">{{ $k3InspectionReport->hari }}</span>
            tanggal <span class="ba-fill" style="min-width: 50px;">{{ $k3InspectionReport->tanggal_ba->format('d') }}</span>
            bulan <span class="ba-fill" style="min-width: 70px;">{{ $k3InspectionReport->bulan_ba }}</span>
            tahun <span class="ba-fill" style="min-width: 50px;">{{ $k3InspectionReport->tahun_ba }}</span>, saya yang bertanda tangan di bawah ini :
        </p>

        <p class="ba-text" style="margin-left: 20px;">
            Nama : {{ $k3InspectionReport->pemeriksa_nama }}<br>
            Jabatan : {{ $k3InspectionReport->pemeriksa_jabatan }}
        </p>

        <p class="ba-text">
            Telah melakukan inspeksi K3 berkala bulan <span class="ba-fill" style="min-width: 90px;">{{ $k3InspectionReport->bulan_inspeksi }}</span> di lokasi <span class="ba-fill" style="min-width: 150px;">{{ $k3InspectionReport->lokasi }}</span> sesuai dengan SOP yang ada. Inspeksi dilakukan dengan memperhatikan standar dan peraturan yang berlaku. Adapun data hasil inspeksi terlampir.
        </p>

        <p class="ba-text">
            Demikian berita acara pemeriksaan atau inspeksi K3 ini dibuat untuk dapat digunakan sebagaimana mestinya.
        </p>

        <div style="text-align: right; margin-top: 20px; font-size: 10px;">
            Yogyakarta, <span class="ba-fill" style="min-width: 120px;">{{ $k3InspectionReport->tanggal_ba->translatedFormat('d F Y') }}</span>
        </div>

        <div class="signature-row">
            <div class="sig-block">
                <div>Pemeriksa</div>
                <div>Koordinator K3 Laboratorium</div>
                <div class="sig-space"></div>
                <div class="sig-name">{{ \App\Models\K3InspectionReport::PEMERIKSA_NAMA }}</div>
            </div>
            <div class="sig-block">
                <div>Mengetahui</div>
                <div>Kepala Pusat Laboratorium Terpadu</div>
                <div class="sig-space"></div>
                <div class="sig-name">{{ \App\Models\K3InspectionReport::MENGETAHUI_NAMA }}</div>
            </div>
        </div>
    </div>

    {{-- HALAMAN 2-3 - CHECKLIST --}}
    <div class="page">
        <div class="doc-header">
            <div class="doc-meta">
                <div>No. Doc : {{ \App\Models\K3InspectionReport::DOC_NUMBER }}</div>
                <div>Date : {{ \App\Models\K3InspectionReport::DOC_DATE }}</div>
                <div>Page : 2 of 5</div>
            </div>
            <h1>INSPEKSI</h1>
            <h1>KESELAMATAN DAN KESEHATAN KERJA</h1>
            <h2>(K3)</h2>
        </div>

        <div class="section-title">CHECKLIST INSPEKSI K3</div>

        <div class="info-row"><div style="width: 90px;">Lokasi</div><div>: {{ $k3InspectionReport->lokasi }}</div></div>
        <div class="info-row"><div style="width: 90px;">Tanggal Inspeksi</div><div>: {{ $k3InspectionReport->tanggal_ba->translatedFormat('d/m/Y') }}</div></div>
        <div class="info-row"><div style="width: 90px;">Nama Inspektor</div><div>: {{ $k3InspectionReport->pemeriksa_nama }}</div></div>

        <table class="checklist">
            <thead>
                <tr>
                    <th style="width: 5%;">No.</th>
                    <th>Item</th>
                    <th style="width: 6%;">Ya</th>
                    <th style="width: 6%;">Tidak</th>
                    <th style="width: 6%;">N/A</th>
                    <th style="width: 16%;">Catatan</th>
                </tr>
            </thead>
            <tbody>
                @foreach($k3InspectionReport->checklist_rows as $row)
                    <tr>
                        <td class="center">{{ $row['item_no'] }}.</td>
                        <td class="item">{{ $row['item_text'] }}</td>
                        <td class="center">{{ $row['ya'] ? '✓' : '' }}</td>
                        <td class="center">{{ $row['tidak'] ? '✓' : '' }}</td>
                        <td class="center">{{ $row['na'] ? '✓' : '' }}</td>
                        <td>{{ $row['catatan'] }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{-- HALAMAN 4 - LAPORAN --}}
    <div class="page">
        <div class="doc-header">
            <div class="doc-meta">
                <div>No. Doc : {{ \App\Models\K3InspectionReport::DOC_NUMBER }}</div>
                <div>Date : {{ \App\Models\K3InspectionReport::DOC_DATE }}</div>
                <div>Page : 4 of 5</div>
            </div>
            <h1>FORM LAPORAN INSPEKSI K3</h1>
        </div>

        <table class="findings" style="margin-top: 20px;">
            <thead>
                <tr>
                    <th style="width: 4%;">No</th>
                    <th>Temuan Inspeksi</th>
                    <th style="width: 14%;">Dokumentasi</th>
                    <th>Saran Perbaikan</th>
                    <th style="width: 12%;">Pelaksana</th>
                    <th style="width: 10%;">Target</th>
                    <th style="width: 14%;">Catatan</th>
                </tr>
            </thead>
            <tbody>
                @forelse($k3InspectionReport->finding_rows as $row)
                    <tr>
                        <td class="center">{{ $loop->iteration }}</td>
                        <td>{!! nl2br(e($row['temuan'])) !!}</td>
                        <td>{{ $row['dokumentasi'] }}</td>
                        <td>{!! nl2br(e($row['saran_perbaikan'])) !!}</td>
                        <td>{{ $row['pelaksana'] }}</td>
                        <td>{{ $row['target'] }}</td>
                        <td>{!! nl2br(e($row['catatan'])) !!}</td>
                    </tr>
                @empty
                    @for($i = 1; $i <= 6; $i++)
                        <tr>
                            <td class="center">{{ $i }}</td>
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
    </div>

    {{-- HALAMAN 5 - TANDA TANGAN --}}
    <div class="page">
        <div class="doc-header">
            <div class="doc-meta">
                <div>No. Doc : {{ \App\Models\K3InspectionReport::DOC_NUMBER }}</div>
                <div>Date : {{ \App\Models\K3InspectionReport::DOC_DATE }}</div>
                <div>Page : 5 of 5</div>
            </div>
            <h1>INSPEKSI</h1>
            <h1>KESELAMATAN DAN KESEHATAN KERJA</h1>
            <h2>(K3)</h2>
        </div>

        <p style="margin-top: 40px; font-size: 10px;">Catatan : -</p>

        <div class="signature-row" style="margin-top: 60px;">
            <div class="sig-block">
                <div>Pemeriksa</div>
                <div>Koordinator K3 Laboratorium</div>
                <div style="margin-top: 2px;">Unisa Yogyakarta</div>
                <div class="sig-space"></div>
                <div class="sig-name">{{ \App\Models\K3InspectionReport::PEMERIKSA_NAMA }}</div>
            </div>
            <div class="sig-block">
                <div>Mengetahui</div>
                <div>{{ \App\Models\K3InspectionReport::MENGETAHUI_JABATAN_PAGE5 }}</div>
                <div class="sig-space"></div>
                <div class="sig-name">{{ \App\Models\K3InspectionReport::MENGETAHUI_NAMA_PAGE5 }}</div>
            </div>
        </div>
    </div>
</body>
</html>
