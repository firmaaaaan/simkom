<div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
    <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
        <div>
            <h2 class="text-base font-semibold text-gray-800">Box Penyimpanan</h2>
            <p class="text-sm text-gray-500">Kelola box penyimpanan komponen</p>
        </div>
        <div class="flex items-center gap-2">
            <x-export-button route="boxes.export" />
            <button onclick="openGenerateModal()" class="inline-flex items-center gap-2 px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700 transition-colors">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
                Generate Box
            </button>
            <button onclick="openBulkAddModal()" class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-colors">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
                Tambah Komponen
            </button>
            <a href="{{ route('boxes.print-labels') }}" target="_blank" class="inline-flex items-center gap-2 px-4 py-2 bg-gray-600 text-white text-sm font-medium rounded-lg hover:bg-gray-700 transition-colors">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0110.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0l.229 2.523a1.125 1.125 0 01-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0021 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 00-1.913-.247M6.34 18H5.25A2.25 2.25 0 013 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 011.913-.247m0 0a48.159 48.159 0 018.5 0m-8.5 0V6.75a2 2 0 012-2h4.5a2 2 0 012 2v1.034" />
                </svg>
                Cetak Label
            </a>
        </div>
    </div>

    <div class="p-6">
        @if($boxes->isEmpty())
            <div class="text-center py-12">
                <svg class="w-16 h-16 mx-auto text-gray-300 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m21 7.5-9-5.25L3 7.5m18 0-9 5.25m9-5.25v9l-9 5.25M3 7.5l9 5.25M3 7.5v9l9 5.25m0-9v9" />
                </svg>
                <p class="text-gray-500 text-sm">Belum ada box penyimpanan</p>
                <p class="text-gray-400 text-xs mt-1">Klik "Generate Box" untuk membuat box baru</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm table-responsive-cards">
                    <thead>
                        <tr class="border-b border-gray-100">
                            <th class="text-left px-4 py-3 font-medium text-gray-500">Kode</th>
                            <th class="text-left px-4 py-3 font-medium text-gray-500">Nama</th>
                            <th class="text-left px-4 py-3 font-medium text-gray-500">Lokasi</th>
                            <th class="text-center px-4 py-3 font-medium text-gray-500">Komponen</th>
                            <th class="text-center px-4 py-3 font-medium text-gray-500">Total Item</th>
                            <th class="text-center px-4 py-3 font-medium text-gray-500">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($boxes as $box)
                            <tr class="border-b border-gray-50 hover:bg-gray-50/50 transition-colors">
                                <td data-label="Kode" class="px-4 py-3">
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-mono font-bold bg-gray-100 text-gray-700">
                                        {{ $box->code }}
                                    </span>
                                </td>
                                <td data-label="Nama" class="px-4 py-3 font-medium text-gray-900">{{ $box->name }}</td>
                                <td data-label="Lokasi" class="px-4 py-3 text-gray-600">{{ $box->location ?? '-' }}</td>
                                <td data-label="Komponen" class="px-4 py-3 text-center">
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-700">
                                        {{ $box->box_components_count ?? 0 }} jenis
                                    </span>
                                </td>
                                <td data-label="Total Item" class="px-4 py-3 text-center">
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-green-100 text-green-700">
                                        {{ $box->box_components_sum_quantity ?? 0 }} item
                                    </span>
                                </td>
                                <td data-label="Aksi" class="px-4 py-3">
                                    <div class="flex items-center justify-center gap-2">
                                        <button onclick="viewBox('{{ $box->id }}')" class="p-1.5 text-blue-600 hover:bg-blue-50 rounded-lg transition-colors" title="Lihat Detail">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            </svg>
                                        </button>
                                        <button onclick="showQR('{{ $box->code }}', '{{ $box->name }}')" class="p-1.5 text-purple-600 hover:bg-purple-50 rounded-lg transition-colors" title="QR Code">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 013.75 9.375v-4.5zM3.75 14.625c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5a1.125 1.125 0 01-1.125-1.125v-4.5zM13.5 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 0113.5 9.375v-4.5z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 6.75h.75v.75h-.75v-.75zM6.75 16.5h.75v.75h-.75v-.75zM16.5 6.75h.75v.75h-.75v-.75zM13.5 13.5h.75v.75h-.75v-.75zM13.5 19.5h.75v.75h-.75v-.75zM19.5 13.5h.75v.75h-.75v-.75zM19.5 19.5h.75v.75h-.75v-.75zM16.5 16.5h.75v.75h-.75v-.75z" />
                                            </svg>
                                        </button>
                                        <button onclick="openEditBoxModal('{{ $box->id }}', '{{ addslashes($box->name) }}', '{{ addslashes($box->location ?? '') }}', '{{ addslashes($box->notes ?? '') }}')" class="p-1.5 text-orange-600 hover:bg-orange-50 rounded-lg transition-colors" title="Edit Box">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" />
                                            </svg>
                                        </button>
                                        <button onclick="deleteBox('{{ $box->id }}', '{{ $box->code }}')" class="p-1.5 text-red-600 hover:bg-red-50 rounded-lg transition-colors" title="Hapus">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                                            </svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>

