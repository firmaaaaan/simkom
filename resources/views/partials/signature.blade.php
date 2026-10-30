{{--
    Blok isi tanda tangan laporan (tanggal, jabatan, gambar TTD, nama).
    Setiap laporan memakai TTD yang ditugaskan; jika tidak ada, memakai TTD default.

    @include('partials.signature', [
        'reportKey'   => 'laporan',              // wajib: Signature::REPORTS key
        'defaultRole' => 'Penanggung jawab',     // wajib: jabatan default
        'defaultName' => 'Firmansyah, S.Kom',    // wajib: nama default
        'variant'     => 'block',                // block | line | print-file | inline
        'officerName' => $check->officer_name,   // opsional: fallback nama (blok per-lab)
    ])
--}}
@php
    $sig = \App\Models\Signature::forReport($reportKey);
    $img = $sig?->imageSrc();
    $role = $sig?->role ?: $defaultRole;
    $name = $sig?->name ?: (($officerName ?? '') !== '' ? $officerName : $defaultName);
    $city = 'Yogyakarta, ' . \Carbon\Carbon::now()->translatedFormat('d F Y');
    $variant = $variant ?? 'block';
    $imgTag = '<img src="'.$img.'" alt="Tanda tangan" style="height: 42px; max-width: 170px; object-fit: contain; vertical-align: middle;">';
    $signatureArea = 'margin-top: 6px; height: 50px; line-height: 50px; text-align: center;';
    $nameLine = 'margin-top: 6px; text-align: center;';
@endphp

@switch($variant)
    @case('inline')
        {{-- Sel tabel (Laporan Inventaris): memakai <div> & <strong> --}}
        <div>{{ $city }}</div>
        <div style="margin-top: 5px; text-align: center;">{{ $role }}</div>
        <div style="{{ $signatureArea }}">{!! $img ? $imgTag : '' !!}</div>
        <div style="{{ $nameLine }}"><strong>{{ $name }}</strong></div>
        @break

    @case('line')
        {{-- signature-block tanpa garis, area TTD bertinggi tetap --}}
        <p>{{ $city }}</p>
        <p style="text-align: center;">{{ $role }}</p>
        <div style="{{ $signatureArea }}">{!! $img ? $imgTag : '' !!}</div>
        <p style="{{ $nameLine }}">{{ $name }}</p>
        @break

    @case('print-file')
        {{-- signature-block DomPDF tanpa garis, area TTD bertinggi tetap --}}
        <p>{{ $city }}</p>
        <p style="text-align: center;">{{ $role }}</p>
        <div style="{{ $signatureArea }}">{!! $img ? $imgTag : '' !!}</div>
        <p style="{{ $nameLine }}">{{ $name }}</p>
        @break

    @default
        {{-- block: signature-block tanpa garis, area TTD bertinggi tetap --}}
        <p>{{ $city }}</p>
        <p style="text-align: center;">{{ $role }}</p>
        <div style="{{ $signatureArea }}">{!! $img ? $imgTag : '' !!}</div>
        <p style="{{ $nameLine }} padding-top: 4px;">{{ $name }}</p>
@endswitch
