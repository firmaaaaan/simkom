<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QR Code Stiker - {{ $komputer->nama_komputer }}</title>
    <style>
        @page {
            size: A4;
            margin: 0;
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: Arial, sans-serif;
            font-size: 9px;
            color: #333;
            background: #fff;
        }
        .page {
            width: 210mm;
            height: 297mm;
            padding: 8mm;
            margin: 0 auto;
        }
        .sticker-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            grid-template-rows: repeat(5, 1fr);
            gap: 3mm;
            height: 100%;
        }
        .sticker {
            border: 1px dashed #ccc;
            padding: 3mm 2mm;
            text-align: center;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            page-break-inside: avoid;
        }
        .sticker-header {
            font-weight: bold;
            font-size: 10px;
            margin-bottom: 2px;
            color: #064e3b;
        }
        .sticker-sub {
            font-size: 8px;
            color: #666;
            margin-bottom: 2px;
        }
        .sticker-qr {
            margin: 0 auto 2px;
        }
        .sticker-info {
            font-size: 8px;
            line-height: 1.3;
        }
        .sticker-code {
            font-weight: bold;
            font-size: 9px;
            color: #059669;
        }
        @media print {
            body { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            .page { margin: 0; padding: 6mm; width: 210mm; height: 297mm; }
            .no-print { display: none !important; }
            .sticker { border: 1px solid #000; }
            .sticker-grid { gap: 2mm; }
        }
    </style>
</head>
<body>
    <div class="no-print" style="text-align: center; padding: 10px; margin-bottom: 15px;">
        <button onclick="window.print()" style="padding: 10px 24px; font-size: 13px; cursor: pointer;">Print 20 Stiker (A4)</button>
        <a href="{{ route('admin.komputer.show', $komputer) }}" style="padding: 10px 24px; font-size: 13px; text-decoration: none; color: #333; margin-left: 10px;">Kembali</a>
    </div>

    <div class="page">
        <div class="sticker-grid">
            @foreach($stickers as $sticker)
                <div class="sticker">
                    <div class="sticker-header">QR Riwayat Pemeliharaan</div>
                    <div class="sticker-qr">{!! QrCode::size(100)->generate($url) !!}</div>
                    <div class="sticker-info">
                        <div class="sticker-code">{{ $komputer->kode_komputer }}</div>
                        <div>{{ $komputer->nama_komputer }}</div>
                        <div>{{ $komputer->laboratorium->nama_laboratorium ?? '-' }}</div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</body>
</html>
