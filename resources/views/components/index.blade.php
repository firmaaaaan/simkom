@extends('layouts.app')

@section('title', 'Komponen')
@section('header', 'Data Komponen')

@section('content')
<div x-data="{ activeTab: '{{ in_array(request('tab'), ['boxes', 'history', 'borrowings']) ? request('tab') : 'components' }}', selected: [], toggleSelection(id) { if (this.selected.includes(id)) { this.selected = this.selected.filter(i => i !== id); } else { this.selected.push(id); } } }">
    <div class="mb-6">
        <p class="text-sm text-gray-500">Kelola seluruh data komponen dan box penyimpanan laboratorium</p>
    </div>

    <div class="flex items-center gap-1 mb-6 border-b border-gray-200">
        <button @click="activeTab = 'components'" :class="activeTab === 'components' ? 'border-green-600 text-green-600' : 'border-transparent text-gray-500 hover:text-gray-700'"
            class="px-4 py-3 text-sm font-semibold border-b-2 transition-colors -mb-px">
            Daftar Komponen
        </button>
        <button @click="activeTab = 'boxes'" :class="activeTab === 'boxes' ? 'border-green-600 text-green-600' : 'border-transparent text-gray-500 hover:text-gray-700'"
            class="px-4 py-3 text-sm font-semibold border-b-2 transition-colors -mb-px">
            Box Penyimpanan
        </button>
        <button @click="activeTab = 'history'" :class="activeTab === 'history' ? 'border-green-600 text-green-600' : 'border-transparent text-gray-500 hover:text-gray-700'"
            class="px-4 py-3 text-sm font-semibold border-b-2 transition-colors -mb-px">
            Riwayat Penggunaan
        </button>
        <button @click="activeTab = 'borrowings'" :class="activeTab === 'borrowings' ? 'border-green-600 text-green-600' : 'border-transparent text-gray-500 hover:text-gray-700'"
            class="px-4 py-3 text-sm font-semibold border-b-2 transition-colors -mb-px">
            Pinjam Komponen
        </button>
    </div>

    <div x-show="activeTab === 'components'" x-cloak>
        <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <p class="text-sm text-gray-500">Kelola seluruh data komponen laboratorium</p>
            <div class="flex items-center gap-2">
                <button x-show="selected.length > 0" x-cloak @click="if(confirm('Yakin ingin menghapus ' + selected.length + ' komponen?')) $refs.bulkForm.submit()"
                    class="inline-flex items-center gap-2 px-4 py-2 bg-red-600 text-white text-sm font-medium rounded-lg hover:bg-red-700 transition-colors">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                    </svg>
                    Hapus Terpilih (<span x-text="selected.length"></span>)
                </button>
                <a href="{{ route('components.import') }}" class="inline-flex items-center gap-2 px-4 py-2 border border-blue-600 text-blue-600 text-sm font-medium rounded-lg hover:bg-blue-50 transition-colors">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5" />
                    </svg>
                    Import
                </a>
                <a href="{{ route('components.export') }}" class="inline-flex items-center gap-2 px-4 py-2 border border-purple-600 text-purple-600 text-sm font-medium rounded-lg hover:bg-purple-50 transition-colors">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" />
                    </svg>
                    Export
                </a>
                <a href="{{ route('components.create') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700 transition-colors">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                    Tambah Komponen
                </a>
            </div>
        </div>

        @if(session('success'))
            <div class="mb-4 px-4 py-3 bg-green-50 border border-green-200 text-green-700 rounded-lg text-sm flex items-center gap-2">
                <svg class="w-5 h-5 text-green-600 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                {{ session('success') }}
            </div>
        @endif

        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <h2 class="text-base font-semibold text-gray-800">Daftar Komponen</h2>
                <form action="{{ route('components.index') }}" method="GET" class="flex items-center gap-2">
                    <input type="hidden" name="tab" value="components">
                    <select name="category" class="text-sm border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-green-500">
                        <option value="">Semua Kategori</option>
                        <option value="IoT" {{ request('category') === 'IoT' ? 'selected' : '' }}>IoT</option>
                        <option value="Jaringan" {{ request('category') === 'Jaringan' ? 'selected' : '' }}>Jaringan</option>
                        <option value="Lain-lain" {{ request('category') === 'Lain-lain' ? 'selected' : '' }}>Lain-lain</option>
                    </select>
                    <select name="status" class="text-sm border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-green-500">
                        <option value="">Semua Status</option>
                        <option value="Tersedia" {{ request('status') === 'Tersedia' ? 'selected' : '' }}>Tersedia</option>
                        <option value="Digunakan" {{ request('status') === 'Digunakan' ? 'selected' : '' }}>Digunakan</option>
                        <option value="Rusak" {{ request('status') === 'Rusak' ? 'selected' : '' }}>Rusak</option>
                        <option value="Maintenance" {{ request('status') === 'Maintenance' ? 'selected' : '' }}>Maintenance</option>
                    </select>
                    <div class="relative">
                        <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                        </svg>
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari komponen..." class="pl-9 pr-4 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent w-full sm:w-48">
                    </div>
                    <button type="submit" class="px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700 transition-colors">Cari</button>
                    @if(request('search') || request('category') || request('status'))
                        <a href="{{ route('components.index') }}?tab=components" class="px-4 py-2 bg-gray-100 text-gray-600 text-sm font-medium rounded-lg hover:bg-gray-200 transition-colors">Reset</a>
                    @endif
                </form>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm table-responsive-cards">
                    <thead>
                        <tr class="border-b border-gray-100">
                            <th class="hidden md:table-cell text-left px-6 py-3 font-medium text-gray-500 w-10">
                                <input type="checkbox" class="rounded border-gray-300 text-green-600 focus:ring-green-500"
                                    @change="selected = $event.target.checked ? @js($components->pluck('id')->toArray()) : []">
                            </th>
                            <th class="text-left px-6 py-3 font-medium text-gray-500">No</th>
                            <th class="text-left px-6 py-3 font-medium text-gray-500">Gambar</th>
                            <th class="text-left px-6 py-3 font-medium text-gray-500">Kode</th>
                            <th class="text-left px-6 py-3 font-medium text-gray-500">Nama</th>
                            <th class="text-left px-6 py-3 font-medium text-gray-500">Kategori</th>
                            <th class="text-left px-6 py-3 font-medium text-gray-500">Merk/Model</th>
                            <th class="text-left px-6 py-3 font-medium text-gray-500">Stok</th>
                            <th class="text-left px-6 py-3 font-medium text-gray-500">Box</th>
                            <th class="text-left px-6 py-3 font-medium text-gray-500">Status</th>
                            <th class="text-left px-6 py-3 font-medium text-gray-500">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($components as $index => $item)
                            <tr class="border-b border-gray-100 hover:bg-gray-50 transition-colors">
                                <td class="hidden md:table-cell px-6 py-3" data-label="Checkbox">
                                    <input type="checkbox" class="rounded border-gray-300 text-green-600 focus:ring-green-500" value="{{ $item->id }}" @change="toggleSelection('{{ $item->id }}')">
                                </td>
                                <td class="px-6 py-3" data-label="No">
                                    {{ $components->firstItem() + $index }}
                                </td>
                                <td class="px-6 py-3" data-label="Gambar">
                                    @if($item->image)
                                        <img src="{{ Storage::disk('public')->url($item->image) }}" alt="{{ $item->name }}" class="w-10 h-10 rounded-lg object-cover">
                                    @else
                                        <div class="w-10 h-10 rounded-lg bg-gray-100 flex items-center justify-center">
                                            <svg class="w-5 h-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909M3.75 21h16.5A2.25 2.25 0 0022.5 18.75V5.25A2.25 2.25 0 0020.25 3H3.75A2.25 2.25 0 001.5 5.25v13.5A2.25 2.25 0 003.75 21z" />
                                            </svg>
                                        </div>
                                    @endif
                                </td>
                                <td class="px-6 py-3 font-medium" data-label="Kode">
                                    {{ $item->code }}
                                </td>
                                <td class="px-6 py-3" data-label="Nama">
                                    {{ $item->name }}
                                </td>
                                <td class="px-6 py-3" data-label="Kategori">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                        {{ $item->category }}
                                    </span>
                                </td>
                                <td class="px-6 py-3" data-label="Merk/Model">
                                    {{ $item->brand }} {{ $item->model }}
                                </td>
                                <td class="px-6 py-3" data-label="Stok">
                                    {{ $item->quantity }}
                                </td>
                                <td class="px-6 py-3" data-label="Box">
                                    {{ $item->box->name ?? '-' }}
                                </td>
                                <td class="px-6 py-3" data-label="Status">
                                    @switch($item->status)
                                        @case('Tersedia')
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                {{ $item->status }}
                                            </span>
                                            @break
                                        @case('Digunakan')
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                                {{ $item->status }}
                                            </span>
                                            @break
                                        @case('Rusak')
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                {{ $item->status }}
                                            </span>
                                            @break
                                        @case('Maintenance')
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                                                {{ $item->status }}
                                            </span>
                                            @break
                                        @default
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                                {{ $item->status }}
                                            </span>
                                    @endswitch
                                </td>
                                <td class="px-6 py-3" data-label="Aksi">
                                    <div class="flex items-center gap-2">
                                        @if($item->quantity > 0)
                                            <button type="button"
                                                class="js-borrow-component inline-flex items-center justify-center w-8 h-8 rounded-lg text-green-600 hover:bg-green-50 transition-colors"
                                                title="Pinjam"
                                                data-component-id="{{ $item->id }}"
                                                data-component-code="{{ $item->code }}"
                                                data-component-name="{{ $item->name }}"
                                                data-component-stock="{{ $item->quantity }}">
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                                                </svg>
                                            </button>
                                        @endif
                                        <a href="{{ route('components.edit', $item) }}" class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-blue-600 hover:bg-blue-50 transition-colors" title="Edit">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" />
                                            </svg>
                                        </a>
                                        <form action="{{ route('components.destroy', $item) }}" method="POST" class="inline" onsubmit="return confirm('Yakin ingin menghapus komponen ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-red-600 hover:bg-red-50 transition-colors" title="Hapus">
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                                                </svg>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="11" class="px-6 py-12 text-center">
                                    <svg class="w-12 h-12 text-gray-300 mx-auto mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5m6 4.125l2.25 2.25m0 0l2.25-2.25M12 13.875V7.5" />
                                    </svg>
                                    <p class="text-gray-500 text-sm">Tidak ada data komponen ditemukan</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($components->hasPages())
                <div class="px-6 py-3 border-t border-gray-100">
                    {{ $components->links() }}
                </div>
            @endif
        </div>

        <form x-ref="bulkForm" action="{{ route('components.bulk-destroy') }}" method="POST" class="hidden">
            @csrf
            @method('DELETE')
            <template x-for="id in selected" :key="id">
                <input type="hidden" name="ids[]" :value="id">
            </template>
        </form>
    </div>

    <div x-show="activeTab === 'boxes'" x-cloak>
        @include('components.boxes', ['boxes' => $boxes ?? collect()])
    </div>

    <div x-show="activeTab === 'history'" x-cloak>
        @include('components.history')
    </div>

    <div x-show="activeTab === 'borrowings'" x-cloak>
        @include('components.borrowings')
    </div>
