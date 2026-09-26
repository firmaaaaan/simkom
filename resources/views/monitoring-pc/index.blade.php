@extends('layouts.app')

@section('title', 'Monitoring PC')
@section('header', 'Monitoring PC')

@section('content')
@php
    // Pemetaan warna ambang penggunaan (hijau < 70% <= kuning < 90% <= merah).
    $tone = fn ($value) => $value >= 90 ? 'red' : ($value >= 70 ? 'amber' : 'green');
    $textClass = ['green' => 'text-green-600', 'amber' => 'text-amber-600', 'red' => 'text-red-600'];
    $barClass = ['green' => 'bg-green-500', 'amber' => 'bg-amber-500', 'red' => 'bg-red-500'];
@endphp

{{-- RINGKASAN --}}
<div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-5 mb-6">
    <div class="bg-white rounded-xl border border-gray-200 p-5 flex items-center gap-4">
        <div class="w-12 h-12 bg-green-100 rounded-xl flex items-center justify-center flex-shrink-0">
            <svg class="w-6 h-6 text-green-700" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 17.25v1.007a3 3 0 01-.879 2.122L7.5 21h9l-.621-.621A3 3 0 0115 18.257V17.25m6-12V15a2.25 2.25 0 01-2.25 2.25H5.25A2.25 2.25 0 013 15V5.25m18 0A2.25 2.25 0 0018.75 3H5.25A2.25 2.25 0 003 5.25m18 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 7.41A2.25 2.25 0 012.25 5.496V5.25" />
            </svg>
        </div>
        <div>
            <p class="text-sm text-gray-500">Total PC</p>
            <p class="text-2xl font-bold text-gray-800">{{ $stats['total'] }}</p>
        </div>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 p-5 flex items-center gap-4">
        <div class="w-12 h-12 bg-green-100 rounded-xl flex items-center justify-center flex-shrink-0">
            <svg class="w-6 h-6 text-green-700" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
        </div>
        <div>
            <p class="text-sm text-gray-500">Online</p>
            <p class="text-2xl font-bold text-green-600">{{ $stats['online'] }}</p>
        </div>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 p-5 flex items-center gap-4">
        <div class="w-12 h-12 bg-red-100 rounded-xl flex items-center justify-center flex-shrink-0">
            <svg class="w-6 h-6 text-red-700" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
            </svg>
        </div>
        <div>
            <p class="text-sm text-gray-500">Offline</p>
            <p class="text-2xl font-bold text-red-600">{{ $stats['offline'] }}</p>
        </div>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 p-5">
        <p class="text-sm text-gray-500 mb-2">Rata-rata Penggunaan</p>
        <div class="space-y-1.5">
            <div class="flex items-center justify-between text-sm">
                <span class="text-gray-600">CPU</span>
                <span class="font-semibold {{ $textClass[$tone($stats['avg_cpu'])] }}">{{ number_format($stats['avg_cpu'], 1) }}%</span>
            </div>
            <div class="flex items-center justify-between text-sm">
                <span class="text-gray-600">RAM</span>
                <span class="font-semibold {{ $textClass[$tone($stats['avg_ram'])] }}">{{ number_format($stats['avg_ram'], 1) }}%</span>
            </div>
            <div class="flex items-center justify-between text-sm">
                <span class="text-gray-600">Disk</span>
                <span class="font-semibold {{ $textClass[$tone($stats['avg_disk'])] }}">{{ number_format($stats['avg_disk'], 1) }}%</span>
            </div>
        </div>
    </div>
</div>

{{-- KETERANGAN STATUS --}}
<div class="flex flex-wrap items-center gap-3 mb-6 text-xs">
    <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-green-100 text-green-800 rounded-full font-medium">
        <span class="w-2 h-2 bg-green-500 rounded-full"></span> Online
    </span>
    <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-red-100 text-red-800 rounded-full font-medium">
        <span class="w-2 h-2 bg-red-500 rounded-full"></span> Offline
    </span>
    <span class="text-gray-400">Halaman dimuat ulang otomatis tiap 5 detik.</span>
</div>

