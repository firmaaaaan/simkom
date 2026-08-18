<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Data Software</title>
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
        .kode { width: 100px; }
        .nama { width: 150px; }
        .kategori { width: 80px; }
        .versi { width: 60px; }
        .lisensi { width: 70px; }
        .status { width: 60px; }
        .catatan { width: 140px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Laporan Data Software</h1>
        <p>Tanggal: {{ now()->translatedFormat('d F Y H:i') }}</p>
        <p>Total: {{ $software->count() }} data</p>
    </div>

    <table>
        <thead>
            <tr>
                <th class="no">No</th>
                <th class="kode">Kode Software</th>
                <th class="nama">Nama Software</th>
                <th class="kategori">Kategori</th>
                <th class="versi">Versi</th>
                <th class="lisensi">Lisensi</th>
                <th class="status">Status</th>
                <th class="catatan">Catatan</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($software as $index => $item)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>{{ $item->kode_software ?: '-' }}</td>
                    <td>{{ $item->nama_software }}</td>
                    <td>{{ $item->kategori }}</td>
                    <td>{{ $item->versi ?: '-' }}</td>
                    <td>{{ ucfirst($item->lisensi) }}</td>
                    <td>{{ ucfirst($item->status) }}</td>
                    <td>{{ $item->catatan ?: '-' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
