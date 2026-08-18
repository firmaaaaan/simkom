<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Data Komputer</title>
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
        .kode { width: 90px; }
        .nama { width: 140px; }
        .lab { width: 120px; }
        .status { width: 70px; }
        .spec { width: 180px; }
        .hardware { width: 160px; }
        .software { width: 160px; }
        .catatan { width: 140px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Laporan Data Komputer</h1>
        <p>Tanggal: {{ now()->translatedFormat('d F Y H:i') }}</p>
        <p>Total: {{ $komputers->count() }} data</p>
    </div>

    <table>
        <thead>
            <tr>
                <th class="no">No</th>
                <th class="kode">Kode Komputer</th>
                <th class="nama">Nama Komputer</th>
                <th class="lab">Laboratorium</th>
                <th class="status">Status</th>
                <th class="spec">Spesifikasi</th>
                <th class="hardware">Hardware</th>
                <th class="software">Software</th>
                <th class="catatan">Catatan</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($komputers as $index => $komputer)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>{{ $komputer->kode_komputer }}</td>
                    <td>{{ $komputer->nama_komputer }}</td>
                    <td>{{ $komputer->laboratorium->nama_laboratorium ?? '-' }}</td>
                    <td>{{ ucfirst(str_replace('_', ' ', $komputer->status)) }}</td>
                    <td>{{ $komputer->spesifikasi ?: '-' }}</td>
                    <td>{{ $komputer->hardware->pluck('nama_hardware')->filter()->join(', ') ?: '-' }}</td>
                    <td>{{ $komputer->software->pluck('nama_software')->filter()->join(', ') ?: '-' }}</td>
                    <td>{{ $komputer->catatan ?: '-' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
