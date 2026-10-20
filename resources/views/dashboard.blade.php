@extends('layouts.app')

@section('title', 'Dashboard')
@section('header', 'Dashboard')

@php
    $monthNames = [
        1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
        5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
        9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
    ];

    $statusColors = [
        'Open' => 'bg-red-100 text-red-800',
        'In Progress' => 'bg-yellow-100 text-yellow-800',
        'Resolved' => 'bg-green-100 text-green-800',
        'Closed' => 'bg-gray-100 text-gray-600',
        'Pending' => 'bg-yellow-100 text-yellow-800',
        'Approved' => 'bg-green-100 text-green-800',
        'Rejected' => 'bg-red-100 text-red-800',
        'Returned' => 'bg-blue-100 text-blue-800',
        'Baik' => 'bg-green-100 text-green-800',
        'Perlu Perbaikan' => 'bg-yellow-100 text-yellow-800',
        'Kritis' => 'bg-red-100 text-red-800',
    ];

    $computerStatusColors = [
        'Aktif' => 'bg-green-100 text-green-800',
        'Maintenance' => 'bg-yellow-100 text-yellow-800',
        'Tidak Aktif' => 'bg-red-100 text-red-800',
    ];

    $rupiah = fn (int $value) => 'Rp ' . number_format($value, 0, ',', '.');

    $quickActionStyles = [
        'red' => 'border-red-200 text-red-700 bg-red-50 hover:bg-red-100',
        'amber' => 'border-amber-200 text-amber-700 bg-amber-50 hover:bg-amber-100',
        'blue' => 'border-blue-200 text-blue-700 bg-blue-50 hover:bg-blue-100',
        'green' => 'border-green-200 text-green-700 bg-green-50 hover:bg-green-100',
    ];

    $quickActionIcons = [
        'red' => 'M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z',
        'amber' => 'M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z',
        'blue' => 'M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
        'green' => 'M9 17.25v1.007a3 3 0 01-.879 2.122L7.5 21h9l-.621-.621A3 3 0 0115 18.257V17.25m6-12V15a2.25 2.25 0 01-2.25 2.25H5.25A2.25 2.25 0 013 15V5.25m18 0A2.25 2.25 0 0018.75 3H5.25A2.25 2.25 0 003 5.25m18 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 7.41A2.25 2.25 0 012.25 5.496V5.25',
    ];
@endphp

@section('content')
{{-- FILTER TAHUN AJARAN --}}
<div class="bg-white rounded-xl border border-gray-200 px-5 py-4 mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
    <div>
        <p class="text-sm font-semibold text-gray-800">Periode Data Dashboard</p>
        <p class="text-xs text-gray-500 mt-0.5">
            @if($selectedAcademicYear)
                Tahun ajaran <span class="font-medium text-gray-700">{{ $selectedAcademicYear->name }}</span>
                &middot; {{ $selectedAcademicYear->periodLabel() }}
            @else
                Semua tahun ajaran (statistik kumulatif).
            @endif
        </p>
    </div>
    <form action="{{ route('dashboard') }}" method="GET" class="flex items-center gap-2">
        <label for="academic_year" class="text-sm text-gray-500 flex-shrink-0">Tahun Ajaran</label>
        <select id="academic_year" name="academic_year" onchange="this.form.submit()"
            class="text-sm border border-gray-200 rounded-lg px-3 py-1.5 text-gray-600 focus:outline-none focus:ring-2 focus:ring-green-500">
            <option value="">Semua Tahun Ajaran</option>
            @foreach($academicYears as $year)
                <option value="{{ $year->id }}" {{ $selectedAcademicYear?->id === $year->id ? 'selected' : '' }}>
                    {{ $year->name }} ({{ $year->periodLabel() }})
                </option>
            @endforeach
        </select>
        <noscript>
            <button type="submit" class="px-3 py-1.5 text-sm font-medium text-white bg-green-600 rounded-lg">Terapkan</button>
        </noscript>
    </form>
</div>

