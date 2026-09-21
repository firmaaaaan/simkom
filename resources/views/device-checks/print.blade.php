@php
    $header = \App\Models\DeviceCheck::headerGroups();
    $columns = \App\Models\DeviceCheck::itemColumns();
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <title>Pengecekan Perangkat - {{ $check->laboratory->name ?? '' }} - {{ $check->academicYear->name ?? '' }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, Helvetica, sans-serif; font-size: 11px; color: #000; padding: 15px; }

        .print-btn {
            display: inline-flex; align-items: center; gap: 6px;
            padding: 8px 20px; background: #16a34a; color: #fff; border: none;
            border-radius: 6px; font-size: 13px; font-weight: 600; cursor: pointer;
            margin-bottom: 15px;
        }
        .print-btn:hover { background: #15803d; }

        .info { margin-bottom: 12px; }
        .info table { border-collapse: collapse; }
        .info td { padding: 2px 8px 2px 0; font-size: 12px; vertical-align: top; }
        .info td:first-child { font-weight: bold; white-space: nowrap; width: 90px; }

        .matrix { width: 100%; border-collapse: collapse; table-layout: fixed; }
        .matrix th,
        .matrix td { border: 1px solid #000; padding: 3px 2px; text-align: center; vertical-align: middle; }
        .matrix thead th { font-weight: bold; font-size: 10px; }
        .matrix th.group-header { background: #e8e8e8; }
        .matrix td.id-pc { text-align: left; font-weight: bold; font-size: 11px; }
        .matrix td.no { width: 30px; }
        .matrix tbody tr:nth-child(even) { background: #f7f7f7; }

        .checkbox { display: inline-block; width: 12px; height: 12px; border: 1px solid #000; background: #d9d9d9; vertical-align: middle; }
        .checkbox.checked { background: #d9d9d9; position: relative; }
        .checkbox.checked::after { content: '✓'; position: absolute; top: -3px; left: 0.5px; font-size: 12px; font-weight: bold; }

        .notes { margin-top: 12px; font-size: 11px; }
        .notes .label { font-weight: bold; }

        .signature { margin-top: 35px; display: flex; justify-content: flex-end; font-size: 11px; }
        .signature-block { width: 220px; }
        .signature-block .line { border-top: 1px solid #000; margin-top: 55px; margin-bottom: 4px; }

        @media print {
            .print-btn { display: none !important; }
            body { padding: 0; }
            @page { size: portrait; margin: 10mm; }
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

    <div class="info">
        <table>
            <tr>
                <td>Nama Lab</td>
                <td>: {{ $check->laboratory->name ?? '-' }}</td>
            </tr>
            <tr>
                <td>Periode</td>
                <td>: {{ $check->academicYear->name ?? '-' }}{{ $check->academicYear ? ' (' . $check->academicYear->periodLabel() . ')' : '' }}</td>
            </tr>
            <tr>
                <td>Tanggal</td>
                <td>: {{ $check->check_date?->translatedFormat('d F Y') ?? '-' }}</td>
            </tr>
            <tr>
                <td>Petugas</td>
                <td>: {{ $check->officer_name ?: '-' }}</td>
            </tr>
        </table>
    </div>

    <table class="matrix">
        <colgroup>
            <col style="width: 30px;">
            <col style="width: 90px;">
            @foreach($columns as $column)
                <col style="width: 46px;">
            @endforeach
        </colgroup>
        <thead>
            <tr>
                <th rowspan="2" class="no">No</th>
                <th rowspan="2">ID PC</th>
                @foreach($header as $column)
                    @if($column['type'] === 'single')
                        <th rowspan="2">{{ $column['label'] }}</th>
                    @else
                        <th colspan="{{ count($column['columns']) }}" class="group-header">{{ $column['label'] }}</th>
                    @endif
                @endforeach
            </tr>
            <tr>
                @foreach($header as $column)
                    @if($column['type'] === 'group')
                        @foreach($column['columns'] as $sub)
                            <th class="group-header">{{ $sub['label'] }}</th>
                        @endforeach
                    @endif
                @endforeach
            </tr>
        </thead>
        <tbody>
            @forelse($computers as $index => $computer)
                <tr>
                    <td class="no">{{ $index + 1 }}</td>
                    <td class="id-pc">{{ $computer->code }}</td>
                    @foreach($columns as $column)
                        @php $key = $computer->id . '.' . $column['key']; @endphp
                        <td>
                            <span class="checkbox {{ !empty($checked[$key]) ? 'checked' : '' }}"></span>
                        </td>
                    @endforeach
                </tr>
            @empty
                <tr>
                    <td colspan="{{ 2 + \App\Models\DeviceCheck::itemColumnCount() }}" style="padding: 14px;">Belum ada komputer di laboratorium ini</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="notes">
        <p><span class="label">Catatan:</span> {{ $check->notes ?: '-' }}</p>
        <p style="margin-top: 4px;">Centang berarti perangkat ada dan berfungsi. Sel kosong berarti perangkat bermasalah atau tidak ada.</p>
    </div>

    <div class="signature">
        <div class="signature-block">
            <p>Yogyakarta, {{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}</p>
            <p>Penanggung jawab</p>
            <p class="line">{{ $check->officer_name ?: 'Firmansyah, S.Kom' }}</p>
        </div>
    </div>
</body>
</html>
