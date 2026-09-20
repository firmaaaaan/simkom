@extends('layouts.app')

@section('title', 'Input Pemeliharaan')
@section('header', 'Input Pemeliharaan Komputer')

@section('content')
<div class="mb-6">
    <a href="{{ route('maintenance.index') }}" class="inline-flex items-center gap-1.5 text-sm text-gray-500 hover:text-green-600 transition-colors">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
        </svg>
        Kembali
    </a>
</div>

<form action="{{ route('maintenance.store') }}" method="POST" id="maintenanceForm">
    @csrf

    <div class="bg-white rounded-xl border border-gray-200 p-6 mb-6">
        <h2 class="text-lg font-bold text-gray-800 mb-4">Informasi Pemeliharaan</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Laboratorium <span class="text-red-500">*</span></label>
                <select name="laboratory_id" id="laboratory_id" required
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 text-sm">
                    <option value="">Pilih Lab</option>
                    @foreach($laboratories as $lab)
                        <option value="{{ $lab->id }}" {{ $selectedLab == $lab->id ? 'selected' : '' }}>{{ $lab->name }}</option>
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
                        <option value="{{ $year->id }}" {{ ($selectedYear == $year->id) || (!$selectedYear && $year->status == 'Aktif') ? 'selected' : '' }}>{{ $year->name }}</option>
                    @endforeach
                </select>
                @error('academic_year_id') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal <span class="text-red-500">*</span></label>
                <input type="date" name="maintenance_date" value="{{ old('maintenance_date', date('Y-m-d')) }}" required
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 text-sm">
                @error('maintenance_date') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Nama Petugas</label>
                <input type="text" name="inspector_name" value="{{ old('inspector_name') }}" placeholder="Nama petugas"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 text-sm">
            </div>
        </div>
    </div>

    @if($selectedLab && $computers->count() > 0)
        @php
            $allItems = \App\Models\MaintenanceChecklist::getChecklistItems();
        @endphp

        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden mb-6">
            <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                <div>
                    <h2 class="text-lg font-bold text-gray-800">Form Pemeriksaan</h2>
                    <p class="text-xs text-gray-500 mt-1">Laboratorium: <span class="font-semibold">{{ $computers->first()->laboratory->name ?? '-' }}</span> | Total: <span class="font-semibold">{{ $computers->count() }}</span> komputer</p>
                </div>
                <label class="inline-flex items-center gap-2 px-3 py-1.5 border border-gray-200 rounded-lg cursor-pointer hover:bg-gray-50 transition-colors">
                    <input type="checkbox" id="checkAll" class="rounded border-gray-300 text-green-600 focus:ring-green-500">
                    <span class="text-sm font-medium text-gray-700">Centang Semua</span>
                </label>
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
                                           title="Centang semua untuk {{ $computer->code }}">
                                </th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($allItems as $category => $categoryData)
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
                                        <td class="text-center border border-gray-200">
                                            <input type="checkbox" name="{{ $category }}_{{ $itemNum }}_{{ $computer->id }}" value="1"
                                                class="check-item rounded border-gray-300 text-green-600 focus:ring-green-500">
                                        </td>
                                    @endforeach
                                </tr>
                            @endforeach
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="bg-white rounded-xl border border-gray-200 p-6 mb-6">
            <h2 class="text-lg font-bold text-gray-800 mb-4">Catatan Lain</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Pemeriksaan Komputer</label>
                    <textarea name="notes_computer" rows="2" placeholder="Catatan pemeriksaan komputer..."
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 text-sm">{{ old('notes_computer') }}</textarea>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Pemeriksaan Mouse dan Keyboard</label>
                    <textarea name="notes_mouse_keyboard" rows="2" placeholder="Catatan pemeriksaan mouse dan keyboard..."
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 text-sm">{{ old('notes_mouse_keyboard') }}</textarea>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Pemeriksaan UPS</label>
                    <textarea name="notes_ups" rows="2" placeholder="Catatan pemeriksaan UPS..."
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 text-sm">{{ old('notes_ups') }}</textarea>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Pemeriksaan Monitor</label>
                    <textarea name="notes_monitor" rows="2" placeholder="Catatan pemeriksaan monitor..."
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 text-sm">{{ old('notes_monitor') }}</textarea>
                </div>
            </div>
        </div>

        <div class="flex items-center gap-3">
            <button type="submit" class="inline-flex items-center gap-2 px-6 py-2 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700 transition-colors">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                </svg>
                Simpan Pemeliharaan
            </button>
            <a href="{{ route('maintenance.index') }}" class="px-4 py-2 text-sm text-gray-600 hover:text-gray-800">Batal</a>
        </div>
    @elseif($selectedLab)
        <div class="bg-white rounded-xl border border-gray-200 p-12 text-center">
            <p class="text-gray-500">Tidak ada komputer di laboratorium ini.</p>
        </div>
    @else
        <div class="bg-white rounded-xl border border-gray-200 p-12 text-center">
            <svg class="w-12 h-12 text-gray-300 mx-auto mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1">
                <path stroke-linecap="round" stroke-linejoin="round" d="M11.42 15.17l-5.1-5.1m0 0L11.42 4.97m-5.1 5.1H21" />
            </svg>
            <p class="text-gray-500">Pilih laboratorium terlebih dahulu untuk memulai pemeliharaan</p>
        </div>
    @endif
</form>

@push('scripts')
<script>
    document.getElementById('laboratory_id').addEventListener('change', function() {
        const params = new URLSearchParams(window.location.search);
        params.set('laboratory_id', this.value);
        window.location.href = '{{ route("maintenance.create") }}?' + params.toString();
    });

    document.getElementById('checkAll').addEventListener('change', function() {
        document.querySelectorAll('.check-item').forEach(function(cb) {
            cb.checked = document.getElementById('checkAll').checked;
        });
        // Update per-computer checkboxes
        document.querySelectorAll('.check-all-computer').forEach(function(cb) {
            cb.checked = document.getElementById('checkAll').checked;
            cb.indeterminate = false;
        });
    });

    // Per-computer "Centang Semua" checkboxes
    document.querySelectorAll('.check-all-computer').forEach(function(computerCheckbox) {
        computerCheckbox.addEventListener('change', function() {
            const computerId = this.dataset.computerId;
            document.querySelectorAll('.check-item[name$="_' + computerId + '"]').forEach(function(cb) {
                cb.checked = computerCheckbox.checked;
            });
        });
    });

    // Sync per-computer checkbox state when individual items change
    document.querySelectorAll('.check-item').forEach(function(itemCheckbox) {
        itemCheckbox.addEventListener('change', function() {
            const nameMatch = this.name.match(/_(\d+)$/);
            if (!nameMatch) return;
            const computerId = nameMatch[1];
            const computerCheckbox = document.getElementById('check-all-computer-' + computerId);
            if (!computerCheckbox) return;

            const allItems = document.querySelectorAll('.check-item[name$="_' + computerId + '"]');
            const checkedItems = document.querySelectorAll('.check-item[name$="_' + computerId + '"]:checked');

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
        });
    });
</script>
@endpush
@endsection