{{-- STAT CARDS --}}
<div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-5 mb-6">
    @if($can['computers'])
        <a href="{{ route('computers.index') }}" class="bg-white rounded-xl border border-gray-200 p-5 flex items-center gap-4 hover:shadow-md transition-shadow">
            <div class="w-12 h-12 bg-green-100 rounded-xl flex items-center justify-center flex-shrink-0">
                <svg class="w-6 h-6 text-green-700" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 17.25v1.007a3 3 0 01-.879 2.122L7.5 21h9l-.621-.621A3 3 0 0115 18.257V17.25m6-12V15a2.25 2.25 0 01-2.25 2.25H5.25A2.25 2.25 0 013 15V5.25m18 0A2.25 2.25 0 0018.75 3H5.25A2.25 2.25 0 003 5.25m18 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 7.41A2.25 2.25 0 012.25 5.496V5.25" />
                </svg>
            </div>
            <div class="min-w-0">
                <p class="text-sm text-gray-500">Total Komputer</p>
                <p class="text-2xl font-bold text-gray-800">{{ $stats['computers_total'] }}</p>
                <p class="text-xs text-gray-400 mt-0.5">
                    {{ $stats['computers_aktif'] }} aktif &middot; {{ $stats['computers_maintenance'] }} maintenance
                </p>
            </div>
        </a>
    @endif

    @if($can['tickets'])
        <a href="{{ route('tickets.index', ['status' => 'Open']) }}" class="bg-white rounded-xl border border-gray-200 p-5 flex items-center gap-4 hover:shadow-md transition-shadow">
            <div class="w-12 h-12 bg-red-100 rounded-xl flex items-center justify-center flex-shrink-0">
                <svg class="w-6 h-6 text-red-700" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
                </svg>
            </div>
            <div class="min-w-0">
                <p class="text-sm text-gray-500">Kendala Aktif</p>
                <p class="text-2xl font-bold text-gray-800">{{ $stats['tickets_open'] + $stats['tickets_in_progress'] }}</p>
                <p class="text-xs text-gray-400 mt-0.5">
                    {{ $stats['tickets_open'] }} open &middot; {{ $stats['tickets_in_progress'] }} dikerjakan
                    @if($selectedAcademicYear) &middot; TA {{ $selectedAcademicYear->name }} @endif
                </p>
            </div>
        </a>
    @endif

    @if($can['borrowings'])
        <a href="{{ route('borrowings.index', ['status' => 'Pending']) }}" class="bg-white rounded-xl border border-gray-200 p-5 flex items-center gap-4 hover:shadow-md transition-shadow">
            <div class="w-12 h-12 bg-amber-100 rounded-xl flex items-center justify-center flex-shrink-0">
                <svg class="w-6 h-6 text-amber-700" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <div class="min-w-0">
                <p class="text-sm text-gray-500">Peminjaman Menunggu</p>
                <p class="text-2xl font-bold text-gray-800">{{ $stats['borrowings_pending'] }}</p>
                <p class="text-xs text-gray-400 mt-0.5">Menunggu persetujuan</p>
            </div>
        </a>
    @endif

    @if($can['computers'])
        <a href="{{ route('reports.card-control') }}" class="bg-white rounded-xl border border-gray-200 p-5 flex items-center gap-4 hover:shadow-md transition-shadow">
            <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center flex-shrink-0">
                <svg class="w-6 h-6 text-blue-700" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <div class="min-w-0">
                <p class="text-sm text-gray-500">Pengecekan {{ $selectedAcademicYear ? 'Tahun Ajaran' : 'Bulan Ini' }}</p>
                <p class="text-2xl font-bold text-gray-800">{{ $stats['checks_period'] }}</p>
                <p class="text-xs text-gray-400 mt-0.5 capitalize">{{ $stats['checks_period_label'] }}</p>
            </div>
        </a>
    @endif

    @if($can['monitoring'] && $monitoringSummary)
        <a href="{{ route('monitoring-pc.index') }}" class="bg-white rounded-xl border border-gray-200 p-5 flex items-center gap-4 hover:shadow-md transition-shadow">
            <div class="w-12 h-12 {{ $monitoringSummary['online'] > 0 ? 'bg-green-100' : 'bg-gray-100' }} rounded-xl flex items-center justify-center flex-shrink-0">
                <svg class="w-6 h-6 {{ $monitoringSummary['online'] > 0 ? 'text-green-700' : 'text-gray-500' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5.25 14.25h13.5m-13.5 0a3 3 0 01-3-3m3 3a3 3 0 100 6h13.5a3 3 0 100-6m-16.5-3a3 3 0 013-3h13.5a3 3 0 013 3m-19.5 0a4.5 4.5 0 01.9-2.7L5.737 5.1a3.375 3.375 0 012.7-1.35h7.126c1.062 0 2.062.5 2.7 1.35l2.587 3.45a4.5 4.5 0 01.9 2.7m0 0a3 3 0 01-3 3m0 3h.008v.008h-.008v-.008zm0-6h.008v.008h-.008v-.008zm-3 6h.008v.008h-.008v-.008zm0-6h.008v.008h-.008v-.008z" />
                </svg>
            </div>
            <div class="min-w-0">
                <p class="text-sm text-gray-500">PC Online</p>
                <p class="text-2xl font-bold text-gray-800">
                    {{ $monitoringSummary['online'] }}<span class="text-base font-medium text-gray-400">/{{ $monitoringSummary['total'] }}</span>
                </p>
                <p class="text-xs text-gray-400 mt-0.5">
                    <span class="inline-block w-2 h-2 rounded-full {{ $monitoringSummary['offline'] > 0 ? 'bg-red-500' : 'bg-green-500' }} mr-1 align-middle"></span>
                    {{ $monitoringSummary['offline'] }} offline
                </p>
            </div>
        </a>
    @endif

    @if($can['lab_usages'] && $labOccupancy)
        <a href="{{ route('lab-usages.index', ['status' => 'In']) }}" class="bg-white rounded-xl border border-gray-200 p-5 flex items-center gap-4 hover:shadow-md transition-shadow">
            <div class="w-12 h-12 bg-teal-100 rounded-xl flex items-center justify-center flex-shrink-0">
                <svg class="w-6 h-6 text-teal-700" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" />
                </svg>
            </div>
            <div class="min-w-0">
                <p class="text-sm text-gray-500">Di Lab Hari Ini</p>
                <p class="text-2xl font-bold text-gray-800">{{ $labOccupancy['in_now'] }}</p>
                <p class="text-xs text-gray-400 mt-0.5">
                    {{ $labOccupancy['out_today'] }} keluar &middot; {{ $labOccupancy['today_total'] }} kunjungan hari ini
                </p>
            </div>
        </a>
    @endif

    @if($can['pengadaan'] && $pengadaanSummary)
        <a href="{{ route('pengadaan.index', ['status' => 'Proses']) }}" class="bg-white rounded-xl border border-gray-200 p-5 flex items-center gap-4 hover:shadow-md transition-shadow">
            <div class="w-12 h-12 bg-purple-100 rounded-xl flex items-center justify-center flex-shrink-0">
                <svg class="w-6 h-6 text-purple-700" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 00-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 01-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 003 15h-.75M15 10.5a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
            </div>
            <div class="min-w-0">
                <p class="text-sm text-gray-500">Pengadaan Diproses</p>
                <p class="text-2xl font-bold text-gray-800">{{ $pengadaanSummary['proses'] }}</p>
                <p class="text-xs text-gray-400 mt-0.5">{{ $rupiah($pengadaanSummary['acc_value']) }} sudah Acc</p>
            </div>
        </a>
    @endif
