@extends('layouts.app')

@section('title', 'Edit Pemeliharaan')
@section('header', 'Edit Pemeliharaan Komputer')

@section('content')
<div class="mb-6">
    <a href="{{ route('maintenance.show', $maintenance) }}" class="inline-flex items-center gap-1.5 text-sm text-gray-500 hover:text-green-600 transition-colors">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
        </svg>
        Kembali
    </a>
</div>

<form action="{{ route('maintenance.update', $maintenance) }}" method="POST" id="maintenanceForm">
    @csrf
    @method('PUT')

    <div class="bg-white rounded-xl border border-gray-200 p-6 mb-6">
        <h2 class="text-lg font-bold text-gray-800 mb-4">Informasi Pemeliharaan</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Laboratorium <span class="text-red-500">*</span></label>
                <select name="laboratory_id" id="laboratory_id" required
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 text-sm">
                    <option value="">Pilih Lab</option>
                    @foreach($laboratories as $lab)
                        <option value="{{ $lab->id }}" {{ $maintenance->laboratory_id == $lab->id ? 'selected' : '' }}>{{ $lab->name }}</option>
                    @endforeach
                </select>
                @error('laboratory_id') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Tahun Ajaran <span class="text-red-500">*</span></label>
                <select name="academic_year_id" required
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 text-sm">
                    <option value="">Pilih Tahun Ajaran</option>
                    @foreach($academicYears as $year)
                        <option value="{{ $year->id }}" {{ ($maintenance->academic_year_id == $year->id) || (!$maintenance->academic_year_id && $year->status == 'Aktif') ? 'selected' : '' }}>{{ $year->name }}</option>
                    @endforeach
                </select>
                @error('academic_year_id') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal <span class="text-red-500">*</span></label>
                <input type="date" name="maintenance_date" value="{{ old('maintenance_date', \Carbon\Carbon::parse($maintenance->maintenance_date)->format('Y-m-d')) }}" required
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 text-sm">
                @error('maintenance_date') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Nama Petugas</label>
                <input type="text" name="inspector_name" value="{{ old('inspector_name', $maintenance->inspector_name) }}" placeholder="Nama petugas"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 text-sm">
            </div>
        </div>
    </div>

    @if($computers->count() > 0)
        @php
            $statusLabels = \App\Models\MaintenanceChecklistItem::statuses();
            $statusSymbols = \App\Models\MaintenanceChecklistItem::symbols();
            $statusSudah = \App\Models\MaintenanceChecklistItem::STATUS_SUDAH;
        @endphp
        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden mb-6">
            <div class="px-6 py-4 border-b border-gray-100 flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h2 class="text-lg font-bold text-gray-800">Form Pemeriksaan</h2>
                    <p class="text-xs text-gray-500 mt-1">Laboratorium: <span class="font-semibold">{{ $maintenance->laboratory->name ?? '-' }}</span> | Total: <span class="font-semibold">{{ $computers->count() }}</span> komputer</p>
                </div>
                <div class="flex flex-wrap items-center gap-1.5">
                    <span class="text-xs text-gray-400 mr-1">Tandai semua:</span>
                    @foreach($statusLabels as $statusKey => $statusLabel)
                        <button type="button" data-bulk="{{ $statusKey }}"
                            class="inline-flex items-center gap-1 px-2.5 py-1.5 border text-xs font-medium rounded-lg transition-colors
                                {{ $statusKey === $statusSudah ? 'border-green-200 text-green-700 bg-green-50 hover:bg-green-100' : ($statusKey === \App\Models\MaintenanceChecklistItem::STATUS_BELUM ? 'border-red-200 text-red-700 bg-red-50 hover:bg-red-100' : 'border-gray-200 text-gray-600 bg-gray-50 hover:bg-gray-100') }}">
                            <span>{{ $statusSymbols[$statusKey] }}</span> {{ $statusLabel }}
                        </button>
                    @endforeach
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm border-collapse">
                    <thead>
                        <tr class="bg-gray-50">
                            <th class="text-left px-4 py-2 font-medium text-gray-600 border border-gray-200 w-10">No</th>
                            <th class="text-left px-4 py-2 font-medium text-gray-600 border border-gray-200 min-w-[300px]">Item</th>
                            @foreach($computers as $computer)
                                <th class="text-center px-2 py-2 font-medium text-gray-600 border border-gray-200 w-14 text-xs">
                                    {{ $computer->code }}
                                </th>
                            @endforeach
                        </tr>
                        <tr class="bg-gray-50">
                            <th class="px-4 py-2 border border-gray-200 w-10"></th>
                            <th class="px-4 py-2 border border-gray-200 min-w-[300px]"></th>
                            @foreach($computers as $computer)
                                <th class="px-2 py-2 border border-gray-200 w-14 text-center">
                                    <input type="checkbox" id="check-all-computer-{{ $computer->id }}"
                                           class="check-all-computer rounded border-gray-300 text-green-600 focus:ring-green-500"
                                           data-computer-id="{{ $computer->id }}"
                                           title="Tandai semua item {{ $computer->code }} sebagai Sudah">
                                </th>
                            @endforeach
                        </tr>
                        <tr class="bg-gray-50">
                            <th class="px-4 py-2 border border-gray-200 w-10"></th>
                            <th class="px-4 py-2 border border-gray-200 min-w-[300px]"></th>
                            @foreach($computers as $computer)
                                <th class="px-2 py-2 border border-gray-200 w-14 text-center">
                                    <button type="button" class="save-computer-btn text-green-600 hover:text-green-800 hover:bg-green-50 p-1 rounded transition-colors"
                                            data-computer-id="{{ $computer->id }}"
                                            data-computer-code="{{ $computer->code }}"
                                            title="Simpan data untuk {{ $computer->code }}">
                                        <svg class="w-4 h-4 mx-auto" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3M17 3l5 5-5 5M17 3v10" />
                                        </svg>
                                    </button>
                                </th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($checklistItems as $category => $categoryData)
                            <tr class="bg-green-50">
                                <td colspan="{{ 2 + $computers->count() }}" class="px-4 py-2 font-bold text-gray-800 border border-gray-200">
                                    {{ $category }}. {{ $categoryData['name'] }}
                                </td>
                            </tr>
                            @foreach($categoryData['items'] as $itemIndex => $question)
                                @php $itemNum = $itemIndex + 1; @endphp
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-2 text-gray-500 border border-gray-200 text-center">{{ $itemNum }}</td>
                                    <td class="px-4 py-2 text-gray-800 border border-gray-200">{{ $question }}</td>
                                    @foreach($computers as $computer)
                                        @php
                                            $key = "{$category}_{$itemNum}_{$computer->id}";
                                            $itemKey = "{$category}_{$itemNum}";
                                            $selected = $statuses[$itemKey][$computer->id] ?? ($items[$key]->status ?? $statusSudah);
                                        @endphp
                                        <td class="text-center border border-gray-200">
                                            <div class="inline-flex border border-gray-200 rounded-md overflow-hidden">
                                                @foreach($statusLabels as $statusKey => $statusLabel)
                                                    <label class="cursor-pointer border-r border-gray-200 last:border-r-0" title="{{ $statusLabel }}">
                                                        <input type="radio" class="status-radio sr-only peer" name="items[{{ $itemKey }}][{{ $computer->id }}]" value="{{ $statusKey }}" data-computer-id="{{ $computer->id }}" {{ $selected === $statusKey ? 'checked' : '' }}>
                                                        <span class="flex items-center justify-center w-6 h-6 text-xs font-bold text-gray-400 bg-white transition-colors peer-checked:text-white
                                                            {{ $statusKey === $statusSudah ? 'peer-checked:bg-green-600' : ($statusKey === \App\Models\MaintenanceChecklistItem::STATUS_BELUM ? 'peer-checked:bg-red-600' : 'peer-checked:bg-gray-500') }}">
                                                            {{ $statusSymbols[$statusKey] }}
                                                        </span>
                                                    </label>
                                                @endforeach
                                            </div>
                                        </td>
                                    @endforeach
                                </tr>
                            @endforeach
                        @endforeach
                    @endforeach
                </tbody>
            </table>
            </div>
            <div class="px-6 py-3 border-t border-gray-100 flex flex-wrap items-center gap-4 text-xs text-gray-500">
                <span class="font-medium">Keterangan:</span>
                @foreach($statusLabels as $statusKey => $statusLabel)
                    <span class="inline-flex items-center gap-1">
                        <span class="inline-flex items-center justify-center w-4 h-4 rounded text-[10px] font-bold text-white
                            {{ $statusKey === $statusSudah ? 'bg-green-600' : ($statusKey === \App\Models\MaintenanceChecklistItem::STATUS_BELUM ? 'bg-red-600' : 'bg-gray-500') }}">{{ $statusSymbols[$statusKey] }}</span>
                        {{ $statusLabel }}
                    </span>
                @endforeach
            </div>
        </div>
        </div>

        <div class="bg-white rounded-xl border border-gray-200 p-6 mb-6">
            <h2 class="text-lg font-bold text-gray-800 mb-4">Catatan Lain</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Pemeriksaan Komputer</label>
                    <textarea name="notes_computer" rows="2" placeholder="Catatan pemeriksaan komputer..."
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 text-sm">{{ old('notes_computer', $maintenance->notes_computer) }}</textarea>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Pemeriksaan Mouse dan Keyboard</label>
                    <textarea name="notes_mouse_keyboard" rows="2" placeholder="Catatan pemeriksaan mouse dan keyboard..."
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 text-sm">{{ old('notes_mouse_keyboard', $maintenance->notes_mouse_keyboard) }}</textarea>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Pemeriksaan UPS</label>
                    <textarea name="notes_ups" rows="2" placeholder="Catatan pemeriksaan UPS..."
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 text-sm">{{ old('notes_ups', $maintenance->notes_ups) }}</textarea>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Pemeriksaan Monitor</label>
                    <textarea name="notes_monitor" rows="2" placeholder="Catatan pemeriksaan monitor..."
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 text-sm">{{ old('notes_monitor', $maintenance->notes_monitor) }}</textarea>
                </div>
            </div>
        </div>

        <div class="flex items-center gap-3">
            <button type="submit" class="inline-flex items-center gap-2 px-6 py-2 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700 transition-colors">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                </svg>
                Perbarui Pemeliharaan
            </button>
            <a href="{{ route('maintenance.show', $maintenance) }}" class="px-4 py-2 text-sm text-gray-600 hover:text-gray-800">Batal</a>
        </div>
    @else
        <div class="bg-white rounded-xl border border-gray-200 p-12 text-center">
            <p class="text-gray-500">Tidak ada komputer di laboratorium ini.</p>
        </div>
    @endif
