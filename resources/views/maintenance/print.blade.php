<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Cetak - Kontrol Pemeliharaan</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Times New Roman', Times, serif; font-size: 10px; color: #000; padding: 15px; }

        .print-btn {
            display: inline-flex; align-items: center; gap: 6px;
            padding: 8px 20px; background: #16a34a; color: #fff; border: none;
            border-radius: 6px; font-size: 13px; font-weight: 600; cursor: pointer;
            margin-bottom: 15px;
        }
        .print-btn:hover { background: #15803d; }

        .header { text-align: center; margin-bottom: 14px; }
        .header h1 { font-size: 13px; font-weight: bold; text-transform: uppercase; margin-bottom: 2px; }
        .header h2 { font-size: 12px; font-weight: bold; text-transform: uppercase; }

        .info { margin-bottom: 10px; }
        .info table { border-collapse: collapse; }
        .info td { padding: 2px 8px 2px 0; font-size: 11px; vertical-align: top; }
        .info td:first-child { font-weight: bold; white-space: nowrap; width: 100px; }

        .checklist-table { width: 100%; border-collapse: collapse; border: 1px solid #000; font-size: 9px; table-layout: fixed; }
        .checklist-table th,
        .checklist-table td { border: 1px solid #000; padding: 2px 3px; text-align: center; vertical-align: middle; }
        .checklist-table th { background: #f0f0f0; font-weight: bold; font-size: 8px; }

        .checklist-table th.no-col,
        .checklist-table td.no-col { width: 28px; }
        .checklist-table th.item-col,
        .checklist-table td.item-col { text-align: left; width: 200px; font-size: 9px; }
        .checklist-table th.pc-col,
        .checklist-table td.pc-col { width: 18px; font-size: 7px; padding: 1px; }

        .checklist-table tr.cat-row td { background: #e8e8e8; font-weight: bold; text-align: left; font-size: 9px; }

        .checkbox { display: inline-block; width: 10px; height: 10px; border: 1px solid #000; background: #fff; vertical-align: middle; }
        .checkbox.checked { background: #fff; position: relative; }
        .checkbox.checked::after { content: '✓'; position: absolute; top: -2px; left: 0.5px; font-size: 11px; font-weight: bold; }

        .id-pc-header { font-size: 8px; font-weight: bold; text-align: center; }

        @media print {
            .print-btn { display: none !important; }
            body { padding: 0; }
            @page { size: landscape; margin: 8mm; }
        }
    </style>
</head>
<body>
    <button class="print-btn" onclick="window.print()">
        <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0110.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0l.229 2.523a1.125 1.125 0 01-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0021 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 00-1.913-.247M6.34 18H5.25A2.25 2.25 0 013 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 011.913-.247m10.5 0a48.536 48.536 0 00-10.5 0m10.5 0V3.375c0-.621-.504-1.125-1.125-1.125h-8.25c-.621 0-1.125.504-1.125 1.125v3.659" />
        </svg>
        Cetak
    </button>

    <div class="header">
        <h1>KONTROL PEMELIHARAAN LABORATORIUM KOMPUTER</h1>
        <h2>UNIVERSITAS 'AISYIYAH YOGYAKARTA</h2>
    </div>

    <div class="info">
        <table>
            <tr>
                <td>Nama Lab</td>
                <td>: {{ $maintenance->laboratory->name ?? '-' }}</td>
            </tr>
            <tr>
                <td>Tahun Ajaran</td>
                <td>: {{ $maintenance->academicYear->name ?? '-' }}</td>
            </tr>
        </table>
    </div>

    <table class="checklist-table">
        <colgroup>
            <col style="width: 28px;">
            <col style="width: 200px;">
            @foreach($computers as $computer)
                <col style="width: 18px;">
            @endforeach
        </colgroup>
        <thead>
            <tr>
                <th class="no-col" rowspan="2">No</th>
                <th class="item-col" rowspan="2">Item</th>
                <th colspan="{{ $computers->count() }}" class="id-pc-header">ID PC</th>
            </tr>
            <tr>
                @foreach($computers as $computer)
                    <th class="pc-col">{{ $computer->code }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @foreach($checklistItems as $category => $categoryData)
                <tr class="cat-row">
                    <td colspan="{{ 2 + $computers->count() }}">{{ $category }}. {{ $categoryData['name'] }}</td>
                </tr>
                @foreach($categoryData['items'] as $itemIndex => $question)
                    @php $itemNum = $itemIndex + 1; @endphp
                    <tr>
                        <td class="no-col">{{ $itemNum }}</td>
                        <td class="item-col">{{ $question }}</td>
                        @foreach($computers as $computer)
                            @php $key = "{$category}_{$itemNum}_{$computer->id}"; @endphp
                            <td class="pc-col">
                                @if(isset($items[$key]) && $items[$key]->is_checked)
                                    <span class="checkbox checked"></span>
                                @else
                                    <span class="checkbox"></span>
                                @endif
                            </td>
                        @endforeach
                    </tr>
                @endforeach
            @endforeach
            <tr class="cat-row">
                <td colspan="{{ 2 + $computers->count() }}">CATATAN LAIN:</td>
            </tr>
            <tr>
                <td colspan="2" class="item-col" style="font-weight: bold;">Pemeriksaan Komputer</td>
                <td colspan="{{ $computers->count() }}" style="text-align: left; padding: 4px 6px;">{{ $maintenance->notes_computer ?? '' }}</td>
            </tr>
            <tr>
                <td colspan="2" class="item-col" style="font-weight: bold;">Pemeriksaan Mouse dan Keyboard</td>
                <td colspan="{{ $computers->count() }}" style="text-align: left; padding: 4px 6px;">{{ $maintenance->notes_mouse_keyboard ?? '' }}</td>
            </tr>
            <tr>
                <td colspan="2" class="item-col" style="font-weight: bold;">Pemeriksaan UPS</td>
                <td colspan="{{ $computers->count() }}" style="text-align: left; padding: 4px 6px;">{{ $maintenance->notes_ups ?? '' }}</td>
            </tr>
            <tr>
                <td colspan="2" class="item-col" style="font-weight: bold;">Pemeriksaan Monitor</td>
                <td colspan="{{ $computers->count() }}" style="text-align: left; padding: 4px 6px;">{{ $maintenance->notes_monitor ?? '' }}</td>
            </tr>
        </tbody>
    </table>

    <div style="margin-top: 40px; display: flex; justify-content: flex-end; font-size: 11px;">
        <div style="width: 220px;">
            <p>Yogyakarta, {{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}</p>
            <p>Penanggung jawab</p>
            <p style="margin-top: 50px; padding-top: 4px; width: 180px;">Firmansyah, S.Kom</p>
        </div>
    </div>
</body>
</html>