</div>

{{-- PERINGATAN TAHUN AJARAN --}}
@if($academicYearAlert)
    @php($isMissing = $academicYearAlert['type'] === 'missing')
    <div class="mb-6 rounded-xl border px-5 py-4 {{ $isMissing ? 'border-red-200 bg-red-50' : 'border-amber-200 bg-amber-50' }}">
        <div class="flex items-start gap-3">
            <svg class="w-5 h-5 flex-shrink-0 mt-0.5 {{ $isMissing ? 'text-red-500' : 'text-amber-500' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
            </svg>
            <div class="flex-1 min-w-0">
                @if($isMissing)
                    <p class="text-sm font-semibold text-red-800">Belum ada tahun ajaran berstatus Aktif</p>
                    <p class="text-sm text-red-700 mt-1">
                        Pengecekan kartu kendali dan tiket baru akan tersimpan tanpa tahun ajaran, sehingga tidak muncul di laporan per tahun ajaran.
                    </p>
                @else
                    <p class="text-sm font-semibold text-amber-800">Tahun ajaran aktif sudah melewati periodenya</p>
                    <ul class="text-sm text-amber-700 mt-1 space-y-0.5">
                        @foreach($academicYearAlert['years'] as $year)
                            <li>
                                <span class="font-medium">{{ $year->name }}</span>
                                <span class="text-amber-600">({{ $year->periodLabel() }})</span>
                            </li>
                        @endforeach
                    </ul>
                    <p class="text-sm text-amber-700 mt-1">
                        Data baru masih akan dicatat pada tahun ajaran ini. Aktifkan tahun ajaran berikutnya agar periodenya sesuai dengan tanggal berjalan.
                    </p>
                @endif

                @if($can['academic_years'])
                    <a href="{{ route('academic-years.index') }}"
                        class="inline-flex items-center gap-1.5 mt-2.5 px-3 py-1.5 bg-white text-xs font-semibold rounded-lg border transition-colors {{ $isMissing ? 'border-red-300 text-red-700 hover:bg-red-100' : 'border-amber-300 text-amber-700 hover:bg-amber-100' }}">
                        Kelola Tahun Ajaran
                    </a>
                @endif
            </div>
        </div>
    </div>
