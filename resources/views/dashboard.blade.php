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
@endphp

@section('content')
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
                <p class="text-sm text-gray-500">Pengecekan Bulan Ini</p>
                <p class="text-2xl font-bold text-gray-800">{{ $stats['checks_this_month'] }}</p>
                <p class="text-xs text-gray-400 mt-0.5">{{ $monthNames[(int) now()->month] }} {{ now()->year }}</p>
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
                            {{ $chart['total'] }} tiket tercatat pada tahun {{ $chart['selectedYear'] }}
                        </p>
                    </div>
                    <form action="{{ route('dashboard') }}" method="GET" class="flex items-center gap-2">
                        <select name="year" onchange="this.form.submit()"
                            class="text-sm border border-gray-200 rounded-lg px-3 py-1.5 text-gray-600 focus:outline-none focus:ring-2 focus:ring-green-500">
                            @foreach($chart['years'] as $year)
                                <option value="{{ $year }}" {{ $chart['selectedYear'] === $year ? 'selected' : '' }}>{{ $year }}</option>
                            @endforeach
                        </select>
                        <noscript>
                            <button type="submit" class="px-3 py-1.5 text-sm font-medium text-white bg-green-600 rounded-lg">Tampilkan</button>
                        </noscript>
                    </form>
                </div>

                @if($chart['total'] > 0)
                    <div class="flex items-end gap-2 sm:gap-3 h-56">
                        @foreach($chart['counts'] as $monthNumber => $value)
                            <a href="{{ route('tickets.index', ['month' => $monthNumber, 'year' => $chart['selectedYear']]) }}"
                               class="flex-1 h-full flex flex-col items-center justify-end gap-1.5 group"
                               title="Klik untuk melihat {{ $value }} tiket {{ $monthNames[$monthNumber] }} {{ $chart['selectedYear'] }}">
                                <span class="text-xs font-semibold {{ $value > 0 ? 'text-gray-600' : 'text-gray-300' }}">{{ $value }}</span>
                                <div class="w-full rounded-t-lg transition-all {{ $value > 0 ? 'bg-green-500 group-hover:bg-green-700' : 'bg-gray-100 group-hover:bg-gray-300' }}"
                                     style="height: {{ $value > 0 ? max(6, round($value / $chart['max'] * 84)) : 4 }}%"></div>
                                <span class="text-xs {{ $chart['currentMonth'] === $monthNumber ? 'font-bold text-green-700' : 'text-gray-500' }} group-hover:text-green-700 group-hover:font-semibold">
                                    {{ mb_substr($monthNames[$monthNumber], 0, 3) }}
                                </span>
                            </a>
                        @endforeach
                    </div>
                    <p class="text-xs text-gray-400 mt-3 text-center">Klik batang untuk melihat daftar tiket bulan tersebut.</p>
                @else
                    <div class="h-56 flex flex-col items-center justify-center text-gray-400">
                        <svg class="w-10 h-10 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z" />
                        </svg>
                        <p class="text-sm">Belum ada tiket pada tahun {{ $chart['selectedYear'] }}</p>
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
                <p class="text-xs text-gray-500 mt-1">{{ $stats['tickets_resolved'] }} tiket sudah selesai (Resolved/Closed).</p>
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
