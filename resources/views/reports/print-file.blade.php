<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <title>{{ $jenisTitle }} - {{ $lab->name ?? '' }} - {{ $year->name }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Times New Roman', serif; font-size: 12px; color: #000; }
        .page { padding: 15mm; }
        .header { text-align: center; margin-bottom: 14px; }
        .header h1 { font-size: 14px; font-weight: bold; text-transform: uppercase; margin-bottom: 2px; }
        .header h2 { font-size: 14px; font-weight: bold; text-transform: uppercase; }
        .info { margin-bottom: 15px; font-size: 12px; }
        .info table td { padding: 2px 8px 2px 0; vertical-align: top; }
        .info table td:first-child { font-weight: bold; width: 110px; }
        table.report { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
        table.report th, table.report td { border: 1px solid #000; padding: 5px 8px; text-align: left; font-size: 11px; }
        table.report thead th { background: #000; color: #fff; text-align: center; font-weight: bold; }
        table.report tbody tr:nth-child(even) { background: #f5f5f5; }
        .empty { text-align: center; color: #555; font-style: italic; padding: 20px; border: 1px dashed #999; }
        .checkbox { display: inline-block; width: 14px; height: 14px; border: 1.5px solid #000; vertical-align: middle; text-align: center; line-height: 12px; font-size: 11px; font-weight: bold; font-family: 'DejaVu Sans', sans-serif; }
        .signature { margin-top: 40px; text-align: right; font-size: 11px; }
        .signature-block { display: inline-block; width: 220px; text-align: left; }
        .signature-block .line { border-top: 1px solid #000; margin-top: 60px; margin-bottom: 4px; }
        @media print { .page { padding: 0; } }
        @page { size: A4 portrait; margin: 10mm; }
    </style>
</head>
<body>
    <div class="page">
        @include('partials.kop', [
            'kopTitle' => 'Laporan '.$jenisTitle,
            'kopLines' => ['UPT LABORATORIUM TERPADU', "Universitas 'Aisyiyah Yogyakarta"],
        ])

        <div class="info">
            <table>
                <tr>
                    <td>Jenis Laporan</td>
                    <td>: {{ $jenisTitle }}</td>
                </tr>
                @if($lab)
                <tr>
                    <td>Nama Lab</td>
                    <td>: {{ $lab->name }}{{ $lab->code ? ' ('.$lab->code.')' : '' }}</td>
                </tr>
                @endif
                <tr>
                    <td>Tahun Ajaran</td>
                    <td>: {{ $year->name }}</td>
                </tr>
                <tr>
                    <td>Tanggal Cetak</td>
                    <td>: {{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}</td>
                </tr>
            </table>
        </div>

        @if($rows->isEmpty())
            <div class="empty">Belum ada data {{ strtolower($jenisTitle) }}</div>
        @else
        <table class="report">
            <thead>
                <tr>
                    <th style="width: 35px;">No</th>
                    @switch($jenis)
                        @case('lab_usages')
                            <th>Nama</th>
                            <th>Prodi</th>
                            <th>Keperluan</th>
                            <th style="width: 85px;">Tanggal</th>
                            <th style="width: 65px;">Jam Masuk</th>
                            <th style="width: 55px;">Status</th>
                            @break
                        @case('borrowings')
                            <th style="width: 100px;">Kode</th>
                            <th>Peminjam</th>
                            <th style="width: 85px;">Komputer</th>
                            <th style="width: 85px;">Tanggal</th>
                            <th style="width: 100px;">Jam</th>
                            <th style="width: 70px;">Status</th>
                            @break
                        @case('device_checks')
                            <th style="width: 85px;">Tanggal Cek</th>
                            <th>Petugas</th>
                            <th>Catatan</th>
                            @break
                        @case('card_control')
                            <th style="width: 130px;">Kode Komputer</th>
                            <th style="width: 85px;">Tanggal</th>
                            <th style="width: 50px;">Baik</th>
                            <th style="width: 50px;">Tidak</th>
                            <th>Keterangan</th>
                            <th style="width: 120px;">PJ</th>
                            @break
                        @case('tickets')
                            <th style="width: 100px;">Kode</th>
                            <th>Judul</th>
                            <th style="width: 80px;">Kategori</th>
                            <th style="width: 70px;">Prioritas</th>
                            <th style="width: 75px;">Status</th>
                            @break
                        @case('maintenances')
                            <th style="width: 85px;">Tanggal</th>
                            <th>Inspektur</th>
                            <th>Catatan</th>
                            @break
                        @case('pengadaan')
                            <th style="width: 100px;">Kode</th>
                            <th>Nama Barang</th>
                            <th style="width: 80px;">Kategori</th>
                            <th style="width: 60px;">Jumlah</th>
                            <th style="width: 85px;">Tanggal</th>
                            <th style="width: 70px;">Status</th>
                            @break
                    @endswitch
                </tr>
            </thead>
            <tbody>
                @foreach($rows as $index => $row)
                    <tr>
                        <td style="text-align: center;">{{ $index + 1 }}</td>
                        @switch($jenis)
                            @case('lab_usages')
                                <td>{{ $row->user_name }}</td>
                                <td>{{ $row->user_prodi ?? '-' }}</td>
                                <td>{{ $row->purpose ?? '-' }}</td>
                                <td style="text-align: center;">{{ $row->checked_in_at?->translatedFormat('d M Y') }}</td>
                                <td style="text-align: center;">{{ $row->checked_in_at?->format('H:i') }}</td>
                                <td style="text-align: center;">{{ $row->status }}</td>
                                @break
                            @case('borrowings')
                                <td>{{ $row->tracking_code }}</td>
                                <td>{{ $row->borrower_name }}</td>
                                <td>{{ $row->computer->code ?? '-' }}</td>
                                <td style="text-align: center;">{{ \Illuminate\Support\Carbon::parse($row->borrow_date)->translatedFormat('d M Y') }}</td>
                                <td style="text-align: center;">{{ $row->borrow_time_start }} - {{ $row->borrow_time_end }}</td>
                                <td style="text-align: center;">{{ $row->status }}</td>
                                @break
                            @case('device_checks')
                                <td style="text-align: center;">{{ $row->check_date?->translatedFormat('d M Y') }}</td>
                                <td>{{ $row->officer_name ?? '-' }}</td>
                                <td>{{ $row->notes ?? '-' }}</td>
                                @break
                            @case('card_control')
                                <td style="font-weight: bold;">{{ $row->code }}</td>
                                <td style="text-align: center;">{{ $row->latestCheck?->created_at?->translatedFormat('d M Y') ?? '-' }}</td>
                                <td style="text-align: center;">
                                    @if($row->is_baik)
                                        <span class="checkbox checked">&#10003;</span>
                                    @elseif($row->latestCheck)
                                        <span class="checkbox"></span>
                                    @else
                                        &ndash;
                                    @endif
                                </td>
                                <td style="text-align: center;">
                                    @if($row->latestCheck && !$row->is_baik)
                                        <span class="checkbox checked">&#10003;</span>
                                    @elseif($row->latestCheck)
                                        <span class="checkbox"></span>
                                    @else
                                        &ndash;
                                    @endif
                                </td>
                                <td>{{ $row->latestCheck->notes ?? '-' }}</td>
                                <td>{{ $row->latestCheck?->checkedBy->name ?? '-' }}</td>
                                @break
                            @case('tickets')
                                <td>{{ $row->tracking_code }}</td>
                                <td>{{ $row->title }}</td>
                                <td>{{ $row->category }}</td>
                                <td style="text-align: center;">{{ $row->priority }}</td>
                                <td style="text-align: center;">{{ $row->status }}</td>
                                @break
                            @case('maintenances')
                                <td style="text-align: center;">{{ \Illuminate\Support\Carbon::parse($row->maintenance_date)->translatedFormat('d M Y') }}</td>
                                <td>{{ $row->inspector_name ?? '-' }}</td>
                                <td>{{ $row->notes_computer ?? $row->notes_mouse_keyboard ?? $row->notes_ups ?? $row->notes_monitor ?? '-' }}</td>
                                @break
                            @case('pengadaan')
                                <td>{{ $row->kode }}</td>
                                <td>{{ $row->nama_barang }}</td>
                                <td>{{ $row->kategori }}</td>
                                <td style="text-align: center;">{{ $row->jumlah }}</td>
                                <td style="text-align: center;">{{ $row->tanggal?->translatedFormat('d M Y') }}</td>
                                <td style="text-align: center;">{{ $row->status }}</td>
                                @break
                        @endswitch
                    </tr>
                @endforeach
            </tbody>
        </table>
        @endif

        <div class="signature">
            <div class="signature-block">
                <p>Yogyakarta, {{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}</p>
                <p>Penanggung jawab</p>
                <div class="line"></div>
                <p>Firmansyah, S.Kom</p>
            </div>
        </div>
    </div>
</body>
</html>
