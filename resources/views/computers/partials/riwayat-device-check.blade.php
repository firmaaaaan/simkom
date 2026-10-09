{{--
    Riwayat pengecekan perangkat (device checks) untuk satu komputer.

    Variabel:
    - $deviceChecks : koleksi App\Models\DeviceCheck dengan relasi laboratory,
                      academicYear, dan items yang sudah difilter hanya untuk
                      komputer ini (lihat DeviceCheck::scopeForComputer).
    - $academicYears : daftar App\Models\AcademicYear untuk dropdown filter
                      tahun ajaran (opsional, dikirim dari controller).

    Filter tahun ajaran dibaca dari query string (?device_academic_year_id=)
    dan dikirim lewat GET ke halaman yang sedang dibuka. Daftar dibatasi 65vh
    agar halaman tidak terlalu panjang.
--}}
@php
    $itemColumns = \App\Models\DeviceCheck::itemColumns();

    $academicYears = $academicYears ?? [];
    $selectedAcademicYear = request('device_academic_year_id');
    $isFiltered = filled($selectedAcademicYear);
@endphp

@if(count($academicYears) > 0)
    <form method="GET" class="px-6 py-4 border-b border-gray-100 flex flex-wrap items-end gap-3">
        {{-- Pertahankan tab pengecekan perangkat aktif setelah filter diterapkan. --}}
        <input type="hidden" name="tab" value="perangkat">
        <div>
            <label for="filter-device-academic-year" class="block text-xs text-gray-400 mb-1">Tahun Ajaran</label>
            <select id="filter-device-academic-year" name="device_academic_year_id" class="px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500">
                <option value="">Semua tahun ajaran</option>
                @foreach($academicYears as $option)
                    <option value="{{ $option->id }}" @selected((string) $selectedAcademicYear === (string) $option->id)>
                        {{ $option->name }}{{ $option->status === 'Aktif' ? ' - Aktif' : '' }}
                    </option>
                @endforeach
            </select>
        </div>
        <button type="submit" class="inline-flex items-center gap-1.5 px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700 transition-colors">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 3c2.755 0 5.455.232 8.083.678.533.09.917.556.917 1.096v1.044a2.25 2.25 0 01-.659 1.591l-5.432 5.432a2.25 2.25 0 00-.659 1.591v2.927a2.25 2.25 0 01-1.244 2.013L9.75 21v-6.568a2.25 2.25 0 00-.659-1.591L3.659 7.409A2.25 2.25 0 013 5.818V4.774c0-.54.384-1.006.917-1.096A48.32 48.32 0 0112 3z" />
            </svg>
            Terapkan
        </button>
        @if($isFiltered)
            <a href="{{ url()->current() }}?tab=perangkat" class="px-4 py-2 text-sm font-medium text-gray-500 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
                Reset
            </a>
        @endif
        <p class="text-xs text-gray-400 sm:ml-auto">{{ $deviceChecks->count() }} pengecekan ditampilkan</p>
    </form>
@endif

<div class="p-6 max-h-[65vh] overflow-y-auto overscroll-contain">
    @forelse($deviceChecks as $check)
        @php
            $checkedMap = $check->items->keyBy('item_key');
            $checkedCount = $check->items->where('is_checked', true)->count();
        @endphp

        <div class="border border-gray-200 rounded-lg mb-3 overflow-hidden">
            <div class="flex flex-wrap items-center justify-between gap-2 px-4 py-3 bg-gray-50">
                <div class="flex flex-wrap items-center gap-2">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                        {{ $check->check_date?->translatedFormat('d F Y') ?? '-' }}
                    </span>
                    @if($check->laboratory)
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs bg-gray-100 text-gray-600">
                            {{ $check->laboratory->name }}
                        </span>
                    @endif
                    @if($check->academicYear)
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs bg-gray-100 text-gray-600">
                            {{ $check->academicYear->name }}
                        </span>
                    @endif
                </div>
                <span class="text-xs text-gray-500">oleh {{ $check->officer_name ?: '-' }}</span>
            </div>

            @if($check->notes)
                <div class="px-4 py-2 text-sm text-gray-600 border-t border-gray-100">
                    {{ $check->notes }}
                </div>
            @endif

            <div class="px-4 py-3 border-t border-gray-100">
                <p class="text-xs text-gray-400 mb-2">
                    {{ $checkedCount }} dari {{ count($itemColumns) }} item berfungsi
                </p>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-1.5">
                    @foreach($itemColumns as $column)
                        @php
                            $label = isset($column['group'])
                                ? $column['group'] . ' ' . $column['label']
                                : $column['label'];
                            $isChecked = (bool) ($checkedMap[$column['key']]->is_checked ?? false);
                        @endphp
                        <div class="flex items-center gap-2 text-sm {{ $isChecked ? 'text-gray-700' : 'text-gray-400' }}">
                            <span class="inline-flex items-center justify-center w-4 h-4 rounded-full text-[10px] font-bold flex-shrink-0 {{ $isChecked ? 'bg-green-100 text-green-700' : 'bg-red-50 text-red-500' }}">
                                {{ $isChecked ? '✓' : '✕' }}
                            </span>
                            <span class="{{ $isChecked ? '' : 'line-through' }}">{{ $label }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @empty
        <div class="text-center py-8 text-gray-400">
            <svg class="w-10 h-10 mx-auto mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
            </svg>
            <p class="text-sm">{{ $isFiltered ? 'Tidak ada pengecekan perangkat pada tahun ajaran yang dipilih' : 'Belum ada riwayat pengecekan perangkat untuk komputer ini' }}</p>
        </div>
    @endforelse
</div>