</form>

@push('scripts')
<script>
    function setAllRadios(value) {
        document.querySelectorAll('.status-radio').forEach(function(radio) {
            radio.checked = radio.value === value;
        });
        document.querySelectorAll('.check-all-computer').forEach(function(cb) {
            syncComputerCheckbox(cb.dataset.computerId);
        });
    }

    function setComputerRadios(computerId, value) {
        document.querySelectorAll('.status-radio[data-computer-id="' + computerId + '"]').forEach(function(radio) {
            radio.checked = radio.value === value;
        });
        syncComputerCheckbox(computerId);
    }

    function syncComputerCheckbox(computerId) {
        const computerCheckbox = document.getElementById('check-all-computer-' + computerId);
        if (!computerCheckbox) return;

        const allItems = document.querySelectorAll('.status-radio[data-computer-id="' + computerId + '"]');
        const checkedItems = document.querySelectorAll('.status-radio[data-computer-id="' + computerId + '"]:checked');

        if (checkedItems.length === 0) {
            computerCheckbox.checked = false;
            computerCheckbox.indeterminate = false;
        } else if (checkedItems.length === allItems.length) {
            computerCheckbox.checked = true;
            computerCheckbox.indeterminate = false;
        } else {
            computerCheckbox.checked = false;
            computerCheckbox.indeterminate = true;
        }
    }

    // Tombol "Tandai semua" per status
    document.querySelectorAll('[data-bulk]').forEach(function(btn) {
        btn.addEventListener('click', function() {
            setAllRadios(this.dataset.bulk);
        });
    });

    // Checkbox header per komputer: centang = semua Sudah, uncentang = semua Belum
    document.querySelectorAll('.check-all-computer').forEach(function(computerCheckbox) {
        computerCheckbox.addEventListener('change', function() {
            setComputerRadios(this.dataset.computerId, this.checked ? SBM__STATUS_SUDAH : SBM__STATUS_BELUM);
        });
    });

    // Sinkronkan state header saat radio individu berubah
    document.querySelectorAll('.status-radio').forEach(function(radio) {
        radio.addEventListener('change', function() {
            syncComputerCheckbox(this.dataset.computerId);
        });
    });

    // Save per computer functionality
    const maintenanceId = {{ $maintenance->id }};
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') 
        || document.querySelector('input[name="_token"]')?.value;

    document.querySelectorAll('.save-computer-btn').forEach(function(btn) {
        btn.addEventListener('click', async function() {
            const computerId = this.dataset.computerId;
            const computerCode = this.dataset.computerCode;
            const originalHtml = this.innerHTML;

            // Show loading state
            this.disabled = true;
            this.innerHTML = '<svg class="w-4 h-4 mx-auto animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>';

            try {
                // Kumpulkan status radio terpilih untuk komputer ini saja
                const formData = new FormData();
                formData.append('_token', csrfToken);

                document.querySelectorAll('.status-radio[data-computer-id="' + computerId + '"]:checked').forEach(function(radio) {
                    formData.append(radio.name, radio.value);
                });

                const response = await fetch('{{ route("maintenance.save-computer", ["maintenance" => ":maintenanceId", "computer" => ":computerId"]) }}'
                    .replace(':maintenanceId', maintenanceId)
                    .replace(':computerId', computerId), {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                    }
                });

                const result = await response.json();

                if (result.success) {
                    // Show success toast
                    showToast('success', result.message);
                    // Update button to show saved state
                    this.innerHTML = '<svg class="w-4 h-4 mx-auto text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg>';
                    this.title = 'Tersimpan: ' + result.saved_at;
                    this.classList.add('bg-green-50');
                    
                    // Reset button after 2 seconds
                    setTimeout(() => {
                        this.innerHTML = originalHtml;
                        this.classList.remove('bg-green-50');
                        this.title = 'Simpan data untuk ' + computerCode;
                    }, 2000);
                } else {
                    throw new Error(result.message || 'Gagal menyimpan');
                }
            } catch (error) {
                showToast('error', error.message);
                this.innerHTML = originalHtml;
            } finally {
                this.disabled = false;
            }
        });
    });

    function showToast(type, message) {
        // Remove existing toasts
        document.querySelectorAll('.toast-notification').forEach(el => el.remove());
        
        const toast = document.createElement('div');
        toast.className = 'toast-notification fixed bottom-4 right-4 z-50 px-4 py-3 rounded-lg shadow-lg text-white text-sm font-medium transition-all duration-300 transform translate-y-0 opacity-100';
        toast.style.backgroundColor = type === 'success' ? '#16a34a' : '#dc2626';
        toast.textContent = message;
        
        document.body.appendChild(toast);
        
        setTimeout(() => {
            toast.style.opacity = '0';
            toast.style.transform = 'translateY(10px)';
            setTimeout(() => toast.remove(), 300);
        }, 3000);
    }
</script>
@endpush
@endsection
