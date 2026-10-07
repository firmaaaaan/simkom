{{--
    Riwayat pemeliharaan (maintenance checklist) untuk satu komputer.

    Variabel:
    - $maintenances : koleksi App\Models\MaintenanceChecklist dengan relasi
                      laboratory, academicYear, dan items yang sudah difilter
                      hanya untuk komputer ini (lihat
                      MaintenanceChecklist::scopeForComputer).

    Teks pertanyaan diambil dari MaintenanceChecklist::getChecklistItems()
    (kategori A-D). Tanpa filter periode — daftar selalu urut terbaru.
--}}
@php
    $categories = \App\Models\MaintenanceChecklist::getChecklistItems();

    $noteLabels = [
        'notes_computer' => 'Catatan Pemeriksaan Komputer',
        'notes_mouse_keyboard' => 'Catatan Mouse & Keyboard',
        'notes_ups' => 'Catatan UPS',
        'notes_monitor' => 'Catatan Monitor',
    ];
@endphp

<div class="p-6">
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
            <p class="text-sm">Belum ada riwayat pemeliharaan untuk komputer ini</p>
        </div>
    @endforelse
</div>
