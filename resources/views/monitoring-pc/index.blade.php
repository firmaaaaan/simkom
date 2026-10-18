@extends('layouts.app')

@section('title', 'Monitoring PC')
@section('header', 'Monitoring PC')

@section('content')
<div x-data="monitoringPanel(@js($payload))" x-cloak>

    {{-- TOAST HASIL AKSI --}}
    <div x-show="toast" x-transition.opacity class="mb-4" x-cloak>
        <div class="rounded-xl border px-4 py-3 text-sm font-medium flex items-start justify-between gap-3"
             :class="toast && toast.ok ? 'bg-green-50 text-green-700 border-green-200' : 'bg-red-50 text-red-700 border-red-200'">
            <span x-text="toast && toast.message"></span>
            <button type="button" @click="toast = null" class="text-current opacity-60 hover:opacity-100 shrink-0" aria-label="Tutup">&times;</button>
        </div>
    </div>

    {{-- RINGKASAN --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-5 mb-6">
        <div class="bg-white rounded-xl border border-gray-200 p-5 flex items-center gap-4">
            <div class="w-12 h-12 bg-green-100 rounded-xl flex items-center justify-center shrink-0">
                <svg class="w-6 h-6 text-green-700" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 17.25v1.007a3 3 0 01-.879 2.122L7.5 21h9l-.621-.621A3 3 0 0115 18.257V17.25m6-12V15a2.25 2.25 0 01-2.25 2.25H5.25A2.25 2.25 0 013 15V5.25m18 0A2.25 2.25 0 0018.75 3H5.25A2.25 2.25 0 003 5.25m18 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 7.41A2.25 2.25 0 012.25 5.496V5.25" />
                </svg>
            </div>
            <div>
                <p class="text-sm text-gray-500">Total PC</p>
                <p class="text-2xl font-bold text-gray-800" x-text="stats.total"></p>
            </div>
        </div>

        <div class="bg-white rounded-xl border border-gray-200 p-5 flex items-center gap-4">
            <div class="w-12 h-12 bg-green-100 rounded-xl flex items-center justify-center shrink-0">
                <svg class="w-6 h-6 text-green-700" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <div>
                <p class="text-sm text-gray-500">Online</p>
                <p class="text-2xl font-bold text-green-600" x-text="stats.online"></p>
            </div>
        </div>

        <div class="bg-white rounded-xl border border-gray-200 p-5 flex items-center gap-4">
            <div class="w-12 h-12 bg-red-100 rounded-xl flex items-center justify-center shrink-0">
                <svg class="w-6 h-6 text-red-700" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 005.636 5.636m12.728 0L5.636 5.636" />
                </svg>
            </div>
            <div>
                <p class="text-sm text-gray-500">Offline</p>
                <p class="text-2xl font-bold text-red-600" x-text="stats.offline"></p>
            </div>
        </div>

        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <p class="text-sm text-gray-500 mb-2">Rata-rata Penggunaan</p>
            <div class="space-y-1.5">
                <div class="flex items-center justify-between text-sm">
                    <span>CPU</span>
                    <span class="font-semibold" :class="textClass[tone(stats.avg_cpu)]" x-text="stats.avg_cpu.toFixed(1) + '%'"></span>
                </div>
                <div class="flex items-center justify-between text-sm">
                    <span>RAM</span>
                    <span class="font-semibold" :class="textClass[tone(stats.avg_ram)]" x-text="stats.avg_ram.toFixed(1) + '%'"></span>
                </div>
                <div class="flex items-center justify-between text-sm">
                    <span>Disk</span>
                    <span class="font-semibold" :class="textClass[tone(stats.avg_disk)]" x-text="stats.avg_disk.toFixed(1) + '%'"></span>
                </div>
            </div>
        </div>
    </div>

    {{-- DAFTAR PC --}}
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-200 flex flex-wrap items-center justify-between gap-3">
            <div>
                <h2 class="text-sm font-semibold text-gray-800">Status PC Lab</h2>
                <p class="text-xs text-gray-500 mt-0.5">
                    Diperbarui otomatis tiap 5 detik &middot; <span x-text="relTime(secondsSince(updatedAt))"></span>
                </p>
            </div>
            <span class="inline-flex items-center gap-1.5 text-xs font-medium text-green-700 bg-green-50 border border-green-200 px-2.5 py-1 rounded-full">
                <span class="w-1.5 h-1.5 rounded-full bg-green-500 animate-pulse"></span> Live
            </span>
        </div>

        <template x-if="pcs.length === 0">
            <div class="px-6 py-12 text-center">
                <svg class="w-12 h-12 mx-auto text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 17.25v1.007a3 3 0 01-.879 2.122L7.5 21h9l-.621-.621A3 3 0 0115 18.257V17.25m6-12V15a2.25 2.25 0 01-2.25 2.25H5.25A2.25 2.25 0 013 15V5.25m18 0A2.25 2.25 0 0018.75 3H5.25A2.25 2.25 0 003 5.25m18 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 7.41A2.25 2.25 0 012.25 5.496V5.25" />
                </svg>
                <p class="text-gray-500 text-sm mt-3">Belum ada PC yang terhubung.</p>
                <p class="text-gray-400 text-xs mt-1">Jalankan agent SimKom di PC lab agar status dan penggunaan hardware muncul di sini.</p>
            </div>
        </template>

        <template x-if="pcs.length > 0">
            <div class="p-5 grid grid-cols-5 gap-4">
                <template x-for="pc in pcs" :key="pc.id">
                    <div class="rounded-xl border p-4 flex flex-col gap-4"
                         :class="pc.status === 'online' ? 'border-green-200 bg-green-50/40' : 'border-gray-200 bg-white'">

                        {{-- Kepala card: PC + status --}}
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0">
                                <p class="font-semibold text-gray-800 truncate" x-text="pc.hostname"></p>
                                <p class="text-xs text-gray-500 mt-0.5 truncate">
                                    <span x-text="pc.ip_address"></span>
                                    <template x-if="pc.mac_address"> &middot; <span x-text="pc.mac_address"></span></template>
                                </p>
                            </div>
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium gap-1 shrink-0"
                                  :class="pc.status === 'online' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'">
                                <span class="w-1.5 h-1.5 rounded-full" :class="pc.status === 'online' ? 'bg-green-500 animate-pulse' : 'bg-red-500'"></span>
                                <span x-text="pc.status === 'online' ? 'Online' : 'Offline'"></span>
                            </span>
                        </div>

                        {{-- Pengguna + terakhir terlihat --}}
                        <div class="grid grid-cols-2 gap-3 text-xs">
                            <div>
                                <p class="text-gray-500">Pengguna</p>
                                <p class="font-medium text-gray-800 mt-0.5 truncate" x-text="pc.active_user || '—'"></p>
                            </div>
                            <div>
                                <p class="text-gray-500">Terakhir Terlihat</p>
                                <p class="font-medium text-gray-800 mt-0.5" x-text="relTime(pc.seconds_since_last_seen)"></p>
                            </div>
                        </div>

                        {{-- Penggunaan hardware --}}
                        <div class="space-y-2.5">
                            <div>
                                <div class="flex items-center justify-between text-xs mb-1">
                                    <span class="text-gray-600">CPU</span>
                                    <span class="font-semibold" :class="textClass[tone(pc.cpu_usage)]" x-text="pc.cpu_usage.toFixed(1) + '%'"></span>
                                </div>
                                <div class="h-2 bg-gray-100 rounded-full overflow-hidden">
                                    <div class="h-2 rounded-full transition-all duration-500" :class="barClass[tone(pc.cpu_usage)]" :style="'width:' + pc.cpu_usage + '%'"></div>
                                </div>
                            </div>
                            <div>
                                <div class="flex items-center justify-between text-xs mb-1">
                                    <span class="text-gray-600">RAM</span>
                                    <span class="font-semibold" :class="textClass[tone(pc.ram_usage)]" x-text="pc.ram_usage.toFixed(1) + '%'"></span>
                                </div>
                                <div class="h-2 bg-gray-100 rounded-full overflow-hidden">
                                    <div class="h-2 rounded-full transition-all duration-500" :class="barClass[tone(pc.ram_usage)]" :style="'width:' + pc.ram_usage + '%'"></div>
                                </div>
                            </div>
                            <div>
                                <div class="flex items-center justify-between text-xs mb-1">
                                    <span class="text-gray-600">Disk</span>
                                    <span class="font-semibold" :class="textClass[tone(pc.disk_usage)]" x-text="pc.disk_usage.toFixed(1) + '%'"></span>
                                </div>
                                <div class="h-2 bg-gray-100 rounded-full overflow-hidden">
                                    <div class="h-2 rounded-full transition-all duration-500" :class="barClass[tone(pc.disk_usage)]" :style="'width:' + pc.disk_usage + '%'"></div>
                                </div>
                            </div>
                        </div>

                        {{-- Screenshot terakhir --}}
                        <template x-if="pc.screenshot_url">
                            <button type="button" @click="openScreenshot(pc)" class="block w-full group" title="Perbesar screenshot">
                                <img :src="pc.screenshot_url" alt="Screenshot layar"
                                     class="w-full h-24 object-cover rounded-lg border border-gray-200 group-hover:opacity-85 transition-opacity" loading="lazy">
                            </button>
                        </template>

                        {{-- Inventaris software --}}
                        <button type="button" @click="openSoftware(pc)"
                                class="text-left text-xs text-gray-600 hover:text-green-700 transition-colors flex items-center gap-1.5">
                            <svg class="w-3.5 h-3.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z" />
                            </svg>
                            <span x-text="pc.software_count + ' aplikasi'"></span>
                            <template x-if="pc.software_synced_at">
                                <span class="text-gray-400" x-text="'· sync ' + relTime(secondsSince(pc.software_synced_at))"></span>
                            </template>
                        </button>

                        {{-- Aksi --}}
                        <div class="mt-auto pt-3 border-t border-gray-100 flex items-center gap-2" x-show="canPower">
                            <template x-if="pc.pending_command">
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-medium bg-amber-50 text-amber-700 border border-amber-200">
                                    <svg class="w-3.5 h-3.5 animate-spin" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                                    </svg>
                                    Menunggu agent
                                </span>
                            </template>
                            <template x-if="!pc.pending_command && pc.status === 'online'">
                                <div class="relative w-full" x-data="{ menu: false }" @click.outside="menu = false">
                                    <button type="button" @click="menu = !menu" :disabled="busy === pc.id"
                                            class="w-full inline-flex items-center justify-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-medium bg-white text-gray-700 border border-gray-200 hover:bg-gray-50 disabled:opacity-50 transition-colors">
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.75a.75.75 0 110-1.5.75.75 0 010 1.5zM12 12.75a.75.75 0 110-1.5.75.75 0 010 1.5zM12 18.75a.75.75 0 110-1.5.75.75 0 010 1.5z" />
                                        </svg>
                                        Aksi
                                        <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                                        </svg>
                                    </button>
                                    <div x-show="menu" x-transition x-cloak
                                         class="absolute right-0 bottom-full mb-1 w-44 bg-white border border-gray-200 rounded-lg shadow-lg z-20 py-1">
                                        <button type="button" @click="menu = false; command('shutdown', pc)"
                                                class="w-full text-left px-3 py-2 text-xs text-red-600 hover:bg-red-50 flex items-center gap-2">
                                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 10-7.5 0v4.5m11.356-1.993l1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 01-1.12-1.243l1.264-12A1.125 1.125 0 015.513 7.5h12.974c.576 0 1.059.435 1.119 1.007z" />
                                            </svg>
                                            Matikan
                                        </button>
                                        <button type="button" @click="menu = false; command('restart', pc)"
                                                class="w-full text-left px-3 py-2 text-xs text-amber-600 hover:bg-amber-50 flex items-center gap-2">
                                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99" />
                                            </svg>
                                            Restart
                                        </button>
                                        <button type="button" @click="menu = false; command('lock', pc)"
                                                class="w-full text-left px-3 py-2 text-xs text-gray-700 hover:bg-gray-50 flex items-center gap-2">
                                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" />
                                            </svg>
                                            Kunci Layar
                                        </button>
                                        <button type="button" @click="menu = false; command('logout', pc)"
                                                class="w-full text-left px-3 py-2 text-xs text-gray-700 hover:bg-gray-50 flex items-center gap-2">
                                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15m3 0l3-3m0 0l-3-3m3 3H9" />
                                            </svg>
                                            Keluarkan User
                                        </button>
                                        <button type="button" @click="menu = false; command('screenshot', pc)"
                                                class="w-full text-left px-3 py-2 text-xs text-blue-600 hover:bg-blue-50 flex items-center gap-2">
                                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M6.827 6.175A2.31 2.31 0 015.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 00-1.134-.175 2.31 2.31 0 01-1.64-1.055l-.822-1.316a2.192 2.192 0 00-1.736-1.039 48.774 48.774 0 00-5.232 0 2.192 2.192 0 00-1.736 1.039l-.821 1.316z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 12.75a4.5 4.5 0 11-9 0 4.5 4.5 0 019 0z" />
                                            </svg>
                                            Ambil Layar
                                        </button>
                                    </div>
                                </div>
                            </template>
                            <template x-if="!pc.pending_command && pc.status === 'offline' && pc.mac_address">
                                <button type="button" @click="powerOn(pc)" :disabled="busy === pc.id"
                                        class="w-full inline-flex items-center justify-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-medium bg-green-50 text-green-700 border border-green-200 hover:bg-green-100 disabled:opacity-50 transition-colors">
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M5.636 5.636a9 9 0 1012.728 0M12 3v9" />
                                    </svg>
                                    Nyalakan
                                </button>
                            </template>
                            <template x-if="!pc.pending_command && pc.status === 'offline' && !pc.mac_address">
                                <span class="text-xs text-gray-400">MAC belum diketahui</span>
                            </template>
                        </div>
                    </div>
                </template>
            </div>
        </template>
    </div>

    {{-- MODAL SCREENSHOT --}}
    <div x-show="screenshotModal" x-transition.opacity class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/70" x-cloak>
        <div class="bg-white rounded-xl overflow-hidden max-w-4xl w-full" @click.outside="screenshotModal = null">
            <div class="px-4 py-3 border-b border-gray-200 flex items-center justify-between">
                <div>
                    <p class="text-sm font-semibold text-gray-800" x-text="screenshotModal && screenshotModal.hostname"></p>
                    <p class="text-xs text-gray-500">Screenshot layar terakhir dari agent</p>
                </div>
                <button type="button" @click="screenshotModal = null" class="text-gray-400 hover:text-gray-600" aria-label="Tutup">&times;</button>
            </div>
            <img x-show="screenshotModal" :src="screenshotModal && screenshotModal.url" alt="Screenshot layar PC"
                 class="w-full max-h-[70vh] object-contain bg-gray-900">
        </div>
    </div>

    {{-- MODAL SOFTWARE --}}
    <div x-show="softwareModal" x-transition.opacity class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/70" x-cloak>
        <div class="bg-white rounded-xl overflow-hidden max-w-3xl w-full flex flex-col max-h-[85vh]" @click.outside="softwareModal = null">
            <div class="px-4 py-3 border-b border-gray-200 flex items-center justify-between gap-3">
                <div class="min-w-0">
                    <p class="text-sm font-semibold text-gray-800 truncate">
                        Software — <span x-text="softwareModal && softwareModal.hostname"></span>
                    </p>
                    <p class="text-xs text-gray-500">
                        <span x-text="softwareModal && softwareModal.items ? softwareModal.items.length + ' aplikasi' : 'Memuat…'"></span>
                        <template x-if="softwareModal && softwareModal.synced_at">
                            &middot; <span x-text="'sync ' + relTime(secondsSince(softwareModal.synced_at))"></span>
                        </template>
                    </p>
                </div>
                <button type="button" @click="softwareModal = null" class="text-gray-400 hover:text-gray-600 shrink-0" aria-label="Tutup">&times;</button>
            </div>
            <div class="px-4 py-3 border-b border-gray-100">
                <input type="search" x-model="softwareSearch" placeholder="Cari nama software…"
                       class="w-full rounded-lg border-gray-200 text-sm focus:border-green-500 focus:ring-green-500">
            </div>
            <div class="overflow-y-auto flex-1">
                <p x-show="softwareModal && softwareModal.loading" class="px-4 py-8 text-center text-sm text-gray-500">Memuat daftar software…</p>
                <p x-show="softwareModal && softwareModal.error" class="px-4 py-8 text-center text-sm text-red-600" x-text="softwareModal && softwareModal.error"></p>
                <table x-show="softwareModal && softwareModal.items" class="w-full text-sm">
                    <thead class="bg-gray-50 text-xs uppercase text-gray-500 sticky top-0">
                        <tr>
                            <th class="px-4 py-2.5 text-left font-semibold">Nama</th>
                            <th class="px-4 py-2.5 text-left font-semibold">Versi</th>
                            <th class="px-4 py-2.5 text-left font-semibold">Penerbit</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <template x-for="(item, index) in (softwareModal && softwareModal.items ? softwareModal.items.filter(i => !softwareSearch || (i.name || '').toLowerCase().includes(softwareSearch.toLowerCase())) : [])" :key="index">
                            <tr>
                                <td class="px-4 py-2 text-gray-800" x-text="item.name"></td>
                                <td class="px-4 py-2 text-gray-600" x-text="item.version || '—'"></td>
                                <td class="px-4 py-2 text-gray-600" x-text="item.publisher || '—'"></td>
                            </tr>
                        </template>
                        <tr x-show="softwareModal && softwareModal.items && softwareModal.items.filter(i => !softwareSearch || (i.name || '').toLowerCase().includes(softwareSearch.toLowerCase())).length === 0">
                            <td colspan="3" class="px-4 py-8 text-center text-sm text-gray-500">Tidak ada software yang cocok.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('monitoringPanel', (initial) => ({
            stats: initial.stats,
            pcs: initial.pcs,
            canPower: initial.can_power,
            updatedAt: initial.generated_at,
            toast: null,
            busy: null,
            softwareModal: null,
            softwareSearch: '',
            screenshotModal: null,
            textClass: { green: 'text-green-600', yellow: 'text-amber-600', red: 'text-red-600' },
            barClass: { green: 'bg-green-500', yellow: 'bg-amber-400', red: 'bg-red-500' },

            init() {
                this.poll();
                setInterval(() => this.poll(), 5000);
            },

            async poll() {
                try {
                    const res = await fetch('{{ route('monitoring-pc.data') }}', {
                        headers: { 'Accept': 'application/json' },
                    });
                    if (!res.ok) return;
                    const data = await res.json();
                    this.stats = data.stats;
                    this.pcs = data.pcs;
                    this.canPower = data.can_power;
                    this.updatedAt = data.generated_at;
                } catch (e) {
                    // server sementara tidak terjangkau — polling berikutnya mencoba lagi.
                }
            },

            tone(value) {
                if (value >= 85) return 'red';
                if (value >= 60) return 'yellow';
                return 'green';
            },

            async command(action, pc) {
                if (this.busy) return;
                this.busy = pc.id;
                try {
                    const res = await fetch('{{ url('/') }}/monitoring-pc/' + pc.id + '/command', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json',
                            'Content-Type': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                        body: JSON.stringify({ action: action }),
                    });
                    const body = await res.json().catch(() => ({}));
                    this.toast = { ok: !!body.ok, message: body.message || 'Permintaan gagal diproses.' };
                } catch (e) {
                    this.toast = { ok: false, message: 'Tidak dapat terhubung ke server.' };
                } finally {
                    this.busy = null;
                    this.poll();
                }
            },

            async powerOn(pc) {
                if (this.busy) return;
                this.busy = pc.id;
                try {
                    const res = await fetch('{{ url('/') }}/monitoring-pc/' + pc.id + '/power-on', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                    });
                    const body = await res.json().catch(() => ({}));
                    this.toast = { ok: !!body.ok, message: body.message || 'Permintaan gagal diproses.' };
                } catch (e) {
                    this.toast = { ok: false, message: 'Tidak dapat terhubung ke server.' };
                } finally {
                    this.busy = null;
                    this.poll();
                }
            },

            openScreenshot(pc) {
                this.screenshotModal = { hostname: pc.hostname, url: pc.screenshot_url };
            },

            async openSoftware(pc) {
                this.softwareSearch = '';
                this.softwareModal = { hostname: pc.hostname, synced_at: pc.software_synced_at, items: null, loading: true, error: null };
                try {
                    const res = await fetch('{{ url('/') }}/monitoring-pc/' + pc.id + '/software', {
                        headers: { 'Accept': 'application/json' },
                    });
                    if (!res.ok) throw new Error();
                    const data = await res.json();
                    this.softwareModal = {
                        hostname: data.hostname,
                        synced_at: data.synced_at,
                        items: data.items,
                        loading: false,
                        error: null,
                    };
                } catch (e) {
                    this.softwareModal = {
                        hostname: pc.hostname,
                        synced_at: null,
                        items: null,
                        loading: false,
                        error: 'Gagal memuat daftar software.',
                    };
                }
            },

            secondsSince(iso) {
                if (!iso) return null;
                return Math.max(0, Math.floor((Date.now() - new Date(iso).getTime()) / 1000));
            },

            relTime(seconds) {
                if (seconds === null || seconds === undefined) return 'belum pernah';
                if (seconds < 5) return 'baru saja';
                if (seconds < 60) return seconds + ' detik lalu';
                if (seconds < 3600) return Math.floor(seconds / 60) + ' menit lalu';
                if (seconds < 86400) return Math.floor(seconds / 3600) + ' jam lalu';
                return Math.floor(seconds / 86400) + ' hari lalu';
            },
        }));
    });
</script>
@endpush
