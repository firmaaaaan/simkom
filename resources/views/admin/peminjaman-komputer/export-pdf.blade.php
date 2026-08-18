<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Peminjaman Komputer</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 11px; }
        .header { text-align: center; margin-bottom: 20px; }
        .header h1 { margin: 0; font-size: 18px; }
        .header p { margin: 5px 0; color: #666; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { border: 1px solid #333; padding: 6px 8px; text-align: left; vertical-align: top; }
        th { background-color: #f2f2f2; font-weight: bold; }
        .text-center { text-align: center; }
        .no { width: 30px; text-align: center; }
        .komputer { width: 120px; }
        .kode { width: 70px; }
        .peminjam { width: 120px; }
        .npm { width: 80px; }
        .tracker { width: 120px; }
        .tanggal { width: 80px; }
        .jam { width: 70px; }
        .status { width: 90px; }
        .catatan { width: 140px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Laporan Peminjaman Komputer</h1>
        <p>Tanggal: {{ now()->translatedFormat('d F Y H:i') }}</p>
        <p>Total: {{ $peminjaman->count() }} data</p>
    </div>

    <table>
        <thead>
            <tr>
                <th class="no">No</th>
                <th class="komputer">Komputer</th>
                <th class="kode">Kode Komputer</th>
                <th class="peminjam">Peminjam</th>
                <th class="npm">NPM/NIM</th>
                <th class="tracker">Kode Tracker</th>
                <th class="tanggal">Tanggal Pinjam</th>
                <th class="jam">Jam</th>
                <th class="status">Status Peminjaman</th>
                <th class="status">Status Komputer</th>
                <th class="catatan">Catatan</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($peminjaman as $index => $item)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>{{ $item->komputer->nama_komputer ?? '-' }}</td>
                    <td>{{ $item->komputer->kode_komputer ?? '-' }}</td>
                    <td>{{ $item->nama_peminjam }}</td>
                    <td>{{ $item->npm_nim }}</td>
                    <td>{{ $item->kode_tracker }}</td>
                    <td>{{ $item->tanggal_pinjam ? \Carbon\Carbon::parse($item->tanggal_pinjam)->translatedFormat('d F Y') : '-' }}</td>
                    <td>{{ $item->jam_mulai }} - {{ $item->jam_selesai }}</td>
                    <td>{{ ucfirst($item->status_peminjaman) }}</td>
                    <td>{{ ucfirst($item->komputer->status ?? '-') }}</td>
                    <td>{{ $item->catatan ?? '-' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
