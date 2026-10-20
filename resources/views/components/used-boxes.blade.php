@php
    $allComponents = \App\Models\Component::orderBy('name')->get();
    $noComponentIds = $usedBoxes->where('components_count', 0)->pluck('id')->values()->all();
@endphp

<div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
    <div class="px-6 py-4 border-b border-gray-100 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
            <h2 class="text-base font-semibold text-gray-800">Box Yang Pernah Digunakan</h2>
            <p class="text-sm text-gray-500">Satu baris per kode box (unit fisik) — tanpa duplikat. {{ $usedBoxStats['total'] }} box pernah dipakai, {{ $usedBoxStats['without_components'] }} belum ada komponen.</p>
        </div>
        <div class="flex items-center gap-2">
            <x-export-button permission="export-components" route="boxes.export-used" label="Export" />
            @can('create-components')
            <button type="button" onclick="openUsedBulkModal()" class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-colors">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
                Tambah Komponen (Bulk)
            </button>
            @endcan
        </div>
    </div>

    <div class="p-6">
        @if($usedBoxes->isEmpty())
            <div class="text-center py-12">
                <svg class="w-16 h-16 mx-auto text-gray-300 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5m8.25 3v6.75m0 0l-3-3m3 3l3-3M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z" />
                </svg>
                <p class="text-gray-500 text-sm">Belum ada box yang pernah digunakan</p>
                <p class="text-gray-400 text-xs mt-1">Rangkuman muncul setelah ada pemindaian QR atau input manual</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm table-responsive-cards">
                    <thead>
                        <tr class="border-b border-gray-100">
                            @can('create-components')
                            <th class="hidden md:table-cell text-left px-4 py-3 font-medium text-gray-500 w-10">
                                <input type="checkbox" class="js-used-select-all rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                                    onchange="usedToggleSelectAll(this)">
                            </th>
                            @endcan
                            <th class="text-left px-4 py-3 font-medium text-gray-500">Kode</th>
                            <th class="text-left px-4 py-3 font-medium text-gray-500">Nama</th>
                            <th class="text-left px-4 py-3 font-medium text-gray-500">Lokasi</th>
                            <th class="text-center px-4 py-3 font-medium text-gray-500">Jumlah Pemakaian</th>
                            <th class="text-left px-4 py-3 font-medium text-gray-500">Terakhir Dipakai</th>
                            <th class="text-center px-4 py-3 font-medium text-gray-500">Komponen</th>
                            @can('create-components')
                            <th class="text-center px-4 py-3 font-medium text-gray-500">Aksi</th>
                            @endcan
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($usedBoxes as $box)
                            <tr class="border-b border-gray-50 hover:bg-gray-50/50 transition-colors">
                                @can('create-components')
                                <td class="hidden md:table-cell px-4 py-3">
                                    <input type="checkbox" name="used_box_ids[]" value="{{ $box->id }}"
                                        class="js-used-box-cb rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                                        onchange="updateUsedBulkSummary()">
                                </td>
                                @endcan
                                <td data-label="Kode" class="px-4 py-3">
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-mono font-bold bg-gray-100 text-gray-700">
                                        {{ $box->code }}
                                    </span>
                                </td>
                                <td data-label="Nama" class="px-4 py-3 font-medium text-gray-900">{{ $box->name }}</td>
                                <td data-label="Lokasi" class="px-4 py-3 text-gray-600">{{ $box->location ?? '-' }}</td>
                                <td data-label="Jumlah Pemakaian" class="px-4 py-3 text-center">
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-700">
                                        {{ $box->usage_count }}x
                                    </span>
                                </td>
                                <td data-label="Terakhir Dipakai" class="px-4 py-3 text-gray-700">
                                    @if($box->last_used_at)
                                        <span class="font-medium">{{ \Illuminate\Support\Carbon::parse($box->last_used_at)->format('d M Y H:i') }}</span>
                                        <br><span class="text-xs text-gray-400">{{ \Illuminate\Support\Carbon::parse($box->last_used_at)->diffForHumans() }}</span>
                                    @else
                                        <span class="text-gray-400">-</span>
                                    @endif
                                </td>
                                <td data-label="Komponen" class="px-4 py-3 text-center">
                                    @if($box->components_count > 0)
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-700">
                                            {{ $box->components_count }} jenis
                                        </span>
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-green-100 text-green-700 ml-1">
                                            {{ $box->total_quantity ?? 0 }} item
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-amber-100 text-amber-700">
                                            Belum ada komponen
                                        </span>
                                    @endif
                                </td>
                                @can('create-components')
                                <td data-label="Aksi" class="px-4 py-3 text-center">
                                    <button type="button"
                                        data-box-id="{{ $box->id }}"
                                        data-box-code="{{ $box->code }}"
                                        data-box-name="{{ $box->name }}"
                                        class="js-used-quick-add inline-flex items-center gap-1 px-3 py-1.5 bg-blue-600 text-white text-xs font-medium rounded-lg hover:bg-blue-700 transition-colors"
                                        title="Tambah komponen ke box ini">
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                                        </svg>
                                        Tambah
                                    </button>
                                </td>
                                @endcan
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @can('create-components')
            <div class="mt-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <button type="button" onclick="usedSelectNoComponents()" class="self-start inline-flex items-center gap-2 px-4 py-2 border border-amber-500 text-amber-600 text-sm font-medium rounded-lg hover:bg-amber-50 transition-colors">
                    Centang semua yang belum ada komponen
                </button>
                <div class="text-sm text-gray-600">
                    Terpilih: <span id="usedSelectedCount" class="font-bold text-blue-700">0</span> box
                </div>
            </div>
            @endcan
        @endif
    </div>
