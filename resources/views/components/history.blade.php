@php
    $statusFilter = request('usage_status');
    $search = request('usage_search');
    $boxFilter = request('usage_box');
@endphp

<div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
    <div class="px-6 py-4 border-b border-gray-100">
        <h2 class="text-base font-semibold text-gray-800">Riwayat Penggunaan Box</h2>
        <p class="text-sm text-gray-500 mt-1">Pantau penggunaan box oleh mahasiswa</p>
    </div>

    <div class="px-6 py-4 border-b border-gray-100 bg-gray-50">
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-4">
            <a href="{{ route('components.index', ['tab' => 'history']) }}"
               class="bg-white border border-gray-200 rounded-lg p-3 hover:shadow-sm transition-shadow {{ !$statusFilter ? 'ring-2 ring-green-400 border-green-400' : '' }}">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 bg-blue-100 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-xl font-bold text-gray-900">{{ $usageStats['total'] }}</p>
                        <p class="text-xs text-gray-500">Total</p>
                    </div>
                </div>
            </a>
            <a href="{{ route('components.index', ['tab' => 'history', 'usage_status' => 'Using']) }}"
               class="bg-white border border-gray-200 rounded-lg p-3 hover:shadow-sm transition-shadow {{ $statusFilter === 'Using' ? 'ring-2 ring-yellow-400 border-yellow-400' : '' }}">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 bg-yellow-100 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-yellow-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 7.5l-2.25-1.313M21 7.5v2.25m0-2.25l-2.25 1.313M3 7.5l2.25-1.313M3 7.5l2.25 1.313M3 7.5v2.25m9 3l2.25-1.313M12 12.75l-2.25-1.313M12 12.75V15m0 6.75l2.25-1.313M12 21.75V19.5m0 2.25l-2.25-1.313m0-16.875L12 2.25l2.25 1.313M21 14.25v2.25l-2.25 1.313m-13.5 0L3 16.5v-2.25" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-xl font-bold text-yellow-600">{{ $usageStats['using'] }}</p>
                        <p class="text-xs text-gray-500">Dipakai</p>
                    </div>
                </div>
            </a>
            <a href="{{ route('components.index', ['tab' => 'history', 'usage_status' => 'Returned']) }}"
               class="bg-white border border-gray-200 rounded-lg p-3 hover:shadow-sm transition-shadow {{ $statusFilter === 'Returned' ? 'ring-2 ring-green-400 border-green-400' : '' }}">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 bg-green-100 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-xl font-bold text-green-600">{{ $usageStats['returned'] }}</p>
                        <p class="text-xs text-gray-500">Dikembalikan</p>
                    </div>
                </div>
            </a>
        </div>

        <form action="{{ route('components.index') }}" method="GET" class="flex items-end gap-3">
            <input type="hidden" name="tab" value="history">
            <div class="flex-1">
                <label class="block text-xs font-medium text-gray-500 mb-1">Cari Pengguna</label>
                <div class="relative">
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                    </svg>
                    <input type="text" name="usage_search" value="{{ $search ?? '' }}" placeholder="Nama atau NIM..."
                        class="w-full pl-9 pr-4 py-2.5 text-sm border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                </div>
            </div>
            <div class="w-48">
                <label class="block text-xs font-medium text-gray-500 mb-1">Box</label>
                <select name="usage_box" class="w-full px-4 py-2.5 text-sm border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                    <option value="">Semua Box</option>
                    @foreach($boxes as $box)
                        <option value="{{ $box->id }}" {{ ($boxFilter ?? '') == $box->id ? 'selected' : '' }}>{{ $box->code }} - {{ $box->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="w-48">
                <label class="block text-xs font-medium text-gray-500 mb-1">Status</label>
                <select name="usage_status" class="w-full px-4 py-2.5 text-sm border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                    <option value="">Semua Status</option>
                    <option value="Using" {{ ($statusFilter ?? '') === 'Using' ? 'selected' : '' }}>Sedang Dipakai</option>
                    <option value="Returned" {{ ($statusFilter ?? '') === 'Returned' ? 'selected' : '' }}>Sudah Dikembalikan</option>
                </select>
            </div>
            <button type="submit" class="px-4 py-2.5 bg-green-600 text-white text-sm font-semibold rounded-lg hover:bg-green-700 transition-colors">
                Filter
            </button>
            @if($search || $statusFilter || $boxFilter)
                <a href="{{ route('components.index', ['tab' => 'history']) }}" class="px-4 py-2.5 bg-gray-100 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-200 transition-colors">
                    Reset
                </a>
            @endif
        </form>
    </div>

    <div class="p-6">
        @if($usages->isEmpty())
            <div class="text-center py-12">
                <svg class="w-16 h-16 mx-auto text-gray-300 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <p class="text-gray-500 text-sm">Belum ada riwayat penggunaan</p>
            </div>
        @else
            @php
                $hasUsing = $usages->contains('status', 'Using');
                $isAdmin = auth()->check() && auth()->user()->hasRole('admin');
            @endphp

            @if($hasUsing && $isAdmin)
                <div class="px-6 py-3 border-b border-gray-100 bg-red-50/50 flex items-center justify-between gap-3 flex-wrap">
                    <label class="flex items-center gap-2 cursor-pointer text-sm text-gray-700">
                        <input type="checkbox" id="selectAllUsing" onchange="toggleSelectAllUsing(this)"
                            class="rounded border-gray-300 text-red-600 focus:ring-red-500">
                        Pilih Semua (yang sedang dipakai)
                    </label>
                    <div class="flex items-center gap-3">
                        <div id="bulkReturnError" class="hidden text-xs text-red-600 bg-red-50 border border-red-200 px-3 py-1.5 rounded-lg"></div>
                        <button onclick="bulkReturnChecked()" id="bulkReturnBtn" disabled
                            class="px-4 py-2 bg-red-600 text-white text-sm font-semibold rounded-lg hover:bg-red-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                            Kembalikan yang Dicentang (0)
                        </button>
                    </div>
                </div>
            @endif

            <div class="overflow-x-auto">
                <table class="w-full text-sm table-responsive-cards">
                    <thead>
                        <tr class="border-b border-gray-100">
                            @if($hasUsing && $isAdmin)
                                <th class="px-4 py-3 w-10"><input type="checkbox" onchange="toggleSelectAllUsing(this)" class="rounded border-gray-300 text-red-600 focus:ring-red-500"></th>
                            @endif
                            <th class="text-left px-4 py-3 font-medium text-gray-500">Waktu Pakai</th>
                            <th class="text-left px-4 py-3 font-medium text-gray-500">Box</th>
                            <th class="text-left px-4 py-3 font-medium text-gray-500">Pengguna</th>
                            <th class="text-left px-4 py-3 font-medium text-gray-500">NIM</th>
                            <th class="text-left px-4 py-3 font-medium text-gray-500">Kelas</th>
                            <th class="text-center px-4 py-3 font-medium text-gray-500">Status</th>
                            <th class="text-center px-4 py-3 font-medium text-gray-500">Durasi</th>
                            <th class="text-left px-4 py-3 font-medium text-gray-500">Catatan</th>
                            @if($isAdmin)
                                <th class="text-center px-4 py-3 font-medium text-gray-500">Aksi</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($usages as $usage)
                            <tr class="border-b border-gray-50 hover:bg-gray-50/50 transition-colors">
                                @if($hasUsing && $isAdmin)
                                    <td class="px-4 py-3">
                                        @if($usage->status === 'Using')
                                            <input type="checkbox" class="usage-return-cb rounded border-gray-300 text-red-600 focus:ring-red-500"
                                                data-usage-id="{{ $usage->id }}" onchange="updateBulkReturnBtn()">
                                        @endif
                                    </td>
                                @endif
                                <td data-label="Waktu Pakai" class="px-4 py-3">
                                    <div class="text-sm text-gray-900">{{ $usage->used_at->format('d M Y') }}</div>
                                    <div class="text-xs text-gray-500">{{ $usage->used_at->format('H:i') }}</div>
                                </td>
                                <td data-label="Box" class="px-4 py-3">
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-mono font-bold bg-gray-100 text-gray-700">
                                        {{ $usage->box->code }}
                                    </span>
                                    <div class="text-xs text-gray-500 mt-0.5">{{ $usage->box->name }}</div>
                                </td>
                                <td data-label="Pengguna" class="px-4 py-3 font-medium text-gray-900">{{ $usage->user_name }}</td>
                                <td data-label="NIM" class="px-4 py-3 text-gray-600 font-mono text-xs">{{ $usage->user_nim }}</td>
                                <td data-label="Kelas" class="px-4 py-3 text-gray-600 text-sm">{{ $usage->user_kelas ?? '-' }}</td>
                                <td data-label="Status" class="px-4 py-3 text-center">
                                    @if($usage->status === 'Using')
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-700">
                                            Sedang Dipakai
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-green-100 text-green-700">
                                            Dikembalikan
                                        </span>
                                    @endif
                                </td>
                                <td data-label="Durasi" class="px-4 py-3 text-center text-sm text-gray-600">
                                    {{ $usage->duration }}
                                </td>
                                <td data-label="Catatan" class="px-4 py-3 text-gray-600 text-xs">
                                    @if($usage->status === 'Using' && $isAdmin)
                                        <input type="text" class="usage-return-note w-full min-w-[140px] text-xs px-2 py-1.5 border border-gray-200 rounded-lg focus:ring-1 focus:ring-red-500 focus:border-red-500"
                                            data-usage-id="{{ $usage->id }}" placeholder="Catatan (opsional)...">
                                    @else
                                        {{ $usage->returnNote?->note ?? '-' }}
                                    @endif
                                </td>
                                @if($isAdmin)
                                    <td data-label="Aksi" class="px-4 py-3 text-center">
                                        @if($usage->status === 'Using')
                                            <button onclick="returnSingleUsage('{{ $usage->id }}', this)"
                                                class="px-3 py-1.5 bg-yellow-600 text-white text-xs font-semibold rounded-lg hover:bg-yellow-700 transition-colors">
                                                Kembalikan
                                            </button>
                                        @else
                                            <span class="text-gray-400 text-xs">—</span>
                                        @endif
                                    </td>
                                @endif
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ ($hasUsing && $isAdmin ? 1 : 0) + ($isAdmin ? 9 : 8) }}" class="py-12 text-center text-gray-400">
                                    Tidak ada data ditemukan
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($usages->hasPages())
                <div class="mt-4 pt-4 border-t border-gray-100">
                    {{ $usages->links() }}
                </div>
            @endif
        @endif
    </div>