</div>

{{-- Modal Pinjam Komponen --}}
<div id="borrowComponentModal" class="fixed inset-0 bg-black/50 z-50 hidden items-center justify-center p-4" style="display:none">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-md" onclick="event.stopPropagation()">
        <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
            <div>
                <h3 class="text-lg font-bold text-gray-900">Pinjam Komponen</h3>
                <p class="text-sm text-gray-500">Stok akan berkurang saat dipinjam</p>
            </div>
            <button type="button" id="closeBorrowModalBtn" class="p-2 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-lg transition-colors">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
        <form id="borrowComponentForm" class="p-6 space-y-4">
            <input type="hidden" id="borrowComponentId" name="component_id">
            <div class="bg-gray-50 border border-gray-200 rounded-xl p-4">
                <p class="text-sm font-semibold text-gray-800" id="borrowComponentName"></p>
                <p class="text-xs text-gray-500 mt-0.5">Kode: <span id="borrowComponentCode" class="font-mono"></span> · Stok: <span id="borrowComponentStock" class="font-bold"></span></p>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Jumlah (Qty) *</label>
                    <input type="number" name="quantity" id="borrowQuantity" min="1" value="1" required
                        class="w-full px-4 py-2.5 text-sm border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal *</label>
                    <input type="date" name="borrowed_at" id="borrowDate" value="{{ now()->format('Y-m-d') }}" readonly
                        class="w-full px-4 py-2.5 text-sm border border-gray-200 rounded-lg bg-gray-50 text-gray-500">
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">NIM *</label>
                <input type="text" name="user_nim" id="borrowNim" required maxlength="50" placeholder="Contoh: 2210512001"
                    class="w-full px-4 py-2.5 text-sm border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Nama *</label>
                <input type="text" name="user_name" id="borrowName" required maxlength="255" placeholder="Nama peminjam"
                    class="w-full px-4 py-2.5 text-sm border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
            </div>

            <div id="borrowError" class="hidden bg-red-50 border border-red-200 text-red-700 text-sm px-4 py-3 rounded-xl"></div>

            <div class="flex items-center gap-3 pt-2">
                <button type="submit" id="borrowSubmitBtn" class="flex-1 px-4 py-2.5 bg-green-600 text-white text-sm font-semibold rounded-lg hover:bg-green-700 transition-colors">
                    Pinjam Sekarang
                </button>
                <button type="button" id="cancelBorrowBtn" class="px-4 py-2.5 bg-gray-100 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-200 transition-colors">
                    Batal
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    (function() {
        const modal = document.getElementById('borrowComponentModal');
        const form = document.getElementById('borrowComponentForm');
        const errorEl = document.getElementById('borrowError');
        const submitBtn = document.getElementById('borrowSubmitBtn');

        function openBorrowModal(btn) {
            document.getElementById('borrowComponentId').value = btn.dataset.componentId;
            document.getElementById('borrowComponentName').textContent = btn.dataset.componentName;
            document.getElementById('borrowComponentCode').textContent = btn.dataset.componentCode;
            document.getElementById('borrowComponentStock').textContent = btn.dataset.componentStock;
            document.getElementById('borrowQuantity').value = 1;
            document.getElementById('borrowQuantity').max = btn.dataset.componentStock;
            document.getElementById('borrowNim').value = '';
            document.getElementById('borrowName').value = '';
            document.getElementById('borrowDate').value = '{{ now()->format('Y-m-d') }}';
            errorEl.classList.add('hidden');
            submitBtn.disabled = false;
            submitBtn.textContent = 'Pinjam Sekarang';
            modal.style.display = 'flex';
            modal.classList.remove('hidden');
        }

        function closeBorrowModal() {
            modal.style.display = 'none';
            modal.classList.add('hidden');
        }

        document.querySelectorAll('.js-borrow-component').forEach(btn => {
            btn.addEventListener('click', () => openBorrowModal(btn));
        });

        document.getElementById('closeBorrowModalBtn').addEventListener('click', closeBorrowModal);
        document.getElementById('cancelBorrowBtn').addEventListener('click', closeBorrowModal);
        modal.addEventListener('click', function(e) {
            if (e.target === this) closeBorrowModal();
        });

        form.addEventListener('submit', async function(e) {
            e.preventDefault();
            const stock = parseInt(document.getElementById('borrowComponentStock').textContent) || 0;
            const qty = parseInt(document.getElementById('borrowQuantity').value) || 0;
            errorEl.classList.add('hidden');

            if (qty < 1) {
                errorEl.textContent = 'Jumlah minimal 1';
                errorEl.classList.remove('hidden');
                return;
            }
            if (qty > stock) {
                errorEl.textContent = 'Jumlah melebihi stok tersedia (' + stock + ')';
                errorEl.classList.remove('hidden');
                return;
            }

            submitBtn.disabled = true;
            submitBtn.textContent = 'Memproses...';

            try {
                const response = await fetch('{{ route('component-borrowings.store') }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({
                        component_id: document.getElementById('borrowComponentId').value,
                        quantity: qty,
                        user_nim: document.getElementById('borrowNim').value.trim(),
                        user_name: document.getElementById('borrowName').value.trim(),
                        borrowed_at: document.getElementById('borrowDate').value,
                    }),
                });
                const data = await response.json();
                if (data.success) {
                    closeBorrowModal();
                    location.reload();
                } else {
                    errorEl.textContent = data.message || 'Terjadi kesalahan';
                    errorEl.classList.remove('hidden');
                    submitBtn.disabled = false;
                    submitBtn.textContent = 'Pinjam Sekarang';
                }
            } catch (err) {
                errorEl.textContent = 'Terjadi kesalahan jaringan';
                errorEl.classList.remove('hidden');
                submitBtn.disabled = false;
                submitBtn.textContent = 'Pinjam Sekarang';
            }
        });
    })();
</script>
@endpush
@endsection
