<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Rincian Spesifikasi Komputer - SimKom - UPT Lab Terpadu</title>
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
    <style>html { scroll-behavior: smooth; }</style>
</head>
<body class="bg-gray-50 font-sans antialiased">

    {{-- Navbar --}}
    @include('layouts.public-navbar')

    {{-- Header --}}
    <header class="bg-gradient-to-br from-green-600 via-emerald-600 to-teal-600 text-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 sm:py-14">
            <h1 class="text-2xl sm:text-3xl lg:text-4xl font-black leading-tight mb-3">
                Rincian Spesifikasi Komputer
            </h1>
            <p class="text-sm sm:text-base text-green-50">
                Daftar hardware yang terpasang pada tiap komputer per laboratorium. Diperbarui otomatis dari data inventaris sistem.
            </p>
        </div>
    </header>

    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

        @php($withComputers = $laboratories->filter(fn ($lab) => $lab->computers->isNotEmpty())->values())

        @if($withComputers->isEmpty())
            <div class="bg-white rounded-2xl border border-gray-200 px-6 py-16 text-center">
                <svg class="w-16 h-16 text-gray-300 mx-auto mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9.75 17.25v-.001m0 0a3.75 3.75 0 01-3.75-3.75h7.5a3.75 3.75 0 01-3.75 3.75zm0 0h.001M17.25 17.25v.001m0 0a3.75 3.75 0 003.75-3.75h-7.5a3.75 3.75 0 003.75 3.75zM6 21a9 9 0 1018 0 9 9 0 00-18 0z" />
                </svg>
                <p class="text-gray-500 text-lg font-medium">Belum ada data spesifikasi komputer</p>
                <p class="text-gray-400 text-sm mt-1">Data akan muncul setelah komputer terdaftar di sistem.</p>
            </div>
        @else
            {{-- Navigasi cepat per laboratorium --}}
            <div class="flex flex-wrap gap-2 mb-8">
                @foreach($withComputers as $lab)
                    <a href="#lab-{{ $lab->id }}"
                        class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-white border border-gray-200 rounded-xl text-xs sm:text-sm font-medium text-gray-600 hover:border-green-400 hover:text-green-700 transition-colors">
                        {{ $lab->name }}
                        <span class="text-[11px] font-semibold text-gray-400">{{ $lab->computers->count() }}</span>
                    </a>
                @endforeach
            </div>

            @foreach($withComputers as $lab)
                <section id="lab-{{ $lab->id }}" class="mb-12 scroll-mt-24">
                    <div class="flex flex-wrap items-center justify-between gap-2 mb-4">
                        <div>
                            <h2 class="text-lg sm:text-xl font-bold text-gray-900">
                                {{ $lab->name }}
                                @if($lab->code)
                                    <span class="ml-1 text-sm font-medium text-gray-400">({{ $lab->code }})</span>
                                @endif
                            </h2>
                            <p class="text-xs text-gray-500 mt-0.5">{{ $lab->computers->count() }} komputer terdata</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-4">
                        @foreach($lab->computers as $computer)
                            <div class="bg-white rounded-2xl border border-gray-200 p-5 flex flex-col">
                                <h3 class="text-base font-bold text-gray-900 mb-3 pb-3 border-b border-gray-100">
                                    {{ $computer->code }}
                                </h3>

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
                </section>
            @endforeach
        @endif
    </main>

    {{-- Footer --}}
    <footer class="mt-4 bg-white border-t border-gray-200 py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col sm:flex-row items-center justify-between gap-3">
            <p class="text-sm text-gray-500">&copy; {{ date('Y') }} SimKom - Sistem Manajemen Komputer</p>
            <div class="flex items-center gap-4">
                <a href="{{ route('daftar-komputer') }}" class="text-sm text-gray-500 hover:text-green-600">Daftar Komputer</a>
                <a href="{{ route('track.index') }}" class="text-sm text-gray-500 hover:text-green-600">Lacak Laporan</a>
            </div>
        </div>
    </footer>
</body>
</html>
