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

                        {{-- Aksi --}}
                        <div class="mt-auto pt-3 border-t border-gray-100 flex items-center gap-2" x-show="canPower">
                            <template x-if="pc.pending_power">
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-medium bg-amber-50 text-amber-700 border border-amber-200">
                                    <svg class="w-3.5 h-3.5 animate-spin" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                                    </svg>
                                    Menunggu agent
                                </span>
                            </template>
                            <template x-if="!pc.pending_power && pc.status === 'online'">
                                <button type="button" @click="powerOff(pc)" :disabled="busy === pc.id"
                                        class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-medium bg-red-50 text-red-700 border border-red-200 hover:bg-red-100 disabled:opacity-50 transition-colors">
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 10-7.5 0v4.5m11.356-1.993l1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 01-1.12-1.243l1.264-12A1.125 1.125 0 015.513 7.5h12.974c.576 0 1.059.435 1.119 1.007z" />
                                    </svg>
                                    Matikan
                                </button>
                            </template>
                            <template x-if="!pc.pending_power && pc.status === 'offline' && pc.mac_address">
                                <button type="button" @click="powerOn(pc)" :disabled="busy === pc.id"
                                        class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-medium bg-green-50 text-green-700 border border-green-200 hover:bg-green-100 disabled:opacity-50 transition-colors">
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M5.636 5.636a9 9 0 1012.728 0M12 3v9" />
                                    </svg>
                                    Nyalakan
                                </button>
                            </template>
                            <template x-if="!pc.pending_power && pc.status === 'offline' && !pc.mac_address">
                                <span class="text-xs text-gray-400">MAC belum diketahui</span>
                            </template>
                        </div>
                    </div>
                </template>
            </div>
        </template>
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

            async power(action, pc) {
                if (this.busy) return;
                this.busy = pc.id;
                try {
                    const res = await fetch('{{ url('/') }}/monitoring-pc/' + pc.id + '/' + action, {
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

            powerOn(pc) { return this.power('power-on', pc); },
            powerOff(pc) { return this.power('power-off', pc); },

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