<div id="generateModal" class="fixed inset-0 bg-black/50 z-50 hidden items-center justify-center p-4" style="display:none">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-lg" onclick="event.stopPropagation()">
        <div class="px-6 py-4 border-b border-gray-100">
            <h3 class="text-lg font-bold text-gray-900">Generate Box</h3>
            <p class="text-sm text-gray-500">Buat box baru dan masukkan komponen sekaligus</p>
        </div>
        <form id="generateForm" class="p-6 space-y-4">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nama Box <span class="text-red-500">*</span></label>
                    <input type="text" name="name" required placeholder="Contoh: Box Sensor IoT"
                        class="w-full px-4 py-2.5 text-sm border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Lokasi</label>
                    <input type="text" name="location" placeholder="Contoh: Rak A-1"
                        class="w-full px-4 py-2.5 text-sm border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Catatan</label>
                <textarea name="notes" rows="2" placeholder="Catatan untuk label (opsional)"
                    class="w-full px-4 py-2.5 text-sm border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"></textarea>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Jumlah Box <span class="text-red-500">*</span></label>
                <input type="number" name="quantity" value="1" min="1" max="100" required
                    class="w-full px-4 py-2.5 text-sm border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
            </div>

            <div class="border-t border-gray-100 pt-4">
                <div class="flex items-center justify-between mb-3">
                    <label class="text-sm font-semibold text-gray-700">Komponen</label>
                    <span class="text-xs text-gray-400">(opsional, bisa ditambah nanti)</span>
                </div>

                <div id="selectedComponents" class="space-y-2 mb-3"></div>

                <div class="flex items-end gap-2">
                    <div class="flex-1 relative" x-data="{ search: '', open: false, selected: null }" @click.outside="open = false">
                        <label class="block text-xs font-medium text-gray-500 mb-1">Cari & Pilih Komponen</label>
                        <input type="text" x-model="search" @focus="open = true" @input="open = true" placeholder="Ketik nama/kode komponen..."
                            class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                        <div x-show="open && search.length > 0" x-cloak
                            class="absolute z-20 w-full mt-1 bg-white border border-gray-200 rounded-lg shadow-lg max-h-48 overflow-y-auto">
                            @php $allComponents = \App\Models\Component::orderBy('name')->get(); @endphp
                            @foreach($allComponents as $comp)
                                <div class="component-option px-3 py-2 text-sm cursor-pointer hover:bg-green-50 flex items-center justify-between"
                                    data-id="{{ $comp->id }}"
                                    data-name="{{ $comp->name }}"
                                    data-code="{{ $comp->code }}"
                                    data-stock="{{ $comp->quantity }}"
                                    x-show="search === '' || '{{ strtolower($comp->name) }}'.includes(search.toLowerCase()) || '{{ strtolower($comp->code) }}'.includes(search.toLowerCase())"
                                    @click="
                                        document.getElementById('componentSelect').value = '{{ $comp->id }}';
                                        document.getElementById('componentStock').textContent = '{{ $comp->quantity }}';
                                        document.getElementById('componentQuantity').max = '{{ $comp->quantity }}';
                                        selected = { id: '{{ $comp->id }}', name: '{{ $comp->name }}', code: '{{ $comp->code }}', stock: {{ $comp->quantity}} };
                                        search = '{{ $comp->name }} ({{ $comp->code }})';
                                        open = false;
                                    ">
                                    <span class="text-gray-800">{{ $comp->name }}</span>
                                    <span class="text-xs text-gray-500 font-mono">{{ $comp->code }} | Stok: {{ $comp->quantity }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    <div class="w-24">
                        <label class="block text-xs font-medium text-gray-500 mb-1">Jumlah</label>
                        <input type="number" id="componentQuantity" value="1" min="1" class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                    </div>
                    <button type="button" onclick="addSelectedComponent()" class="px-4 py-2 bg-blue-600 text-white text-sm font-semibold rounded-lg hover:bg-blue-700 transition-colors whitespace-nowrap mb-0.5">
                        Tambah
                    </button>
                </div>
                <input type="hidden" id="componentSelect" value="">
                <div class="flex items-center gap-2 mt-2">
                    <span class="text-xs text-gray-500">Stok tersedia:</span>
                    <span id="componentStock" class="text-xs font-semibold text-gray-700">-</span>
                </div>
            </div>

            <div id="generateError" class="hidden bg-red-50 border border-red-200 text-red-700 text-sm px-4 py-3 rounded-xl"></div>
            <div class="flex items-center gap-3 pt-2">
                <button type="submit" class="flex-1 px-4 py-2.5 bg-green-600 text-white text-sm font-semibold rounded-lg hover:bg-green-700 transition-colors">
                    Generate & Simpan
                </button>
                <button type="button" onclick="closeGenerateModal()" class="px-4 py-2.5 bg-gray-100 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-200 transition-colors">
                    Batal
                </button>
            </div>
        </form>
    </div>
</div>

<div id="bulkAddModal" class="fixed inset-0 bg-black/50 z-50 hidden items-center justify-center p-4" style="display:none">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-lg" onclick="event.stopPropagation()">
        <div class="px-6 py-4 border-b border-gray-100">
            <h3 class="text-lg font-bold text-gray-900">Tambah Komponen ke Box</h3>
            <p class="text-sm text-gray-500">Distribusikan komponen ke beberapa box sekaligus</p>
        </div>
        <form id="bulkAddForm" class="p-6 space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Komponen <span class="text-red-500">*</span></label>
                <div class="relative" x-data="{ search: '', open: false }" @click.outside="open = false">
                    <input type="text" x-model="search" @focus="open = true" @input="open = true" placeholder="Ketik nama/kode komponen..."
                        class="w-full px-4 py-2.5 text-sm border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <div x-show="open && search.length > 0" x-cloak
                        class="absolute z-20 w-full mt-1 bg-white border border-gray-200 rounded-lg shadow-lg max-h-48 overflow-y-auto">
                        @foreach($allComponents as $comp)
                            <div class="bulk-comp-option px-3 py-2 text-sm cursor-pointer hover:bg-blue-50 flex items-center justify-between"
                                data-id="{{ $comp->id }}"
                                data-name="{{ $comp->name }}"
                                data-code="{{ $comp->code }}"
                                data-stock="{{ $comp->quantity }}"
                                x-show="search === '' || '{{ strtolower($comp->name) }}'.includes(search.toLowerCase()) || '{{ strtolower($comp->code) }}'.includes(search.toLowerCase())"
                                @click="
                                    document.getElementById('bulkComponentId').value = '{{ $comp->id }}';
                                    document.getElementById('bulkComponentStock').textContent = '{{ $comp->quantity }}';
                                    document.getElementById('bulkQuantity').max = '{{ $comp->quantity }}';
                                    search = '{{ $comp->name }} ({{ $comp->code }})';
                                    open = false;
                                    updateBulkSummary();
                                ">
                                <span class="text-gray-800">{{ $comp->name }}</span>
                                <span class="text-xs text-gray-500 font-mono">{{ $comp->code }} | Stok: {{ $comp->quantity }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
                <input type="hidden" id="bulkComponentId" value="">
                <div class="flex items-center gap-2 mt-2">
                    <span class="text-xs text-gray-500">Stok tersedia:</span>
                    <span id="bulkComponentStock" class="text-xs font-semibold text-gray-700">-</span>
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Jumlah per Box <span class="text-red-500">*</span></label>
                <input type="number" id="bulkQuantity" name="quantity" value="1" min="1" required
                    class="w-full px-4 py-2.5 text-sm border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    oninput="updateBulkSummary()">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Distribusikan ke Box <span class="text-red-500">*</span></label>
                @php
                    $boxGroups = $boxes->groupBy('name');
                @endphp
                <div class="space-y-2 max-h-48 overflow-y-auto border border-gray-200 rounded-lg p-3">
                    @foreach($boxGroups as $groupName => $groupBoxes)
                        <label class="flex items-center gap-3 cursor-pointer hover:bg-gray-50 p-2 rounded-lg transition-colors">
                            <input type="checkbox" name="box_names[]" value="{{ $groupName }}"
                                class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                                onchange="updateBulkSummary()">
                            <div class="flex-1">
                                <span class="text-sm font-medium text-gray-800">{{ $groupName }}</span>
                                <span class="text-xs text-gray-500 ml-2">({{ $groupBoxes->count() }} box)</span>
                            </div>
                            <div class="text-xs text-gray-400">
                                @foreach($groupBoxes as $gb)
                                    <span class="font-mono">{{ $gb->code }}</span>{{ !$loop->last ? ', ' : '' }}
                                @endforeach
                            </div>
                        </label>
                    @endforeach
                </div>
            </div>

            <div id="bulkSummary" class="bg-blue-50 border border-blue-200 rounded-xl p-4 hidden">
                <div class="flex items-center justify-between text-sm">
                    <span class="text-blue-700">Total box dipilih:</span>
                    <span id="bulkBoxCount" class="font-semibold text-blue-800">0</span>
                </div>
                <div class="flex items-center justify-between text-sm mt-1">
                    <span class="text-blue-700">Jumlah per box:</span>
                    <span id="bulkQtyPerBox" class="font-semibold text-blue-800">0</span>
                </div>
                <div class="flex items-center justify-between text-sm mt-1 border-t border-blue-200 pt-2">
                    <span class="text-blue-700 font-semibold">Total dibutuhkan:</span>
                    <span id="bulkTotalNeeded" class="font-bold text-blue-800">0</span>
                </div>
                <div id="bulkStockStatus" class="mt-2 text-xs"></div>
            </div>

            <div id="bulkAddError" class="hidden bg-red-50 border border-red-200 text-red-700 text-sm px-4 py-3 rounded-xl"></div>
            <div class="flex items-center gap-3 pt-2">
                <button type="submit" id="bulkSubmitBtn" class="flex-1 px-4 py-2.5 bg-blue-600 text-white text-sm font-semibold rounded-lg hover:bg-blue-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                    Distribusikan
                </button>
                <button type="button" onclick="closeBulkAddModal()" class="px-4 py-2.5 bg-gray-100 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-200 transition-colors">
                    Batal
                </button>
            </div>
        </form>
    </div>
</div>

<div id="boxDetailModal" class="fixed inset-0 bg-black/50 z-50 hidden items-center justify-center p-4" style="display:none">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-2xl max-h-[90vh] overflow-hidden flex flex-col" onclick="event.stopPropagation()">
        <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
            <div>
                <h3 id="boxDetailTitle" class="text-lg font-bold text-gray-900"></h3>
                <p id="boxDetailLocation" class="text-sm text-gray-500"></p>
            </div>
            <button onclick="closeBoxDetailModal()" class="p-2 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-lg transition-colors">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
        <div class="flex-1 overflow-y-auto p-6 space-y-6">
            <div id="boxUsageStatus"></div>
            <div>
                <h4 class="text-sm font-semibold text-gray-700 mb-3">Tambah Komponen</h4>
                <form id="addComponentForm" class="flex items-end gap-3">
                    <div class="flex-1">
                        <label class="block text-xs font-medium text-gray-500 mb-1">Komponen</label>
                        <select name="component_id" required class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                            <option value="">Pilih Komponen</option>
                        </select>
                    </div>
                    <div class="w-28">
                        <label class="block text-xs font-medium text-gray-500 mb-1">Jumlah</label>
                        <input type="number" name="quantity" value="1" min="1" required class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                    </div>
                    <button type="submit" class="px-4 py-2 bg-green-600 text-white text-sm font-semibold rounded-lg hover:bg-green-700 transition-colors whitespace-nowrap">
                        Tambah
                    </button>
                </form>
                <div id="addComponentError" class="hidden mt-2 bg-red-50 border border-red-200 text-red-700 text-sm px-4 py-3 rounded-xl"></div>
            </div>
            <div>
                <h4 class="text-sm font-semibold text-gray-700 mb-3">Komponen dalam Box</h4>
                <div id="boxComponentsList">
                    <p class="text-sm text-gray-400 italic">Memuat data...</p>
                </div>
            </div>
        </div>
    </div>
</div>

<div id="qrModal" class="fixed inset-0 bg-black/50 z-50 hidden items-center justify-center p-4" style="display:none">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-sm" onclick="event.stopPropagation()">
        <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
            <div>
                <h3 class="text-lg font-bold text-gray-900">QR Code Box</h3>
                <p class="text-sm text-gray-500">Scan untuk menggunakan box</p>
            </div>
            <button onclick="closeQRModal()" class="p-2 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-lg transition-colors">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
        <div class="p-6 flex flex-col items-center space-y-4">
            <div class="text-center">
                <span id="qrBoxCode" class="inline-flex items-center px-3 py-1 rounded-lg text-sm font-mono font-bold bg-gray-100 text-gray-700"></span>
                <p id="qrBoxName" class="text-sm text-gray-600 mt-1"></p>
            </div>
            <div id="qrCode" class="bg-white p-4 rounded-xl border border-gray-200"></div>
            <div class="w-full">
                <label class="block text-xs font-medium text-gray-500 mb-1 text-center">URL Scan</label>
                <p id="qrUrl" class="text-xs text-gray-600 bg-gray-50 rounded-lg px-3 py-2 text-center break-all font-mono"></p>
            </div>
            <div class="flex items-center gap-3 w-full">
                <button onclick="downloadQR()" class="flex-1 px-4 py-2.5 bg-purple-600 text-white text-sm font-semibold rounded-lg hover:bg-purple-700 transition-colors">
                    Download QR
                </button>
                <button onclick="closeQRModal()" class="px-4 py-2.5 bg-gray-100 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-200 transition-colors">
                    Tutup
                </button>
            </div>
        </div>
    </div>
</div>

<div id="returnModal" class="fixed inset-0 bg-black/50 z-50 hidden items-center justify-center p-4" style="display:none">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-md" onclick="event.stopPropagation()">
        <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
            <div>
                <h3 class="text-lg font-bold text-gray-900">Kembalikan Box</h3>
                <p class="text-sm text-gray-500">Konfirmasi pengembalian box</p>
            </div>
            <button onclick="closeReturnModal()" class="p-2 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-lg transition-colors">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
        <div class="p-6 space-y-4">
            <div class="bg-yellow-50 border border-yellow-200 rounded-xl p-4">
                <div class="flex items-center gap-2 mb-2">
                    <svg class="w-5 h-5 text-yellow-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
                    </svg>
                    <span class="text-sm font-semibold text-yellow-800">Box Sedang Digunakan</span>
                </div>
                <div class="space-y-1">
                    <p class="text-sm text-yellow-700"><strong>Pengguna:</strong> <span id="returnUserName"></span></p>
                    <p class="text-sm text-yellow-700"><strong>NIM:</strong> <span id="returnUserNim"></span></p>
                    <p class="text-sm text-yellow-700"><strong>Kelas:</strong> <span id="returnUserKelas"></span></p>
                    <p class="text-sm text-yellow-700"><strong>Sejak:</strong> <span id="returnUsedAt"></span></p>
                </div>
            </div>
            <div id="returnError" class="hidden bg-red-50 border border-red-200 text-red-700 text-sm px-4 py-3 rounded-xl"></div>
        </div>
        <div class="px-6 py-4 border-t border-gray-100 flex items-center gap-3">
            <button id="returnConfirmBtn" onclick="confirmReturnBox()" class="flex-1 px-4 py-2.5 bg-yellow-600 text-white text-sm font-semibold rounded-lg hover:bg-yellow-700 transition-colors">
                Kembalikan
            </button>
            <button onclick="closeReturnModal()" class="px-4 py-2.5 bg-gray-100 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-200 transition-colors">
                Batal
            </button>
        </div>
    </div>
</div>

<div id="editBoxModal" class="fixed inset-0 bg-black/50 z-50 hidden items-center justify-center p-4" style="display:none">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-lg" onclick="event.stopPropagation()">
        <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
            <div>
                <h3 class="text-lg font-bold text-gray-900">Edit Box</h3>
                <p class="text-sm text-gray-500">Perubahan akan diterapkan ke semua box dengan nama yang sama</p>
            </div>
            <button onclick="closeEditBoxModal()" class="p-2 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-lg transition-colors">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
        <form id="editBoxForm" class="p-6 space-y-4">
            <input type="hidden" id="editBoxId" name="id">
            <input type="hidden" id="editBoxName" name="name">
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Nama Box (tidak bisa diubah)</label>
                <input type="text" id="editBoxNameDisplay" readonly
                    class="w-full px-4 py-2.5 text-sm border border-gray-200 rounded-lg bg-gray-50 text-gray-500">
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Lokasi</label>
                <input type="text" name="location" id="editBoxLocation" placeholder="Contoh: Rak A-1"
                    class="w-full px-4 py-2.5 text-sm border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Catatan <span class="text-gray-400">(untuk label)</span></label>
                <textarea name="notes" id="editBoxNotes" rows="4" placeholder="Catatan untuk label (opsional)..."
                    class="w-full px-4 py-2.5 text-sm border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"></textarea>
            </div>
            
            <div id="editBoxError" class="hidden bg-red-50 border border-red-200 text-red-700 text-sm px-4 py-3 rounded-xl"></div>
            
            <div class="flex items-center gap-3 pt-2">
                <button type="submit" class="flex-1 px-4 py-2.5 bg-green-600 text-white text-sm font-semibold rounded-lg hover:bg-green-700 transition-colors">
                    Simpan & Sync ke Grup
                </button>
                <button type="button" onclick="closeEditBoxModal()" class="px-4 py-2.5 bg-gray-100 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-200 transition-colors">
                    Batal
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/qrcodejs@1.0.0/qrcode.min.js"></script>
<script>
    let currentBoxId = null;
    let selectedComponentsList = [];
    let currentQRCode = null;

    function openGenerateModal() {
        const modal = document.getElementById('generateModal');
        modal.style.display = 'flex';
        modal.classList.remove('hidden');
        document.getElementById('generateForm').reset();
        document.getElementById('generateError').classList.add('hidden');
        document.getElementById('componentSelect').value = '';
        document.getElementById('componentStock').textContent = '-';
        document.getElementById('componentQuantity').value = 1;
        selectedComponentsList = [];
        renderSelectedComponents();
    }

    function closeGenerateModal() {
        const modal = document.getElementById('generateModal');
        modal.style.display = 'none';
        modal.classList.add('hidden');
    }

    function openBulkAddModal() {
        const modal = document.getElementById('bulkAddModal');
        modal.style.display = 'flex';
        modal.classList.remove('hidden');
        document.getElementById('bulkAddForm').reset();
        document.getElementById('bulkAddError').classList.add('hidden');
        document.getElementById('bulkComponentId').value = '';
        document.getElementById('bulkComponentStock').textContent = '-';
        document.getElementById('bulkSummary').classList.add('hidden');
        document.getElementById('bulkSubmitBtn').disabled = true;
    }

    function closeBulkAddModal() {
        const modal = document.getElementById('bulkAddModal');
        modal.style.display = 'none';
        modal.classList.add('hidden');
    }

    function updateBulkSummary() {
        const componentId = document.getElementById('bulkComponentId').value;
        const quantity = parseInt(document.getElementById('bulkQuantity').value) || 0;
        const checkedBoxes = document.querySelectorAll('input[name="box_names[]"]:checked');
        const stock = parseInt(document.getElementById('bulkComponentStock').textContent) || 0;

        const boxCount = checkedBoxes.length;
        const totalNeeded = quantity * boxCount;

        const summaryEl = document.getElementById('bulkSummary');
        const submitBtn = document.getElementById('bulkSubmitBtn');

        if (boxCount > 0 && componentId) {
            summaryEl.classList.remove('hidden');
            document.getElementById('bulkBoxCount').textContent = boxCount;
            document.getElementById('bulkQtyPerBox').textContent = quantity;
            document.getElementById('bulkTotalNeeded').textContent = totalNeeded;

            const statusEl = document.getElementById('bulkStockStatus');
            if (stock >= totalNeeded) {
                statusEl.innerHTML = '<span class="text-green-600 font-semibold">✓ Stok cukup</span>';
                submitBtn.disabled = false;
            } else {
                statusEl.innerHTML = `<span class="text-red-600 font-semibold">✗ Stok tidak cukup (kurang ${totalNeeded - stock})</span>`;
                submitBtn.disabled = true;
            }
        } else {
            summaryEl.classList.add('hidden');
            submitBtn.disabled = true;
        }
    }

    document.getElementById('bulkAddForm').addEventListener('submit', async function(e) {
        e.preventDefault();
        const errorEl = document.getElementById('bulkAddError');
        errorEl.classList.add('hidden');

        const componentId = document.getElementById('bulkComponentId').value;
        const quantity = document.getElementById('bulkQuantity').value;
        const checkedBoxes = document.querySelectorAll('input[name="box_names[]"]:checked');

        if (!componentId) {
            errorEl.textContent = 'Pilih komponen terlebih dahulu';
            errorEl.classList.remove('hidden');
            return;
        }

        if (checkedBoxes.length === 0) {
            errorEl.textContent = 'Pilih minimal satu box';
            errorEl.classList.remove('hidden');
            return;
        }

        const boxNames = Array.from(checkedBoxes).map(cb => cb.value);

        try {
            const response = await fetch('{{ route("boxes.bulk-add-component") }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                },
                body: JSON.stringify({
                    component_id: componentId,
                    quantity: quantity,
                    box_names: boxNames,
                }),
            });

            const data = await response.json();

            if (data.success) {
                closeBulkAddModal();
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

    function addSelectedComponent() {
        const componentId = document.getElementById('componentSelect').value;
        const quantity = parseInt(document.getElementById('componentQuantity').value);

        if (!componentId) {
            showToast('warning', 'Perhatian', 'Pilih komponen terlebih dahulu');
            return;
        }

        if (quantity < 1) {
            showToast('warning', 'Perhatian', 'Jumlah minimal 1');
            return;
        }

        const existing = selectedComponentsList.find(c => c.id == componentId);
        if (existing) {
            existing.quantity += quantity;
        } else {
            const option = document.querySelector(`.component-option[data-id="${componentId}"]`);
            if (option) {
                selectedComponentsList.push({
                    id: componentId,
                    name: option.dataset.name,
                    code: option.dataset.code,
                    stock: parseInt(option.dataset.stock),
                    quantity: quantity
                });
            }
        }

        document.getElementById('componentSelect').value = '';
        document.getElementById('componentStock').textContent = '-';
        document.getElementById('componentQuantity').value = 1;

        const searchInput = document.querySelector('#generateForm [x-data] input[type="text"]');
        if (searchInput) {
            searchInput.value = '';
            searchInput.dispatchEvent(new Event('input'));
        }

        renderSelectedComponents();
    }

    function removeSelectedComponent(index) {
        selectedComponentsList.splice(index, 1);
        renderSelectedComponents();
    }

    function renderSelectedComponents() {
        const container = document.getElementById('selectedComponents');

        if (selectedComponentsList.length === 0) {
            container.innerHTML = '<p class="text-xs text-gray-400 italic">Belum ada komponen dipilih</p>';
            return;
        }

        let html = '<div class="space-y-2">';
        selectedComponentsList.forEach((comp, index) => {
            html += `
                <div class="flex items-center justify-between bg-gray-50 rounded-lg px-3 py-2">
                    <div class="flex-1">
                        <span class="text-sm font-medium text-gray-800">${comp.name}</span>
                        <span class="text-xs text-gray-500 ml-2 font-mono">${comp.code}</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="text-xs text-gray-500">x${comp.quantity}</span>
                        <button type="button" onclick="removeSelectedComponent(${index})" class="p-1 text-red-500 hover:bg-red-50 rounded transition-colors">
                            <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </div>
            `;
        });
        html += '</div>';
        container.innerHTML = html;
    }

    document.getElementById('generateForm').addEventListener('submit', async function(e) {
        e.preventDefault();
        const errorEl = document.getElementById('generateError');
        errorEl.classList.add('hidden');

        const formData = new FormData(this);
        formData.delete('components');

        selectedComponentsList.forEach(comp => {
            formData.append('components[]', JSON.stringify({
                id: comp.id,
                quantity: comp.quantity
            }));
        });

        try {
            const response = await fetch('{{ route("boxes.store") }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json',
                },
                body: formData,
            });

            const data = await response.json();

            if (data.success) {
                closeGenerateModal();
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

    async function viewBox(id) {
        currentBoxId = id;
        const modal = document.getElementById('boxDetailModal');
        modal.style.display = 'flex';
        modal.classList.remove('hidden');

        document.getElementById('boxComponentsList').innerHTML = '<p class="text-sm text-gray-400 italic">Memuat data...</p>';
        document.getElementById('addComponentError').classList.add('hidden');
        document.getElementById('boxUsageStatus').innerHTML = '';

        try {
            const response = await fetch(`/boxes/${id}`, {
                headers: { 'Accept': 'application/json' },
            });
            const data = await response.json();

            document.getElementById('boxDetailTitle').textContent = `${data.box.code}: ${data.box.name}`;
            document.getElementById('boxDetailLocation').textContent = data.box.location ? `Lokasi: ${data.box.location}` : '';

            const select = document.querySelector('#addComponentForm select[name="component_id"]');
            select.innerHTML = '<option value="">Pilih Komponen</option>';
            data.components.forEach(c => {
                const option = document.createElement('option');
                option.value = c.id;
                option.textContent = `${c.name} (Stok: ${c.quantity})`;
                option.disabled = c.quantity <= 0;
                select.appendChild(option);
            });

            if (data.active_usage) {
                const kelas = data.active_usage.user_kelas ? `<p class="text-xs text-yellow-600">Kelas: ${data.active_usage.user_kelas}</p>` : '';
                document.getElementById('boxUsageStatus').innerHTML = `
                    <div class="bg-yellow-50 border border-yellow-200 rounded-xl p-4 mb-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-semibold text-yellow-800">Sedang Digunakan</p>
                                <p class="text-sm text-yellow-700">${data.active_usage.user_name} (${data.active_usage.user_nim})</p>
                                ${kelas}
                                <p class="text-xs text-yellow-600">Sejak ${new Date(data.active_usage.used_at).toLocaleString('id-ID')}</p>
                            </div>
                            <button onclick="returnBox(${data.active_usage.id}, '${data.active_usage.user_name}', '${data.active_usage.user_nim}', '${data.active_usage.user_kelas || ''}', '${new Date(data.active_usage.used_at).toLocaleString('id-ID')}')" class="px-4 py-2 bg-yellow-600 text-white text-sm font-semibold rounded-lg hover:bg-yellow-700 transition-colors">
                                Kembalikan
                            </button>
                        </div>
                    </div>
                `;
            }

            renderBoxComponents(data.box.box_components);
        } catch (err) {
            document.getElementById('boxComponentsList').innerHTML = '<p class="text-sm text-red-500">Gagal memuat data</p>';
        }
    }

    function renderBoxComponents(components) {
        const container = document.getElementById('boxComponentsList');

        if (!components || components.length === 0) {
            container.innerHTML = '<p class="text-sm text-gray-400 italic">Belum ada komponen dalam box ini</p>';
            return;
        }

        let html = `
            <div class="overflow-x-auto">
                <table class="w-full text-sm table-responsive-cards">
                    <thead>
                        <tr class="border-b border-gray-100">
                            <th class="text-left py-2 px-3 font-semibold text-gray-600">Komponen</th>
                            <th class="text-center py-2 px-3 font-semibold text-gray-600">Jumlah</th>
                            <th class="text-center py-2 px-3 font-semibold text-gray-600">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
        `;

        components.forEach(bc => {
            html += `
                <tr class="border-b border-gray-50">
                    <td data-label="Komponen" class="py-2 px-3 font-medium text-gray-900">${bc.component.name}</td>
                    <td data-label="Jumlah" class="py-2 px-3 text-center">
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-700">
                            ${bc.quantity}
                        </span>
                    </td>
                    <td data-label="Aksi" class="py-2 px-3 text-center">
                        <button onclick="removeComponent(${bc.id})" class="p-1 text-red-600 hover:bg-red-50 rounded transition-colors" title="Keluarkan">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </td>
                </tr>
            `;
        });

        html += '</tbody></table></div>';
        container.innerHTML = html;
    }

    document.getElementById('addComponentForm').addEventListener('submit', async function(e) {
        e.preventDefault();
        const errorEl = document.getElementById('addComponentError');
        errorEl.classList.add('hidden');

        const formData = new FormData(this);

        try {
            const response = await fetch(`/boxes/${currentBoxId}/components`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json',
                },
                body: formData,
            });

            const data = await response.json();

            if (data.success) {
                viewBox(currentBoxId);
                this.reset();
                this.querySelector('input[name="quantity"]').value = '1';
            } else {
                errorEl.textContent = data.message || 'Terjadi kesalahan';
                errorEl.classList.remove('hidden');
            }
        } catch (err) {
            errorEl.textContent = 'Terjadi kesalahan jaringan';
            errorEl.classList.remove('hidden');
        }
    });

    async function removeComponent(boxComponentId) {
        if (!confirm('Yakin ingin mengeluarkan komponen dari box? Stok akan dikembalikan.')) return;

        try {
            const response = await fetch(`/boxes/${currentBoxId}/components/${boxComponentId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json',
                },
            });

            const data = await response.json();

            if (data.success) {
                viewBox(currentBoxId);
            } else {
                showToast('error', 'Gagal', data.message || 'Gagal mengeluarkan komponen');
            }
        } catch (err) {
            showToast('error', 'Kesalahan', 'Terjadi kesalahan jaringan');
        }
    }

    function closeBoxDetailModal() {
        const modal = document.getElementById('boxDetailModal');
        modal.style.display = 'none';
        modal.classList.add('hidden');
    }

    let currentReturnUsageId = null;

    function returnBox(usageId, userName, userNim, userKelas, usedAt) {
        currentReturnUsageId = usageId;
        document.getElementById('returnUserName').textContent = userName;
        document.getElementById('returnUserNim').textContent = userNim;
        document.getElementById('returnUserKelas').textContent = userKelas || '-';
        document.getElementById('returnUsedAt').textContent = usedAt;
        document.getElementById('returnError').classList.add('hidden');
        document.getElementById('returnConfirmBtn').disabled = false;
        document.getElementById('returnConfirmBtn').textContent = 'Kembalikan';

        const modal = document.getElementById('returnModal');
        modal.style.display = 'flex';
        modal.classList.remove('hidden');
    }

    function closeReturnModal() {
        const modal = document.getElementById('returnModal');
        modal.style.display = 'none';
        modal.classList.add('hidden');
    }

    async function confirmReturnBox() {
        const btn = document.getElementById('returnConfirmBtn');
        const errorEl = document.getElementById('returnError');
        btn.disabled = true;
        btn.textContent = 'Memproses...';
        errorEl.classList.add('hidden');

        try {
            const response = await fetch(`/box-scan/return/${currentReturnUsageId}`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json',
                },
            });

            const data = await response.json();

            if (data.success) {
                closeReturnModal();
                viewBox(currentBoxId);
            } else {
                errorEl.textContent = data.message || 'Gagal mengembalikan box';
                errorEl.classList.remove('hidden');
                btn.disabled = false;
                btn.textContent = 'Kembalikan';
            }
        } catch (err) {
            errorEl.textContent = 'Terjadi kesalahan jaringan';
            errorEl.classList.remove('hidden');
            btn.disabled = false;
            btn.textContent = 'Kembalikan';
        }
    }

    async function deleteBox(id, code) {
        if (!confirm(`Yakin ingin menghapus ${code}? Semua komponen dalam box akan dikembalikan stoknya.`)) return;

        try {
            const response = await fetch(`/boxes/${id}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json',
                },
            });

            const data = await response.json();

            if (data.success) {
                location.reload();
            } else {
                showToast('error', 'Gagal', data.message || 'Gagal menghapus box');
            }
        } catch (err) {
            showToast('error', 'Kesalahan', 'Terjadi kesalahan jaringan');
        }
    }

    document.getElementById('generateModal').addEventListener('click', function(e) {
        if (e.target === this) closeGenerateModal();
    });

    document.getElementById('bulkAddModal').addEventListener('click', function(e) {
        if (e.target === this) closeBulkAddModal();
    });

    document.getElementById('boxDetailModal').addEventListener('click', function(e) {
        if (e.target === this) closeBoxDetailModal();
    });

    document.getElementById('qrModal').addEventListener('click', function(e) {
        if (e.target === this) closeQRModal();
    });

    document.getElementById('returnModal').addEventListener('click', function(e) {
        if (e.target === this) closeReturnModal();
    });

    document.getElementById('editBoxModal').addEventListener('click', function(e) {
        if (e.target === this) closeEditBoxModal();
    });

    function openEditBoxModal(id, name, location, notes) {
        document.getElementById('editBoxId').value = id;
        document.getElementById('editBoxName').value = name;
        document.getElementById('editBoxNameDisplay').value = name;
        document.getElementById('editBoxLocation').value = location || '';
        document.getElementById('editBoxNotes').value = notes || '';
        document.getElementById('editBoxError').classList.add('hidden');

        const modal = document.getElementById('editBoxModal');
        modal.style.display = 'flex';
        modal.classList.remove('hidden');
    }

    function closeEditBoxModal() {
        const modal = document.getElementById('editBoxModal');
        modal.style.display = 'none';
        modal.classList.add('hidden');
    }

    document.getElementById('editBoxForm').addEventListener('submit', async function(e) {
        e.preventDefault();
        const errorEl = document.getElementById('editBoxError');
        const btn = this.querySelector('button[type="submit"]');
        errorEl.classList.add('hidden');
        btn.disabled = true;
        btn.textContent = 'Menyimpan...';

        const boxId = document.getElementById('editBoxId').value;
        const notes = document.getElementById('editBoxNotes').value;

        try {
            const response = await fetch(`/boxes/${boxId}`, {
                method: 'PUT',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ notes: notes }),
            });

            const data = await response.json();

            if (data.success) {
                closeEditBoxModal();
                location.reload();
            } else {
                errorEl.textContent = data.message || 'Gagal menyimpan catatan';
                errorEl.classList.remove('hidden');
                btn.disabled = false;
                btn.textContent = 'Simpan & Sync ke Grup';
            }
        } catch (err) {
            errorEl.textContent = 'Terjadi kesalahan jaringan';
            errorEl.classList.remove('hidden');
            btn.disabled = false;
            btn.textContent = 'Simpan & Sync ke Grup';
        }
    });

    function showQR(code, name) {
        const modal = document.getElementById('qrModal');
        modal.style.display = 'flex';
        modal.classList.remove('hidden');

        document.getElementById('qrBoxCode').textContent = code;
        document.getElementById('qrBoxName').textContent = name;

        const url = '{{ url("/") }}/box-scan/' + code;
        document.getElementById('qrUrl').textContent = url;

        const qrContainer = document.getElementById('qrCode');
        qrContainer.innerHTML = '';

        if (currentQRCode) {
            currentQRCode = null;
        }

        currentQRCode = new QRCode(qrContainer, {
            text: url,
            width: 200,
            height: 200,
            colorDark: '#000000',
            colorLight: '#ffffff',
            correctLevel: QRCode.CorrectLevel.M,
        });
    }

    function closeQRModal() {
        const modal = document.getElementById('qrModal');
        modal.style.display = 'none';
        modal.classList.add('hidden');
    }

    function downloadQR() {
        const canvas = document.querySelector('#qrCode canvas');
        if (!canvas) return;

        const link = document.createElement('a');
        link.download = 'QR-' + document.getElementById('qrBoxCode').textContent + '.png';
        link.href = canvas.toDataURL('image/png');
        link.click();
    }

    function showToast(type, title, message) {
        const container = document.getElementById('boxesToastContainer') || (function() {
            const c = document.createElement('div');
            c.id = 'boxesToastContainer';
            c.className = 'fixed bottom-6 right-6 z-[100] max-w-sm';
            c.innerHTML = `
                <div id="boxesToast" class="bg-white rounded-xl shadow-2xl border p-4 flex items-start gap-3 hidden transition-all duration-300">
                    <div id="boxesToastIcon" class="w-8 h-8 rounded-lg flex items-center justify-center flex-shrink-0"></div>
                    <div class="flex-1 min-w-0">
                        <p id="boxesToastTitle" class="text-sm font-semibold text-gray-900"></p>
                        <p id="boxesToastMsg" class="text-sm text-gray-600 mt-0.5"></p>
                    </div>
                    <button onclick="document.getElementById('boxesToast').classList.add('hidden')" class="text-gray-400 hover:text-gray-600 flex-shrink-0">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
            `;
            document.body.appendChild(c);
            return c;
        })();

        const toast = document.getElementById('boxesToast');
        const icon = document.getElementById('boxesToastIcon');
        const colors = { error: 'bg-red-100', warning: 'bg-yellow-100', success: 'bg-green-100' };
        const iconColors = { error: 'text-red-600', warning: 'text-yellow-600', success: 'text-green-600' };
        const icons = {
            error: '<svg class="w-5 h-5 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/></svg>',
            warning: '<svg class="w-5 h-5 text-yellow-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/></svg>',
            success: '<svg class="w-5 h-5 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>'
        };

        icon.className = 'w-8 h-8 rounded-lg flex items-center justify-center flex-shrink-0 ' + (colors[type] || colors.error);
        icon.innerHTML = icons[type] || icons.error;
        document.getElementById('boxesToastTitle').textContent = title;
        document.getElementById('boxesToastMsg').textContent = message;
        toast.classList.remove('hidden');

        clearTimeout(window._boxesToastTimer);
        window._boxesToastTimer = setTimeout(() => toast.classList.add('hidden'), 3500);
    }
</script>
@endpush
