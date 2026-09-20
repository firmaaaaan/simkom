@extends('layouts.app')

@section('title', 'Laporan Pengecekan')
@section('header', 'Laporan Pengecekan Perangkat')

@section('content')
@php
    $selectedYearId = request('academic_year_id') ?: \App\Models\AcademicYear::current()?->id;
    $selectable = $rows->where('selectable', true);
    $checkedCount = $selectable->where('selected', true)->count();
    $selectedChecks = $selectedRows->whereNotNull('check');
    $previousLabId = null;
@endphp

<div class="space-y-6">
    {{-- FILTER --}}
    <div class="bg-white rounded-xl border border-gray-200 p-6">
        <form action="{{ route('device-checks.report') }}" method="GET" class="flex flex-col sm:flex-row gap-4 items-end">
            <div class="flex-1 w-full">
                <label class="block text-sm font-medium text-gray-700 mb-1">Laboratorium</label>
                <select name="laboratory_id" required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-green-500 focus:border-green-500">
                    <option value="all" {{ $selectedLab && $selectedLab->id === 'all' ? 'selected' : '' }}>Semua Laboratorium</option>
                    @foreach($laboratories as $lab)
                        <option value="{{ $lab->id }}" {{ $selectedLab && $selectedLab->id == $lab->id ? 'selected' : '' }}>{{ $lab->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex-1 w-full">
                <label class="block text-sm font-medium text-gray-700 mb-1">Tahun Ajaran</label>
                <select name="academic_year_id" required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-green-500 focus:border-green-500">
                    <option value="">Pilih Tahun Ajaran</option>
                    @foreach($academicYears as $year)
                        <option value="{{ $year->id }}" {{ (string) $selectedYearId === (string) $year->id ? 'selected' : '' }}>
                            {{ $year->name }} ({{ $year->periodLabel() }}){{ $year->status === 'Aktif' ? ' - Aktif' : '' }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="flex gap-2">
                <button type="submit" class="px-6 py-2 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700 transition-colors">
                    Tampilkan
                </button>
                @if($selectedYear)
                    <a href="{{ route('device-checks.report-print', ['laboratory_id' => $selectedLab->id, 'academic_year_id' => $selectedYear->id]) }}" target="_blank"
                        class="inline-flex items-center gap-2 px-6 py-2 border border-gray-300 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-50 transition-colors">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0110.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0l.229 2.523a1.125 1.125 0 01-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0021 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 00-1.913-.247M6.34 18H5.25A2.25 2.25 0 013 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 011.913-.247m10.5 0a48.536 48.536 0 00-10.5 0m10.5 0V3.375c0-.621-.504-1.125-1.125-1.125h-8.25c-.621 0-1.125.504-1.125 1.125v3.659" />
                        </svg>
                        Cetak Semua
                    </a>
                @endif
            </div>
        </form>
    </div>

    @if($selectedYear)
        {{-- RINGKASAN (mengikuti baris yang dipilih) --}}
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="bg-white rounded-xl border border-gray-200 p-4">
                <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Laboratorium</p>
                <p class="mt-1 text-2xl font-bold text-gray-900">{{ $summary['labs'] }}</p>
                <p class="text-xs text-gray-400 mt-0.5">{{ $summary['checked_labs'] }} lab sudah dicek</p>
            </div>
            <div class="bg-white rounded-xl border border-gray-200 p-4">
                <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Pengecekan Dipilih</p>
                <p class="mt-1 text-2xl font-bold text-gray-900">{{ $summary['checks'] }}</p>
                <p class="text-xs text-gray-400 mt-0.5">dari {{ $selectable->count() }} pada tahun ini · {{ $summary['covered_computers'] }} komputer</p>
            </div>
            <div class="bg-white rounded-xl border border-gray-200 p-4">
                <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Ada &amp; Berfungsi</p>
                <p class="mt-1 text-2xl font-bold text-green-600">{{ $summary['ok'] }}</p>
                <p class="text-xs text-gray-400 mt-0.5">sel perangkat normal dari {{ $summary['cells'] }} sel</p>
            </div>
            <div class="bg-white rounded-xl border border-gray-200 p-4">
                <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Perlu Perhatian</p>
                <p class="mt-1 text-2xl font-bold {{ $summary['problems'] > 0 ? 'text-red-600' : 'text-gray-900' }}">{{ $summary['problems'] }}</p>
                <p class="text-xs text-gray-400 mt-0.5">sel perangkat bermasalah/kosong</p>
            </div>
        </div>

        {{-- REKAP + PILIH BARIS UNTUK DICETAK --}}
        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
            <form action="{{ route('device-checks.report') }}" method="GET" id="recapForm">
                <input type="hidden" name="laboratory_id" value="{{ $selectedLab->id }}">
                <input type="hidden" name="academic_year_id" value="{{ $selectedYear->id }}">

                <div class="px-6 py-4 border-b border-gray-100 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-3">
                    <div>
                        <h2 class="text-base font-semibold text-gray-800">Rekap Pengecekan</h2>
                        <p class="text-xs text-gray-500 mt-1">
                            Tahun Ajaran <span class="font-semibold">{{ $selectedYear->name }}</span>
                            ({{ $selectedYear->periodLabel() }}) — semua pengecekan ditampilkan, termasuk beberapa tanggal di lab yang sama.
                        </p>
                        <p class="text-xs mt-1 {{ $checkedCount === 0 ? 'text-red-600' : 'text-gray-500' }}">
                            <span class="font-semibold">{{ $checkedCount }}</span> dari {{ $selectable->count() }} baris dipilih.
                            Hanya baris tercentang yang dilaporkan/dicetak.
                        </p>
                    </div>
                    <div class="flex flex-wrap items-center gap-3">
                        <label class="inline-flex items-center gap-2 px-3 py-2 border border-gray-200 rounded-lg cursor-pointer hover:bg-gray-50 transition-colors">
                            <input type="checkbox" id="selectAllRows" class="rounded border-gray-300 text-green-600 focus:ring-green-500"
                                {{ $selectable->count() > 0 && $checkedCount === $selectable->count() ? 'checked' : '' }}>
                            <span class="text-sm font-medium text-gray-700">Pilih Semua</span>
                        </label>
                        <button type="submit" formaction="{{ route('device-checks.report') }}"
                            class="px-4 py-2 border border-gray-300 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-50 transition-colors">
                            Terapkan Pilihan
                        </button>
                        <button type="submit" formaction="{{ route('device-checks.report-print') }}" formtarget="_blank"
                            class="inline-flex items-center gap-2 px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700 transition-colors">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0110.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0l.229 2.523a1.125 1.125 0 01-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0021 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 00-1.913-.247M6.34 18H5.25A2.25 2.25 0 013 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 011.913-.247m10.5 0a48.536 48.536 0 00-10.5 0m10.5 0V3.375c0-.621-.504-1.125-1.125-1.125h-8.25c-.621 0-1.125.504-1.125 1.125v3.659" />
                            </svg>
                            Cetak yang Dipilih
                        </button>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-sm table-responsive-cards">
                        <thead>
                            <tr class="border-b border-gray-100">
                                <th class="text-center px-4 py-3 font-medium text-gray-500 w-12">Pilih</th>
                                <th class="text-left px-4 py-3 font-medium text-gray-500 w-10">No</th>
                                <th class="text-left px-4 py-3 font-medium text-gray-500">Laboratorium</th>
                                <th class="text-left px-4 py-3 font-medium text-gray-500">Tanggal</th>
                                <th class="text-left px-4 py-3 font-medium text-gray-500">Petugas</th>
                                <th class="text-left px-4 py-3 font-medium text-gray-500">Komputer</th>
                                <th class="text-left px-4 py-3 font-medium text-gray-500">Berfungsi</th>
                                <th class="text-left px-4 py-3 font-medium text-gray-500">Perlu Perhatian</th>
                                <th class="text-left px-4 py-3 font-medium text-gray-500">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($rows as $index => $row)
                                @php
                                    $sameLab = $previousLabId === $row['laboratory']->id;
                                    $previousLabId = $row['laboratory']->id;
                                @endphp
                                <tr class="border-b border-gray-50 hover:bg-gray-50 transition-colors {{ $row['selected'] ? 'bg-green-50/40' : '' }} {{ $sameLab ? '' : 'border-t border-gray-200' }}">
                                    <td data-label="Pilih" class="px-4 py-4 text-center">
                                        @if($row['selectable'])
                                            <input type="checkbox" name="checks[]" value="{{ $row['check']->id }}"
                                                class="row-check rounded border-gray-300 text-green-600 focus:ring-green-500"
                                                {{ $row['selected'] ? 'checked' : '' }}>
                                        @else
                                            <span class="text-gray-300">&ndash;</span>
                                        @endif
                                    </td>
                                    <td data-label="No" class="px-4 py-4 text-gray-500">{{ $index + 1 }}</td>
                                    <td data-label="Laboratorium" class="px-4 py-4 {{ $sameLab ? 'text-gray-400' : 'font-medium text-gray-800' }}">
                                        @if($sameLab)
                                            <span class="text-xs">↳ lanjutan</span>
                                        @else
                                            {{ $row['laboratory']->name }}
                                        @endif
                                    </td>
                                    <td data-label="Tanggal" class="px-4 py-4 text-gray-700 whitespace-nowrap">
                                        {{ $row['check']?->check_date?->format('d M Y') ?? '—' }}
                                        @if($row['check'])
                                            <span class="block text-xs text-gray-400">{{ $row['check']->created_at->format('H:i') }} WIB</span>
                                        @endif
                                    </td>
                                    <td data-label="Petugas" class="px-4 py-4 text-gray-600">{{ $row['check']?->officer_name ?: '—' }}</td>
                                    <td data-label="Komputer" class="px-4 py-4 text-gray-600">{{ $row['check'] ? $row['computers']->count() : '—' }}</td>
                                    @if($row['check'])
                                        <td data-label="Berfungsi" class="px-4 py-4 text-green-600 font-medium">{{ $row['ok'] }}</td>
                                        <td data-label="Perlu Perhatian" class="px-4 py-4 font-medium {{ $row['problems'] > 0 ? 'text-red-600' : 'text-gray-400' }}">{{ $row['problems'] }}</td>
                                    @else
                                        <td data-label="Berfungsi" class="px-4 py-4 text-gray-300">&ndash;</td>
                                        <td data-label="Perlu Perhatian" class="px-4 py-4 text-gray-300">&ndash;</td>
                                    @endif
                                    <td data-label="Aksi" class="px-4 py-4">
                                        @if($row['check'])
                                            <div class="flex items-center gap-2 whitespace-nowrap">
                                                <a href="{{ route('device-checks.show', $row['check']) }}" class="text-sm text-green-600 hover:text-green-700 font-medium">Lihat</a>
                                                <a href="{{ route('device-checks.report-print', ['laboratory_id' => $selectedLab->id, 'academic_year_id' => $selectedYear->id, 'checks' => [$row['check']->id]]) }}"
                                                    target="_blank" class="text-sm text-gray-500 hover:text-gray-700">Cetak ini</a>
                                            </div>
                                        @else
                                            <span class="text-xs text-gray-400">Belum dicek</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="px-6 py-12 text-center text-gray-500 text-sm">Tidak ada laboratorium pada filter ini</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </form>
        </div>

        {{-- MATRIKS BARIS YANG DIPILIH --}}
        @forelse($selectedChecks as $row)
            <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                    <div>
                        <h2 class="text-base font-semibold text-gray-800">{{ $row['laboratory']->name }}</h2>
                        <p class="text-xs text-gray-500 mt-1">
                            Dicek {{ $row['check']->check_date?->translatedFormat('d F Y') }}
                            {{ $row['check']->created_at->format('H:i') }} WIB
                            oleh {{ $row['check']->officer_name ?: 'petugas tidak dicatat' }}
                        </p>
                    </div>
                    <div class="flex gap-4 text-xs">
                        <span class="text-green-600">Berfungsi: <strong>{{ $row['ok'] }}</strong></span>
                        <span class="{{ $row['problems'] > 0 ? 'text-red-600' : 'text-gray-500' }}">Perlu perhatian: <strong>{{ $row['problems'] }}</strong></span>
                        <a href="{{ route('device-checks.print', $row['check']) }}" target="_blank" class="text-green-600 hover:text-green-700 font-medium">Cetak lab ini</a>
                    </div>
                </div>

                @include('device-checks.partials.matrix', ['editable' => false, 'computers' => $row['computers'], 'checked' => $row['checked']])
            </div>
        @empty
            <div class="bg-white rounded-xl border border-gray-200 p-12 text-center">
                <p class="text-sm text-gray-500">Tidak ada baris yang dipilih — centang minimal satu baris rekap untuk menampilkan matriksnya.</p>
            </div>
        @endforelse
    @else
        <div class="bg-white rounded-xl border border-gray-200 p-12 text-center">
            <svg class="w-16 h-16 mx-auto text-gray-300 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25z" />
            </svg>
            <h3 class="text-lg font-semibold text-gray-700 mb-2">Pilih Filter Untuk Menampilkan Laporan</h3>
            <p class="text-gray-500 text-sm">Pilih laboratorium dan tahun ajaran di atas, lalu klik "Tampilkan"</p>
        </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
    const selectAllRows = document.getElementById('selectAllRows');
    if (selectAllRows) {
        selectAllRows.addEventListener('change', function () {
            document.querySelectorAll('.row-check').forEach(function (cb) {
                cb.checked = selectAllRows.checked;
            });
        });
    }
</script>
@endpush
