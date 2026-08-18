<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak QR Stiker Berdasarkan Kategori</title>
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
        <a href="{{ route('admin.inventaris-iot-jaringan.index') }}" style="padding: 10px 24px; font-size: 13px; text-decoration: none; color: #333; margin-left: 10px;">Kembali</a>
    </div>

    <div class="no-print" style="max-width: 600px; margin: 0 auto 20px; padding: 20px; border: 1px solid #ddd; border-radius: 8px;">
        <h3 style="margin-bottom: 15px; text-align: center;">Pilih Kategori</h3>
        <form method="GET" action="{{ route('admin.inventaris-iot-jaringan.qr-stiker') }}" style="display: flex; gap: 10px; align-items: flex-end;">
            <div style="flex: 1;">
                <label style="display: block; margin-bottom: 5px; font-weight: bold;">Kategori <span style="color: red;">*</span></label>
                <select name="kategori" class="form-select" required>
                    <option value="">-- Pilih Kategori --</option>
                    @foreach($kategoriOptions as $kategori)
                        <option value="{{ $kategori }}" {{ $selectedKategori == $kategori ? 'selected' : '' }}>
                            {{ $kategori }}
                        </option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Tampilkan QR Stiker</button>
        </form>
    </div>

    @if($selectedKategori && $inventaris->count() > 0)
        @foreach($inventaris->chunk(20) as $chunkIndex => $chunk)
            <div class="page">
                <div style="text-align: center; margin-bottom: 10px;">
                    <h2>QR Stiker - {{ $selectedKategori }}</h2>
                    <p>Halaman {{ $chunkIndex + 1 }} dari {{ ceil($inventaris->count() / 20) }} | Total: {{ $inventaris->count() }} inventaris</p>
                </div>
                <div class="sticker-grid">
                    @foreach($chunk as $item)
                        @php
                            $url = route('inventaris-iot-jaringan.peminjaman.create', $item);
                        @endphp
                        <div class="sticker">
                            <div class="sticker-header">QR Peminjaman</div>
                            <div class="sticker-qr">{!! QrCode::size(90)->generate($url) !!}</div>
                            <div class="sticker-info">
                                <div class="sticker-code">{{ $item->kode_perangkat ?? $item->id }}</div>
                                <div>{{ $item->nama_inventaris }}</div>
                                <div>{{ $item->kategori }} - {{ $item->jenis }}</div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
            @if(!$loop->last)
                <div style="page-break-after: always;"></div>
            @endif
        @endforeach
    @elseif(request('kategori'))
        <div style="text-align: center; padding: 40px; color: #666;">
            <p>Tidak ada data inventaris IoT & Jaringan untuk kategori ini.</p>
        </div>
    @endif
</body>
</html>
