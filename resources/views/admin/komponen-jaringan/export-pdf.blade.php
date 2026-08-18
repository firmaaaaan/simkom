<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Data Komponen Jaringan</title>
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
        .kategori { width: 70px; }
        .merek { width: 70px; }
        .jumlah { width: 50px; text-align: center; }
        .lokasi { width: 100px; }
        .status { width: 60px; }
        .catatan { width: 140px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Laporan Data Komponen Jaringan</h1>
        <p>Tanggal: {{ now()->translatedFormat('d F Y H:i') }}</p>
        <p>Total: {{ $komponen->count() }} data</p>
    </div>

    <table>
        <thead>
            <tr>
                <th class="no">No</th>
                <th class="kode">Kode Komponen</th>
                <th class="nama">Nama Komponen</th>
                <th class="kategori">Kategori</th>
                <th class="merek">Merek</th>
                <th class="jumlah">Jumlah</th>
                <th class="lokasi">Lokasi</th>
                <th class="status">Status</th>
                <th class="catatan">Catatan</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($komponen as $index => $item)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>{{ $item->kode_komponen ?: '-' }}</td>
                    <td>{{ $item->nama_komponen }}</td>
                    <td>{{ $item->kategori }}</td>
                    <td>{{ $item->merek ?: '-' }}</td>
                    <td class="text-center">{{ $item->jumlah }}</td>
                    <td>{{ $item->lokasi ?: '-' }}</td>
                    <td>{{ ucfirst($item->status) }}</td>
                    <td>{{ $item->catatan ?: '-' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
