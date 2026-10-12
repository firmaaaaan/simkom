<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <title>Jadwal Perkuliahan - SimKom - UPT Lab Terpadu</title>
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
    <style>[x-cloak] { display: none !important; }</style>
</head>
<body class="bg-gray-50 font-sans antialiased min-h-screen flex flex-col">

    <nav class="bg-white border-b border-gray-200 sticky top-0 z-40">
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
                <a href="{{ url('/#denah') }}"
                    class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-green-700 bg-green-50 border border-green-200 rounded-lg hover:bg-green-100 transition-colors">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
                    </svg>
                    Kembali ke Denah
                </a>
            </div>
        </div>
    </nav>

    <main class="flex-1 w-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

        {{-- Header --}}
        <div class="mb-6 flex flex-col sm:flex-row sm:items-end sm:justify-between gap-3">
            <div>
                <h1 class="text-2xl sm:text-3xl font-black text-gray-900">Jadwal Perkuliahan Laboratorium</h1>
                <p class="text-gray-500 mt-1 text-sm sm:text-base">Kalender mingguan jadwal penggunaan laboratorium dari SIMPTT — tanpa perlu login.</p>
            </div>
            <p class="text-xs text-gray-400">Diperbarui: {{ now()->translatedFormat('d F Y, H:i') }}</p>
        </div>

        @if($error)
            <div class="mb-4 px-4 py-3 bg-amber-50 border border-amber-200 text-amber-800 rounded-xl text-sm flex items-start gap-2">
                <svg class="w-5 h-5 text-amber-500 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                </svg>
                <span>{{ $error }}</span>
            </div>
        @endif

        {{-- Toolbar: navigasi minggu + filter --}}
        <form method="GET" action="{{ route('jadwal.index') }}" class="mb-4 bg-white rounded-xl border border-gray-200 p-4">
            <div class="flex flex-col lg:flex-row lg:items-end gap-3">
                <div class="flex items-center gap-2">
                    @if($prevUrl)
                        <a href="{{ $prevUrl }}" title="Minggu sebelumnya"
                            class="shrink-0 w-10 h-10 flex items-center justify-center border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors text-gray-600">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5" />
                            </svg>
                        </a>
                    @else
                        <span title="Jadwal minggu lampau tidak ditampilkan"
                            class="shrink-0 w-10 h-10 flex items-center justify-center border border-gray-100 rounded-lg text-gray-300 cursor-not-allowed select-none">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5" />
                            </svg>
                        </span>
                    @endif
                    <div class="px-1">
                        <p class="text-[11px] font-semibold uppercase tracking-wider text-gray-400 mb-0.5">Minggu</p>
                        <p class="text-sm font-bold text-gray-800 whitespace-nowrap">{{ $weekLabel }}</p>
                    </div>
                    <a href="{{ $nextUrl }}" title="Minggu berikutnya"
                        class="shrink-0 w-10 h-10 flex items-center justify-center border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors text-gray-600">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                        </svg>
                    </a>
                </div>
                <div>
                    <label for="tanggal" class="block text-sm font-medium text-gray-700 mb-1">Tanggal</label>
                    <input type="date" name="tanggal" id="tanggal" value="{{ $tanggal }}"
                        class="px-4 py-2.5 text-sm border border-gray-200 rounded-lg bg-white focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent">
                </div>
                <div class="flex-1 min-w-0">
                    <label for="kdruang" class="block text-sm font-medium text-gray-700 mb-1">Ruangan</label>
                    <select name="kdruang" id="kdruang"
                        class="w-full px-4 py-2.5 text-sm border border-gray-200 rounded-lg bg-white focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent">
                        <option value="">Semua Ruang</option>
                        @foreach($rooms as $room)
                            <option value="{{ $room['kdruang'] }}" @selected($kdruang === $room['kdruang'])>{{ $room['ruang'] }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex items-center gap-2">
                    <button type="submit" class="px-4 py-2.5 text-sm font-medium text-white bg-green-600 rounded-lg hover:bg-green-700 transition-colors">Terapkan</button>
                    <a href="{{ $todayUrl }}" class="px-4 py-2.5 text-sm font-medium border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors bg-white">Minggu Ini</a>
                </div>
            </div>
        </form>

        @if($isPastWeek)
            {{-- Minggu lampau disembunyikan --}}
            <div class="bg-white rounded-xl border border-gray-200 p-10 text-center">
                <svg class="w-10 h-10 mx-auto text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <p class="mt-3 font-bold text-gray-800">Minggu sudah berlalu</p>
                <p class="text-sm text-gray-500 mt-1">Jadwal minggu lampau tidak ditampilkan. Silakan lihat minggu berjalan atau minggu berikutnya.</p>
                <a href="{{ $todayUrl }}" class="mt-4 inline-flex px-4 py-2 text-sm font-medium text-white bg-green-600 rounded-lg hover:bg-green-700 transition-colors">Ke Minggu Ini</a>
            </div>
        @else
            {{-- Ringkasan + legenda --}}
            <div class="mb-4 flex flex-wrap items-center gap-2 text-xs">
                <span class="px-2.5 py-1 bg-green-50 text-green-700 border border-green-200 rounded-full font-medium">{{ $weekLabel }}</span>
                <span class="px-2.5 py-1 bg-blue-50 text-blue-700 border border-blue-200 rounded-full font-medium">{{ $totalEntries }} jadwal terisi</span>
                <span class="px-2.5 py-1 bg-gray-100 text-gray-600 border border-gray-200 rounded-full font-medium">{{ $totalSlots }} slot dari API</span>
                @if($kdruang !== null)
                    <span class="px-2.5 py-1 bg-amber-50 text-amber-700 border border-amber-200 rounded-full font-medium">Filter ruang aktif</span>
                @endif
                <span class="ml-auto flex items-center gap-3 text-gray-500">
                    <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-purple-500"></span> Skill Lab</span>
                    <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-amber-500"></span> Evaluasi</span>
                    <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-blue-500"></span> Lainnya</span>
                    <span class="flex items-center gap-1.5 text-gray-400">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.042 21.672L13.684 16.6m0 0l-2.51 2.225.569-9.47 5.227 7.917-3.286-.672zM12 2.25V4.5m5.834.166l-1.591 1.591M20.25 10.5H18M7.757 14.743l-1.59 1.59M6 10.5H3.75m.757 7.086l1.59-1.59M12 18.75V21m-4.183-4.183l-2.51-2.225m0 0l2.51-2.225" />
                        </svg>
                        Klik jadwal untuk detail
                    </span>
                </span>
            </div>

            @if($totalEntries === 0)
                <div class="mb-4 px-4 py-3 bg-white border border-gray-200 text-gray-500 rounded-xl text-sm text-center">
                    @if($error && $totalSlots === 0)
                        Data tidak tersedia karena API tidak dapat dijangkau.
                    @else
                        Tidak ada jadwal terisi pada {{ $weekLabel }}{{ $kdruang !== null ? ' untuk ruang terpilih' : '' }}.
                    @endif
                </div>
            @endif

            {{-- Grid mingguan (slot 2 jam × hari) + modal (Alpine) --}}
            <div class="bg-white rounded-xl border border-gray-200 overflow-hidden"
                x-data="{
                    cal: {{ \Illuminate\Support\Js::from($entriesByDay) }},
                    labels: {{ \Illuminate\Support\Js::from($dayLabels) }},
                    openDay: null,
                    openIdx: null,
                    get dayEntries() { return this.cal[this.openDay] || []; },
                    get entry() { return this.openIdx !== null ? (this.dayEntries[this.openIdx] || null) : null; },
                    closeTop() { if (this.openIdx !== null) { this.openIdx = null; } else { this.openDay = null; } }
                }"
                @keydown.escape.window="closeTop()">

                <div class="overflow-x-auto">
                    <table class="w-full border-collapse text-sm min-w-[900px]">
                        <thead>
                            <tr class="bg-gray-50">
                                <th class="border-b border-r border-gray-200 px-3 py-2.5 text-right text-[11px] font-bold uppercase tracking-wider text-gray-500 w-20">Jam</th>
                                @foreach($days as $day)
                                    <th class="border-b border-r border-gray-200 px-2 py-2.5 text-center last:border-r-0 {{ $day['isWeekend'] ? 'bg-gray-100/70' : '' }}">
                                        <span class="block text-[11px] font-semibold uppercase tracking-wider {{ $day['isToday'] ? 'text-green-700' : 'text-gray-500' }}">{{ $day['short'] }}</span>
                                        <span class="mt-1 inline-flex items-center justify-center w-7 h-7 rounded-full text-sm font-semibold {{ $day['isToday'] ? 'bg-green-600 text-white' : ($day['isWeekend'] ? 'text-gray-400' : 'text-gray-800') }}">{{ $day['num'] }}</span>
                                    </th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $chipClass = fn (?string $jenis) => match ($jenis) {
                                    'Skill Lab' => 'bg-purple-50 text-purple-700 border-purple-200 hover:bg-purple-100',
                                    'Evaluasi' => 'bg-amber-50 text-amber-700 border-amber-200 hover:bg-amber-100',
                                    default => 'bg-blue-50 text-blue-700 border-blue-200 hover:bg-blue-100',
                                };
                            @endphp

                            @foreach($slots as $slotIndex => $slot)
                                @if(!empty($slot['isBreak']))
                                <tr>
                                    <td class="border-b border-r border-gray-100 px-3 py-1.5 text-xs font-semibold text-amber-700 text-right bg-amber-50/70 whitespace-nowrap">{{ $slot['label'] }}</td>
                                    <td colspan="{{ count($days) }}" class="border-b border-gray-100 px-3 py-1.5 text-center text-xs font-bold tracking-wide text-amber-700 bg-amber-50/70">ISTIRAHAT</td>
                                </tr>
                                @else
                                <tr>
                                    <td class="border-b border-r border-gray-100 px-3 py-1 text-xs font-semibold text-gray-500 text-right align-top bg-gray-50/50 whitespace-nowrap">{{ $slot['label'] }}</td>
                                    @foreach($days as $day)
                                        @php $items = $cells[$day['date']][$slotIndex] ?? []; @endphp
                                        <td class="border-b border-r border-gray-100 p-1 align-top transition-colors cursor-pointer min-w-[110px] {{ $day['isToday'] ? 'bg-green-50/40' : ($day['isWeekend'] ? 'bg-gray-50/60' : '') }} {{ $items ? 'hover:bg-green-50' : 'hover:bg-gray-50' }}"
                                            role="button" tabindex="0"
                                            @click="openDay = '{{ $day['date'] }}'; openIdx = null"
                                            @keydown.enter="openDay = '{{ $day['date'] }}'; openIdx = null">
                                            @foreach($items as $item)
                                                <button type="button"
                                                    @click.stop="openDay = '{{ $day['date'] }}'; openIdx = {{ $item['idx'] }}"
                                                    title="{{ $item['entry']['jam'] }} — {{ $item['entry']['nama'] }} ({{ $item['entry']['ruang'] }})"
                                                    class="block w-full text-left px-1.5 py-1 mb-1 rounded border transition-colors {{ $chipClass($item['entry']['jenis']) }}">
                                                    <span class="block text-[10px] font-semibold opacity-70 truncate">{{ $item['entry']['jam'] }}</span>
                                                    <span class="block text-[11px] leading-tight truncate">{{ $item['entry']['nama'] }}</span>
                                                </button>
                                            @endforeach
                                        </td>
                                    @endforeach
                                </tr>
                                @endif
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Modal: daftar jadwal satu hari --}}
                <div x-show="openDay !== null && openIdx === null" x-cloak x-transition.opacity
                    class="fixed inset-0 z-50 flex items-end sm:items-center justify-center bg-gray-500/60 p-4 sm:p-6"
                    @click.self="openDay = null">
                    <div class="w-full max-w-lg bg-white rounded-xl shadow-xl overflow-hidden flex flex-col max-h-[85vh]">
                        <div class="bg-green-600 px-5 py-4 flex items-start justify-between gap-3 shrink-0">
                            <div class="min-w-0">
                                <h3 class="text-base font-bold text-white truncate" x-text="labels[openDay] || ''"></h3>
                                <p class="text-sm text-green-100 mt-0.5" x-text="dayEntries.length + ' jadwal terisi'"></p>
                            </div>
                            <button type="button" @click="openDay = null" class="shrink-0 text-green-100 hover:text-white transition-colors" aria-label="Tutup">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>

                        <div class="overflow-y-auto divide-y divide-gray-100 shrink-0">
                            <template x-for="(e, i) in dayEntries" :key="i">
                                <button type="button" @click="openIdx = i"
                                    class="w-full text-left px-5 py-3 flex items-center gap-3 hover:bg-green-50 transition-colors">
                                    <span class="text-xs font-semibold text-gray-500 w-24 shrink-0" x-text="e.jam"></span>
                                    <span class="min-w-0 flex-1">
                                        <span class="block text-sm font-medium text-gray-900 truncate" x-text="e.nama"></span>
                                        <span class="block text-xs text-gray-500 truncate" x-text="e.pengguna + ' · Kelas ' + e.kls"></span>
                                    </span>
                                    <span class="shrink-0 px-2 py-0.5 text-[10px] font-medium rounded-full bg-gray-100 text-gray-600" x-text="e.jenis"></span>
                                    <svg class="w-4 h-4 text-gray-300 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                                    </svg>
                                </button>
                            </template>

                            <div x-show="dayEntries.length === 0" class="px-5 py-10 text-center text-sm text-gray-500">
                                Tidak ada jadwal terisi pada hari ini.
                            </div>
                        </div>

                        <div class="px-5 py-3 border-t border-gray-100 flex justify-end shrink-0 bg-gray-50">
                            <button type="button" @click="openDay = null" class="px-4 py-2 text-sm font-medium border border-gray-300 rounded-lg hover:bg-white transition-colors">Tutup</button>
                        </div>
                    </div>
                </div>

                {{-- Modal: detail jadwal --}}
                <div x-show="openIdx !== null" x-cloak x-transition.opacity
                    class="fixed inset-0 z-[60] flex items-end sm:items-center justify-center bg-gray-500/70 p-4 sm:p-6"
                    @click.self="openIdx = null">
                    <div class="w-full max-w-md bg-white rounded-xl shadow-xl overflow-hidden">
                        <div class="bg-green-600 px-5 py-4 flex items-start justify-between gap-3">
                            <div class="min-w-0">
                                <p class="text-xs text-green-100 uppercase tracking-wider font-semibold">Detail Jadwal</p>
                                <h3 class="text-base font-bold text-white mt-0.5" x-text="entry ? entry.nama : ''"></h3>
                            </div>
                            <button type="button" @click="openIdx = null" class="shrink-0 text-green-100 hover:text-white transition-colors" aria-label="Tutup">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>

                        <div class="px-5 py-5 space-y-3 text-sm">
                            <div class="flex justify-between gap-4 pb-3 border-b border-gray-100">
                                <span class="text-gray-500">Tanggal</span>
                                <span class="font-medium text-gray-900 text-right" x-text="labels[openDay] || ''"></span>
                            </div>
                            <div class="flex justify-between gap-4">
                                <span class="text-gray-500">Jam</span>
                                <span class="font-medium text-gray-900" x-text="entry ? entry.jam : ''"></span>
                            </div>
                            <div class="flex justify-between gap-4">
                                <span class="text-gray-500">Kode</span>
                                <span class="font-mono text-xs font-medium text-gray-900" x-text="entry ? entry.kode : ''"></span>
                            </div>
                            <div class="flex justify-between gap-4">
                                <span class="text-gray-500">Dosen</span>
                                <span class="font-medium text-gray-900 text-right" x-text="entry ? entry.pengguna : ''"></span>
                            </div>
                            <div class="flex justify-between gap-4">
                                <span class="text-gray-500">Kelas</span>
                                <span class="font-medium text-gray-900" x-text="entry ? entry.kls : ''"></span>
                            </div>
                            <div class="flex justify-between gap-4">
                                <span class="text-gray-500">Jenis</span>
                                <span class="px-2 py-0.5 text-xs font-medium rounded-full bg-gray-100 text-gray-700" x-text="entry ? entry.jenis : ''"></span>
                            </div>
                            <div class="flex justify-between gap-4">
                                <span class="text-gray-500">Ruangan</span>
                                <span class="font-medium text-gray-900 text-right" x-text="entry ? entry.ruang : ''"></span>
                            </div>
                            <div class="flex justify-between gap-4" x-show="entry && entry.keterangan">
                                <span class="text-gray-500">Keterangan</span>
                                <span class="font-medium text-gray-900 text-right" x-text="entry && entry.keterangan ? entry.keterangan : ''"></span>
                            </div>
                        </div>

                        <div class="px-5 py-3 border-t border-gray-100 flex justify-between gap-2 bg-gray-50">
                            <button type="button" @click="openIdx = null" class="px-4 py-2 text-sm font-medium text-gray-600 border border-gray-300 rounded-lg hover:bg-white transition-colors">Kembali</button>
                            <button type="button" @click="openIdx = null; openDay = null" class="px-4 py-2 text-sm font-medium border border-gray-300 rounded-lg hover:bg-white transition-colors">Tutup</button>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <p class="text-xs text-gray-400 mt-4 text-center">
            Jadwal dapat berubah sewaktu-waktu. Untuk pertanyaan, silakan hubungi pengelola laboratorium.
        </p>
    </main>

    <footer class="py-6 text-center text-xs text-gray-400 border-t border-gray-200 bg-white">
        <a href="{{ url('/') }}" class="inline-flex items-center gap-2 hover:text-green-600 transition-colors">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"/>
            </svg>
            Kembali ke Denah Lab
        </a>
    </footer>

    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</body>
</html>
