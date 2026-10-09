{{--
    Riwayat pemeliharaan (maintenance checklist) untuk satu komputer.

    Variabel:
    - $maintenances : koleksi App\Models\MaintenanceChecklist dengan relasi
                      laboratory, academicYear, dan items yang sudah difilter
                      hanya untuk komputer ini (lihat
                      MaintenanceChecklist::scopeForComputer).

    Teks pertanyaan diambil dari MaintenanceChecklist::getChecklistItems()
    (kategori A-D). Filter tahun ajaran dibaca dari query string
    (?maintenance_academic_year_id=) dan dikirim lewat GET ke halaman yang
    sedang dibuka. Daftar dibatasi 65vh agar halaman tidak terlalu panjang.
--}}
@php
    $categories = \App\Models\MaintenanceChecklist::getChecklistItems();

    $academicYears = $academicYears ?? [];
    $selectedAcademicYear = request('maintenance_academic_year_id');
    $isFiltered = filled($selectedAcademicYear);

    $noteLabels = [
        'notes_computer' => 'Catatan Pemeriksaan Komputer',
        'notes_mouse_keyboard' => 'Catatan Mouse & Keyboard',
        'notes_ups' => 'Catatan UPS',
        'notes_monitor' => 'Catatan Monitor',
    ];
@endphp

@if(count($academicYears) > 0)
    <form method="GET" class="px-6 py-4 border-b border-gray-100 flex flex-wrap items-end gap-3">
        {{-- Pertahankan tab pemeliharaan aktif setelah filter diterapkan. --}}
        <input type="hidden" name="tab" value="pemeliharaan">
        <div>
            <label for="filter-maintenance-academic-year" class="block text-xs text-gray-400 mb-1">Tahun Ajaran</label>
            <select id="filter-maintenance-academic-year" name="maintenance_academic_year_id" class="px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500">
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
            <a href="{{ url()->current() }}?tab=pemeliharaan" class="px-4 py-2 text-sm font-medium text-gray-500 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
                Reset
            </a>
        @endif
        <p class="text-xs text-gray-400 sm:ml-auto">{{ $maintenances->count() }} pemeliharaan ditampilkan</p>
    </form>
@endif

<div class="p-6 max-h-[65vh] overflow-y-auto overscroll-contain">
    @forelse($maintenances as $maintenance)
        @php
            $itemMap = [];
            foreach ($maintenance->items as $item) {
                $itemMap[$item->category . '.' . $item->item_number] = $item;
            }
        @endphp

        <div class="border border-gray-200 rounded-lg mb-3 overflow-hidden">
            <div class="flex flex-wrap items-center justify-between gap-2 px-4 py-3 bg-gray-50">
                <div class="flex flex-wrap items-center gap-2">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                        {{ \Illuminate\Support\Carbon::parse($maintenance->maintenance_date)->translatedFormat('d F Y') }}
                    </span>
                    @if($maintenance->laboratory)
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs bg-gray-100 text-gray-600">
                            {{ $maintenance->laboratory->name }}
                        </span>
                    @endif
                    @if($maintenance->academicYear)
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs bg-gray-100 text-gray-600">
                            {{ $maintenance->academicYear->name }}
                        </span>
                    @endif
                </div>
                <span class="text-xs text-gray-500">oleh {{ $maintenance->inspector_name ?: '-' }}</span>
            </div>

            @foreach($categories as $categoryKey => $category)
                <div class="px-4 py-3 border-t border-gray-100">
                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">
                        {{ $categoryKey }}. {{ $category['name'] }}
                    </p>
                    <ul class="space-y-1.5">
                        @foreach($category['items'] as $index => $question)
                            @php
                                $item = $itemMap[$categoryKey . '.' . ($index + 1)] ?? null;
                                $isChecked = (bool) ($item->is_checked ?? false);
                            @endphp
                            <li class="flex items-start gap-2 text-sm {{ $isChecked ? 'text-gray-700' : 'text-gray-400' }}">
                                <span class="inline-flex items-center justify-center w-4 h-4 rounded-full text-[10px] font-bold flex-shrink-0 mt-0.5 {{ $isChecked ? 'bg-green-100 text-green-700' : 'bg-red-50 text-red-500' }}">
                                    {{ $isChecked ? '✓' : '✕' }}
                                </span>
                                <span class="{{ $isChecked ? '' : 'line-through' }}">{{ $question }}</span>
                                @if($item && $item->saved_at)
                                    <span class="text-xs text-gray-400 whitespace-nowrap ml-auto">
                                        {{ \Illuminate\Support\Carbon::parse($item->saved_at)->format('d/m/Y') }}
                                    </span>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endforeach

            @php
                $notes = collect($noteLabels)
                    ->map(fn ($label, $field) => filled($maintenance->$field) ? ['label' => $label, 'text' => $maintenance->$field] : null)
                    ->filter();
            @endphp
            @if($notes->isNotEmpty())
                <div class="px-4 py-3 border-t border-gray-100 bg-amber-50/50">
                    @foreach($notes as $note)
                        <p class="text-sm text-gray-600">
                            <span class="font-medium text-gray-700">{{ $note['label'] }}:</span>
                            {{ $note['text'] }}
                        </p>
                    @endforeach
                </div>
            @endif
        </div>
    @empty
        <div class="text-center py-8 text-gray-400">
            <svg class="w-10 h-10 mx-auto mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1">
                <path stroke-linecap="round" stroke-linejoin="round" d="M11.42 15.17L17.25 21A2.652 2.652 0 0021 17.25l-5.877-5.877M11.42 15.17l2.496-3.03c.317-.384.74-.626 1.208-.766M11.42 15.17l-4.655 5.653a2.548 2.548 0 11-3.586-3.586l6.837-5.63m5.108-.233c.55-.164 1.163-.188 1.743-.14a4.5 4.5 0 004.486-6.336l-3.276 3.277a3.004 3.004 0 01-2.25-2.25l3.276-3.276a4.5 4.5 0 00-6.336 4.486c.091 1.076-.071 2.264-.904 2.95l-.102.085m-1.745 1.437L5.909 7.5H4.5L2.25 3.75l1.5-1.5L7.5 4.5v1.409l4.26 4.26m-1.745 1.437l1.77-1.707 2.25 2.25" />
            </svg>
            <p class="text-sm">{{ $isFiltered ? 'Tidak ada pemeliharaan pada tahun ajaran yang dipilih' : 'Belum ada riwayat pemeliharaan untuk komputer ini' }}</p>
        </div>
    @endforelse
</div>
