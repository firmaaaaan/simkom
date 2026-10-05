<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Scan Box - {{ $box->code }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body { font-family: 'Inter', system-ui, -apple-system, sans-serif; }
    </style>
</head>
<body class="bg-gray-50 min-h-screen flex items-center justify-center p-4">
    <div class="w-full max-w-md">
        <div class="bg-white rounded-2xl shadow-lg border border-gray-200 overflow-hidden">
            <div class="bg-gradient-to-r from-green-600 to-green-700 px-6 py-4 text-white">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 bg-white/20 rounded-xl flex items-center justify-center">
                        <svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m21 7.5-9-5.25L3 7.5m18 0-9 5.25m9-5.25v9l-9 5.25M3 7.5l9 5.25M3 7.5v9l9 5.25m0-9v9" />
                        </svg>
                    </div>
                    <div>
                        <h1 class="text-xl font-bold">{{ $box->code }}</h1>
                        <p class="text-green-100 text-sm">{{ $box->name }}</p>
                    </div>
                </div>
            </div>

            <div class="p-6 space-y-5">
                <div class="flex items-center gap-2 text-sm text-gray-600">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z" />
                    </svg>
                    <span>{{ $box->location ?? 'Lokasi tidak ditentukan' }}</span>
                </div>

                @if($box->boxComponents->isNotEmpty())
                    <div>
                        <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Komponen dalam Box</h3>
                        <div class="bg-gray-50 rounded-xl p-3 space-y-1">
                            @foreach($box->boxComponents as $bc)
                                <div class="flex items-center justify-between text-sm">
                                    <span class="text-gray-700">{{ $bc->component->name }}</span>
                                    <span class="font-medium text-gray-900">x{{ $bc->quantity }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                <div class="border-t border-gray-100 pt-5">
                    @if($activeUsage)
                        @php
                            $otherActiveUsages = \App\Models\BoxUsage::with('box')
                                ->where('user_nim', $activeUsage->user_nim)
                                ->where('status', 'Using')
                                ->get();
                        @endphp
                        <div class="bg-yellow-50 border border-yellow-200 rounded-xl p-4">
                            <div class="flex items-center gap-2 mb-2">
                                <svg class="w-5 h-5 text-yellow-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
                                </svg>
                                <span class="text-sm font-semibold text-yellow-800">Box Sedang Digunakan</span>
                            </div>
                            <p class="text-sm text-yellow-700">
                                Oleh: <strong>{{ $activeUsage->user_name }}</strong> ({{ $activeUsage->user_nim }})
                            </p>
                            @if($activeUsage->user_kelas)
                                <p class="text-xs text-yellow-600 mt-1">Kelas: {{ $activeUsage->user_kelas }}</p>
                            @endif
                            <p class="text-xs text-yellow-600 mt-1">
                                Sejak {{ $activeUsage->used_at->format('d M Y H:i') }}
                            </p>

                            @if($otherActiveUsages->count() > 1)
                                <div class="mt-3 pt-3 border-t border-yellow-200">
                                    <p class="text-xs font-semibold text-yellow-800 mb-1">Box yang sedang digunakan ({{ $otherActiveUsages->count() }}):</p>
                                    <ul class="space-y-0.5">
                                        @foreach($otherActiveUsages as $other)
                                            <li class="text-xs text-yellow-700 flex items-center gap-1">
                                                <span class="font-mono">{{ $other->box->code }}</span>
                                                <span class="text-yellow-500">—</span>
                                                <span>{{ $other->box->name }}</span>
                                                @if($other->box_id === $box->id)
                                                    <span class="text-[9px] bg-yellow-200 text-yellow-800 px-1 rounded">(ini)</span>
                                                @endif
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif
                            <p class="text-xs text-yellow-600 mt-3 italic">Hubungi admin/lab untuk pengembalian box</p>
                        </div>
                    @else
                        <div class="bg-green-50 border border-green-200 rounded-xl p-4 mb-4">
                            <div class="flex items-center gap-2">
                                <svg class="w-5 h-5 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <span class="text-sm font-semibold text-green-800">Tersedia</span>
                            </div>
                        </div>

                        @if($lastUsageByNim && !$activeUsage)
                            <div class="bg-blue-50 border border-blue-200 rounded-xl p-4">
                                <p class="text-sm text-blue-700 mb-3">
                                    Halo <strong>{{ $lastUsageByNim->user_name }}</strong> ({{ $lastUsageByNim->user_nim }})
                                    <br><span class="text-xs text-blue-600">Anda telah menggunakan box ini sebelumnya.</span>
                                </p>
                                <div id="selectedBoxesInfo" class="hidden mb-3 bg-purple-50 border border-purple-200 rounded-lg px-3 py-2">
                                    <p class="text-xs font-semibold text-purple-700 mb-1">Box terpilih: <span id="selectedCount">1</span></p>
                                    <ul id="selectedBoxesList" class="text-xs text-purple-600 space-y-0.5"></ul>
                                </div>
                                <div class="flex gap-2 mb-3">
                                    <button onclick="openAddBoxModal()" id="addBoxBtnQuick"
                                        class="flex-1 px-3 py-2 bg-purple-100 text-purple-700 text-xs font-semibold rounded-lg hover:bg-purple-200 transition-colors border border-purple-200">
                                        + Tambah Box
                                    </button>
                                </div>
                                <div id="quickUseError" class="hidden bg-red-50 border border-red-200 text-red-700 text-sm px-4 py-3 rounded-xl mb-3"></div>
                                <button onclick="quickUse()" id="quickUseBtn"
                                    class="w-full px-4 py-2.5 bg-blue-600 text-white text-sm font-semibold rounded-lg hover:bg-blue-700 transition-colors">
                                    <span id="quickUseBtnText">Gunakan</span>
                                </button>
                            </div>
                        @else
                            <div id="useFormSection">
                                <h3 class="text-sm font-semibold text-gray-700 mb-3">Isi Data Diri</h3>
                                <form id="useForm" class="space-y-3">
                                    <div>
                                        <label class="block text-xs font-medium text-gray-500 mb-1">Nama Lengkap</label>
                                        <input type="text" name="user_name" required placeholder="Contoh: Budi Santoso"
                                            class="w-full px-4 py-2.5 text-sm border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-medium text-gray-500 mb-1">NIM</label>
                                        <input type="text" name="user_nim" required placeholder="Contoh: 12345678"
                                            class="w-full px-4 py-2.5 text-sm border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-medium text-gray-500 mb-1">Kelas</label>
                                        <input type="text" name="user_kelas" required placeholder="Contoh: TI-2A"
                                            class="w-full px-4 py-2.5 text-sm border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                                    </div>
                                    <div id="selectedBoxesInfo" class="hidden bg-purple-50 border border-purple-200 rounded-lg px-3 py-2">
                                        <p class="text-xs font-semibold text-purple-700 mb-1">Box terpilih: <span id="selectedCount">1</span></p>
                                        <ul id="selectedBoxesList" class="text-xs text-purple-600 space-y-0.5"></ul>
                                    </div>
                                    <div class="flex gap-2">
                                        <button type="button" onclick="openAddBoxModal()" id="addBoxBtnForm"
                                            class="flex-1 px-3 py-2 bg-purple-100 text-purple-700 text-xs font-semibold rounded-lg hover:bg-purple-200 transition-colors border border-purple-200">
                                            + Tambah Box
                                        </button>
                                    </div>
                                    <div id="useError" class="hidden bg-red-50 border border-red-200 text-red-700 text-sm px-4 py-3 rounded-xl"></div>
                                    <button type="submit" id="useBtn"
                                        class="w-full px-4 py-2.5 bg-green-600 text-white text-sm font-semibold rounded-lg hover:bg-green-700 transition-colors">
                                        <span id="useBtnText">Gunakan</span>
                                    </button>
                                </form>
                            </div>
                        @endif
                    @endif
                </div>
            </div>
        </div>

        <p class="text-center text-xs text-gray-400 mt-4">SimKom - Sistem Informasi Laboratorium</p>
    </div>

    {{-- Modal Tambah Box --}}
    <div id="addBoxModal" class="fixed inset-0 bg-black/50 z-50 hidden items-center justify-center p-4" style="display:none">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-md max-h-[85vh] overflow-hidden flex flex-col" onclick="event.stopPropagation()">
            <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-bold text-gray-900">Tambah Box Lain</h3>
                    <p class="text-xs text-gray-500 mt-0.5">Filter prefix: <span id="modalPrefix" class="font-mono font-semibold text-purple-600"></span></p>
                </div>
                <button onclick="closeAddBoxModal()" class="p-2 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-lg transition-colors">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <div class="flex-1 overflow-y-auto p-5">
                <div id="availableBoxesList" class="space-y-2">
                    <p class="text-sm text-gray-400 italic text-center py-4">Memuat data box...</p>
                </div>
            </div>
            <div class="px-5 py-4 border-t border-gray-100">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-xs text-gray-500">Total dipilih:</span>
                    <span id="modalSelectedCount" class="text-sm font-bold text-purple-700">0 box</span>
                </div>
                <div class="flex gap-2">
                    <button onclick="confirmAddBoxes()" id="confirmAddBtn"
                        class="flex-1 px-4 py-2.5 bg-purple-600 text-white text-sm font-semibold rounded-lg hover:bg-purple-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                        Tambahkan
                    </button>
                    <button onclick="closeAddBoxModal()" class="px-4 py-2.5 bg-gray-100 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-200 transition-colors">
                        Batal
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        const boxCode = '{{ $box->code }}';
        const isQuickUseMode = @json($lastUsageByNim && !$activeUsage);
        let selectedExtraBoxes = [];

        function getCsrfToken() {
            return document.querySelector('meta[name="csrf-token"]').content;
        }

        // === Modal Tambah Box ===
        async function openAddBoxModal() {
            const modal = document.getElementById('addBoxModal');
            modal.style.display = 'flex';
            modal.classList.remove('hidden');
            document.getElementById('availableBoxesList').innerHTML = '<p class="text-sm text-gray-400 italic text-center py-4">Memuat data box...</p>';
            document.getElementById('confirmAddBtn').disabled = true;

            try {
                const response = await fetch(`/box-scan/${boxCode}/available`, {
                    headers: { 'Accept': 'application/json' },
                });
                const data = await response.json();

                if (data.success) {
                    document.getElementById('modalPrefix').textContent = `BOX-${data.prefix}-*`;

                    if (data.boxes.length === 0) {
                        document.getElementById('availableBoxesList').innerHTML = '<p class="text-sm text-gray-400 italic text-center py-4">Tidak ada box lain yang tersedia dengan prefix ini.</p>';
                        return;
                    }

                    let html = '';
                    data.boxes.forEach(b => {
                        const checked = selectedExtraBoxes.includes(b.code) ? 'checked' : '';
                        html += `
                            <label class="flex items-center gap-3 cursor-pointer hover:bg-purple-50 p-3 rounded-lg border border-gray-100 transition-colors">
                                <input type="checkbox" value="${b.code}" ${checked}
                                    onchange="toggleBoxSelection('${b.code}', this)"
                                    class="rounded border-gray-300 text-purple-600 focus:ring-purple-500">
                                <div class="flex-1 min-w-0">
                                    <span class="text-sm font-medium text-gray-800 font-mono">${b.code}</span>
                                    <p class="text-xs text-gray-500 truncate">${b.name}${b.location ? ' — ' + b.location : ''}</p>
                                </div>
                            </label>
                        `;
                    });
                    document.getElementById('availableBoxesList').innerHTML = html;
                    updateModalCount();
                }
            } catch (err) {
                document.getElementById('availableBoxesList').innerHTML = '<p class="text-sm text-red-500 text-center py-4">Gagal memuat data box</p>';
            }
        }

        function closeAddBoxModal() {
            const modal = document.getElementById('addBoxModal');
            modal.style.display = 'none';
            modal.classList.add('hidden');
        }

        function toggleBoxSelection(code, checkbox) {
            if (checkbox.checked) {
                if (!selectedExtraBoxes.includes(code)) selectedExtraBoxes.push(code);
            } else {
                selectedExtraBoxes = selectedExtraBoxes.filter(c => c !== code);
            }
            updateModalCount();
        }

        function updateModalCount() {
            const total = selectedExtraBoxes.length;
            document.getElementById('modalSelectedCount').textContent = `${total} box`;
            document.getElementById('confirmAddBtn').disabled = total === 0;
        }

        function confirmAddBoxes() {
            updateSelectedBoxesUI();
            closeAddBoxModal();
        }

        function updateSelectedBoxesUI() {
            const infoEl = document.getElementById('selectedBoxesInfo');
            const countEl = document.getElementById('selectedCount');
            const listEl = document.getElementById('selectedBoxesList');
            const useBtnText = document.getElementById('useBtnText');
            const quickBtnText = document.getElementById('quickUseBtnText');

            const totalCount = selectedExtraBoxes.length + 1;

            if (selectedExtraBoxes.length > 0) {
                infoEl.classList.remove('hidden');
                countEl.textContent = totalCount;
                listEl.innerHTML = `<li class="font-mono">${boxCode} (box ini)</li>` +
                    selectedExtraBoxes.map(c => `<li class="font-mono">${c}</li>`).join('');

                if (useBtnText) useBtnText.textContent = `Gunakan Semua (${totalCount} box)`;
                if (quickBtnText) quickBtnText.textContent = `Gunakan Semua (${totalCount} box)`;
            } else {
                infoEl.classList.add('hidden');
                if (useBtnText) useBtnText.textContent = 'Gunakan';
                if (quickBtnText) quickBtnText.textContent = 'Gunakan';
            }
        }

        // === Multi-use / Single-use submit ===
        document.getElementById('useForm')?.addEventListener('submit', async function(e) {
            e.preventDefault();
            const errorEl = document.getElementById('useError');
            const btn = document.getElementById('useBtn');
            errorEl.classList.add('hidden');
            btn.disabled = true;
            document.getElementById('useBtnText').textContent = 'Memproses...';

            const formData = new FormData(this);
            const isMulti = selectedExtraBoxes.length > 0;

            try {
                let url, body;
                if (isMulti) {
                    url = '/box-scan/multi-use';
                    body = JSON.stringify({
                        box_codes: [boxCode, ...selectedExtraBoxes],
                        user_name: formData.get('user_name'),
                        user_nim: formData.get('user_nim'),
                        user_kelas: formData.get('user_kelas'),
                    });
                } else {
                    url = `/box-scan/${boxCode}/use`;
                    body = formData;
                }

                const response = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': getCsrfToken(),
                        'Accept': 'application/json',
                        ...(isMulti ? { 'Content-Type': 'application/json' } : {}),
                    },
                    body: body,
                });

                const data = await response.json();

                if (data.success) {
                    location.reload();
                } else {
                    errorEl.textContent = data.message || 'Terjadi kesalahan';
                    errorEl.classList.remove('hidden');
                    btn.disabled = false;
                    document.getElementById('useBtnText').textContent = isMulti ? `Gunakan Semua (${selectedExtraBoxes.length + 1} box)` : 'Gunakan';
                }
            } catch (err) {
                errorEl.textContent = 'Terjadi kesalahan jaringan';
                errorEl.classList.remove('hidden');
                btn.disabled = false;
                document.getElementById('useBtnText').textContent = isMulti ? `Gunakan Semua (${selectedExtraBoxes.length + 1} box)` : 'Gunakan';
            }
        });

        // === Quick-use / Multi-quick-use submit ===
        async function quickUse() {
            const btn = document.getElementById('quickUseBtn');
            const errorEl = document.getElementById('quickUseError');
            btn.disabled = true;
            document.getElementById('quickUseBtnText').textContent = 'Memproses...';
            errorEl.classList.add('hidden');

            const isMulti = selectedExtraBoxes.length > 0;

            try {
                let url, body;
                if (isMulti) {
                    url = '/box-scan/multi-quick-use';
                    body = JSON.stringify({
                        nim: '{{ $nim ?? "" }}',
                        box_codes: [boxCode, ...selectedExtraBoxes],
                    });
                } else {
                    url = `/box-scan/${boxCode}/quick-use`;
                    body = JSON.stringify({ nim: '{{ $nim ?? "" }}' });
                }

                const response = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': getCsrfToken(),
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                    },
                    body: body,
                });

                const data = await response.json();

                if (data.success) {
                    location.reload();
                } else {
                    errorEl.textContent = data.message || 'Terjadi kesalahan';
                    errorEl.classList.remove('hidden');
                    btn.disabled = false;
                    document.getElementById('quickUseBtnText').textContent = isMulti ? `Gunakan Semua (${selectedExtraBoxes.length + 1} box)` : 'Gunakan';
                }
            } catch (err) {
                errorEl.textContent = 'Terjadi kesalahan jaringan';
                errorEl.classList.remove('hidden');
                btn.disabled = false;
                document.getElementById('quickUseBtnText').textContent = isMulti ? `Gunakan Semua (${selectedExtraBoxes.length + 1} box)` : 'Gunakan';
            }
        }
    </script>
</body>
</html>
