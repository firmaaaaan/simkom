<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <title>Kartu Kendali {{ $computer->code }} - {{ config('app.name', 'SimKom') }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700" rel="stylesheet" />
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'ui-sans-serif', 'system-ui', 'sans-serif'],
                    },
                }
            }
        }
    </script>
</head>
<body class="bg-gray-50 font-sans antialiased">

    {{-- Navbar --}}
    <nav class="bg-white border-b border-gray-200 sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <a href="{{ url('/') }}" class="flex items-center gap-3">
                    <div class="w-9 h-9 bg-gradient-to-br from-green-500 to-emerald-600 rounded-xl flex items-center justify-center shadow-lg shadow-green-200">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 3.104v5.714a2.25 2.25 0 01-.659 1.591L5 14.5M9.75 3.104c-.251.023-.501.05-.75.082m.75-.082a24.301 24.301 0 014.5 0m0 0v5.714a2.25 2.25 0 00.659 1.591L19 14.5m-4.25-11.396c.251.023.501.05.75.082M12 21a8.966 8.966 0 005.982-2.275M12 21a8.966 8.966 0 01-5.982-2.275M15.75 3.186a24.284 24.284 0 012.038.443M8.25 3.186a24.284 24.284 0 00-2.038.443M18 14.5l-6 6-6-6" />
                        </svg>
                    </div>
                    <span class="text-xl font-extrabold text-gray-800">Sim<span class="text-green-600">Lab</span></span>
                </a>
                <div class="flex items-center gap-2 sm:gap-3">
                    <a href="{{ route('track.index') }}" class="inline-flex items-center gap-2 px-3 sm:px-4 py-2 text-sm font-medium text-gray-600 hover:text-green-600 transition-colors">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                        </svg>
                        <span class="hidden sm:inline">Lacak Laporan</span>
                    </a>
                    <a href="{{ url('/') }}" class="inline-flex items-center px-4 py-2 text-sm font-semibold bg-green-600 text-white rounded-xl hover:bg-green-700 transition-colors shadow-sm">
                        Beranda
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

        {{-- Info Pemakaian --}}
        <div class="bg-green-50 border border-green-200 text-green-800 rounded-xl px-4 py-3 text-sm mb-6 flex items-start gap-2">
            <svg class="w-5 h-5 text-green-600 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z" />
            </svg>
            <p>Halaman ini menampilkan riwayat pengecekan komputer. Untuk melaporkan kendala atau mengajukan peminjaman, silakan gunakan tombol pada halaman beranda.</p>
        </div>

        {{-- Header Kartu --}}
        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden mb-6">
            <div class="bg-green-600 text-white px-6 py-4">
                <div class="flex items-center justify-between gap-4">
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 bg-white/20 rounded-lg flex items-center justify-center flex-shrink-0">
                            <svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 17.25v1.007a3 3 0 01-.879 2.122L7.5 21h9l-.621-.621A3 3 0 0115 18.257V17.25m6-12V15a2.25 2.25 0 01-2.25 2.25H5.25A2.25 2.25 0 013 15V5.25m18 0A2.25 2.25 0 0018.75 3H5.25A2.25 2.25 0 003 5.25m18 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 7.41A2.25 2.25 0 012.25 5.496V5.25" />
                            </svg>
                        </div>
                        <div>
                            <h1 class="text-xl font-bold">KARTU KENDALI KOMPUTER</h1>
                            <p class="text-green-100 text-sm">{{ $computer->laboratory?->name ?? 'Laboratorium' }}</p>
                        </div>
                    </div>
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-bold flex-shrink-0
                        {{ $computer->status === 'Aktif' ? 'bg-white text-green-700' : ($computer->status === 'Maintenance' ? 'bg-yellow-400 text-yellow-900' : 'bg-gray-300 text-gray-700') }}">
                        {{ $computer->status }}
                    </span>
                </div>
            </div>

            <div class="p-6 grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="bg-gray-50 rounded-lg p-3">
                    <p class="text-xs text-gray-400 mb-1">Kode Komputer</p>
                    <p class="text-lg font-bold text-green-700">{{ $computer->code }}</p>
                </div>
                <div class="bg-gray-50 rounded-lg p-3">
                    <p class="text-xs text-gray-400 mb-1">Lokasi</p>
                    <p class="text-sm font-semibold text-gray-800">{{ $computer->laboratory?->name ?? '-' }}</p>
                </div>
            </div>
        </div>

        {{-- Riwayat Pengecekan (tanpa nama item & catatan per item) --}}
        <div class="bg-white rounded-xl border border-gray-200">
            <div class="px-6 py-4 border-b border-gray-100">
                <h2 class="text-base font-semibold text-gray-800">Riwayat Pengecekan</h2>
                <p class="text-xs text-gray-500 mt-1">Riwayat pengecekan komputer ini, bisa disaring per bulan.</p>
            </div>

            @include('computers.partials.riwayat-pengecekan', ['checks' => $checks, 'showItemNames' => false, 'years' => $years])
        </div>
    </div>

    {{-- Footer --}}
    <footer class="mt-12 bg-white border-t border-gray-200 py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col sm:flex-row items-center justify-between gap-3">
            <p class="text-sm text-gray-500">&copy; {{ date('Y') }} SimKom - Sistem Manajemen Komputer</p>
            <div class="flex items-center gap-4">
                <a href="{{ route('track.index') }}" class="text-sm text-gray-500 hover:text-green-600 transition-colors">Lacak Laporan</a>
            </div>
        </div>
    </footer>

</body>
</html>
