{{--
    Blok isi tanda tangan laporan (tanggal, jabatan, gambar TTD, nama).
    Setiap laporan memakai TTD yang ditugaskan; jika tidak ada, memakai TTD default.

    @include('partials.signature', [
        'reportKey'   => 'laporan',              // wajib: Signature::REPORTS key
        'defaultRole' => 'Penanggung jawab',     // wajib: jabatan default
        'defaultName' => 'Firmansyah, S.Kom',    // wajib: nama default
        'variant'     => 'block',                // block | line | print-file | inline
        'officerName' => $check->officer_name,   // opsional: fallback nama (blok per-lab)
        'nameStyle'   => 'width: 180px;',        // opsional: style tambahan utk nama (block, hanya tanpa gambar)
    ])
--}}
@php
    $sig = \App\Models\Signature::forReport($reportKey);
    $img = $sig?->imageSrc();
    $role = $sig?->role ?: $defaultRole;
    $name = $sig?->name ?: (($officerName ?? '') !== '' ? $officerName : $defaultName);
    $city = 'Yogyakarta, ' . \Carbon\Carbon::now()->translatedFormat('d F Y');
    $variant = $variant ?? 'block';
    $imgTag = '<img src="'.$img.'" alt="Tanda tangan" style="height: 42px; max-width: 170px; object-fit: contain;">';
@endphp

@switch($variant)
    @case('inline')
        {{-- Sel tabel (Laporan Inventaris): memakai <div> & <strong> --}}
        <div>{{ $city }}</div>
        <div style="margin-top: 5px;">{{ $role }}</div>
        @if($img)
            <div style="margin-top: 8px; text-align: center;">{!! $imgTag !!}</div>
            <div style="margin-top: 6px;"><strong>{{ $name }}</strong></div>
        @else
            <div style="margin-top: 50px;"><strong>{{ $name }}</strong></div>
        @endif
        @break

    @case('line')
        {{-- signature-block tanpa garis, jeda 50px utk tanda tangan basah --}}
        <p>{{ $city }}</p>
        <p>{{ $role }}</p>
        @if($img)
            <p style="text-align: center; margin-top: 8px;">{!! $imgTag !!}</p>
            <p style="margin-top: 6px; text-align: center;">{{ $name }}</p>
        @else
            <p style="margin-top: 50px;">{{ $name }}</p>
        @endif
        @break

    @case('print-file')
        {{-- signature-block DomPDF tanpa garis, jeda 50px utk tanda tangan basah --}}
        <p>{{ $city }}</p>
        <p>{{ $role }}</p>
        @if($img)
            <p style="text-align: center; margin-top: 8px;">{!! $imgTag !!}</p>
            <p style="margin-top: 6px; text-align: center;">{{ $name }}</p>
        @else
            <p style="margin-top: 50px;">{{ $name }}</p>
        @endif
        @break

    @default
        {{-- block: signature-block tanpa garis, jeda 50px utk tanda tangan basah --}}
        <p>{{ $city }}</p>
        <p>{{ $role }}</p>
        @if($img)
            {{-- nama ikut di-center agar TTD tepat di atas tengah nama (nameStyle dilepas agar tidak offset) --}}
            <p style="text-align: center; margin-top: 8px;">{!! $imgTag !!}</p>
            <p style="margin-top: 6px; padding-top: 4px; text-align: center;">{{ $name }}</p>
        @else
            <p style="margin-top: 50px; padding-top: 4px; {{ $nameStyle ?? '' }}">{{ $name }}</p>
        @endif
@endswitch
