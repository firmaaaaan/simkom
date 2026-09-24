@php
    $borrowStatusFilter = request('borrow_status');
    $borrowSearch = request('borrow_search');
@endphp

<div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
    <div class="px-6 py-4 border-b border-gray-100">
        <h2 class="text-base font-semibold text-gray-800">Pinjam Komponen</h2>
        <p class="text-sm text-gray-500 mt-1">Riwayat peminjaman komponen per mahasiswa</p>
    </div>

    <div class="px-6 py-4 border-b border-gray-100 bg-gray-50">
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-4">
            <a href="{{ route('components.index', ['tab' => 'borrowings']) }}"
               class="bg-white border border-gray-200 rounded-lg p-3 hover:shadow-sm transition-shadow {{ !$borrowStatusFilter ? 'ring-2 ring-green-400 border-green-400' : '' }}">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 bg-blue-100 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-xl font-bold text-gray-900">{{ $borrowingStats['total'] ?? 0 }}</p>
                        <p class="text-xs text-gray-500">Total</p>
                    </div>
                </div>
            </a>
            <a href="{{ route('components.index', ['tab' => 'borrowings', 'borrow_status' => 'Using']) }}"
               class="bg-white border border-gray-200 rounded-lg p-3 hover:shadow-sm transition-shadow {{ $borrowStatusFilter === 'Using' ? 'ring-2 ring-yellow-400 border-yellow-400' : '' }}">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 bg-yellow-100 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-yellow-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 7.5l-2.25-1.313M21 7.5v2.25m0-2.25l-2.25 1.313M3 7.5l2.25-1.313M3 7.5l2.25 1.313M3 7.5v2.25m9 3l2.25-1.313M12 12.75l-2.25-1.313M12 12.75V15m0 6.75l2.25-1.313M12 21.75V19.5m0 2.25l-2.25-1.313m0-16.875L12 2.25l2.25 1.313M21 14.25v2.25l-2.25 1.313m-13.5 0L3 16.5v-2.25" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-xl font-bold text-yellow-600">{{ $borrowingStats['using'] ?? 0 }}</p>
                        <p class="text-xs text-gray-500">Dipinjam</p>
                    </div>
                </div>
            </a>
            <a href="{{ route('components.index', ['tab' => 'borrowings', 'borrow_status' => 'Returned']) }}"
               class="bg-white border border-gray-200 rounded-lg p-3 hover:shadow-sm transition-shadow {{ $borrowStatusFilter === 'Returned' ? 'ring-2 ring-green-400 border-green-400' : '' }}">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 bg-green-100 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-xl font-bold text-green-600">{{ $borrowingStats['returned'] ?? 0 }}</p>
                        <p class="text-xs text-gray-500">Dikembalikan</p>
                    </div>
                </div>
            </a>
        </div>

        <form action="{{ route('components.index') }}" method="GET" class="flex items-end gap-3">
            <input type="hidden" name="tab" value="borrowings">
            <div class="flex-1">
                <label class="block text-xs font-medium text-gray-500 mb-1">Cari Pengguna</label>
                <div class="relative">
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                    </svg>
                    <input type="text" name="borrow_search" value="{{ $borrowSearch ?? '' }}" placeholder="Nama atau NIM..."
                        class="w-full pl-9 pr-4 py-2.5 text-sm border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                </div>
            </div>
            <div class="w-48">
                <label class="block text-xs font-medium text-gray-500 mb-1">Status</label>
                <select name="borrow_status" class="w-full px-4 py-2.5 text-sm border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                    <option value="">Semua Status</option>
                    <option value="Using" {{ ($borrowStatusFilter ?? '') === 'Using' ? 'selected' : '' }}>Sedang Dipinjam</option>
                    <option value="Returned" {{ ($borrowStatusFilter ?? '') === 'Returned' ? 'selected' : '' }}>Sudah Dikembalikan</option>
                </select>
            </div>
            <button type="submit" class="px-4 py-2.5 bg-green-600 text-white text-sm font-semibold rounded-lg hover:bg-green-700 transition-colors">
                Filter
            </button>
            @if($borrowSearch || $borrowStatusFilter)
                <a href="{{ route('components.index', ['tab' => 'borrowings']) }}" class="px-4 py-2.5 bg-gray-100 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-200 transition-colors">
                    Reset
                </a>
            @endif
        </form>
    </div>

    <div class="p-6">
        @if(($borrowings ?? collect())->isEmpty())
            <div class="text-center py-12">
                <svg class="w-16 h-16 mx-auto text-gray-300 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <p class="text-gray-500 text-sm">Belum ada riwayat peminjaman komponen</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm table-responsive-cards">
                    <thead>
                        <tr class="border-b border-gray-100">
                            <th class="text-left px-4 py-3 font-medium text-gray-500">Tanggal Pinjam</th>
                            <th class="text-left px-4 py-3 font-medium text-gray-500">Komponen</th>
                            <th class="text-center px-4 py-3 font-medium text-gray-500">Qty</th>
                            <th class="text-left px-4 py-3 font-medium text-gray-500">NIM</th>
                            <th class="text-left px-4 py-3 font-medium text-gray-500">Nama</th>
                            <th class="text-center px-4 py-3 font-medium text-gray-500">Status</th>
                            <th class="text-left px-4 py-3 font-medium text-gray-500">Catatan</th>
                            <th class="text-center px-4 py-3 font-medium text-gray-500">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($borrowings as $borrowing)
                            <tr class="border-b border-gray-50 hover:bg-gray-50/50 transition-colors">
                                <td data-label="Tanggal Pinjam" class="px-4 py-3">
                                    <div class="text-sm text-gray-900">{{ $borrowing->borrowed_at->format('d M Y') }}</div>
                                </td>
                                <td data-label="Komponen" class="px-4 py-3">
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-mono font-bold bg-gray-100 text-gray-700">
                                        {{ $borrowing->component?->code }}
                                    </span>
                                    <div class="text-xs text-gray-500 mt-0.5">{{ $borrowing->component?->name }}</div>
                                </td>
                                <td data-label="Qty" class="px-4 py-3 text-center">{{ $borrowing->quantity }}</td>
                                <td data-label="NIM" class="px-4 py-3 text-gray-600 font-mono text-xs">{{ $borrowing->user_nim }}</td>
                                <td data-label="Nama" class="px-4 py-3 font-medium text-gray-900">{{ $borrowing->user_name }}</td>
                                <td data-label="Status" class="px-4 py-3 text-center">
                                    @if($borrowing->status === 'Using')
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-700">
                                            Sedang Dipinjam
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-green-100 text-green-700">
                                            Dikembalikan
                                        </span>
                                    @endif
                                </td>
                                <td data-label="Catatan" class="px-4 py-3 text-gray-600 text-xs">
                                    @if($borrowing->status === 'Using')
                                        <input type="text" class="borrow-return-note w-full min-w-[140px] text-xs px-2 py-1.5 border border-gray-200 rounded-lg focus:ring-1 focus:ring-yellow-500 focus:border-yellow-500"
                                            data-borrowing-id="{{ $borrowing->id }}" placeholder="Catatan (opsional)...">
                                    @else
                                        {{ $borrowing->return_note ?? '-' }}
                                    @endif
                                </td>
                                <td data-label="Aksi" class="px-4 py-3 text-center">
                                    @if($borrowing->status === 'Using')
                                        <button type="button" onclick="returnBorrowing('{{ $borrowing->id }}', this)"
                                            class="px-3 py-1.5 bg-yellow-600 text-white text-xs font-semibold rounded-lg hover:bg-yellow-700 transition-colors">
                                            Kembalikan
                                        </button>
                                    @else
                                        <span class="text-gray-400 text-xs">—</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="py-12 text-center text-gray-400">
                                    Tidak ada data ditemukan
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($borrowings->hasPages())
                <div class="mt-4 pt-4 border-t border-gray-100">
                    {{ $borrowings->links() }}
                </div>
            @endif
        @endif
    </div>