@endif

{{-- QUICK ACTIONS + NOTIFIKASI --}}
@if(count($quickActions) > 0 || $unreadNotifications > 0)
    <div class="bg-white rounded-xl border border-gray-200 p-5 mb-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-4">
            @if(count($quickActions) > 0)
                <h2 class="text-base font-semibold text-gray-800">Aksi Cepat</h2>
            @else
                <h2 class="text-base font-semibold text-gray-800">Notifikasi</h2>
            @endif
            @if($unreadNotifications > 0)
                <a href="{{ route('notifications.index') }}" class="inline-flex items-center gap-1.5 text-xs font-semibold text-amber-700 bg-amber-50 border border-amber-200 px-3 py-1.5 rounded-lg hover:bg-amber-100 transition-colors">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0" />
                    </svg>
                    {{ $unreadNotifications }} notifikasi belum dibaca
                </a>
            @endif
        </div>
        @if(count($quickActions) > 0)
            <div class="flex flex-wrap gap-2.5">
                @foreach($quickActions as $action)
                    <a href="{{ $action['url'] }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg border text-sm font-medium transition-colors {{ $quickActionStyles[$action['color']] ?? $quickActionStyles['blue'] }}">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="{{ $quickActionIcons[$action['color']] ?? $quickActionIcons['blue'] }}" />
                        </svg>
                        {{ $action['label'] }}
                    </a>
                @endforeach
            </div>
        @endif
    </div>
@endif

