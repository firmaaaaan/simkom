<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}?v={{ filemtime(public_path('favicon.svg')) }}">
    <title>Formulir Analisis Keselamatan Pekerjaan (JSA) - {{ $jsa->kode }}</title>
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

        .title { text-align: center; margin: 12px 0 14px; }
        .title h1 { font-size: 12px; font-weight: bold; text-transform: uppercase; letter-spacing: .3px; text-decoration: underline; }

        .info { margin: 14px 0 8px; }
        .info table { border-collapse: collapse; }
        .info td { padding: 2px 8px 2px 0; font-size: 11px; vertical-align: top; }
        .info td:first-child { white-space: nowrap; width: 130px; }

        .apd { margin: 12px 0; }
        .apd-title { font-size: 11px; margin-bottom: 6px; }
        .apd table { width: 100%; border-collapse: collapse; table-layout: fixed; }
        .apd td { font-size: 11px; padding: 1px 6px 1px 0; vertical-align: top; width: 33.33%; }

        .sheet { width: 100%; border-collapse: collapse; table-layout: fixed; margin-top: 8px; }
        .sheet th, .sheet td { border: 1px solid #000; padding: 4px 5px; vertical-align: top; }
        .sheet thead th { font-weight: bold; font-size: 10px; text-align: center; background: #f2f2f2; }
        .sheet td.no { text-align: center; width: 26px; }
        .sheet td.blank { min-height: 26px; }

        .keterangan { margin-top: 12px; font-size: 9px; line-height: 1.5; }

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
        'kopTitle' => 'FORMULIR ANALISIS KESELAMATAN PEKERJAAN',
        'kopLines' => ['JOB SAFETY ANALYSIS (JSA)'],
    ])

    <div class="doc-meta">
        <span>No. Doc : {{ \App\Models\JobSafetyAnalysis::DOC_NUMBER }}</span>
        <span>Date : {{ \App\Models\JobSafetyAnalysis::DOC_DATE }}</span>
        <span>Page : 1 of 1</span>
    </div>

    <div class="title">
        <h1>Formulir Analisis Keselamatan Pekerjaan / Job Safety Analysis (JSA)</h1>
    </div>

    <div class="info">
        <table>
            <tr>
                <td>Nama Pekerja</td>
                <td>: {{ $jsa->nama_pekerja }}</td>
            </tr>
            <tr>
                <td>Nama Pekerjaan</td>
                <td>: {{ $jsa->nama_pekerjaan }}</td>
            </tr>
            <tr>
                <td>Tanggal Pekerjaan</td>
                <td>: {{ $jsa->tanggal_pekerjaan->translatedFormat('d F Y') }}</td>
            </tr>
            <tr>
                <td>Tempat</td>
                <td>: {{ $jsa->tempat }}</td>
            </tr>
        </table>
    </div>

    <div class="apd">
        <p class="apd-title">Alat Pelindung Diri yang diperlukan untuk melaksanakan pekerjaan:</p>
        <table>
            <tr>
                @foreach(\App\Models\JobSafetyAnalysis::APD_ITEMS as $key => $item)
                    <td>{{ ! empty($jsa->apd[$key]) ? '☑' : '☐' }} {{ $item['label_id'] }}/{{ $item['label'] }}</td>
                    @if($loop->iteration % 3 === 0 && ! $loop->last)
                        </tr>
                        <tr>
                    @endif
                @endforeach
                @php $apdSisa = 3 - (count(\App\Models\JobSafetyAnalysis::APD_ITEMS) % 3); @endphp
                @for($i = 0; $i < ($apdSisa === 3 ? 0 : $apdSisa); $i++)
                    <td></td>
                @endfor
            </tr>
        </table>
        @if($jsa->apd_lain)
            <p style="margin-top: 4px;">lain-lain / Others : {{ $jsa->apd_lain }}</p>
        @endif
    </div>

    <table class="sheet">
        <thead>
            <tr>
                <th style="width: 26px;">No</th>
                <th>Urutan Langkah Pekerjaan</th>
                <th>Identifikasi Bahaya</th>
                <th>Pengendalian</th>
                <th style="width: 18%;">Penanggung Jawab</th>
            </tr>
        </thead>
        <tbody>
            @foreach($jsa->step_rows as $row)
                <tr>
                    <td class="no">{{ $loop->iteration }}.</td>
                    <td>{{ $row['langkah'] }}</td>
                    <td>{{ $row['bahaya'] }}</td>
                    <td>{{ $row['pengendalian'] }}</td>
                    <td>{{ $row['penanggung_jawab'] }}</td>
                </tr>
            @endforeach
            @for($i = count($jsa->step_rows); $i < 5; $i++)
                <tr>
                    <td class="no">{{ $i + 1 }}.</td>
                    <td class="blank">&nbsp;</td>
                    <td class="blank">&nbsp;</td>
                    <td class="blank">&nbsp;</td>
                    <td class="blank">&nbsp;</td>
                </tr>
            @endfor
        </tbody>
    </table>

    <div class="keterangan">
        <p>Keterangan:</p>
        <p>*Untuk pekerjaan yang memerlukan perpanjangan waktu dengan kasus yang sama dengan hasil identifikasi dan pengendalian yang sama, maka dapat diperpanjang satu kali perpanjangan.</p>
        <p>- Ahli Teknik terkait merupakan Ahli Teknik sesuai bidangnya/ Penanggungjawab Proses.</p>
        <p>- Pengendalian bersifat teknis, perlengkapan APK, APD, harus berdasarkan standar dan/atau Peraturan perundangan sesuai dengan tingkat risiko hasil identifikasi bahaya.</p>
    </div>
</body>
</html>