</div>

{{-- Modal: Tambah komponen bulk (target per kode box) --}}
<div id="usedBulkModal" class="fixed inset-0 bg-black/50 z-50 hidden items-center justify-center p-4" style="display:none">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-lg" onclick="event.stopPropagation()">
        <div class="px-6 py-4 border-b border-gray-100">
            <h3 class="text-lg font-bold text-gray-900">Tambah Komponen ke Box Terpilih</h3>
            <p class="text-sm text-gray-500">Distribusikan komponen ke box yang dipilih dari daftar rangkuman</p>
        </div>
        <form id="usedBulkForm" class="p-6 space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Komponen <span class="text-red-500">*</span></label>
                <div class="relative" x-data="{ search: '', open: false }" @click.outside="open = false">
                    <input type="text" x-model="search" @focus="open = true" @input="open = true" placeholder="Ketik nama/kode komponen..."
                        class="w-full px-4 py-2.5 text-sm border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <div x-show="open && search.length > 0" x-cloak
                        class="absolute z-20 w-full mt-1 bg-white border border-gray-200 rounded-lg shadow-lg max-h-48 overflow-y-auto z-10">
                        @foreach($allComponents as $comp)
                            <div class="px-3 py-2 text-sm cursor-pointer hover:bg-blue-50 flex items-center justify-between"
                                x-show="search === '' || '{{ strtolower($comp->name) }}'.includes(search.toLowerCase()) || '{{ strtolower($comp->code) }}'.includes(search.toLowerCase())"
                                @click="
                                    document.getElementById('usedBulkComponentId').value = '{{ $comp->id }}';
                                    document.getElementById('usedBulkComponentName').textContent = '{{ $comp->name }} ({{ $comp->code }})';
                                    document.getElementById('usedBulkComponentStock').textContent = '{{ $comp->quantity }}';
                                    search = '{{ $comp->name }} ({{ $comp->code }})';
                                    open = false;
                                ">
                                <span class="text-gray-800">{{ $comp->name }}</span>
                                <span class="text-xs text-gray-500 font-mono">{{ $comp->code }} | Stok: {{ $comp->quantity }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
                <input type="hidden" id="usedBulkComponentId" value="">
                <div class="flex items-end gap-2 mt-2">
                    <div class="flex-1 flex items-center gap-2 min-w-0">
                        <span class="text-xs text-gray-500 shrink-0">Stok:</span>
                        <span id="usedBulkComponentStock" class="text-xs font-semibold text-gray-700">-</span>
                        <span id="usedBulkComponentName" class="text-xs text-gray-500 truncate"></span>
                    </div>
                    <div class="w-24 shrink-0">
                        <input type="number" id="usedBulkPickQuantity" value="1" min="1" aria-label="Jumlah per box"
                            class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    <button type="button" onclick="addUsedSelectedComponent()"
                        class="px-4 py-2 bg-blue-600 text-white text-sm font-semibold rounded-lg hover:bg-blue-700 transition-colors whitespace-nowrap">
                        Tambah
                    </button>
                </div>

                <div id="usedBulkSelected" class="space-y-2 mt-3"></div>
            </div>

            <div>
                <div class="flex items-center justify-between mb-2">
                    <label class="block text-sm font-medium text-gray-700">Box Tujuan <span class="text-red-500">*</span></label>
                    <label class="flex items-center gap-2 text-xs font-medium text-blue-700 cursor-pointer select-none">
                        <input type="checkbox" id="usedBulkSelectAll" onchange="usedBulkToggleSelectAll(this)"
                            class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                        Pilih Semua
                    </label>
                </div>
                <div class="space-y-1 max-h-48 overflow-y-auto border border-gray-200 rounded-lg p-3">
                    @foreach($usedBoxes as $box)
                        <label class="flex items-center gap-3 cursor-pointer hover:bg-gray-50 p-2 rounded-lg transition-colors">
                            <input type="checkbox" name="used_box_ids[]" value="{{ $box->id }}"
                                class="js-used-box-cb rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                                onchange="updateUsedBulkSummary()">
                            <div class="flex-1">
                                <span class="text-xs font-mono font-bold text-gray-800">{{ $box->code }}</span>
                                <span class="text-xs text-gray-500 ml-2">{{ $box->name }}</span>
                            </div>
                            <span class="text-xs text-gray-400">{{ $box->usage_count }}x pemakaian</span>
                        </label>
                    @endforeach
                </div>
            </div>

            <div id="usedBulkSummary" class="bg-blue-50 border border-blue-200 rounded-xl p-4 hidden">
                <div class="flex items-center justify-between text-sm">
                    <span class="text-blue-700">Total box dipilih:</span>
                    <span id="usedBulkBoxCount" class="font-semibold text-blue-800">0</span>
                </div>
                <div class="flex items-center justify-between text-sm mt-1 border-t border-blue-200 pt-2">
                    <span class="text-blue-700 font-semibold">Total item dibutuhkan:</span>
                    <span id="usedBulkTotalNeeded" class="font-bold text-blue-800">0</span>
                </div>
                <div id="usedBulkItemLines" class="mt-2 space-y-1 text-xs"></div>
                <div id="usedBulkStockStatus" class="mt-2 text-xs"></div>
            </div>

            <div id="usedBulkError" class="hidden bg-red-50 border border-red-200 text-red-700 text-sm px-4 py-3 rounded-xl"></div>
            <div class="flex items-center gap-3 pt-2">
                <button type="submit" id="usedBulkSubmitBtn" class="flex-1 px-4 py-2.5 bg-blue-600 text-white text-sm font-semibold rounded-lg hover:bg-blue-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                    Distribusikan
                </button>
                <button type="button" onclick="closeUsedBulkModal()" class="px-4 py-2.5 bg-gray-100 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-200 transition-colors">
                    Batal
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Modal: Tambah komponen cepat untuk satu box --}}
<div id="usedQuickAddModal" class="fixed inset-0 bg-black/50 z-50 hidden items-center justify-center p-4" style="display:none">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-md" onclick="event.stopPropagation()">
        <div class="px-6 py-4 border-b border-gray-100">
            <h3 class="text-lg font-bold text-gray-900">Tambah Komponen</h3>
            <p class="text-sm text-gray-500"><span id="usedQuickBoxCode" class="font-mono font-bold text-gray-700"></span> — <span id="usedQuickBoxName" class="text-gray-700"></span></p>
        </div>
        <form id="usedQuickAddForm" class="p-6 space-y-4">
            <input type="hidden" id="usedQuickBoxId" value="">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Komponen <span class="text-red-500">*</span></label>
                <div class="relative" x-data="{ search: '', open: false }" @click.outside="open = false">
                    <input type="text" x-model="search" @focus="open = true" @input="open = true" placeholder="Ketik nama/kode komponen..."
                        class="w-full px-4 py-2.5 text-sm border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <div x-show="open && search.length > 0" x-cloak
                        class="absolute z-20 w-full mt-1 bg-white border border-gray-200 rounded-lg shadow-lg max-h-48 overflow-y-auto">
                        @foreach($allComponents as $comp)
                            <div class="px-3 py-2 text-sm cursor-pointer hover:bg-blue-50 flex items-center justify-between"
                                x-show="search === '' || '{{ strtolower($comp->name) }}'.includes(search.toLowerCase()) || '{{ strtolower($comp->code) }}'.includes(search.toLowerCase())"
                                @click="
                                    document.getElementById('usedQuickComponentId').value = '{{ $comp->id }}';
                                    document.getElementById('usedQuickComponentStock').textContent = '{{ $comp->quantity }}';
                                    document.getElementById('usedQuickQuantity').max = '{{ $comp->quantity }}';
                                    search = '{{ $comp->name }} ({{ $comp->code }})';
                                    open = false;
                                ">
                                <span class="text-gray-800">{{ $comp->name }}</span>
                                <span class="text-xs text-gray-500 font-mono">{{ $comp->code }} | Stok: {{ $comp->quantity }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
                <input type="hidden" id="usedQuickComponentId" name="component_id" value="">
                <div class="flex items-center gap-2 mt-2">
                    <span class="text-xs text-gray-500">Stok tersedia:</span>
                    <span id="usedQuickComponentStock" class="text-xs font-semibold text-gray-700">-</span>
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Jumlah <span class="text-red-500">*</span></label>
                <input type="number" id="usedQuickQuantity" name="quantity" value="1" min="1" required
                    class="w-full px-4 py-2.5 text-sm border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            </div>

            <div id="usedQuickAddError" class="hidden bg-red-50 border border-red-200 text-red-700 text-sm px-4 py-3 rounded-xl"></div>
            <div class="flex items-center gap-3 pt-2">
                <button type="submit" id="usedQuickSubmitBtn" class="flex-1 px-4 py-2.5 bg-blue-600 text-white text-sm font-semibold rounded-lg hover:bg-blue-700 transition-colors disabled:opacity-50">
                    Tambahkan
                </button>
                <button type="button" onclick="closeUsedQuickAddModal()" class="px-4 py-2.5 bg-gray-100 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-200 transition-colors">
                    Batal
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    (function() {
        var usedSelectedComponents = [];

        function usedEsc(value) {
            return String(value).replace(/[&<>"']/g, function(c) {
                return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
            });
        }

        function usedCheckedIds() {
            return Array.from(document.querySelectorAll('input[name="used_box_ids[]"]:checked'))
                .map(cb => cb.value);
        }

        window.usedToggleSelectAll = function(el) {
            document.querySelectorAll('input[name="used_box_ids[]"]').forEach(cb => {
                cb.checked = el.checked;
            });
            updateUsedBulkSummary();
        };

        window.usedSelectNoComponents = function() {
            @can('create-components')
            var target = @js($noComponentIds).map(String);
            document.querySelectorAll('input[name="used_box_ids[]"]').forEach(cb => {
                if (target.includes(cb.value)) cb.checked = true;
            });
            updateUsedBulkSummary();
            @endcan
        };

        window.updateUsedBulkSummary = function() {
            var countEl = document.getElementById('usedSelectedCount');
            if (countEl) countEl.textContent = usedCheckedIds().length;

            var modalBoxes = document.querySelectorAll('#usedBulkModal input[name="used_box_ids[]"]');
            var modalChecked = document.querySelectorAll('#usedBulkModal input[name="used_box_ids[]"]:checked').length;
            var boxCount = modalChecked;

            var selectAllEl = document.getElementById('usedBulkSelectAll');
            if (selectAllEl) {
                selectAllEl.checked = modalBoxes.length > 0 && modalChecked === modalBoxes.length;
            }

            var summaryEl = document.getElementById('usedBulkSummary');
            var submitBtn = document.getElementById('usedBulkSubmitBtn');
            var linesEl = document.getElementById('usedBulkItemLines');
            var statusEl = document.getElementById('usedBulkStockStatus');

            if (boxCount > 0 && usedSelectedComponents.length > 0) {
                summaryEl.classList.remove('hidden');
                document.getElementById('usedBulkBoxCount').textContent = boxCount;

                var totalItems = 0;
                var allStockOk = true;
                var html = '';

                usedSelectedComponents.forEach(function(c) {
                    var needed = c.quantity * boxCount;
                    totalItems += needed;
                    var ok = c.stock >= needed;
                    if (!ok) allStockOk = false;
                    html += '<div class="flex items-center justify-between">' +
                        '<span class="text-blue-700">' + usedEsc(c.name) + ' (' + usedEsc(c.code) + ') × ' + c.quantity + '/box</span>' +
                        '<span class="' + (ok ? 'text-green-700 font-semibold' : 'text-red-600 font-semibold') + '">' +
                            needed + '/' + c.stock + (ok ? ' ✓' : ' ✗ kurang ' + (needed - c.stock)) +
                        '</span></div>';
                });

                document.getElementById('usedBulkTotalNeeded').textContent = totalItems;
                linesEl.innerHTML = html;

                if (allStockOk) {
                    statusEl.innerHTML = '<span class="text-green-600 font-semibold">✓ Semua stok cukup</span>';
                    submitBtn.disabled = false;
                } else {
                    statusEl.innerHTML = '<span class="text-red-600 font-semibold">✗ Ada stok yang tidak cukup</span>';
                    submitBtn.disabled = true;
                }
            } else {
                summaryEl.classList.add('hidden');
                submitBtn.disabled = true;
            }
        };

        window.addUsedSelectedComponent = function() {
            var id = document.getElementById('usedBulkComponentId').value;
            if (!id) return;

            var qty = parseInt(document.getElementById('usedBulkPickQuantity').value) || 0;
            if (qty < 1) qty = 1;

            var nameEl = document.getElementById('usedBulkComponentName');
            var stock = parseInt(document.getElementById('usedBulkComponentStock').textContent) || 0;
            var label = (nameEl.textContent || '').trim();
            var name = label;
            var code = '';
            var m = label.match(/^(.*)\s\((.*)\)$/);
            if (m) { name = m[1]; code = m[2]; }

            var existing = usedSelectedComponents.find(c => String(c.id) === String(id));
            if (existing) {
                existing.quantity += qty;
            } else {
                usedSelectedComponents.push({ id: id, name: name, code: code, stock: stock, quantity: qty });
            }

            document.getElementById('usedBulkComponentId').value = '';
            nameEl.textContent = '';
            document.getElementById('usedBulkComponentStock').textContent = '-';
            document.getElementById('usedBulkPickQuantity').value = 1;

            renderUsedSelected();
            updateUsedBulkSummary();
        };

        window.removeUsedSelectedComponent = function(index) {
            usedSelectedComponents.splice(index, 1);
            renderUsedSelected();
            updateUsedBulkSummary();
        };

        window.updateUsedBulkQty = function(index, value) {
            var qty = parseInt(value) || 1;
            if (qty < 1) qty = 1;
            usedSelectedComponents[index].quantity = qty;
            updateUsedBulkSummary();
        };

        function renderUsedSelected() {
            var container = document.getElementById('usedBulkSelected');
            if (!container) return;

            if (usedSelectedComponents.length === 0) {
                container.innerHTML = '<p class="text-xs text-gray-400 italic">Belum ada komponen ditambahkan</p>';
                return;
            }

            var html = '<div class="space-y-2">';
            usedSelectedComponents.forEach(function(c, index) {
                html += '<div class="flex items-center gap-2 bg-gray-50 rounded-lg px-3 py-2">' +
                    '<div class="flex-1 min-w-0">' +
                        '<span class="text-sm font-medium text-gray-800">' + usedEsc(c.name) + '</span>' +
                        '<span class="text-xs text-gray-500 ml-2 font-mono">' + usedEsc(c.code) + '</span>' +
                        '<span class="block text-xs text-gray-400">Stok: ' + usedEsc(c.stock) + '</span>' +
                    '</div>' +
                    '<input type="number" min="1" value="' + c.quantity + '" aria-label="Jumlah per box"' +
                        ' onchange="updateUsedBulkQty(' + index + ', this.value)"' +
                        ' class="w-20 px-2 py-1 text-sm border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">' +
                    '<span class="text-xs text-gray-400">/box</span>' +
                    '<button type="button" onclick="removeUsedSelectedComponent(' + index + ')"' +
                        ' class="p-1 text-red-500 hover:bg-red-50 rounded transition-colors" title="Hapus">' +
                        '<svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">' +
                        '<path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>' +
                    '</button>' +
                '</div>';
            });
            html += '</div>';
            container.innerHTML = html;
        }

        window.usedBulkToggleSelectAll = function(el) {
            document.querySelectorAll('#usedBulkModal input[name="used_box_ids[]"]').forEach(function(cb) {
                cb.checked = el.checked;
            });
            updateUsedBulkSummary();
        };

        window.openUsedBulkModal = function() {
            var modal = document.getElementById('usedBulkModal');
            modal.style.display = 'flex';
            modal.classList.remove('hidden');
            document.getElementById('usedBulkForm').reset();
            document.getElementById('usedBulkError').classList.add('hidden');
            document.getElementById('usedBulkComponentId').value = '';
            document.getElementById('usedBulkComponentStock').textContent = '-';
            document.getElementById('usedBulkComponentName').textContent = '';
            usedSelectedComponents = [];
            renderUsedSelected();

            // Sinkronkan pilihan dari tabel ke daftar checkbox di modal.
            var selected = usedCheckedIds();
            modal.querySelectorAll('input[name="used_box_ids[]"]').forEach(function(cb) {
                cb.checked = selected.includes(cb.value);
            });

            document.getElementById('usedBulkSummary').classList.add('hidden');
            document.getElementById('usedBulkSubmitBtn').disabled = true;
            updateUsedBulkSummary();
        };

        window.closeUsedBulkModal = function() {
            var modal = document.getElementById('usedBulkModal');
            modal.style.display = 'none';
            modal.classList.add('hidden');
            updateUsedBulkSummary();
        };

        window.openUsedQuickAddModal = function(boxId, boxCode, boxName) {
            document.getElementById('usedQuickBoxId').value = boxId;
            document.getElementById('usedQuickBoxCode').textContent = boxCode;
            document.getElementById('usedQuickBoxName').textContent = boxName;
            document.getElementById('usedQuickAddForm').reset();
            document.getElementById('usedQuickComponentId').value = '';
            document.getElementById('usedQuickComponentStock').textContent = '-';
            document.getElementById('usedQuickAddError').classList.add('hidden');
            var modal = document.getElementById('usedQuickAddModal');
            modal.style.display = 'flex';
            modal.classList.remove('hidden');
        };

        window.closeUsedQuickAddModal = function() {
            var modal = document.getElementById('usedQuickAddModal');
            modal.style.display = 'none';
            modal.classList.add('hidden');
        };

        document.querySelectorAll('.js-used-quick-add').forEach(function(btn) {
            btn.addEventListener('click', function() {
                window.openUsedQuickAddModal(btn.dataset.boxId, btn.dataset.boxCode, btn.dataset.boxName);
            });
        });

        document.getElementById('usedBulkModal').addEventListener('click', function(e) {
            if (e.target === this) closeUsedBulkModal();
        });

        document.getElementById('usedQuickAddModal').addEventListener('click', function(e) {
            if (e.target === this) closeUsedQuickAddModal();
        });

        document.getElementById('usedBulkForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            var errorEl = document.getElementById('usedBulkError');
            errorEl.classList.add('hidden');

            var boxIds = Array.from(document.querySelectorAll('#usedBulkModal input[name="used_box_ids[]"]:checked'))
                .map(cb => cb.value);

            if (usedSelectedComponents.length === 0) {
                errorEl.textContent = 'Pilih minimal satu komponen';
                errorEl.classList.remove('hidden');
                return;
            }

            if (boxIds.length === 0) {
                errorEl.textContent = 'Pilih minimal satu box';
                errorEl.classList.remove('hidden');
                return;
            }

            try {
                var response = await fetch('{{ route('boxes.bulk-add-component') }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({
                        components: usedSelectedComponents.map(function(c) {
                            return { component_id: c.id, quantity: c.quantity };
                        }),
                        box_ids: boxIds,
                    }),
                });

                var data = await response.json();

                if (data.success) {
                    closeUsedBulkModal();
                    location.reload();
                } else {
                    errorEl.textContent = data.message || 'Terjadi kesalahan';
                    errorEl.classList.remove('hidden');
                }
            } catch (err) {
                errorEl.textContent = 'Terjadi kesalahan jaringan';
                errorEl.classList.remove('hidden');
            }
        });

        document.getElementById('usedQuickAddForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            var errorEl = document.getElementById('usedQuickAddError');
            errorEl.classList.add('hidden');

            var boxId = document.getElementById('usedQuickBoxId').value;
            var componentId = document.getElementById('usedQuickComponentId').value;

            if (!componentId) {
                errorEl.textContent = 'Pilih komponen terlebih dahulu';
                errorEl.classList.remove('hidden');
                return;
            }

            var submitBtn = document.getElementById('usedQuickSubmitBtn');
            submitBtn.disabled = true;

            try {
                var response = await fetch('/boxes/' + boxId + '/components', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    },
                    body: new FormData(this),
                });

                var data = await response.json();

                if (data.success) {
                    closeUsedQuickAddModal();
                    location.reload();
                } else {
                    errorEl.textContent = data.message || 'Terjadi kesalahan';
                    errorEl.classList.remove('hidden');
                }
            } catch (err) {
                errorEl.textContent = 'Terjadi kesalahan jaringan';
                errorEl.classList.remove('hidden');
            } finally {
                submitBtn.disabled = false;
            }
        });

        renderUsedSelected();
        updateUsedBulkSummary();
    })();
</script>
