{{-- Kop cetak: logo UNISA di kiri, judul + baris teks di kanan.
     Parameter: $kopTitle (string wajib), $kopLines (array opsional baris h2). --}}
<div style="display: flex; align-items: center; gap: 14px; margin-bottom: 14px;">
    <img src="{{ asset('kop/cropped-logo-unisa-crop.png') }}" alt="Logo Universitas 'Aisyiyah Yogyakarta"
        style="width: 64px; height: 64px; object-fit: contain; flex-shrink: 0;">
    <div>
        <h1 style="font-size: 13px; font-weight: bold; text-transform: uppercase; margin-bottom: 2px;">{{ $kopTitle }}</h1>
        @foreach($kopLines ?? [] as $line)
            <h2 style="font-size: 12px; font-weight: bold; text-transform: uppercase; margin-bottom: 1px;">{{ $line }}</h2>
        @endforeach
    </div>
</div>
