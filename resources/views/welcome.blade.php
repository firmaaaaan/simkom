<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Welcome - SimKom - UPT Lab Terpadu</title>
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
    <style>
        html { scroll-behavior: smooth; }
        [x-cloak] { display: none !important; }
        .computer-card { transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); }
        .computer-card:hover { transform: translateY(-4px) scale(1.02); box-shadow: 0 20px 40px -12px rgba(0,0,0,0.15); }
        .computer-card:active { transform: translateY(-1px) scale(0.98); }
        .legend-pill { transition: all 0.2s ease; }
        .legend-pill:hover { transform: scale(1.05); }
    </style>
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
                    <span class="text-xl font-extrabold text-gray-800">Sim<span class="text-green-600">Kom</span></span>
                </a>
                <div class="flex items-center gap-2 sm:gap-3">
                    <a href="{{ route('jadwal-lab.index') }}" class="inline-flex items-center gap-2 px-3 sm:px-4 py-2 text-sm font-medium text-gray-600 hover:text-green-600 transition-colors">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" />
                        </svg>
                        <span class="hidden sm:inline">Jadwal Lab</span>
                    </a>
                    <a href="{{ route('track.index') }}" class="inline-flex items-center gap-2 px-3 sm:px-4 py-2 text-sm font-medium text-gray-600 hover:text-green-600 transition-colors">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                        </svg>
                        <span class="hidden sm:inline">Lacak Laporan</span>
                    </a>
                </div>
            </div>
        </div>
    </nav>

    {{-- Hero --}}
    <header class="bg-gradient-to-br from-green-600 via-emerald-600 to-teal-600 text-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-14 sm:py-20">
            <div class="max-w-2xl">
                <h1 class="text-3xl sm:text-4xl lg:text-5xl font-black leading-tight mb-4">
                    Lapor Kendala &amp; Pinjam Komputer Laboratorium
                </h1>
                <p class="text-base sm:text-lg text-green-50 mb-8">
                    Pilih laboratorium, temukan komputer yang Anda gunakan, lalu laporkan kendala atau ajukan peminjaman tanpa perlu login.
                </p>
                <div class="flex flex-wrap items-center gap-3">
                    <a href="#denah" class="inline-flex items-center gap-2 px-5 py-3 bg-white text-green-700 text-sm font-semibold rounded-xl hover:bg-green-50 transition-colors shadow-lg">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6z" />
                        </svg>
                        Lihat Denah Lab
                    </a>
                    <a href="{{ route('jadwal-lab.index') }}" class="inline-flex items-center gap-2 px-5 py-3 bg-white/15 border border-white/30 text-white text-sm font-semibold rounded-xl hover:bg-white/25 transition-colors">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" />
                        </svg>
                        Jadwal Lab
                    </a>
                    <a href="{{ route('track.index') }}" class="inline-flex items-center gap-2 px-5 py-3 bg-white/15 border border-white/30 text-white text-sm font-semibold rounded-xl hover:bg-white/25 transition-colors">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                        </svg>
                        Lacak Laporan
                    </a>
                </div>
            </div>
        </div>
    </header>

    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

        {{-- Filter Laboratorium --}}
        <section id="denah" class="mb-8">
            <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4 mb-5">
                <div>
                    <h2 class="text-2xl font-black text-gray-900">Denah Komputer</h2>
                    <p class="text-sm text-gray-500 mt-1">Pilih laboratorium untuk melihat daftar komputernya.</p>
                </div>

                @if($selectedLab)
                    <div class="text-sm text-gray-500">
                        <span class="font-semibold text-gray-800">{{ $computers->count() }}</span> komputer di
                        <span class="font-semibold text-gray-800">{{ $selectedLab->name }}</span>
                    </div>
                @endif
            </div>

            @if($laboratories->count() > 0)
                <div class="flex flex-wrap gap-2 mb-6">
                    @foreach($laboratories as $lab)
                        <a href="{{ url('/?laboratory_id=' . $lab->id) }}"
                           class="legend-pill inline-flex items-center gap-2 px-4 py-2 text-sm font-medium rounded-xl border transition-colors
                               {{ $selectedLab && $selectedLab->id === $lab->id
                                    ? 'bg-green-600 border-green-600 text-white shadow-sm'
                                    : 'bg-white border-gray-200 text-gray-600 hover:border-green-300 hover:text-green-600' }}">
                            {{ $lab->name }}
                            @if($lab->code)
                                <span class="text-xs {{ $selectedLab && $selectedLab->id === $lab->id ? 'text-green-100' : 'text-gray-400' }}">{{ $lab->code }}</span>
                            @endif
                        </a>
                    @endforeach
                </div>
            @endif

            {{-- Keterangan Status --}}
            <div class="flex flex-wrap items-center gap-3 mb-6 text-xs">
                <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-green-100 text-green-800 rounded-full font-medium">
                    <span class="w-2 h-2 bg-green-500 rounded-full"></span> Aktif
                </span>
                <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-yellow-100 text-yellow-800 rounded-full font-medium">
                    <span class="w-2 h-2 bg-yellow-500 rounded-full"></span> Maintenance
                </span>
                <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-gray-100 text-gray-600 rounded-full font-medium">
                    <span class="w-2 h-2 bg-gray-400 rounded-full"></span> Tidak Aktif
                </span>
            </div>

            {{-- Lab Layout Canvas (Published) --}}
            @if($activeLayout)
                <div class="mb-6">
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
                        <div class="p-4 border-b border-gray-200 bg-gray-50 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                            <div class="flex items-center gap-3">
                                <h3 class="text-lg font-semibold text-gray-900">{{ $activeLayout->name }}</h3>
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">Published</span>
                            </div>
                            <div class="flex items-center gap-3 text-sm text-gray-600">
                                <span>Canvas: {{ $activeLayout->canvas_width }} × {{ $activeLayout->canvas_height }} px</span>
                                <label class="inline-flex items-center gap-2 cursor-pointer">
                                    <input type="checkbox" id="welcomeGridToggle" checked onchange="toggleWelcomeGrid(this.checked)" class="rounded border-gray-300 text-green-600 focus:ring-green-500">
                                    Grid
                                </label>
                            </div>
                        </div>
                        <div class="p-4 relative" style="background: {{ $activeLayout->background_color }};">
                            <svg id="welcomeLayoutSvg"
                                 :viewBox="'0 0 ' + canvasWidth + ' ' + canvasHeight"
                                 class="w-full h-auto border border-gray-200 rounded-lg"
                                 style="max-width: 100%; height: auto;"
                                 x-data="{
                                     canvasWidth: {{ $activeLayout->canvas_width }},
                                     canvasHeight: {{ $activeLayout->canvas_height }},
                                     cellSize: {{ $activeLayout->cell_size }},
                                     gridCols: {{ $activeLayout->grid_cols }},
                                     gridRows: {{ $activeLayout->grid_rows }},
                                     items: @json($activeLayout->items),
                                     showGrid: true,
                                     itemW(item) { return item.type === 'computer' ? 80 : 30; },
                                     itemH(item) { return item.type === 'computer' ? 60 : 80; },
                                     itemTransform(item) {
                                         const x = item.grid_x * this.cellSize;
                                         const y = item.grid_y * this.cellSize;
                                         const cx = this.itemW(item)/2, cy = this.itemH(item)/2;
                                         return `translate(${x},${y}) rotate(${item.rotation||0},${cx},${cy})`;
                                     },
                                     textTransform(item) {
                                         const rot = item.rotation || 0;
                                         if (rot === 0) return '';
                                         const cx = this.itemW(item)/2, cy = this.itemH(item)/2;
                                         return `rotate(${-rot},${cx},${cy})`;
                                     }
                                 }">
                                <defs>
                                    <pattern id="welcomeGridPattern" :width="cellSize" :height="cellSize" patternUnits="userSpaceOnUse">
                                        <path d="M {{ $activeLayout->cell_size }} 0 L 0 0 0 {{ $activeLayout->cell_size }}" fill="none" stroke="#e5e7eb" stroke-width="0.5"/>
                                    </pattern>
                                </defs>
                                <rect width="100%" height="100%" :fill="showGrid ? 'url(#welcomeGridPattern)' : 'none'" />
                                
                                <template x-for="item in items" :key="item.id">
                                    <g :transform="itemTransform(item)">
                                        <rect :width="itemW(item)" :height="itemH(item)"
                                              :class="item.type === 'computer' ? 'fill-blue-100 stroke-blue-500' : 'fill-gray-200 stroke-gray-500'"
                                              rx="4" stroke-width="2"/>
                                        <text x="50%" y="50%" dominant-baseline="middle" text-anchor="middle"
                                              class="text-xs font-medium pointer-events-none"
                                              :transform="textTransform(item)">
                                            @{{ item.label }}
                                        </text>
                                    </g>
                                </template>
                            </svg>
                        </div>
                    </div>
                </div>
            @endif

            {{-- Daftar Komputer --}}
            @if($computers->count() > 0)
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                    @foreach($computers as $computer)
                        <div class="computer-card bg-white rounded-2xl border border-gray-200 p-5 flex flex-col">
                            <div class="flex items-start justify-between mb-4">
                                <div class="w-12 h-12 rounded-xl bg-green-100 flex items-center justify-center">
                                    <svg class="w-6 h-6 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 17.25v1.007a3 3 0 01-.879 2.122L7.5 21h9l-.621-.621A3 3 0 0115 18.257V17.25m6-12V15a2.25 2.25 0 01-2.25 2.25H5.25A2.25 2.25 0 013 15V5.25m18 0A2.25 2.25 0 0018.75 3H5.25A2.25 2.25 0 003 5.25m18 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 7.41A2.25 2.25 0 012.25 5.496V5.25" />
                                    </svg>
                                </div>
                                <div class="flex flex-col items-end gap-1.5">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                        {{ $computer->status === 'Aktif' ? 'bg-green-100 text-green-800' : ($computer->status === 'Maintenance' ? 'bg-yellow-100 text-yellow-800' : 'bg-gray-100 text-gray-600') }}">
                                        {{ $computer->status }}
                                    </span>
                                    @if(($computer->tickets_count ?? 0) > 0)
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-700">
                                            <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
                                            </svg>
                                            {{ $computer->tickets_count }} tiket
                                        </span>
                                    @endif
                                </div>
                            </div>

                            <h3 class="text-lg font-bold text-gray-900">{{ $computer->code }}</h3>
                            <p class="text-sm text-gray-500 mb-4">{{ $computer->laboratory?->name ?? '-' }}</p>

                            <div class="mt-auto pt-4 border-t border-gray-100 space-y-2">
                                @if($showSpec)
                                <button type="button"
                                        data-spec-trigger
                                        data-spec-target="#spec-{{ $computer->id }}"
                                        data-spec-title="{{ $computer->code }}"
                                        data-spec-subtitle="{{ $computer->laboratory?->name ?? '-' }}"
                                        class="w-full inline-flex items-center justify-center gap-1.5 px-3 py-2 text-xs font-semibold text-gray-600 bg-gray-50 rounded-lg hover:bg-gray-100 hover:text-gray-800 transition-colors">
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z" />
                                    </svg>
                                    Lihat Spesifikasi
                                </button>
                                @endif

                                @if($computer->status !== 'Aktif' || ($computer->tickets_count ?? 0) > 0)
                                    <div class="space-y-2">
                                        @if($computer->status === 'Maintenance')
                                            <div class="flex items-center justify-center gap-1.5 px-3 py-2 text-xs font-semibold text-yellow-700 bg-yellow-50 rounded-lg">
                                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M11.412 15.822l-3.739 3.738a1.5 1.5 0 01-2.122 0l-1.2-1.2a1.5 1.5 0 010-2.122l3.738-3.739M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-6 0a3 3 0 11-6 0 3 3 0 016 0z" />
                                                </svg>
                                                Komputer dalam maintenance
                                            </div>
                                        @elseif($computer->status === 'Tidak Aktif')
                                            <div class="flex items-center justify-center gap-1.5 px-3 py-2 text-xs font-semibold text-gray-600 bg-gray-50 rounded-lg">
                                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
                                                </svg>
                                                Komputer tidak aktif
                                            </div>
                                        @endif
                                        @if(($computer->tickets_count ?? 0) > 0)
                                            <div class="flex items-center justify-center gap-1.5 px-3 py-2 text-xs font-semibold text-red-700 bg-red-50 rounded-lg">
                                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
                                                </svg>
                                                Sedang dalam penanganan tiket
                                            </div>
                                        @endif
                                    </div>
                                @else
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                        <a href="{{ route('lapor-kendala.create', $computer) }}"
                                           class="inline-flex items-center justify-center gap-1.5 px-3 py-2 text-xs font-semibold text-orange-700 bg-orange-50 rounded-lg hover:bg-orange-100 transition-colors">
                                            Lapor Kendala
                                        </a>
                                        <a href="{{ route('pinjam-komputer.create', $computer) }}"
                                           class="inline-flex items-center justify-center gap-1.5 px-3 py-2 text-xs font-semibold text-blue-700 bg-blue-50 rounded-lg hover:bg-blue-100 transition-colors">
                                            Pinjam
                                        </a>
                                    </div>
                                @endif
                            </div>

                            @if($showSpec)
                            {{-- Data spesifikasi (dipakai modal) --}}
                            <div id="spec-{{ $computer->id }}" class="hidden">
                                <div class="space-y-6">
                                    <section>
                                        <div class="flex items-center justify-between mb-2.5">
                                            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Hardware</p>
                                            <span class="text-xs text-gray-400">{{ $computer->hardware->count() }} item</span>
                                        </div>
                                        @if($computer->hardware->isNotEmpty())
                                            <div class="flex flex-wrap gap-1.5">
                                                @foreach($computer->hardware as $hw)
                                                    <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-medium bg-blue-50 text-blue-700">
                                                        {{ $hw->name }}
                                                        @if($hw->category)
                                                            <span class="ml-1 text-blue-400">({{ $hw->category }})</span>
                                                        @endif
                                                    </span>
                                                @endforeach
                                            </div>
                                        @else
                                            <p class="text-sm text-gray-400 italic">Belum ada data hardware.</p>
                                        @endif
                                    </section>

                                    <section>
                                        <div class="flex items-center justify-between mb-2.5">
                                            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Software</p>
                                            <span class="text-xs text-gray-400">{{ $computer->software->count() }} item</span>
                                        </div>
                                        @if($computer->software->isNotEmpty())
                                            <div class="flex flex-wrap gap-1.5">
                                                @foreach($computer->software as $sw)
                                                    <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-medium bg-purple-50 text-purple-700">
                                                        {{ $sw->name }}
                                                        @if($sw->version || $sw->category)
                                                            <span class="ml-1 text-purple-400">({{ $sw->version ?? $sw->category }})</span>
                                                        @endif
                                                    </span>
                                                @endforeach
                                            </div>
                                        @else
                                            <p class="text-sm text-gray-400 italic">Belum ada data software.</p>
                                        @endif
                                    </section>
                                </div>
                            </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            @else
                <div class="bg-white rounded-2xl border border-gray-200 px-6 py-16 text-center">
                    <svg class="w-16 h-16 text-gray-300 mx-auto mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 17.25v1.007a3 3 0 01-.879 2.122L7.5 21h9l-.621-.621A3 3 0 0115 18.257V17.25m6-12V15a2.25 2.25 0 01-2.25 2.25H5.25A2.25 2.25 0 013 15V5.25m18 0A2.25 2.25 0 0018.75 3H5.25A2.25 2.25 0 003 5.25m18 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 7.41A2.25 2.25 0 012.25 5.496V5.25" />
                    </svg>
                    <p class="text-gray-500 text-lg font-medium">Belum ada komputer pada laboratorium ini</p>
                    <p class="text-gray-400 text-sm mt-1">Pilih laboratorium lain di atas untuk melihat daftar komputer.</p>
                </div>
            @endif
        </section>
    </main>

    {{-- Footer --}}
    <footer class="mt-12 bg-white border-t border-gray-200 py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col sm:flex-row items-center justify-between gap-3">
            <p class="text-sm text-gray-500">&copy; {{ date('Y') }} SimKom - Sistem Manajemen Komputer</p>
            <div class="flex items-center gap-4">
                <a href="{{ route('track.index') }}" class="text-sm text-gray-500 hover:text-green-600 transition-colors">Lacak Laporan</a>
            </div>
        </div>
    </footer>
    @if($showSpec)
    {{-- Modal Spesifikasi --}}
    <div id="specModal" class="fixed inset-0 z-50 hidden" role="dialog" aria-modal="true" aria-labelledby="specModalTitle">
        <div data-spec-close class="absolute inset-0 bg-gray-900/50 backdrop-blur-sm"></div>
        <div class="relative min-h-full flex items-end sm:items-center justify-center p-0 sm:p-4">
            <div class="relative w-full sm:max-w-lg bg-white rounded-t-3xl sm:rounded-2xl shadow-2xl max-h-[85vh] flex flex-col">
                <div class="flex items-start justify-between gap-4 px-6 py-4 border-b border-gray-100">
                    <div>
                        <p class="text-xs font-semibold text-green-600 uppercase tracking-wider mb-1">Spesifikasi</p>
                        <h3 id="specModalTitle" class="text-lg font-bold text-gray-900">-</h3>
                        <p id="specModalSubtitle" class="text-sm text-gray-500"></p>
                    </div>
                    <button type="button" id="specModalClose" data-spec-close aria-label="Tutup"
                            class="flex-shrink-0 p-2 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-xl transition-colors">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                <div id="specModalBody" class="px-6 py-5 overflow-y-auto"></div>
            </div>
        </div>
    </div>

    <script>
        (function () {
            const modal = document.getElementById('specModal');
            const modalBody = document.getElementById('specModalBody');
            const modalTitle = document.getElementById('specModalTitle');
            const modalSubtitle = document.getElementById('specModalSubtitle');
            const closeButton = document.getElementById('specModalClose');
            let lastTrigger = null;

            function openSpec(trigger) {
                const source = document.querySelector(trigger.dataset.specTarget);
                if (!source) return;

                lastTrigger = trigger;
                modalTitle.textContent = trigger.dataset.specTitle || 'Spesifikasi';
                modalSubtitle.textContent = trigger.dataset.specSubtitle || '';
                modalBody.innerHTML = source.innerHTML;
                modal.classList.remove('hidden');
                document.body.classList.add('overflow-hidden');
                closeButton.focus();
            }

            function closeSpec() {
                modal.classList.add('hidden');
                document.body.classList.remove('overflow-hidden');
                modalBody.innerHTML = '';
                lastTrigger?.focus();
                lastTrigger = null;
            }

            document.addEventListener('click', function (event) {
                const trigger = event.target.closest('[data-spec-trigger]');

                if (trigger) {
                    openSpec(trigger);
                    return;
                }

                if (event.target.closest('[data-spec-close]')) {
                    closeSpec();
                }
            });

            document.addEventListener('keydown', function (event) {
                if (event.key === 'Escape' && !modal.classList.contains('hidden')) {
                    closeSpec();
                }
            });
        })();
    </script>
    @endif

<script>
function toggleWelcomeGrid(checked) {
    const svg = document.getElementById('welcomeLayoutSvg');
    if (svg && svg.__x) {
        svg.__x.showGrid = checked;
    }
}
</script>

</body>
</html>
