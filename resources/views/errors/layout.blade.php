@php
    $code = $code ?? 500;
    $title = $title ?? 'Terjadi Kesalahan';
    $message = $message ?? 'Permintaan tidak dapat diproses. Silakan coba lagi.';
    $icon = $icon ?? '<path stroke-linecap="round" stroke-linejoin="round" d="M12 9v4m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z" />';
    $toneBg = $toneBg ?? 'bg-amber-50';
    $toneText = $toneText ?? 'text-amber-500';
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <title>{{ $code }} - SimKom - UPT Lab Terpadu</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800" rel="stylesheet" />
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @else
        <script src="https://cdn.tailwindcss.com"></script>
    @endif
    <style>
        body { font-family: 'Inter', ui-sans-serif, system-ui, sans-serif; }
        @keyframes pop-in {
            0% { opacity: 0; transform: scale(.7); }
            70% { transform: scale(1.06); }
            100% { opacity: 1; transform: scale(1); }
        }
        @keyframes rise-in {
            from { opacity: 0; transform: translateY(12px); }
            to { opacity: 1; transform: translateY(0); }
        }
        @keyframes drift {
            0%, 100% { transform: translate(0, 0) scale(1); }
            50% { transform: translate(24px, -18px) scale(1.06); }
        }
        .anim-pop { animation: pop-in .55s cubic-bezier(.21, 1.02, .73, 1.24) both; }
        .anim-rise { animation: rise-in .5s ease-out both; }
        .anim-rise-2 { animation: rise-in .5s ease-out .1s both; }
        .anim-rise-3 { animation: rise-in .5s ease-out .2s both; }
        .anim-drift { animation: drift 14s ease-in-out infinite; }
        .anim-drift-slow { animation: drift 20s ease-in-out infinite reverse; }
    </style>
</head>
<body class="bg-gray-50 min-h-screen relative overflow-hidden antialiased">
    {{-- Latar dekoratif --}}
    <div class="pointer-events-none absolute -top-32 -left-32 w-96 h-96 bg-green-200/60 rounded-full blur-3xl anim-drift"></div>
    <div class="pointer-events-none absolute -bottom-40 -right-24 w-[28rem] h-[28rem] bg-emerald-100/80 rounded-full blur-3xl anim-drift-slow"></div>
    <div class="pointer-events-none absolute top-1/3 right-1/4 w-64 h-64 bg-green-100/70 rounded-full blur-3xl anim-drift"></div>

    <div class="relative z-10 min-h-screen flex flex-col items-center justify-center px-4 py-12">
        {{-- Logo --}}
        <div class="text-center mb-8 anim-rise">
            <div class="inline-flex items-center justify-center w-14 h-14 bg-green-600 rounded-2xl mb-3 shadow-lg shadow-green-600/20">
                <svg class="w-8 h-8 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9.75 3.104v5.714a2.25 2.25 0 01-.659 1.591L5 14.5M9.75 3.104c-.251.023-.501.05-.75.082m.75-.082a24.301 24.301 0 014.5 0m0 0v5.714a2.25 2.25 0 00.659 1.591L19 14.5m-4.25-11.396c.251.023.501.05.75.082M12 21a8.966 8.966 0 005.982-2.275M12 21a8.966 8.966 0 01-5.982-2.275M15.75 3.186a24.284 24.284 0 012.038.443M8.25 3.186a24.284 24.284 0 00-2.038.443M18 14.5l-6 6-6-6" />
                </svg>
            </div>
            <h1 class="text-xl font-bold text-gray-800">Sim<span class="text-green-600">Kom</span></h1>
            <p class="text-xs text-gray-500">Sistem Manajemen Komputer</p>
        </div>

        {{-- Kartu error --}}
        <div class="w-full max-w-md bg-white rounded-2xl shadow-sm border border-gray-200 p-8 sm:p-10 text-center">
            {{-- Ilustrasi --}}
            <div class="anim-pop mx-auto mb-6">
                <div class="inline-flex items-center justify-center w-24 h-24 rounded-full {{ $toneBg }}">
                    <svg class="w-12 h-12 {{ $toneText }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        {!! $icon !!}
                    </svg>
                </div>
            </div>

            {{-- Kode status --}}
            <div class="anim-rise-2 text-6xl sm:text-7xl font-extrabold tracking-tight leading-none bg-gradient-to-r from-green-600 to-emerald-400 bg-clip-text text-transparent">
                {{ $code }}
            </div>

            <h2 class="anim-rise-2 mt-4 text-xl font-bold text-gray-800">{{ $title }}</h2>
            <p class="anim-rise-3 mt-2 text-sm text-gray-500 leading-relaxed">{!! $message !!}</p>

            <div class="anim-rise-3 mt-8 flex flex-col sm:flex-row gap-3 justify-center">
                <button type="button" onclick="history.length > 1 ? history.back() : window.location.href = '{{ url('/') }}'"
                    class="inline-flex items-center justify-center gap-2 px-5 py-2.5 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition-colors">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
                    </svg>
                    Kembali
                </button>
                <a href="{{ url('/') }}"
                    class="inline-flex items-center justify-center gap-2 px-5 py-2.5 border border-gray-300 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition-colors">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l9-9 9 9M5 10v10a1 1 0 001 1h3m10-11v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                    </svg>
                    Ke Beranda
                </a>
            </div>
        </div>

        <p class="mt-8 text-xs text-gray-400 anim-rise-3">
            © {{ date('Y') }} SimKom — UPT Lab Terpadu
        </p>
    </div>
</body>
</html>
