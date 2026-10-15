@extends('layouts.app')

@section('title', 'Monitoring PC')
@section('header', 'Monitoring PC')

@section('content')
<div x-data="monitoringPanel(@js($payload))" x-cloak>

    {{-- TOAST HASIL AKSI POWER --}}
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
            <div class="w-12 h-12 bg-green-100 rounded-xl flex items-center justify-center flex-shrink-0">
                <svg class="w-6 h-6 text-green-700" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 17.25v1.007a3 3 0 01-.879 2.122L7.5 21h9l-.621-.621A3 3 0 0115 18.257V17.25m-6-15H5.25A2.25 2.25 0 003 4.5v15a2.25 2.25 0 002.25 2.25h13.5A2.25 2.25 0 0021 19.5V9.75m-18 0h18M9 3h6" />
                </svg>
            </div>
            <div>
                <p class="text-sm text-gray-500">Total PC</p>
                <p class="text-2xl font-bold text-gray-800" x-text="stats.total"></p>
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
                <p class="text-2xl font-bold text-green-600" x-text="stats.online"></p>
            </div>
        </div>

        <div class="bg-white rounded-xl border border-gray-200 p-5 flex items-center gap-4">
            <div class="w-12 h-12 bg-red-100 rounded-xl flex items-center justify-center flex-shrink-0">
                <svg class="w-6 h-6 text-red-700" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 0L5.636 5.636" />
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

    {{-- KETERANGAN STATUS --}}
    <div class="flex flex-wrap items-center gap-3 mb-6 text-xs">
        <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-green-100 text-green-800 rounded-full font-medium">
            <span class="w-2 h-2 bg-green-500 rounded-full"></span> Online
        </span>
        <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-red-100 text-red-800 rounded-full font-medium">
            <span class="w-2 h-2 bg-red-500 rounded-full"></span> Offline
        </span>
        <span class="text-gray-400">
            Diperbarui otomatis tiap 5 detik &mdash; <span x-text="refreshLabel()"></span>
        </span>
    </div>

    {{-- EMPTY STATE --}}
    <template x-if="pcs.length === 0">
        <div class="bg-white rounded-2xl border border-dashed border-gray-300 p-10 text-center">
            <svg class="w-12 h-12 mx-auto text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 17.25v1.007a3 3 0 01-.879 2.122L7.5 21h9l-.621-.621A3 3 0 0115 18.257V17.25m-6-15H5.25A2.25 2.25 0 003 4.5v15a2.25 2.25 0 002.25 2.25h13.5A2.25 2.25 0 0021 19.5V9.75m-18 0h18M9 3h6" />
            </svg>
            <p class="mt-4 font-semibold text-gray-700">Belum ada PC terdaftar</p>
            <p class="text-sm text-gray-500 mt-1">Jalankan agent (<code class="text-xs bg-gray-100 px-1.5 py-0.5 rounded">tools/agent/agent.py</code> atau <code class="text-xs bg-gray-100 px-1.5 py-0.5 rounded">SimKomAgent.exe</code>) di PC lab agar muncul di sini.</p>
        </div>
    </template>

    {{-- GRID CARD PC --}}
    <div x-show="pcs.length > 0" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
        <template x-for="pc in pcs" :key="pc.id">
            <div class="bg-white rounded-2xl border p-5 flex flex-col"
                 :class="pc.status === 'online' ? 'border-green-200' : 'border-red-200'">

                <div class="flex items-start justify-between mb-4">
                    <div class="w-11 h-11 rounded-xl flex items-center justify-center"
                         :class="pc.status === 'online' ? 'bg-green-100' : 'bg-red-100'">
                        <svg class="w-6 h-6" :class="pc.status === 'online' ? 'text-green-600' : 'text-red-600'" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 17.25v1.007a3 3 0 01-.879 2.122L7.5 21h9l-.621-.621A3 3 0 0115 18.257V17.25m-6-15H5.25A2.25 2.25 0 003 4.5v15a2.25 2.25 0 002.25 2.25h13.5A2.25 2.25 0 0021 19.5V9.75m-18 0h18M9 3h6" />
                        </svg>
                    </div>
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-medium"
                          :class="pc.status === 'online' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'">
                        <span class="w-1.5 h-1.5 rounded-full" :class="pc.status === 'online' ? 'bg-green-500' : 'bg-red-500'"></span>
                        <span x-text="pc.status === 'online' ? 'Online' : 'Offline'"></span>
                    </span>
                </div>

                <h3 class="text-lg font-bold text-gray-900 truncate" x-text="pc.hostname"></h3>
                <p class="text-sm text-gray-500 font-mono" x-text="pc.ip_address"></p>
                <p class="text-xs text-gray-400 font-mono truncate" x-show="pc.mac_address" x-text="pc.mac_address"></p>

                <div class="mt-3 flex items-center gap-1.5 text-sm text-gray-600">
                    <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
                    </svg>
                    <span class="truncate" x-text="pc.active_user || 'Tidak ada user'"></span>
                </div>

                <div class="mt-4 space-y-2.5">
                    <div>
                        <div class="flex items-center justify-between text-[11px] mb-1">
                            <span class="text-gray-500">CPU</span>
                            <span class="font-semibold" :class="textClass[tone(pc.cpu_usage)]" x-text="pc.cpu_usage.toFixed(1) + '%'"></span>
                        </div>
                        <div class="h-1.5 bg-gray-100 rounded-full overflow-hidden">
                            <div class="h-full rounded-full transition-all duration-500" :class="barClass[tone(pc.cpu_usage)]" :style="'width:' + barWidth(pc.cpu_usage) + '%'"></div>
                        </div>
                    </div>
                    <div>
                        <div class="flex items-center justify-between text-[11px] mb-1">
                            <span class="text-gray-500">RAM</span>
                            <span class="font-semibold" :class="textClass[tone(pc.ram_usage)]" x-text="pc.ram_usage.toFixed(1) + '%'"></span>
                        </div>
                        <div class="h-1.5 bg-gray-100 rounded-full overflow-hidden">
                            <div class="h-full rounded-full transition-all duration-500" :class="barClass[tone(pc.ram_usage)]" :style="'width:' + barWidth(pc.ram_usage) + '%'"></div>
                        </div>
                    </div>
                    <div>
                        <div class="flex items-center justify-between text-[11px] mb-1">
                            <span class="text-gray-500">Disk</span>
                            <span class="font-semibold" :class="textClass[tone(pc.disk_usage)]" x-text="pc.disk_usage.toFixed(1) + '%'"></span>
                        </div>
                        <div class="h-1.5 bg-gray-100 rounded-full overflow-hidden">
                            <div class="h-full rounded-full transition-all duration-500" :class="barClass[tone(pc.disk_usage)]" :style="'width:' + barWidth(pc.disk_usage) + '%'"></div>
                        </div>
                    </div>
                </div>

                {{-- KONTROL POWER (hanya pemegang control-monitoring) --}}
                <div x-show="canPower" class="mt-4 pt-3 border-t border-gray-100 flex flex-wrap items-center gap-2">
                    <template x-if="pc.pending_power">
                        <button type="button" disabled class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold bg-amber-100 text-amber-700 cursor-not-allowed">
                            <svg class="w-3.5 h-3.5 animate-spin" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                            </svg>
                            Menunggu agent…
                        </button>
                    </template>

                    <template x-if="!pc.pending_power && pc.status === 'online'">
                        <button type="button" @click="powerOff(pc)" :disabled="busy[pc.id]"
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold bg-red-50 text-red-600 border border-red-200 hover:bg-red-600 hover:text-white hover:border-red-600 transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5.636 5.636a9 9 0 1012.728 0M12 3v9" />
                            </svg>
                            <span x-text="busy[pc.id] ? 'Mengirim…' : 'Matikan'"></span>
                        </button>
                    </template>

                    <template x-if="!pc.pending_power && pc.status === 'offline'">
                        <button type="button" @click="powerOn(pc)" :disabled="busy[pc.id] || !pc.mac_address"
                                :title="pc.mac_address ? '' : 'MAC address belum diketahui — jalankan agent dulu'"
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold bg-green-50 text-green-700 border border-green-200 hover:bg-green-600 hover:text-white hover:border-green-600 transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5.636 5.636a9 9 0 1012.728 0" />
                            </svg>
                            <span x-text="busy[pc.id] ? 'Mengirim…' : 'Nyalakan'"></span>
                        </button>
                    </template>
                </div>

                <p class="mt-4 pt-3 border-t border-gray-100 text-[11px] text-gray-400" x-text="lastSeenText(pc)"></p>
            </div>
        </template>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('monitoringPanel', (payload) => ({
            stats: payload.stats,
            pcs: payload.pcs,
            canPower: payload.can_power,
            updatedAt: payload.generated_at,
            toast: null,
            busy: {},
            toastTimer: null,

            textClass: { green: 'text-green-600', amber: 'text-amber-600', red: 'text-red-600' },
            barClass: { green: 'bg-green-500', amber: 'bg-amber-500', red: 'bg-red-500' },

            init() {
                setInterval(() => this.refresh(), 5000);
            },

            // Pemetaan warna ambang penggunaan (hijau < 70% <= kuning < 90% <= merah).
            tone(value) {
                return value >= 90 ? 'red' : (value >= 70 ? 'amber' : 'green');
            },

            barWidth(value) {
                return Math.min(100, Math.max(0, Math.round(value)));
            },

            refreshLabel() {
                if (!this.updatedAt) return '—';
                return 'diperbarui ' + new Date(this.updatedAt).toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
            },

            lastSeenText(pc) {
                if (!pc.last_seen_at) return 'Belum pernah terlihat';
                const s = pc.seconds_since_last_seen;
                if (s < 60) return 'Terlihat ' + s + ' detik lalu';
                if (s < 3600) return 'Terlihat ' + Math.floor(s / 60) + ' menit lalu';
                return 'Terlihat ' + new Date(pc.last_seen_at).toLocaleString('id-ID', {
                    day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit',
                });
            },

            async refresh() {
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
                    // Server tidak terjangkau — coba lagi pada siklus berikutnya.
                }
            },

            async powerOn(pc) {
                if (this.busy[pc.id]) return;
                this.busy[pc.id] = true;
                const result = await this.postAction(pc, 'power-on');
                delete this.busy[pc.id];
                this.showToast(result);
                if (result.ok) await this.refresh();
            },

            async powerOff(pc) {
                if (this.busy[pc.id]) return;
                if (!confirm('Matikan ' + pc.hostname + '? PC akan shutdown dalam ±15 detik bila agent berjalan.')) return;
                this.busy[pc.id] = true;
                const result = await this.postAction(pc, 'power-off');
                delete this.busy[pc.id];
                this.showToast(result);
                if (result.ok) {
                    pc.pending_power = true;
                    await this.refresh();
                }
            },

            postAction(pc, action) {
                const url = '{{ url('/monitoring-pc') }}/' + pc.id + '/' + action;
                return fetch(url, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                    },
                })
                    .then(async (res) => {
                        const data = await res.json().catch(() => ({}));
                        return {
                            ok: res.ok && data.ok !== false,
                            message: data.message || 'Permintaan gagal diproses.',
                        };
                    })
                    .catch(() => ({ ok: false, message: 'Tidak bisa terhubung ke server.' }));
            },

            showToast(result) {
                this.toast = result;
                clearTimeout(this.toastTimer);
                this.toastTimer = setTimeout(() => { this.toast = null; }, 6000);
            },
        }));
    });
</script>
@endpush