</div>

@push('scripts')
<script>
    function toggleSelectAllUsing(master) {
        document.querySelectorAll('.usage-return-cb').forEach(cb => {
            cb.checked = master.checked;
        });
        updateBulkReturnBtn();
    }

    function updateBulkReturnBtn() {
        const checked = document.querySelectorAll('.usage-return-cb:checked');
        const btn = document.getElementById('bulkReturnBtn');
        const masters = document.querySelectorAll('#selectAllUsing, thead input[type="checkbox"]');
        masters.forEach(m => {
            const total = document.querySelectorAll('.usage-return-cb').length;
            m.checked = total > 0 && checked.length === total;
        });
        if (btn) {
            btn.textContent = `Kembalikan yang Dicentang (${checked.length})`;
            btn.disabled = checked.length === 0;
        }
    }

    function showHistoryToast(type, title, message) {
        let container = document.getElementById('historyToastContainer');
        if (!container) {
            container = document.createElement('div');
            container.id = 'historyToastContainer';
            container.className = 'fixed bottom-6 right-6 z-[100] max-w-sm';
            container.innerHTML = `
                <div id="historyToast" class="bg-white rounded-xl shadow-2xl border p-4 flex items-start gap-3 hidden transition-all duration-300">
                    <div id="historyToastIcon" class="w-8 h-8 rounded-lg flex items-center justify-center flex-shrink-0"></div>
                    <div class="flex-1 min-w-0">
                        <p id="historyToastTitle" class="text-sm font-semibold text-gray-900"></p>
                        <p id="historyToastMsg" class="text-sm text-gray-600 mt-0.5"></p>
                    </div>
                    <button onclick="document.getElementById('historyToast').classList.add('hidden')" class="text-gray-400 hover:text-gray-600 flex-shrink-0">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
            `;
            document.body.appendChild(container);
        }
        const toast = document.getElementById('historyToast');
        const icon = document.getElementById('historyToastIcon');
        const colors = { error: 'bg-red-100', warning: 'bg-yellow-100', success: 'bg-green-100' };
        const icons = {
            error: '<svg class="w-5 h-5 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/></svg>',
            success: '<svg class="w-5 h-5 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>'
        };
        icon.className = 'w-8 h-8 rounded-lg flex items-center justify-center flex-shrink-0 ' + (colors[type] || colors.error);
        icon.innerHTML = icons[type] || icons.error;
        document.getElementById('historyToastTitle').textContent = title;
        document.getElementById('historyToastMsg').textContent = message;
        toast.classList.remove('hidden');
        clearTimeout(window._historyToastTimer);
        window._historyToastTimer = setTimeout(() => toast.classList.add('hidden'), 3500);
    }

    async function bulkReturnChecked() {
        const btn = document.getElementById('bulkReturnBtn');
        const errorEl = document.getElementById('bulkReturnError');
        const items = [];
        document.querySelectorAll('.usage-return-cb:checked').forEach(cb => {
            const id = cb.getAttribute('data-usage-id');
            const noteInput = document.querySelector(`.usage-return-note[data-usage-id="${id}"]`);
            items.push({ usage_id: id, note: noteInput ? noteInput.value.trim() : '' });
        });
        if (items.length === 0) return;
        btn.disabled = true;
        btn.textContent = 'Memproses...';
        errorEl.classList.add('hidden');
        try {
            const response = await fetch('/box-scan/multi-return', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ items }),
            });
            const data = await response.json();
            if (data.success) {
                showHistoryToast('success', 'Berhasil', data.message);
                setTimeout(() => location.reload(), 600);
            } else {
                errorEl.textContent = data.message || 'Terjadi kesalahan';
                errorEl.classList.remove('hidden');
                btn.disabled = false;
                updateBulkReturnBtn();
            }
        } catch (err) {
            errorEl.textContent = 'Terjadi kesalahan jaringan';
            errorEl.classList.remove('hidden');
            btn.disabled = false;
            updateBulkReturnBtn();
        }
    }

    async function returnSingleUsage(usageId, btn) {
        const noteInput = document.querySelector(`.usage-return-note[data-usage-id="${usageId}"]`);
        const originalText = btn.textContent;
        btn.disabled = true;
        btn.textContent = '...';
        try {
            const response = await fetch(`/box-scan/return/${usageId}`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ note: noteInput ? noteInput.value.trim() : '' }),
            });
            const data = await response.json();
            if (data.success) {
                showHistoryToast('success', 'Berhasil', data.message || 'Box dikembalikan');
                setTimeout(() => location.reload(), 600);
            } else {
                showHistoryToast('error', 'Gagal', data.message || 'Gagal mengembalikan box');
                btn.disabled = false;
                btn.textContent = originalText;
            }
        } catch (err) {
            showHistoryToast('error', 'Kesalahan', 'Terjadi kesalahan jaringan');
            btn.disabled = false;
            btn.textContent = originalText;
        }
    }
</script>
@endpush
