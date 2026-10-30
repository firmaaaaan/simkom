<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Spesifikasi {{ $laboratory->name }} - SimKom - UPT Lab Terpadu</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
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
<body class="bg-gray-50 font-sans antialiased min-h-screen flex flex-col">

    {{-- Header: identitas laboratorium --}}
    <header class="bg-gradient-to-br from-green-600 via-emerald-600 to-teal-600 text-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-10">
            <p class="text-xs font-semibold uppercase tracking-widest text-green-100 mb-2">
                Rincian Spesifikasi Komputer
            </p>
            <div class="flex flex-wrap items-center gap-x-3 gap-y-2">
                <h1 class="text-2xl sm:text-3xl font-black leading-tight">{{ $laboratory->name }}</h1>
                @if($laboratory->code)
                    <span class="inline-flex items-center px-2.5 py-1 rounded-full bg-white/15 border border-white/30 text-xs font-semibold">
                        {{ $laboratory->code }}
                    </span>
                @endif
            </div>
            <p class="text-sm text-green-50 mt-2">
                {{ $laboratory->computers->count() }} komputer terdata &middot; diperbarui otomatis dari data inventaris sistem.
            </p>
        </div>
    </header>

    <main class="flex-1 max-w-7xl mx-auto w-full px-4 sm:px-6 lg:px-8 py-8">
        @if($laboratory->computers->isEmpty())
            <div class="bg-white rounded-2xl border border-gray-200 px-6 py-16 text-center">
                <svg class="w-16 h-16 text-gray-300 mx-auto mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9.75 17.25v-.001m0 0a3.75 3.75 0 01-3.75-3.75h7.5a3.75 3.75 0 01-3.75 3.75zm0 0h.001M17.25 17.25v.001m0 0a3.75 3.75 0 003.75-3.75h-7.5a3.75 3.75 0 003.75 3.75zM6 21a9 9 0 1018 0 9 9 0 00-18 0z" />
                </svg>
                <p class="text-gray-500 text-lg font-medium">Belum ada komputer di laboratorium ini</p>
                <p class="text-gray-400 text-sm mt-1">Data spesifikasi akan muncul setelah komputer terdaftar di sistem.</p>
            </div>
        @else
            <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-4">
                @foreach($laboratory->computers as $computer)
                    <div class="bg-white rounded-2xl border border-gray-200 p-5 flex flex-col">
                        <div class="flex items-center justify-between gap-3 mb-3 pb-3 border-b border-gray-100">
                            <h3 class="text-base font-bold text-gray-900">{{ $computer->code }}</h3>
                            <div class="text-right shrink-0">
                                <p class="text-[10px] text-gray-400 uppercase tracking-wide">Kode Inventaris</p>
                                <p class="text-xs font-mono text-gray-700">{{ $computer->barcode ?? '-' }}</p>
                            </div>
                        </div>

                        @forelse($computer->hardwareGroupedByCategory() as $group)
                            <div class="mb-3 last:mb-0">
                                <p class="text-[11px] font-semibold text-gray-400 uppercase tracking-wide mb-1">{{ $group['label'] }}</p>
                                <ul class="space-y-0.5">
                                    @foreach($group['items'] as $hw)
                                        <li class="text-sm text-gray-700">
                                            {{ $hw->name }}
                                            @if($hw->brand || $hw->model)
                                                <span class="text-xs text-gray-400">({{ trim(($hw->brand ?: '').' '.($hw->model ?: '')) }})</span>
                                            @endif
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        @empty
                            <p class="text-sm text-gray-400 italic">Belum ada hardware tercatat</p>
                        @endforelse
                    </div>
                @endforeach
            </div>
        @endif
    </main>

    {{-- Footer --}}
    <footer class="bg-white border-t border-gray-200 py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col sm:flex-row items-center justify-between gap-3">
            <p class="text-sm text-gray-500">&copy; {{ date('Y') }} SimKom - Sistem Manajemen Komputer</p>
            <div class="flex items-center gap-4">
                <a href="{{ url('/') }}" class="text-sm text-gray-500 hover:text-green-600">Beranda</a>
                <a href="{{ route('daftar-komputer') }}" class="text-sm text-gray-500 hover:text-green-600">Daftar Komputer</a>
            </div>
        </div>
    </footer>
</body>
</html>
