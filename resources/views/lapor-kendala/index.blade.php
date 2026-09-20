<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Lapor Kendala - {{ config('app.name', 'SimKom') }}</title>
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
    <style>
        html { scroll-behavior: smooth; }
    </style>
</head>
<body class="bg-gray-50 font-sans antialiased">

    {{-- Navbar --}}
    <nav class="bg-white border-b border-gray-200 sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 bg-gradient-to-br from-green-500 to-emerald-600 rounded-xl flex items-center justify-center shadow-lg shadow-green-200">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 3.104v5.714a2.25 2.25 0 01-.659 1.591L5 14.5M9.75 3.104c-.251.023-.501.05-.75.082m.75-.082a24.301 24.301 0 014.5 0m0 0v5.714a2.25 2.25 0 00.659 1.591L19 14.5m-4.25-11.396c.251.023.501.05.75.082M12 21a8.966 8.966 0 005.982-2.275M12 21a8.966 8.966 0 01-5.982-2.275M15.75 3.186a24.284 24.284 0 012.038.443M8.25 3.186a24.284 24.284 0 00-2.038.443M18 14.5l-6 6-6-6" />
                        </svg>
                    </div>
                    <span class="text-xl font-extrabold text-gray-800">Sim<span class="text-green-600">Lab</span></span>
                </div>
                <div class="flex items-center gap-3">
                    <a href="{{ route('track.index') }}" class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-gray-600 hover:text-green-600 transition-colors">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                        </svg>
                        Lacak Laporan
                    </a>
                    <a href="{{ url('/') }}" class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-gray-600 hover:text-green-600 transition-colors">
                        Beranda
                    </a>
                </div>
            </div>
        </div>
    </nav>

    {{-- Content --}}
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

        {{-- Header --}}
        <div class="mb-8">
            <div class="inline-flex items-center gap-2 px-4 py-2 bg-orange-50 border border-orange-200 rounded-full mb-4">
                <span class="w-2 h-2 bg-orange-500 rounded-full animate-pulse"></span>
                <span class="text-sm font-medium text-orange-700">Lapor Kendala Praktikum</span>
            </div>
            <h1 class="text-3xl font-black text-gray-900 mb-2">Pilih Komputer yang Bermasalah</h1>
            <p class="text-gray-600">Pilih laboratorium terlebih dahulu, lalu klik komputer yang ingin dilaporkan kendalanya.</p>
        </div>

        {{-- Filter Lab --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6 mb-8">
            <form action="{{ route('lapor-kendala.index') }}" method="GET" class="flex flex-col sm:flex-row items-start sm:items-end gap-4">
                <div class="flex-1 w-full">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Filter Laboratorium</label>
                    <select name="laboratory_id" id="labFilter" onchange="this.form.submit()"
                        class="w-full px-4 py-3 text-sm border border-gray-200 rounded-xl focus:ring-2 focus:ring-green-500 focus:border-green-500 bg-gray-50">
                        <option value="">Semua Laboratorium</option>
                        @foreach($laboratories as $lab)
                            <option value="{{ $lab->id }}" {{ request('laboratory_id') == $lab->id ? 'selected' : '' }}>
                                {{ $lab->name }} ({{ $lab->code }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="text-sm text-gray-500">
                    <span class="font-semibold text-gray-800">{{ $computers->count() }}</span> komputer ditemukan
                </div>
            </form>
        </div>

        {{-- Selected Lab Info --}}
        @if($selectedLab)
            <div class="mb-6 flex items-center gap-3">
                <span class="text-sm text-gray-500">Menampilkan komputer di:</span>
                <span class="inline-flex items-center px-3 py-1 bg-green-100 text-green-800 text-sm font-semibold rounded-full">
                    {{ $selectedLab->name }}
                    <a href="{{ route('lapor-kendala.index') }}" class="ml-2 text-green-600 hover:text-green-800">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </a>
                </span>
            </div>
        @endif

        {{-- Computer Cards --}}
        @if($computers->count() > 0)
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                @foreach($computers as $computer)
                    <a href="{{ route('lapor-kendala.create', $computer) }}"
                       class="group bg-white rounded-xl border border-gray-200 p-5 hover:shadow-lg hover:border-green-300 hover:-translate-y-1 transition-all duration-200">
                        <div class="flex items-start justify-between mb-4">
                            <div class="w-12 h-12 rounded-xl bg-green-100 flex items-center justify-center group-hover:bg-green-200 transition-colors">
                                <svg class="w-6 h-6 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 17.25v1.007a3 3 0 01-.879 2.122L7.5 21h9l-.621-.621A3 3 0 0115 18.257V17.25m6-12V15a2.25 2.25 0 01-2.25 2.25H5.25A2.25 2.25 0 013 15V5.25m18 0A2.25 2.25 0 0018.75 3H5.25A2.25 2.25 0 003 5.25m18 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 7.41A2.25 2.25 0 012.25 5.496V5.25" />
                                </svg>
                            </div>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                {{ $computer->status === 'Aktif' ? 'bg-green-100 text-green-800' : ($computer->status === 'Maintenance' ? 'bg-yellow-100 text-yellow-800' : 'bg-gray-100 text-gray-600') }}">
                                {{ $computer->status }}
                            </span>
                        </div>
                        <h3 class="text-lg font-bold text-gray-900 mb-1 group-hover:text-green-600 transition-colors">{{ $computer->code }}</h3>
                        <p class="text-sm text-gray-500">{{ $computer->laboratory?->name ?? '-' }}</p>
                        <div class="mt-4 pt-3 border-t border-gray-100 flex items-center gap-2 text-sm text-green-600 font-medium opacity-0 group-hover:opacity-100 transition-opacity">
                            Laporkan Kendala
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
                            </svg>
                        </div>
                    </a>
                @endforeach
            </div>
        @else
            <div class="bg-white rounded-2xl border border-gray-200 px-6 py-16 text-center">
                <svg class="w-16 h-16 text-gray-300 mx-auto mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 17.25v1.007a3 3 0 01-.879 2.122L7.5 21h9l-.621-.621A3 3 0 0115 18.257V17.25m6-12V15a2.25 2.25 0 01-2.25 2.25H5.25A2.25 2.25 0 013 15V5.25m18 0A2.25 2.25 0 0018.75 3H5.25A2.25 2.25 0 003 5.25m18 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 7.41A2.25 2.25 0 012.25 5.496V5.25" />
                </svg>
                <p class="text-gray-500 text-lg font-medium">Tidak ada komputer ditemukan</p>
                <p class="text-gray-400 text-sm mt-1">Pilih laboratorium lain atau lihat semua komputer</p>
                <a href="{{ route('lapor-kendala.index') }}" class="inline-flex items-center gap-2 mt-4 px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700 transition-colors">
                    Lihat Semua Komputer
                </a>
            </div>
        @endif
    </div>

    {{-- Footer --}}
    <footer class="mt-12 bg-white border-t border-gray-200 py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <p class="text-sm text-gray-500">&copy; {{ date('Y') }} SimKom - Sistem Manajemen Laboratorium</p>
        </div>
    </footer>

</body>
</html>