{{-- CHART + ACTIVITY --}}
@if($chart || $activities->isNotEmpty())
    <div class="grid grid-cols-1 xl:grid-cols-3 gap-5 mb-6">
        @if($chart)
            {{-- CHART --}}
            <div class="xl:col-span-2 bg-white rounded-xl border border-gray-200 p-6">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
                    <div>
                        <h2 class="text-base font-semibold text-gray-800">Statistik Kendala Bulanan</h2>
                        <p class="text-xs text-gray-500 mt-1">
                            {{ $chart['total'] }} tiket tercatat pada {{ $chart['scopeLabel'] }}
                        </p>
                    </div>
                </div>

                @if($chart['total'] > 0)
                    <div class="h-64" data-chart-fallback>
                        <canvas id="ticketMonthlyChart" aria-label="Grafik batang tiket per bulan" role="img"></canvas>
                    </div>
                    <p class="hidden text-sm text-gray-400 text-center py-10" data-chart-unavailable>Grafik tidak dapat dimuat (Chart.js gagal dimuat dari CDN).</p>
                    <p class="text-xs text-gray-400 mt-3 text-center">Klik batang untuk melihat daftar tiket bulan tersebut.</p>
                @else
                    <div class="h-64 flex flex-col items-center justify-center text-gray-400">
                        <svg class="w-10 h-10 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z" />
                        </svg>
                        <p class="text-sm">Belum ada tiket pada {{ $chart['scopeLabel'] }}</p>
                    </div>
                @endif
            </div>
        @endif

        {{-- RECENT ACTIVITY --}}
        <div class="bg-white rounded-xl border border-gray-200 p-6">
            <h2 class="text-base font-semibold text-gray-800 mb-4">Aktivitas Terbaru</h2>

            @forelse($activities as $activity)
                <a href="{{ $activity['url'] }}" class="flex items-start gap-3 py-2.5 border-b border-gray-50 last:border-0 hover:bg-gray-50 -mx-2 px-2 rounded-lg transition-colors">
                    <div class="mt-0.5 w-8 h-8 rounded-full flex items-center justify-center flex-shrink-0
                        {{ $activity['color'] === 'red' ? 'bg-red-100' : ($activity['color'] === 'amber' ? 'bg-amber-100' : 'bg-blue-100') }}">
                        @if($activity['color'] === 'red')
                            <svg class="w-4 h-4 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
                            </svg>
                        @elseif($activity['color'] === 'amber')
                            <svg class="w-4 h-4 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        @else
                            <svg class="w-4 h-4 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        @endif
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm text-gray-700 truncate">{{ $activity['title'] }}</p>
                        <p class="text-xs text-gray-400 mt-0.5 truncate">{{ $activity['meta'] }}</p>
                    </div>
                    <div class="text-right flex-shrink-0">
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $statusColors[$activity['status']] ?? 'bg-gray-100 text-gray-600' }}">
                            {{ $activity['status'] }}
                        </span>
                        <p class="text-xs text-gray-400 mt-1" title="{{ $activity['at']->format('d M Y H:i') }}">
                            {{ $activity['at']->locale('id')->diffForHumans() }}
                        </p>
                    </div>
                </a>
            @empty
                <div class="text-center py-8 text-gray-400">
                    <svg class="w-10 h-10 mx-auto mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <p class="text-sm">Belum ada aktivitas</p>
                </div>
            @endforelse
        </div>
    </div>
@endif

{{-- ANALISIS TIKET: PRIORITAS + KATEGORI --}}
@if($can['tickets'] && $ticketAnalysis)
    <div class="grid grid-cols-1 xl:grid-cols-2 gap-5 mb-6">
        {{-- DONUT PRIORITAS --}}
        <div class="bg-white rounded-xl border border-gray-200 p-6">
            <h2 class="text-base font-semibold text-gray-800">Prioritas Kendala Aktif</h2>
            <p class="text-xs text-gray-500 mt-1 mb-5">
                {{ $ticketAnalysis['priority']['total'] }} tiket berstatus Open / In Progress
                @if($selectedAcademicYear) pada TA {{ $selectedAcademicYear->name }} @endif
            </p>

            @if($ticketAnalysis['priority']['total'] > 0)
                <div class="flex flex-col sm:flex-row items-center gap-6">
                    <div class="w-44 h-44 flex-shrink-0" data-chart-fallback>
                        <canvas id="ticketPriorityChart" aria-label="Grafik donut prioritas tiket aktif" role="img"></canvas>
                    </div>
                    <p class="hidden text-sm text-gray-400 text-center py-6" data-chart-unavailable>Grafik tidak dapat dimuat (Chart.js gagal dimuat dari CDN).</p>
                    <ul class="flex-1 space-y-2.5 w-full">
                        @foreach($ticketAnalysis['priority']['labels'] as $i => $label)
                            @php($count = $ticketAnalysis['priority']['counts'][$i])
                            <li class="flex items-center justify-between text-sm">
                                <span class="inline-flex items-center gap-2 text-gray-700">
                                    <span class="w-3 h-3 rounded-full flex-shrink-0" style="background-color: {{ $ticketAnalysis['priority']['colors'][$i] }}"></span>
                                    {{ $label }}
                                </span>
                                <span class="font-semibold text-gray-800">
                                    {{ $count }}
                                    <span class="text-xs font-normal text-gray-400">
                                        ({{ $ticketAnalysis['priority']['total'] > 0 ? round($count / $ticketAnalysis['priority']['total'] * 100) : 0 }}%)
                                    </span>
                                </span>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @else
                <div class="h-40 flex flex-col items-center justify-center text-gray-400">
                    <svg class="w-9 h-9 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <p class="text-sm">Tidak ada kendala aktif pada periode ini</p>
                </div>
            @endif
        </div>

        {{-- BAR KATEGORI --}}
        <div class="bg-white rounded-xl border border-gray-200 p-6">
            <h2 class="text-base font-semibold text-gray-800">Kategori Kendala</h2>
            <p class="text-xs text-gray-500 mt-1 mb-5">
                {{ $ticketAnalysis['category']['total'] }} tiket tercatat
                @if($selectedAcademicYear) pada TA {{ $selectedAcademicYear->name }} @endif
            </p>

            @if($ticketAnalysis['category']['total'] > 0)
                <div class="h-56" data-chart-fallback>
                    <canvas id="ticketCategoryChart" aria-label="Grafik batang kategori tiket" role="img"></canvas>
                </div>
                <p class="hidden text-sm text-gray-400 text-center py-10" data-chart-unavailable>Grafik tidak dapat dimuat (Chart.js gagal dimuat dari CDN).</p>
            @else
                <div class="h-40 flex flex-col items-center justify-center text-gray-400">
                    <svg class="w-9 h-9 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z" />
                    </svg>
                    <p class="text-sm">Belum ada tiket pada periode ini</p>
                </div>
            @endif
        </div>
    </div>
