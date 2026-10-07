{{--
    Riwayat pengecekan perangkat (device checks) untuk satu komputer.

    Variabel:
    - $deviceChecks : koleksi App\Models\DeviceCheck dengan relasi laboratory,
                      academicYear, dan items yang sudah difilter hanya untuk
                      komputer ini (lihat DeviceCheck::scopeForComputer).

    Tanpa filter periode — daftar selalu urut terbaru.
--}}
@php
    $itemColumns = \App\Models\DeviceCheck::itemColumns();
@endphp

<div class="p-6">
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
            <p class="text-sm">Belum ada riwayat pengecekan perangkat untuk komputer ini</p>
        </div>
    @endforelse
</div>
