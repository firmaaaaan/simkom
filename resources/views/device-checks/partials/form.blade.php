@php
    $isEdit = $isEdit ?? false;
    $selectedLab = old('laboratory_id', $check->laboratory_id);
    $selectedYear = old('academic_year_id', $check->academic_year_id ?: \App\Models\AcademicYear::current()?->id);
    $formAction = $isEdit ? route('device-checks.update', $check) : route('device-checks.store');
@endphp

<form action="{{ $formAction }}" method="POST" id="deviceCheckForm">
    @csrf
    @if($isEdit)
        @method('PUT')
    @endif

    {{-- INFORMASI --}}
    <div class="bg-white rounded-xl border border-gray-200 p-6 mb-6">
        <h2 class="text-lg font-bold text-gray-800 mb-4">Informasi Pengecekan</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Laboratorium <span class="text-red-500">*</span></label>
                @if($isEdit)
                    <input type="hidden" name="laboratory_id" value="{{ $selectedLab }}">
                    <input type="text" value="{{ $laboratories->firstWhere('id', (int) $selectedLab)?->name ?? '-' }}" disabled
                        class="w-full px-3 py-2 border border-gray-200 bg-gray-50 rounded-lg text-sm text-gray-600">
                    <p class="mt-1 text-xs text-gray-400">Laboratorium tidak dapat diubah. Hapus lalu buat pengecekan baru bila salah pilih lab.</p>
                @else
                    <select name="laboratory_id" id="laboratory_id" required
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 text-sm">
                        <option value="">Pilih Lab</option>
                        @foreach($laboratories as $lab)
                            <option value="{{ $lab->id }}" {{ (string) $selectedLab === (string) $lab->id ? 'selected' : '' }}>{{ $lab->name }}</option>
                        @endforeach
                    </select>
                    @error('laboratory_id') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                @endif
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Tahun Ajaran <span class="text-red-500">*</span></label>
                <select name="academic_year_id" id="academic_year_id" required
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 text-sm">
                    <option value="">Pilih Tahun Ajaran</option>
                    @foreach($academicYears as $year)
                        <option value="{{ $year->id }}" {{ (string) $selectedYear === (string) $year->id ? 'selected' : '' }}>
                            {{ $year->name }} ({{ $year->periodLabel() }}){{ $year->status === 'Aktif' ? ' - Aktif' : '' }}
                        </option>
                    @endforeach
                </select>
                @error('academic_year_id') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal <span class="text-red-500">*</span></label>
                <input type="date" name="check_date" value="{{ old('check_date', $check->check_date?->format('Y-m-d') ?? now()->toDateString()) }}" required
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 text-sm">
                @error('check_date') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Nama Petugas</label>
                <input type="text" name="officer_name" value="{{ old('officer_name', $check->officer_name ?: auth()->user()->name) }}" placeholder="Nama petugas"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 text-sm">
            </div>
        </div>
    </div>

    @if($selectedLab)
        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden mb-6">
            <div class="px-6 py-4 border-b border-gray-100 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <div>
                    <h2 class="text-lg font-bold text-gray-800">Matriks Pengecekan Perangkat</h2>
                    <p class="text-xs text-gray-500 mt-1">
                        Laboratorium: <span class="font-semibold">{{ $laboratories->firstWhere('id', (int) $selectedLab)?->name ?? '-' }}</span>
                        | Total: <span class="font-semibold">{{ $computers->count() }}</span> komputer
                        | <span class="font-semibold">{{ \App\Models\DeviceCheck::itemColumnCount() }}</span> item perangkat
                    </p>
                </div>
                <label class="inline-flex items-center gap-2 px-3 py-1.5 border border-gray-200 rounded-lg cursor-pointer hover:bg-gray-50 transition-colors">
                    <input type="checkbox" id="checkAll" class="rounded border-gray-300 text-green-600 focus:ring-green-500">
                    <span class="text-sm font-medium text-gray-700">Centang Semua</span>
                </label>
            </div>

            <p class="px-6 pt-4 text-xs text-gray-500">
                Centang berarti perangkat <span class="font-semibold text-gray-700">ada dan berfungsi</span>.
                Sel yang dibiarkan kosong menandakan perangkat bermasalah atau tidak ada.
            </p>

            @include('device-checks.partials.matrix', ['editable' => true, 'computers' => $computers, 'checked' => $checked])
        </div>

        <div class="bg-white rounded-xl border border-gray-200 p-6 mb-6">
            <label class="block text-sm font-medium text-gray-700 mb-1">Catatan Tambahan</label>
            <textarea name="notes" rows="3" placeholder="Catatan umum pengecekan perangkat..."
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 text-sm">{{ old('notes', $check->notes) }}</textarea>
        </div>

        <div class="flex items-center gap-3">
            <button type="submit" class="inline-flex items-center gap-2 px-6 py-2 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700 transition-colors">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                </svg>
                {{ $isEdit ? 'Simpan Perubahan' : 'Simpan Pengecekan' }}
            </button>
            <a href="{{ $isEdit ? route('device-checks.show', $check) : route('device-checks.index') }}" class="px-4 py-2 text-sm text-gray-600 hover:text-gray-800">Batal</a>
        </div>
    @else
        <div class="bg-white rounded-xl border border-gray-200 p-12 text-center">
            <svg class="w-12 h-12 text-gray-300 mx-auto mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
            </svg>
            <p class="text-gray-500 text-sm">Pilih laboratorium terlebih dahulu untuk menampilkan matriks pengecekan</p>
        </div>
    @endif
</form>