@endif

{{-- RINGKASAN PER LABORATORIUM --}}
@if($laboratorySummary->isNotEmpty())
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden mb-6">
        <div class="px-6 py-4 border-b border-gray-100 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <h2 class="text-base font-semibold text-gray-800">Pemeliharaan &amp; Pengecekan Terakhir per Laboratorium</h2>
                <p class="text-xs text-gray-500 mt-1">{{ $laboratorySummary->count() }} laboratorium terdaftar.</p>
            </div>
            @if($can['maintenance'])
                <a href="{{ route('maintenance.index') }}" class="px-4 py-2 border border-green-600 text-green-600 text-sm font-medium rounded-lg hover:bg-green-50 transition-colors">
                    Kelola Pemeliharaan
                </a>
            @endif
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm table-responsive-cards">
                <thead>
                    <tr class="border-b border-gray-100">
                        <th class="text-left px-6 py-3 font-medium text-gray-500">Laboratorium</th>
                        <th class="text-left px-6 py-3 font-medium text-gray-500">Komputer</th>
                        @if($can['computers'])
                            <th class="text-left px-6 py-3 font-medium text-gray-500">Status Komputer</th>
                        @endif
                        @if($can['maintenance'])
                            <th class="text-left px-6 py-3 font-medium text-gray-500">Pemeliharaan Terakhir</th>
                        @endif
                        @if($can['computers'])
                            <th class="text-left px-6 py-3 font-medium text-gray-500">Pengecekan Terakhir</th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    @foreach($laboratorySummary as $row)
                        <tr class="border-b border-gray-50 hover:bg-gray-50 transition-colors">
                            <td data-label="Laboratorium" class="px-6 py-4 font-medium text-gray-800">{{ $row['laboratory']->name }}</td>
                            <td data-label="Komputer" class="px-6 py-4 text-gray-600">{{ $row['total_computers'] }} unit</td>

                            @if($can['computers'])
                                <td data-label="Status Komputer" class="px-6 py-4">
                                    <div class="flex flex-wrap gap-1.5">
                                        @foreach(['Aktif', 'Maintenance', 'Tidak Aktif'] as $computerStatus)
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $computerStatusColors[$computerStatus] }}">
                                                {{ (int) $row['computers_by_status']->get($computerStatus, 0) }} {{ $computerStatus }}
                                            </span>
                                        @endforeach
                                    </div>
                                </td>
                            @endif

                            @if($can['maintenance'])
                                <td data-label="Pemeliharaan Terakhir" class="px-6 py-4">
                                    @if($row['last_maintenance'])
                                        <p class="text-gray-700">{{ \Illuminate\Support\Carbon::parse($row['last_maintenance']->maintenance_date)->format('d M Y') }}</p>
                                        <p class="text-xs text-gray-400">{{ $row['last_maintenance']->inspector_name ?: 'Tanpa pemeriksa' }}</p>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-600">Belum ada</span>
                                    @endif
                                </td>
                            @endif

                            @if($can['computers'])
                                <td data-label="Pengecekan Terakhir" class="px-6 py-4">
                                    @if($row['last_checked_at'])
                                        <p class="text-gray-700">{{ $row['last_checked_at']->format('d M Y H:i') }}</p>
                                        <p class="text-xs text-gray-400">
                                            {{ $row['total_checks'] }} pengecekan &middot; {{ $row['last_checked_at']->locale('id')->diffForHumans() }}
                                        </p>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-600">Belum ada</span>
                                    @endif
                                </td>
                            @endif
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endif

