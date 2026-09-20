@extends('layouts.app')

@section('title', 'Kartu Kendali')
@section('header', 'Kartu Kendali per Tahun Ajaran')

@section('content')
<div class="space-y-6">
    {{-- Filter --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <form action="{{ route('reports.card-control') }}" method="GET" class="flex flex-col sm:flex-row gap-4 items-end">
            <div class="flex-1 w-full">
                <label class="block text-sm font-medium text-gray-700 mb-1">Laboratorium</label>
                <select name="laboratory_id" required class="w-full rounded-lg border-gray-300 border px-3 py-2 text-sm focus:ring-green-500 focus:border-green-500">
                    <option value="">Pilih Laboratorium</option>
                    <option value="all" {{ $selectedLab && $selectedLab->id == 'all' ? 'selected' : '' }}>Semua Laboratorium</option>
                    @foreach($laboratories as $lab)
                        <option value="{{ $lab->id }}" {{ $selectedLab && $selectedLab->id == $lab->id ? 'selected' : '' }}>
                            {{ $lab->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="flex-1 w-full">
                <label class="block text-sm font-medium text-gray-700 mb-1">Tahun Ajaran</label>
                <select name="academic_year_id" required class="w-full rounded-lg border-gray-300 border px-3 py-2 text-sm focus:ring-green-500 focus:border-green-500">
                    <option value="">Pilih Tahun Ajaran</option>
                    @foreach($academicYears as $year)
                        <option value="{{ $year->id }}" {{ ($selectedYear && $selectedYear->id == $year->id) || (!$selectedYear && $year->status == 'Aktif') ? 'selected' : '' }}>
                            {{ $year->name }} ({{ $year->periodLabel() }}){{ $year->status === 'Aktif' ? ' - Aktif' : '' }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="flex gap-2">
                <button type="submit" class="px-6 py-2 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700 transition-colors">
                    Tampilkan
                </button>
                @if($selectedLab && $selectedYear)
                    <a href="{{ route('reports.card-control-print', ['laboratory_id' => $selectedLab->id, 'academic_year_id' => $selectedYear->id]) }}" target="_blank" class="px-6 py-2 bg-gray-600 text-white text-sm font-medium rounded-lg hover:bg-gray-700 transition-colors">
                        Cetak
                    </a>
                @endif
            </div>
        </form>
    </div>

    {{-- Report Table --}}
    @if($selectedLab && $selectedYear)
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            {{-- Header --}}
            <div class="p-6 border-b border-gray-200">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                    <div>
                        <span class="font-semibold text-gray-700">Nama Lab</span>
                        <span class="text-gray-500 mx-2">:</span>
                        <span class="text-gray-900">{{ $selectedLab->name }}</span>
                    </div>
                    <div>
                        <span class="font-semibold text-gray-700">Periode</span>
                        <span class="text-gray-500 mx-2">:</span>
                        <span class="text-gray-900">{{ $selectedYear->name }}</span>
                        <span class="text-gray-500">({{ $selectedYear->periodLabel() }})</span>
                    </div>
                </div>
            </div>

            {{-- Table --}}
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-green-600 text-white">
                            <th class="px-4 py-3 text-center w-12">No</th>
                            @if($selectedLab->id === 'all')
                                <th class="px-4 py-3 text-left">Lab</th>
                            @endif
                            <th class="px-4 py-3 text-left">Kode Komputer</th>
                            <th class="px-4 py-3 text-left" rowspan="2">Tanggal</th>
                            <th class="px-4 py-3 text-center" colspan="2">Fungsi</th>
                            <th class="px-4 py-3 text-left">Keterangan</th>
                            <th class="px-4 py-3 text-left">PJ</th>
                        </tr>
                        <tr class="bg-green-500 text-white">
                            <th colspan="{{ $selectedLab->id === 'all' ? 3 : 2 }}"></th>
                            <th class="px-4 py-2 text-center font-medium">Baik</th>
                            <th class="px-4 py-2 text-center font-medium">Tidak</th>
                            <th colspan="2"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($computers as $index => $computer)
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-4 py-3 text-center text-gray-600">{{ $index + 1 }}</td>
                                @if($selectedLab->id === 'all')
                                    <td class="px-4 py-3 text-gray-600">{{ $computer->laboratory->name ?? '-' }}</td>
                                @endif
                                <td class="px-4 py-3 font-medium text-gray-900">{{ $computer->code }}</td>
                                <td class="px-4 py-3 text-gray-600 whitespace-nowrap">
                                    @if($computer->latestCheck)
                                        {{ $computer->latestCheck->created_at->translatedFormat('d M Y') }}
                                        <span class="block text-xs text-gray-400">{{ $computer->latestCheck->created_at->format('H:i') }}</span>
                                    @else
                                        <span class="text-gray-300">&ndash;</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-center">
                                    @if($computer->is_baik)
                                        <span class="inline-flex items-center justify-center w-5 h-5 rounded border-2 border-green-500 bg-green-500">
                                            <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="3">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/>
                                            </svg>
                                        </span>
                                    @elseif($computer->latestCheck)
                                        <span class="inline-flex items-center justify-center w-5 h-5 rounded border-2 border-gray-300 bg-white"></span>
                                    @else
                                        <span class="text-gray-300">&ndash;</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-center">
                                    @if($computer->latestCheck && !$computer->is_baik)
                                        <span class="inline-flex items-center justify-center w-5 h-5 rounded border-2 border-red-500 bg-red-500">
                                            <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="3">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/>
                                            </svg>
                                        </span>
                                    @elseif($computer->latestCheck)
                                        <span class="inline-flex items-center justify-center w-5 h-5 rounded border-2 border-gray-300 bg-white"></span>
                                    @else
                                        <span class="text-gray-300">&ndash;</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-gray-600">{{ $computer->latestCheck->notes ?? '-' }}</td>
                                <td class="px-4 py-3 text-gray-900">{{ $computer->latestCheck->checkedBy->name ?? '-' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ $selectedLab->id === 'all' ? 8 : 7 }}" class="px-4 py-12 text-center text-gray-500">
                                    <svg class="w-12 h-12 mx-auto text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                    </svg>
                                    <p class="font-medium">Tidak ada data komputer</p>
                                    <p class="text-sm mt-1">di laboratorium ini untuk tahun ajaran yang dipilih</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Summary --}}
            @if($computers->count() > 0)
                <div class="px-6 py-4 border-t border-gray-200 bg-gray-50">
                    <div class="flex gap-6 text-sm">
                        <span class="text-gray-600">Total: <strong class="text-gray-900">{{ $summary['total'] }}</strong> komputer</span>
                        <span class="text-green-600">Baik: <strong>{{ $summary['baik'] }}</strong></span>
                        <span class="text-red-600">Tidak Baik: <strong>{{ $summary['tidak_baik'] }}</strong></span>
                        <span class="text-gray-500">Belum dicek: <strong>{{ $summary['belum_dicek'] }}</strong></span>
                    </div>
                </div>
            @endif
        </div>
    @else
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-12 text-center">
            <svg class="w-16 h-16 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 17.25v1.007a3 3 0 01-.879 2.122L7.5 21h9l-.621-.621A3 3 0 0115 18.257V17.25m6-12V15a2.25 2.25 0 01-2.25 2.25H5.25A2.25 2.25 0 013 15V5.25m18 0A2.25 2.25 0 0018.75 3H5.25A2.25 2.25 0 003 5.25m18 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 7.41A2.25 2.25 0 012.25 5.496V5.25"/>
            </svg>
            <h3 class="text-lg font-semibold text-gray-700 mb-2">Pilih Filter Untuk Menampilkan Laporan</h3>
            <p class="text-gray-500 text-sm">Pilih laboratorium dan tahun ajaran di atas, lalu klik "Tampilkan"</p>
        </div>
    @endif
</div>
@endsection