</div>

@push('scripts')
<script>
    async function returnBorrowing(borrowingId, btn) {
        const noteInput = document.querySelector(`.borrow-return-note[data-borrowing-id="${borrowingId}"]`);
        const originalText = btn.textContent;
        btn.disabled = true;
        btn.textContent = '...';
        try {
            const response = await fetch(`/component-borrowings/${borrowingId}/return`, {
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
                showBorrowToast('success', 'Berhasil', data.message || 'Komponen dikembalikan');
                setTimeout(() => location.reload(), 600);
            } else {
                showBorrowToast('error', 'Gagal', data.message || 'Gagal mengembalikan komponen');
                btn.disabled = false;
                btn.textContent = originalText;
            }
        } catch (err) {
            showBorrowToast('error', 'Kesalahan', 'Terjadi kesalahan jaringan');
            btn.disabled = false;
            btn.textContent = originalText;
        }
    }

    function showBorrowToast(type, title, message) {
        let container = document.getElementById('borrowToastContainer');
        if (!container) {
            container = document.createElement('div');
            container.id = 'borrowToastContainer';
            container.className = 'fixed bottom-6 right-6 z-[100] max-w-sm';
            container.innerHTML = `
                <div id="borrowToast" class="bg-white rounded-xl shadow-2xl border p-4 flex items-start gap-3 hidden transition-all duration-300">
                    <div id="borrowToastIcon" class="w-8 h-8 rounded-lg flex items-center justify-center flex-shrink-0"></div>
                    <div class="flex-1 min-w-0">
                        <p id="borrowToastTitle" class="text-sm font-semibold text-gray-900"></p>
                        <p id="borrowToastMsg" class="text-sm text-gray-600 mt-0.5"></p>
                    </div>
                    <button onclick="document.getElementById('borrowToast').classList.add('hidden')" class="text-gray-400 hover:text-gray-600 flex-shrink-0">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
            `;
            document.body.appendChild(container);
        }
        const toast = document.getElementById('borrowToast');
        const icon = document.getElementById('borrowToastIcon');
        const colors = { error: 'bg-red-100', success: 'bg-green-100' };
        const icons = {
            error: '<svg class="w-5 h-5 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/></svg>',
            success: '<svg class="w-5 h-5 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>'
        };
        icon.className = 'w-8 h-8 rounded-lg flex items-center justify-center flex-shrink-0 ' + (colors[type] || colors.error);
        icon.innerHTML = icons[type] || icons.error;
        document.getElementById('borrowToastTitle').textContent = title;
        document.getElementById('borrowToastMsg').textContent = message;
        toast.classList.remove('hidden');
        clearTimeout(window._borrowToastTimer);
        window._borrowToastTimer = setTimeout(() => toast.classList.add('hidden'), 3500);
    }
</script>
@endpush
