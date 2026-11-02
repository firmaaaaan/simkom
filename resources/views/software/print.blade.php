<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Daftar Software</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, Helvetica, sans-serif; font-size: 10px; color: #000; padding: 15px; }
        .print-btn { display: inline-flex; padding: 8px 20px; background: #16a34a; color: #fff; border: none; border-radius: 6px; font-size: 13px; font-weight: 600; cursor: pointer; margin-bottom: 15px; }
        .header { text-align: left; margin-bottom: 12px; }
        .header h1 { font-size: 13px; font-weight: bold; text-transform: uppercase; margin-bottom: 2px; }
        .header h2 { font-size: 12px; font-weight: bold; text-transform: uppercase; }
        .info { margin-bottom: 10px; }
        .info table { border-collapse: collapse; }
        .info td { padding: 2px 8px 2px 0; font-size: 11px; vertical-align: top; }
        .info td:first-child { font-weight: bold; white-space: nowrap; width: 110px; }
        table.data { width: 100%; border-collapse: collapse; }
        table.data th, table.data td { border: 1px solid #000; padding: 3px 5px; font-size: 10px; text-align: left; vertical-align: top; }
        table.data thead th { background: #e8e8e8; font-weight: bold; }
        table.data td.num, table.data th.num { text-align: center; width: 40px; }
        @media print {
            .print-btn { display: none !important; }
            .header { position: fixed; top: 0; left: 0; right: 0; background: #fff; padding-bottom: 8px; }
            @page { size: landscape; margin: 15mm; }
            body { padding: 0; margin-top: 90px; }
        }
    </style>
</head>
<body>
    <button class="print-btn" onclick="window.print()">Cetak</button>
    <div class="header">
        @include('partials.kop', [
            'kopTitle' => 'Daftar Software',
            'kopLines' => ['UPT LABORATORIUM TERPADU', "Universitas 'Aisyiyah Yogyakarta"],
        ])
    </div>
    <div class="info">
        <table>
            <tr><td>Dicetak</td><td>: {{ \Carbon\Carbon::now()->translatedFormat('d F Y H:i') }} WIB</td></tr>
            <tr><td>Jumlah Software</td><td>: {{ $software->count() }}</td></tr>
        </table>
    </div>
    <table class="data">
        <thead>
            <tr>
                <th class="num">No</th>
                <th>Kode</th>
                <th>Nama</th>
                <th>Versi</th>
                <th>Kategori</th>
                <th>Lisensi</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($software as $index => $item)
                <tr>
                    <td class="num">{{ $index + 1 }}</td>
                    <td>{{ $item->code }}</td>
                    <td>{{ $item->name }}</td>
                    <td>{{ $item->version }}</td>
                    <td>{{ $item->category }}</td>
                    <td>{{ $item->licenses_count }} lisensi</td>
                    <td>{{ $item->status }}</td>
                </tr>
            @empty
                <tr><td colspan="7" style="text-align:center;">Tidak ada data software</td></tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>