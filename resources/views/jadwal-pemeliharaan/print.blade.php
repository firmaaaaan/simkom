<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}?v={{ filemtime(public_path('favicon.svg')) }}">
    <title>Jadwal Pemeliharaan Alat dan Bahan - {{ $jadwalPemeliharaan->periode_label }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, Helvetica, sans-serif; font-size: 10px; color: #000; padding: 10px; }
        .print-btn { display: inline-flex; align-items: center; gap: 6px; padding: 8px 20px; background: #16a34a; color: #fff; border: none; border-radius: 6px; font-size: 13px; font-weight: 600; cursor: pointer; margin-bottom: 10px; }
        .print-btn:hover { background: #15803d; }
        .doc-meta { display: flex; justify-content: space-between; font-size: 9px; margin-bottom: 8px; }
        .title { text-align: center; margin: 8px 0 4px; }
        .title h1 { font-size: 12px; font-weight: bold; text-transform: uppercase; letter-spacing: .3px; }
        .title h2 { font-size: 11px; font-weight: bold; text-transform: uppercase; margin-top: 2px; }
        .petunjuk { font-size: 8.5px; font-style: italic; margin: 6px 0 10px; }
        .info { margin: 8px 0 10px; width: 100%; border-collapse: collapse; }
        .info td { padding: 3px 8px 3px 0; font-size: 10px; vertical-align: top; }
        .info td:first-child { font-weight: bold; white-space: nowrap; width: 160px; }
        .sheet { width: 100%; border-collapse: collapse; table-layout: fixed; }
        .sheet th, .sheet td { border: 1px solid #000; padding: 3px 4px; vertical-align: middle; word-wrap: break-word; }
        .sheet thead th { font-weight: bold; font-size: 8.5px; text-align: center; background: #f2f2f2; line-height: 1.2; }
        .sheet td { font-size: 9px; }
        .sheet td.no { text-align: center; }
        .sheet td.chk { text-align: center; font-size: 13px; width: 34px; }
        .sheet th.chk { width: 34px; }
        .sheet th.no { width: 26px; }
        .contoh { font-size: 8.5px; font-style: italic; margin-top: 6px; }
        .signature { margin-top: 14px; width: 100%; border-collapse: collapse; }
        .signature td { width: 33.333%; text-align: center; vertical-align: top; padding-top: 4px; font-size: 9.5px; }
        .sig-space { height: 55px; }
        .sig-name { font-weight: bold; text-decoration: underline; }
        @media print { .print-btn { display: none !important; } body { padding: 0; } @page { size: landscape; margin: 10mm; } }
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
        'kopTitle' => 'FORMULIR JADWAL PEMELIHARAAN ALAT DAN BAHAN LABORATORIUM',
        'kopLines' => ["UNIVERSITAS 'AISYIYAH YOGYAKARTA"],
    ])

    <div class="doc-meta">
        <span>No. Dokumen : {{ $jadwalPemeliharaan->kode_dokumen }}</span>
        <span>Revisi : ........</span>
        <span>Tanggal Berlaku : ....................</span>
        <span>Halaman : 1 dari 1</span>
    </div>

    <div class="title">
        <h1>Jadwal Pemeliharaan Alat dan Bahan Laboratorium</h1>
    </div>
    <p class="petunjuk">Petunjuk: Form digunakan untuk merencanakan dan memantau pemeliharaan alat/bahan laboratorium secara periodik sesuai jenis dan karakteristiknya.</p>

    <table class="info">
        <tr>
            <td>Laboratorium/Unit</td>
            <td>: {{ $jadwalPemeliharaan->nama_lab }}</td>
            <td style="width: 30px;"></td>
            <td style="font-weight: bold; width: 150px;">PIC/Petugas</td>
            <td>: {{ $jadwalPemeliharaan->pic }}</td>
        </tr>
        <tr>
            <td>Periode</td>
            <td>: {{ $jadwalPemeliharaan->periode_label }}</td>
            <td></td>
            <td style="font-weight: bold;">Tanggal Penyusunan</td>
            <td>: {{ $jadwalPemeliharaan->tanggal_penyusunan->translatedFormat('d / m / Y') }}</td>
        </tr>
    </table>

    <table class="sheet">
        <thead>
            <tr>
                <th class="no" rowspan="2">No.</th>
                <th rowspan="2">Nama Alat/Bahan</th>
                <th rowspan="2">Kode/No. Inventaris</th>
                <th rowspan="2">Lokasi</th>
                <th rowspan="2">Jenis Pemeliharaan</th>
                <th rowspan="2">Frekuensi</th>
                <th rowspan="2">Jadwal</th>
                <th rowspan="2">PIC</th>
                <th class="chk" colspan="3">Status</th>
                <th rowspan="2">Catatan/Hasil</th>
            </tr>
            <tr>
                <th class="chk" style="font-size: 8px;">Terjadwal</th>
                <th class="chk" style="font-size: 8px;">Selesai</th>
                <th class="chk" style="font-size: 8px;">Tunda</th>
            </tr>
        </thead>
        <tbody>
            @forelse($jadwalPemeliharaan->items as $item)
                <tr>
                    <td class="no">{{ $loop->iteration }}</td>
                    <td>{{ $item->nama_alat_bahan }}</td>
                    <td>
                        @if(count($item->kode_list))
                            @foreach($item->kode_list as $code)
                                <div>{{ $code }}</div>
                            @endforeach
                        @endif
                    </td>
                    <td>{{ $item->lokasi ?? '' }}</td>
                    <td>{{ $item->jenis_pemeliharaan ?? '' }}</td>
                    <td>{{ $item->frekuensi ?? '' }}</td>
                    <td>{{ $item->jadwal?->translatedFormat('d/m/Y') ?? '' }}</td>
                    <td>{{ $item->pic ?? '' }}</td>
                    <td class="chk">{{ $item->status === 'Terjadwal' ? '☑' : '☐' }}</td>
                    <td class="chk">{{ $item->status === 'Selesai' ? '☑' : '☐' }}</td>
                    <td class="chk">{{ $item->status === 'Tunda' ? '☑' : '☐' }}</td>
                    <td>{{ $item->catatan_hasil ?? '' }}</td>
                </tr>
            @empty
                @for($i = 1; $i <= 10; $i++)
                    <tr>
                        <td class="no">{{ $i }}</td>
                        <td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td>
                        <td>&nbsp;</td><td>&nbsp;</td>
                        <td class="chk">☐</td><td class="chk">☐</td><td class="chk">☐</td>
                        <td>&nbsp;</td>
                    </tr>
                @endfor
            @endforelse
        </tbody>
    </table>

    <p class="contoh">Contoh jenis pemeliharaan: pembersihan, pengecekan fungsi, pelumasan, penggantian komponen, sterilisasi, pengondisian/penyimpanan, atau tindakan lain sesuai karakteristik alat/bahan.</p>

    <table class="signature">
        <tr>
            <td>
                <div>Disusun oleh</div>
                <div>PIC/Petugas Laboratorium</div>
                <div class="sig-space"></div>
                <div class="sig-name">{{ $jadwalPemeliharaan->disusun_oleh ?? $jadwalPemeliharaan->pic }}</div>
                <div>Tanggal: {{ $jadwalPemeliharaan->disusun_tanggal?->translatedFormat('d / m / Y') ?? $jadwalPemeliharaan->tanggal_penyusunan->translatedFormat('d / m / Y') }}</div>
            </td>
            <td>
                <div>Diperiksa oleh</div>
                <div>Kepala UPT/Kepala Laboratorium</div>
                <div class="sig-space"></div>
                <div class="sig-name">{{ $jadwalPemeliharaan->diperiksa_oleh ?? '(...........................................) ' }}</div>
                <div>Tanggal: {{ $jadwalPemeliharaan->diperiksa_tanggal?->translatedFormat('d / m / Y') ?? '.... / .... / ........' }}</div>
            </td>
            <td>
                <div>Mengetahui</div>
                <div>Pejabat/Penanggung Jawab</div>
                <div class="sig-space"></div>
                <div class="sig-name">{{ $jadwalPemeliharaan->mengetahui_oleh ?? '(...........................................) ' }}</div>
                <div>Tanggal: {{ $jadwalPemeliharaan->mengetahui_tanggal?->translatedFormat('d / m / Y') ?? '.... / .... / ........' }}</div>
            </td>
        </tr>
    </table>
</body>
</html>
