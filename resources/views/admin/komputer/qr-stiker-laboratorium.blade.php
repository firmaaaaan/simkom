<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak QR Stiker Berdasarkan Laboratorium</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; font-size: 10px; color: #333; }
        .page { width: 210mm; min-height: 297mm; padding: 10mm; margin: 0 auto; }
        .sticker-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 4mm; }
        .sticker { border: 1px dashed #ccc; padding: 3mm; text-align: center; page-break-inside: avoid; }
        .sticker-header { font-weight: bold; font-size: 9px; margin-bottom: 2px; color: #064e3b; }
        .sticker-sub { font-size: 8px; color: #666; margin-bottom: 3px; }
        .sticker-qr { margin: 0 auto 2px; }
        .sticker-info { font-size: 8px; line-height: 1.2; }
        .sticker-code { font-weight: bold; font-size: 8px; color: #059669; }
        @media print {
            body { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            .page { margin: 0; padding: 10mm; width: 100%; }
            .no-print { display: none !important; }
            .sticker { border: 1px solid #000; }
        }
    </style>
</head>
<body>
    <div class="no-print" style="text-align: center; padding: 10px; margin-bottom: 15px;">
        <button onclick="window.print()" style="padding: 10px 24px; font-size: 13px; cursor: pointer;">Print Stiker</button>
        <a href="{{ route('admin.komputer.index') }}" style="padding: 10px 24px; font-size: 13px; text-decoration: none; color: #333; margin-left: 10px;">Kembali</a>
    </div>

    <div class="no-print" style="max-width: 600px; margin: 0 auto 20px; padding: 20px; border: 1px solid #ddd; border-radius: 8px;">
        <h3 style="margin-bottom: 15px; text-align: center;">Pilih Laboratorium</h3>
        <form method="GET" action="{{ route('admin.komputer.qr-stiker') }}" style="display: flex; gap: 10px; align-items: flex-end;">
            <div style="flex: 1;">
                <label style="display: block; margin-bottom: 5px; font-weight: bold;">Laboratorium <span style="color: red;">*</span></label>
                <select name="laboratorium_id" class="form-select" required>
                    <option value="">-- Pilih Laboratorium --</option>
                    @foreach($laboratoriums as $lab)
                        <option value="{{ $lab->id }}" {{ request('laboratorium_id') == $lab->id ? 'selected' : '' }}>
                            {{ $lab->nama_laboratorium }} ({{ $lab->kode_laboratorium }})
                        </option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Tampilkan QR Stiker</button>
        </form>
    </div>

    @if($laboratorium && $komputers->count() > 0)
        @foreach($komputers->chunk(20) as $chunkIndex => $chunk)
            <div class="page">
                <div style="text-align: center; margin-bottom: 10px;">
                    <h2>QR Stiker - {{ $laboratorium->nama_laboratorium }}</h2>
                    <p>Halaman {{ $chunkIndex + 1 }} dari {{ ceil($komputers->count() / 20) }} | Total: {{ $komputers->count() }} komputer</p>
                </div>
                <div class="sticker-grid">
                    @foreach($chunk as $komputer)
                        @php
                            $url = route('admin.komputer.pemeliharaan', $komputer);
                        @endphp
                        <div class="sticker">
                            <div class="sticker-header">QR Riwayat Pemeliharaan</div>
                            <div class="sticker-qr">{!! QrCode::size(90)->generate($url) !!}</div>
                            <div class="sticker-info">
                                <div class="sticker-code">{{ $komputer->kode_komputer }}</div>
                                <div>{{ $komputer->nama_komputer }}</div>
                                <div>{{ $komputer->laboratorium->nama_laboratorium ?? '-' }}</div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
            @if(!$loop->last)
                <div style="page-break-after: always;"></div>
            @endif
        @endforeach
    @elseif(request('laboratorium_id'))
        <div style="text-align: center; padding: 40px; color: #666;">
            <p>Tidak ada data komputer di laboratorium ini.</p>
        </div>
    @endif
</body>
</html>
