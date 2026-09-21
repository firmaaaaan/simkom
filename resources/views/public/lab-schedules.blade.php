<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <title>Jadwal Lab - SimKom - UPT Lab Terpadu</title>
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
                    <a href="{{ url('/') }}" class="inline-flex items-center gap-2 px-3 sm:px-4 py-2 text-sm font-medium text-gray-600 hover:text-green-600 transition-colors">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6z" />
                        </svg>
                        <span class="hidden sm:inline">Denah Lab</span>
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <main class="flex-1 w-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

        {{-- Header --}}
        <div class="mb-6">
            <h1 class="text-2xl sm:text-3xl font-black text-gray-900">Jadwal Penggunaan Laboratorium</h1>
            <p class="text-gray-500 mt-1 text-sm sm:text-base">
                Jadwal perkuliahan dan praktikum per slot waktu — tanpa perlu login.
            </p>
        </div>

        {{-- RealtimeUrl warning banner --}}
        @if($realtimeUrl)
            <div class="mb-5 bg-amber-50 border border-amber-200 rounded-xl p-4 flex items-start gap-3">
                <svg class="w-5 h-5 text-amber-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
                </svg>
                <div class="text-sm">
                    <p class="font-semibold text-amber-800">Jadwal di halaman ini bersifat tidak real-time</p>
                    <p class="text-amber-700 mt-0.5">
                        Data jadwal diperbarui secara berkala oleh pengelola laboratorium, sehingga bisa berbeda dengan kondisi terkini.
                        Untuk melihat jadwal real-time, kunjungi
                        <a href="{{ $realtimeUrl }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-1 font-semibold text-green-700 hover:text-green-800 underline decoration-green-400 underline-offset-2">
                            tautan jadwal real-time berikut
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 003 8.25v10.5A2.25 2.25 0 005.25 21h10.5A2.25 2.25 0 0018 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25" /></svg>
                        </a>, kemudian login menggunakan akun SIMPTT masing-masing.
                    </p>
                </div>
            </div>
        @endif

        {{-- Filter lab --}}
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-5">
            <form id="lab-filter" method="GET" action="{{ route('jadwal-lab.index') }}">
                <input type="hidden" name="day" value="{{ $day }}">
                <label class="block text-xs font-medium text-gray-500 mb-1">Filter Laboratorium</label>
                <select name="laboratory_id" onchange="document.getElementById('lab-filter').submit()"
                    class="w-full sm:w-72 px-4 py-2.5 text-sm border border-gray-200 rounded-xl bg-white focus:ring-2 focus:ring-green-500 focus:border-green-500">
                    <option value="">Semua Laboratorium</option>
                    @foreach($laboratories as $lab)
                        <option value="{{ $lab->id }}" @selected($selectedLab?->id === $lab->id)>{{ $lab->code }} — {{ $lab->name }}</option>
                    @endforeach
                </select>
            </form>
            <p class="text-xs text-gray-400">
                @if($isToday)
                    <span class="inline-flex items-center gap-1.5 text-green-600 font-medium">
                        <span class="w-1.5 h-1.5 rounded-full bg-green-500 animate-pulse"></span>
                        Sekarang: {{ now()->translatedFormat('l, H:i') }}
                    </span>
                @else
                    Diperbarui: {{ now()->translatedFormat('d F Y, H:i') }}
                @endif
            </p>
        </div>

        {{-- Tab hari --}}
        <div class="flex items-center gap-2 mb-4 overflow-x-auto pb-1">
            <a href="{{ route('jadwal-lab.index', array_filter(['laboratory_id' => $selectedLab?->id])) }}"
                class="px-4 py-2 text-sm font-medium rounded-lg whitespace-nowrap transition-colors {{ $allDays ? 'bg-green-600 text-white shadow-sm' : 'bg-white border border-gray-200 text-gray-700 hover:bg-gray-50' }}">
                Semua
            </a>
            @foreach($days as $dayKey => $dayLabel)
                <a href="{{ route('jadwal-lab.index', array_filter(['day' => $dayKey, 'laboratory_id' => $selectedLab?->id])) }}"
                    class="px-4 py-2 text-sm font-medium rounded-lg whitespace-nowrap transition-colors {{ ! $allDays && $dayKey === $day ? 'bg-green-600 text-white shadow-sm' : 'bg-white border border-gray-200 text-gray-700 hover:bg-gray-50' }}">
                    {{ $dayLabel }}
                    @if($dayKey === $today)
                        <span class="ml-1 text-[10px] align-middle opacity-80">&bull; Hari ini</span>
                    @endif
                </a>
            @endforeach
        </div>

        {{-- Grid jadwal (read-only) --}}
        @php
            $visibleLabs = $selectedLab ? collect([$selectedLab]) : $laboratories;
            $displayDays = $allDays ? array_keys($days) : [$day];

            $dayColors = [
                'Monday' => 'bg-blue-50 text-blue-800',
                'Tuesday' => 'bg-emerald-50 text-emerald-800',
                'Wednesday' => 'bg-orange-50 text-orange-800',
                'Thursday' => 'bg-amber-50 text-amber-800',
                'Friday' => 'bg-sky-50 text-sky-800',
            ];
        @endphp

        @if($visibleLabs->isEmpty())
            <div class="bg-white rounded-xl border border-gray-200 p-10 text-center text-gray-500 text-sm">
                Belum ada laboratorium aktif.
            </div>
        @else
            <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm border-collapse min-w-[600px]">
                        <thead>
                            <tr>
                                <th class="bg-gray-50 text-left px-4 py-3 font-medium text-gray-600 border border-gray-200 w-[120px]">Waktu</th>
                                @foreach($visibleLabs as $lab)
                                    <th class="bg-green-600 text-white text-center px-4 py-3 font-semibold border border-green-500">
                                        <div class="text-sm">{{ $lab->code }}</div>
                                        <div class="text-[10px] font-normal opacity-90 mt-0.5">{{ $lab->name }}</div>
                                    </th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($displayDays as $displayDay)
                                @php
                                    $dayClass = $dayColors[$displayDay] ?? 'bg-gray-50 text-gray-800';
                                @endphp
                                <tr>
                                    <td colspan="{{ $visibleLabs->count() + 1 }}" class="{{ $dayClass }} px-4 py-2 font-bold text-sm border border-gray-200">{{ $days[$displayDay] }}</td>
                                </tr>
                                @foreach($timeSlots as $slot)
                                    @php $isSlotNow = ! $allDays && !empty($slot['is_now']); @endphp
                                    @if($slot['is_break'] ?? false)
                                        <tr class="bg-amber-50/50">
                                            <td class="px-4 py-2.5 font-semibold text-amber-700 text-xs border border-gray-200">{{ $slot['label'] }}</td>
                                            <td colspan="{{ $visibleLabs->count() }}" class="text-center text-amber-600 text-xs font-medium italic border border-gray-200">
                                                <svg class="w-4 h-4 inline-block mr-1 -mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                                ISTIRAHAT
                                            </td>
                                        </tr>
                                    @else
                                        <tr @class(['slot-now bg-emerald-50/70' => $isSlotNow])>
                                            <td class="px-4 py-2.5 font-semibold text-xs border border-gray-200 {{ $isSlotNow ? 'text-emerald-700 bg-emerald-100/60' : 'text-gray-700 bg-gray-50/50' }}">
                                                {{ $slot['label'] }}
                                                @if($isSlotNow)
                                                    <span class="ml-1 inline-flex items-center gap-1 px-1.5 py-0.5 rounded-full bg-emerald-600 text-white text-[9px] font-bold uppercase tracking-wide align-middle">
                                                        <span class="w-1 h-1 rounded-full bg-white animate-pulse"></span> Berlangsung
                                                    </span>
                                                @endif
                                            </td>
                                            @foreach($visibleLabs as $lab)
                                                @php
                                                    $cellKey = $displayDay . '_' . $slot['start'] . '_' . $lab->id;
                                                    $schedule = $schedules->get($cellKey);
                                                @endphp
                                                @if($schedule)
                                                    <td class="border border-gray-200 bg-green-50/40 text-left p-2.5 align-top {{ $isSlotNow ? 'ring-1 ring-inset ring-emerald-400' : '' }}" id="cell-{{ $cellKey }}">
                                                        <div class="space-y-0.5">
                                                            <p class="font-bold text-blue-800 text-xs leading-tight">{{ $schedule->course_name }}</p>
                                                            <p class="text-emerald-700 text-[10px] font-medium">{{ $schedule->study_program }}</p>
                                                            @if($schedule->semester || $schedule->class_group)
                                                                <p class="text-gray-500 text-[10px]">
                                                                    @if($schedule->semester)Sem {{ $schedule->semester }}@endif
                                                                    @if($schedule->semester && $schedule->class_group) &middot; @endif
                                                                    @if($schedule->class_group)Kelas {{ $schedule->class_group }}@endif
                                                                </p>
                                                            @endif
                                                            @if($schedule->instructor)
                                                                <p class="text-gray-400 text-[10px] truncate" title="{{ $schedule->instructor }}">{{ $schedule->instructor }}</p>
                                                            @endif
                                                        </div>
                                                    </td>
                                                @else
                                                    <td class="border border-gray-200 bg-gray-50/30"></td>
                                                @endif
                                            @endforeach
                                        </tr>
                                    @endif
                                @endforeach
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            @if($schedules->isEmpty())
                <div class="bg-white rounded-xl border border-gray-200 mt-4 p-8 text-center text-gray-500 text-sm">
                    <svg class="w-10 h-10 mx-auto text-gray-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" />
                    </svg>
                    @if($allDays)
                        Belum ada jadwal yang terdaftar{{ $selectedLab ? ' di ' . $selectedLab->name : '' }}.
                    @else
                        Belum ada jadwal untuk {{ $days[$day] }}{{ $selectedLab ? ' di ' . $selectedLab->name : '' }}.
                    @endif
                </div>
            @endif
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

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const current = document.querySelector('tr.slot-now');
            if (current) {
                setTimeout(() => {
                    current.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }, 300);
            }
        });
    </script>

</body>
</html>