{{-- GRID CARD PC --}}
@if($pcs->isEmpty())
    <div class="bg-white rounded-2xl border border-dashed border-gray-300 p-10 text-center">
        <svg class="w-12 h-12 mx-auto text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 17.25v1.007a3 3 0 01-.879 2.122L7.5 21h9l-.621-.621A3 3 0 0115 18.257V17.25m6-12V15a2.25 2.25 0 01-2.25 2.25H5.25A2.25 2.25 0 013 15V5.25m18 0A2.25 2.25 0 0018.75 3H5.25A2.25 2.25 0 003 5.25m18 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 7.41A2.25 2.25 0 012.25 5.496V5.25" />
        </svg>
        <p class="mt-4 font-semibold text-gray-700">Belum ada PC terdaftar</p>
        <p class="text-sm text-gray-500 mt-1">Jalankan agent (<code class="text-xs bg-gray-100 px-1.5 py-0.5 rounded">tools/agent/agent.py</code>) di PC lab agar muncul di sini.</p>
    </div>
@else
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
        @foreach($pcs as $pc)
            @php
                $since = $pc->secondsSinceLastSeen();
                $lastSeen = ! $pc->last_seen_at
                    ? 'Belum pernah terlihat'
                    : ($since < 60
                        ? "Terlihat {$since} detik lalu"
                        : 'Terlihat ' . $pc->last_seen_at->format('d M Y H:i'));
            @endphp
            <div class="bg-white rounded-2xl border p-5 flex flex-col {{ $pc->isOnline() ? 'border-green-200' : 'border-red-200' }}">
                <div class="flex items-start justify-between mb-4">
                    <div class="w-11 h-11 rounded-xl flex items-center justify-center {{ $pc->isOnline() ? 'bg-green-100' : 'bg-red-100' }}">
                        <svg class="w-6 h-6 {{ $pc->isOnline() ? 'text-green-600' : 'text-red-600' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 17.25v1.007a3 3 0 01-.879 2.122L7.5 21h9l-.621-.621A3 3 0 0115 18.257V17.25m6-12V15a2.25 2.25 0 01-2.25 2.25H5.25A2.25 2.25 0 013 15V5.25m18 0A2.25 2.25 0 0018.75 3H5.25A2.25 2.25 0 003 5.25m18 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 7.41A2.25 2.25 0 012.25 5.496V5.25" />
                        </svg>
                    </div>
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-medium {{ $pc->isOnline() ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                        <span class="w-1.5 h-1.5 rounded-full {{ $pc->isOnline() ? 'bg-green-500' : 'bg-red-500' }}"></span>
                        {{ $pc->isOnline() ? 'Online' : 'Offline' }}
                    </span>
                </div>

                <h3 class="text-lg font-bold text-gray-900 truncate">{{ $pc->hostname }}</h3>
                <p class="text-sm text-gray-500 font-mono">{{ $pc->ip_address }}</p>
                @if($pc->mac_address)
                    <p class="text-xs text-gray-400 font-mono truncate">{{ $pc->mac_address }}</p>
                @endif

                <div class="mt-3 flex items-center gap-1.5 text-sm text-gray-600">
                    <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
                    </svg>
                    <span class="truncate">{{ $pc->active_user ?? 'Tidak ada user' }}</span>
                </div>

                <div class="mt-4 space-y-2.5">
                    @foreach([
                        'CPU' => $pc->cpu_usage,
                        'RAM' => $pc->ram_usage,
                        'Disk' => $pc->disk_usage,
                    ] as $label => $usage)
                        @php $toneKey = $tone($usage); @endphp
                        <div>
                            <div class="flex items-center justify-between text-[11px] mb-1">
                                <span class="text-gray-500">{{ $label }}</span>
                                <span class="font-semibold {{ $textClass[$toneKey] }}">{{ number_format($usage, 1) }}%</span>
                            </div>
                            <div class="h-1.5 bg-gray-100 rounded-full overflow-hidden">
                                <div class="h-full rounded-full {{ $barClass[$toneKey] }}" style="width: {{ min(100, max(0, (int) round($usage))) }}%"></div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <p class="mt-4 pt-3 border-t border-gray-100 text-[11px] text-gray-400">{{ $lastSeen }}</p>
            </div>
        @endforeach
    </div>
@endif

@push('scripts')
<script>
    // Auto-reload agar status online/offline & penggunaan selalu terbaru.
    setTimeout(function () { window.location.reload(); }, 5000);
</script>
@endpush
@endsection
