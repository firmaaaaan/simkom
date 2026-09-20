{{--
    Riwayat pengecekan komputer.

    Variabel:
    - $checks        : koleksi App\Models\ComputerCheck
    - $showItemNames : true  -> tampilkan nama hardware/software + statusnya (area admin)
                       false -> hanya ringkasan jumlah per status (halaman publik)
    - $years         : daftar tahun untuk dropdown filter (opsional)

    Filter bulan/tahun dibaca dari query string (?month=&year=) dan dikirim lewat GET
    ke halaman yang sedang dibuka, jadi form ini tidak butuh action khusus.
--}}
@php
    $showItemNames = $showItemNames ?? true;
    $years = $years ?? [];

    $selectedMonth = request('month');
    $selectedYear = request('year');
    $isFiltered = filled($selectedMonth) || filled($selectedYear);

    $monthNames = [
        1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
        5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
        9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
    ];

    $statusColors = [
        'Baik' => 'bg-green-50 text-green-700',
        'Perlu Perbaikan' => 'bg-yellow-50 text-yellow-700',
        'Rusak' => 'bg-red-50 text-red-700',
        'Usang' => 'bg-gray-100 text-gray-600',
        'Tidak Terdeteksi' => 'bg-gray-100 text-gray-600',
    ];
@endphp

@if(count($years) > 0)
    {{-- Filter Bulanan --}}
    <form method="GET" class="px-6 py-4 border-b border-gray-100 flex flex-wrap items-end gap-3">
        <div>
            <label for="filter-month" class="block text-xs text-gray-400 mb-1">Bulan</label>
            <select id="filter-month" name="month" class="px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500">
                <option value="">Semua bulan</option>
                @foreach($monthNames as $number => $label)
                    <option value="{{ $number }}" @selected((string) $selectedMonth === (string) $number)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label for="filter-year" class="block text-xs text-gray-400 mb-1">Tahun</label>
            <select id="filter-year" name="year" class="px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500">
                <option value="">Semua tahun</option>
                @foreach($years as $option)
                    <option value="{{ $option }}" @selected((string) $selectedYear === (string) $option)>{{ $option }}</option>
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
            <a href="{{ url()->current() }}" class="px-4 py-2 text-sm font-medium text-gray-500 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
                Reset
            </a>
        @endif
        <p class="text-xs text-gray-400 sm:ml-auto">{{ $checks->count() }} pengecekan ditampilkan</p>
    </form>
@endif

<div class="p-6">
    @forelse($checks as $check)
        @php
            $hardwareChecks = $check->hardwareChecks->where('checkable_type', 'App\Models\Hardware');
            $softwareChecks = $check->hardwareChecks->where('checkable_type', 'App\Models\Software');
        @endphp

        <div class="border border-gray-200 rounded-lg mb-3 overflow-hidden">
            <div class="flex items-center justify-between px-4 py-3 bg-gray-50">
                <div class="flex items-center gap-3">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                        {{ $check->overall_status === 'Baik' ? 'bg-green-100 text-green-800' : ($check->overall_status === 'Kritis' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800') }}">
                        {{ $check->overall_status }}
                    </span>
                    <span class="text-xs text-gray-500">{{ $check->created_at->format('d M Y H:i') }}</span>
                    @if($check->academicYear)
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs bg-gray-100 text-gray-600">
                            {{ $check->academicYear->name }}
                        </span>
                    @endif
                </div>
                <span class="text-xs text-gray-500">oleh {{ $check->checkedBy?->name ?? 'System' }}</span>
            </div>

            @if($check->notes)
                <div class="px-4 py-2 text-sm text-gray-600 border-t border-gray-100">
                    {{ $check->notes }}
                </div>
            @endif

            @if($showItemNames)
                @if($hardwareChecks->count() > 0)
                    <div class="px-4 py-2 border-t border-gray-100">
                        <p class="text-xs text-gray-400 mb-1">Hardware:</p>
                        <div class="flex flex-wrap gap-1">
                            @foreach($hardwareChecks as $item)
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs {{ $statusColors[$item->status] ?? 'bg-gray-100 text-gray-600' }}">
                                    {{ $item->checkable?->name ?? 'N/A' }}: {{ $item->status }}
                                </span>
                            @endforeach
                        </div>
                    </div>
                @endif

                @if($softwareChecks->count() > 0)
                    <div class="px-4 py-2 border-t border-gray-100">
                        <p class="text-xs text-gray-400 mb-1">Software:</p>
                        <div class="flex flex-wrap gap-1">
                            @foreach($softwareChecks as $item)
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs {{ $statusColors[$item->status] ?? 'bg-gray-100 text-gray-600' }}">
                                    {{ $item->checkable?->name ?? 'N/A' }}: {{ $item->status }}
                                </span>
                            @endforeach
                        </div>
                    </div>
                @endif
            @else
                {{-- Halaman publik: tanpa nama item, hanya rekap jumlah per status --}}
                @foreach([['label' => 'Hardware', 'items' => $hardwareChecks], ['label' => 'Software', 'items' => $softwareChecks]] as $group)
                    @if($group['items']->count() > 0)
                        <div class="px-4 py-2 border-t border-gray-100 flex flex-wrap items-center gap-1.5">
                            <p class="text-xs text-gray-400 mr-1">{{ $group['label'] }} ({{ $group['items']->count() }}):</p>
                            @foreach($group['items']->groupBy('status') as $status => $grouped)
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs {{ $statusColors[$status] ?? 'bg-gray-100 text-gray-600' }}">
                                    {{ $grouped->count() }} {{ $status }}
                                </span>
                            @endforeach
                        </div>
                    @endif
                @endforeach
            @endif
        </div>
    @empty
        <div class="text-center py-8 text-gray-400">
            <svg class="w-10 h-10 mx-auto mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <p class="text-sm">{{ $isFiltered ? 'Tidak ada pengecekan pada periode yang dipilih' : 'Belum ada riwayat pengecekan' }}</p>
        </div>
    @endforelse
</div>