{{-- TIKET TERBARU --}}
@if($can['tickets'])
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <h2 class="text-base font-semibold text-gray-800">Tiket Terbaru</h2>
                <p class="text-xs text-gray-500 mt-1">{{ $stats['tickets_resolved'] }} tiket sudah selesai (Resolved/Closed){{ $selectedAcademicYear ? ' pada TA ' . $selectedAcademicYear->name : '' }}.</p>
            </div>
            <a href="{{ route('tickets.index') }}" class="px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700 transition-colors">
                Lihat Semua Tiket
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm table-responsive-cards">
                <thead>
                    <tr class="border-b border-gray-100">
                        <th class="text-left px-6 py-3 font-medium text-gray-500">Kode</th>
                        <th class="text-left px-6 py-3 font-medium text-gray-500">Judul</th>
                        <th class="text-left px-6 py-3 font-medium text-gray-500">Laboratorium</th>
                        <th class="text-left px-6 py-3 font-medium text-gray-500">Kategori</th>
                        <th class="text-left px-6 py-3 font-medium text-gray-500">Status</th>
                        <th class="text-left px-6 py-3 font-medium text-gray-500">Tanggal</th>
                        <th class="text-left px-6 py-3 font-medium text-gray-500">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentTickets as $ticket)
                        <tr class="border-b border-gray-50 hover:bg-gray-50 transition-colors">
                            <td data-label="Kode" class="px-6 py-4 font-mono text-xs text-gray-600">{{ $ticket->tracking_code }}</td>
                            <td data-label="Judul" class="px-6 py-4">
                                <p class="font-medium text-gray-800">{{ $ticket->title }}</p>
                                <p class="text-xs text-gray-400">{{ $ticket->computer?->code ?? 'Tanpa komputer' }}</p>
                            </td>
                            <td data-label="Laboratorium" class="px-6 py-4 text-gray-600">{{ $ticket->laboratory?->name ?? '-' }}</td>
                            <td data-label="Kategori" class="px-6 py-4 text-gray-600">{{ $ticket->category }}</td>
                            <td data-label="Status" class="px-6 py-4">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $statusColors[$ticket->status] ?? 'bg-gray-100 text-gray-600' }}">
                                    {{ $ticket->status }}
                                </span>
                            </td>
                            <td data-label="Tanggal" class="px-6 py-4 text-gray-500">{{ $ticket->created_at->format('d M Y H:i') }}</td>
                            <td data-label="Aksi" class="px-6 py-4">
                                <a href="{{ route('tickets.show', $ticket) }}" class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-medium text-green-700 bg-green-50 rounded-lg hover:bg-green-100 transition-colors">
                                    Detail
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center text-gray-500">
                                <svg class="w-12 h-12 mx-auto text-gray-300 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <p class="font-medium">Belum ada tiket</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endif
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.9/dist/chart.umd.min.js"></script>
    <script>
        (function () {
            var monthNames = @json($monthNames);
            var chartData = @json($chart);
            var analysisData = @json($ticketAnalysis);
            var ticketsUrl = @json(route('tickets.index'));

            if (typeof Chart === 'undefined') {
                document.querySelectorAll('[data-chart-fallback]').forEach(function (el) { el.classList.add('hidden'); });
                document.querySelectorAll('[data-chart-unavailable]').forEach(function (el) { el.classList.remove('hidden'); });
                return;
            }

            Chart.defaults.font.family = "'Inter', ui-sans-serif, system-ui, sans-serif";
            Chart.defaults.color = '#6b7280';

            if (chartData && chartData.total > 0) {
                var monthlyCtx = document.getElementById('ticketMonthlyChart');
                if (monthlyCtx) {
                    var months = chartData.months;
                    new Chart(monthlyCtx, {
                        type: 'bar',
                        data: {
                            labels: months.map(function (m) { return monthNames[m.month].substring(0, 3); }),
                            datasets: [{
                                data: chartData.counts,
                                backgroundColor: months.map(function (m) {
                                    return m.month === chartData.currentMonth ? '#15803d' : '#22c55e';
                                }),
                                borderRadius: 6,
                                maxBarThickness: 36
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            onClick: function (evt, elements) {
                                if (!elements.length) { return; }
                                var slot = months[elements[0].index];
                                window.location.href = ticketsUrl + '?month=' + slot.month + '&year=' + slot.year;
                            },
                            plugins: {
                                legend: { display: false },
                                tooltip: {
                                    callbacks: {
                                        title: function (items) {
                                            var slot = months[items[0].dataIndex];
                                            return monthNames[slot.month] + ' ' + slot.year;
                                        },
                                        label: function (item) { return item.parsed.y + ' tiket'; }
                                    }
                                }
                            },
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    ticks: { precision: 0 },
                                    grid: { color: '#f3f4f6' }
                                },
                                x: { grid: { display: false } }
                            }
                        }
                    });
                }
            }

            if (analysisData && analysisData.priority && analysisData.priority.total > 0) {
                var priorityCtx = document.getElementById('ticketPriorityChart');
                if (priorityCtx) {
                    new Chart(priorityCtx, {
                        type: 'doughnut',
                        data: {
                            labels: analysisData.priority.labels,
                            datasets: [{
                                data: analysisData.priority.counts,
                                backgroundColor: analysisData.priority.colors,
                                borderWidth: 2,
                                borderColor: '#ffffff'
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            cutout: '62%',
                            plugins: {
                                legend: { display: false },
                                tooltip: {
                                    callbacks: {
                                        label: function (item) {
                                            return item.label + ': ' + item.parsed + ' tiket';
                                        }
                                    }
                                }
                            }
                        }
                    });
                }
            }

            if (analysisData && analysisData.category && analysisData.category.total > 0) {
                var categoryCtx = document.getElementById('ticketCategoryChart');
                if (categoryCtx) {
                    var valueLabels = {
                        id: 'dashboardValueLabels',
                        afterDatasetsDraw: function (chart) {
                            var ctx = chart.ctx;
                            chart.data.datasets.forEach(function (dataset, di) {
                                chart.getDatasetMeta(di).data.forEach(function (bar, i) {
                                    var value = dataset.data[i];
                                    ctx.save();
                                    ctx.fillStyle = '#6b7280';
                                    ctx.font = '600 12px Inter, sans-serif';
                                    ctx.textBaseline = 'middle';
                                    ctx.fillText(String(value), bar.x + 8, bar.y);
                                    ctx.restore();
                                });
                            });
                        }
                    };

                    new Chart(categoryCtx, {
                        type: 'bar',
                        data: {
                            labels: analysisData.category.labels,
                            datasets: [{
                                data: analysisData.category.counts,
                                backgroundColor: analysisData.category.colors,
                                borderRadius: 6,
                                maxBarThickness: 22
                            }]
                        },
                        options: {
                            indexAxis: 'y',
                            responsive: true,
                            maintainAspectRatio: false,
                            layout: { padding: { right: 36 } },
                            plugins: {
                                legend: { display: false },
                                tooltip: {
                                    callbacks: {
                                        label: function (item) { return item.parsed.x + ' tiket'; }
                                    }
                                }
                            },
                            scales: {
                                x: {
                                    beginAtZero: true,
                                    ticks: { precision: 0 },
                                    grid: { color: '#f3f4f6' }
                                },
                                y: { grid: { display: false } }
                            }
                        },
                        plugins: [valueLabels]
                    });
                }
            }
        })();
    </script>
@endpush
