@extends('layouts.app')

@section('title', 'Laporan')
@section('header', 'Laporan Laboratorium')

@section('content')
@php
    $jenisOptions = [
        'lab_usages' => 'Penggunaan Lab',
        'borrowings' => 'Peminjaman Komputer',
        'device_checks' => 'Pengecekan Perangkat',
        'card_control' => 'Kartu Kendali',
        'tickets' => 'Kendala Praktikum',
        'maintenances' => 'Pemeliharaan',
        'pengadaan' => 'Pengadaan',
    ];
@endphp
<div class="space-y-6" x-data="{ jenis: '{{ $jenis }}' }">
    {{-- Filter --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <form action="{{ route('reports.laporan') }}" method="GET" class="flex flex-col sm:flex-row gap-4 items-end">
            <div class="flex-1 w-full">
                <label class="block text-sm font-medium text-gray-700 mb-1">Jenis Laporan</label>
                <select name="jenis" x-model="jenis" class="w-full rounded-lg border-gray-300 border px-3 py-2 text-sm focus:ring-green-500 focus:border-green-500">
                    @foreach($jenisOptions as $key => $title)
                        <option value="{{ $key }}" {{ $jenis === $key ? 'selected' : '' }}>{{ $title }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex-1 w-full" x-show="jenis !== 'pengadaan'" x-cloak>
                <label class="block text-sm font-medium text-gray-700 mb-1">Laboratorium</label>
                <select name="laboratory_id" class="w-full rounded-lg border-gray-300 border px-3 py-2 text-sm focus:ring-green-500 focus:border-green-500">
                    <option value="all" {{ $selectedLab->id == 'all' ? 'selected' : '' }}>Semua Laboratorium</option>
                    @foreach($laboratories as $lab)
                        <option value="{{ $lab->id }}" {{ $selectedLab->id == $lab->id ? 'selected' : '' }}>
                            {{ $lab->name }} ({{ $lab->code }})
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="flex-1 w-full">
                <label class="block text-sm font-medium text-gray-700 mb-1">Tahun Ajaran</label>
                <select name="academic_year_id" class="w-full rounded-lg border-gray-300 border px-3 py-2 text-sm focus:ring-green-500 focus:border-green-500">
                    <option value="all" {{ $allYears ? 'selected' : '' }}>Semua Tahun Ajaran</option>
                    @foreach($academicYears as $year)
                        <option value="{{ $year->id }}" {{ $selectedYear && $selectedYear->id == $year->id ? 'selected' : '' }}>
                            {{ $year->name }}{{ $year->status === 'Aktif' ? ' - Aktif' : '' }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="flex gap-2">
                <button type="submit" class="px-6 py-2 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700 transition-colors">
                    Tampilkan
                </button>
                @can('print-reports')
                <a href="{{ route('reports.laporan-download', request()->query()) }}" class="px-6 py-2 bg-gray-600 text-white text-sm font-medium rounded-lg hover:bg-gray-700 transition-colors">
                    Download ZIP
                </a>
                @endcan
            </div>
        </form>
    </div>

    {{-- Info filter --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 text-sm">
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div>
                <span class="font-semibold text-gray-700">Jenis Laporan</span>
                <span class="text-gray-500 mx-2">:</span>
                <span class="text-gray-900">{{ $jenisOptions[$jenis] }}</span>
            </div>
            @if($jenis !== 'pengadaan')
            <div>
                <span class="font-semibold text-gray-700">Laboratorium</span>
                <span class="text-gray-500 mx-2">:</span>
                <span class="text-gray-900">{{ $selectedLab->name }}</span>
            </div>
            @endif
            <div>
                <span class="font-semibold text-gray-700">Tahun Ajaran</span>
                <span class="text-gray-500 mx-2">:</span>
                <span class="text-gray-900">{{ $allYears ? 'Semua Tahun Ajaran' : ($selectedYear->name ?? '-') }}</span>
            </div>
        </div>
    </div>

    {{-- Tabel preview --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="font-semibold text-gray-900">{{ $jenisOptions[$jenis] }}
                <span class="text-sm font-normal text-gray-500">({{ $total }} data)</span>
            </h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm table-responsive-cards">
                <thead>
                    <tr class="bg-green-600 text-white">
                        <th class="px-4 py-3 text-center w-12">No</th>
                        @if($allYears)
                            <th class="px-4 py-3 text-left">Tahun Ajaran</th>
                        @endif
                        @switch($jenis)
                            @case('lab_usages')
                                @if($selectedLab->id === 'all')<th class="px-4 py-3 text-left">Lab</th>@endif
                                <th class="px-4 py-3 text-left">Nama</th>
                                <th class="px-4 py-3 text-left">Prodi</th>
                                <th class="px-4 py-3 text-left">Keperluan</th>
                                <th class="px-4 py-3 text-left">Tanggal</th>
                                <th class="px-4 py-3 text-left">Jam Masuk</th>
                                <th class="px-4 py-3 text-left">Status</th>
                                @break
                            @case('borrowings')
                                @if($selectedLab->id === 'all')<th class="px-4 py-3 text-left">Lab</th>@endif
                                <th class="px-4 py-3 text-left">Kode</th>
                                <th class="px-4 py-3 text-left">Peminjam</th>
                                <th class="px-4 py-3 text-left">Komputer</th>
                                <th class="px-4 py-3 text-left">Tanggal</th>
                                <th class="px-4 py-3 text-left">Jam</th>
                                <th class="px-4 py-3 text-left">Status</th>
                                @break
                            @case('device_checks')
                                @if($selectedLab->id === 'all')<th class="px-4 py-3 text-left">Lab</th>@endif
                                <th class="px-4 py-3 text-left">Tanggal Cek</th>
                                <th class="px-4 py-3 text-left">Petugas</th>
                                <th class="px-4 py-3 text-left">Catatan</th>
                                @break
                            @case('card_control')
                                @if($selectedLab->id === 'all')<th class="px-4 py-3 text-left">Lab</th>@endif
                                <th class="px-4 py-3 text-left">Kode Komputer</th>
                                <th class="px-4 py-3 text-left">Tanggal Cek</th>
                                <th class="px-4 py-3 text-left">Status</th>
                                <th class="px-4 py-3 text-left">Keterangan</th>
                                <th class="px-4 py-3 text-left">PJ</th>
                                @break
                            @case('tickets')
                                @if($selectedLab->id === 'all')<th class="px-4 py-3 text-left">Lab</th>@endif
                                <th class="px-4 py-3 text-left">Kode</th>
                                <th class="px-4 py-3 text-left">Judul</th>
                                <th class="px-4 py-3 text-left">Kategori</th>
                                <th class="px-4 py-3 text-left">Prioritas</th>
                                <th class="px-4 py-3 text-left">Status</th>
                                @break
                            @case('maintenances')
                                @if($selectedLab->id === 'all')<th class="px-4 py-3 text-left">Lab</th>@endif
                                <th class="px-4 py-3 text-left">Tanggal</th>
                                <th class="px-4 py-3 text-left">Inspektur</th>
                                <th class="px-4 py-3 text-left">Catatan</th>
                                @break
                            @case('pengadaan')
                                <th class="px-4 py-3 text-left">Kode</th>
                                <th class="px-4 py-3 text-left">Nama Barang</th>
                                <th class="px-4 py-3 text-left">Kategori</th>
                                <th class="px-4 py-3 text-left">Jumlah</th>
                                <th class="px-4 py-3 text-left">Tanggal</th>
                                <th class="px-4 py-3 text-left">Status</th>
                                @break
                        @endswitch
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($rows as $index => $row)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-4 py-3 text-center text-gray-600" data-label="No">{{ $index + 1 }}</td>
                            @if($allYears)
                                <td class="px-4 py-3 text-gray-600" data-label="Tahun Ajaran">{{ $row->academic_year_name }}</td>
                            @endif
                            @switch($jenis)
                                @case('lab_usages')
                                    @if($selectedLab->id === 'all')<td class="px-4 py-3 text-gray-600" data-label="Lab">{{ $row->laboratory->name ?? '-' }}</td>@endif
                                    <td class="px-4 py-3 font-medium text-gray-900" data-label="Nama">{{ $row->user_name }}</td>
                                    <td class="px-4 py-3 text-gray-600" data-label="Prodi">{{ $row->user_prodi ?? '-' }}</td>
                                    <td class="px-4 py-3 text-gray-600" data-label="Keperluan">{{ $row->purpose ?? '-' }}</td>
                                    <td class="px-4 py-3 text-gray-600 whitespace-nowrap" data-label="Tanggal">{{ $row->checked_in_at?->translatedFormat('d M Y') }}</td>
                                    <td class="px-4 py-3 text-gray-600 whitespace-nowrap" data-label="Jam Masuk">{{ $row->checked_in_at?->format('H:i') }}</td>
                                    <td class="px-4 py-3" data-label="Status">
                                        <span class="px-2 py-1 text-xs font-medium rounded-full {{ $row->status === 'In' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600' }}">{{ $row->status }}</span>
                                    </td>
                                    @break
                                @case('borrowings')
                                    @if($selectedLab->id === 'all')<td class="px-4 py-3 text-gray-600" data-label="Lab">{{ $row->laboratory->name ?? '-' }}</td>@endif
                                    <td class="px-4 py-3 font-medium text-gray-900" data-label="Kode">{{ $row->tracking_code }}</td>
                                    <td class="px-4 py-3 text-gray-600" data-label="Peminjam">{{ $row->borrower_name }}</td>
                                    <td class="px-4 py-3 text-gray-600" data-label="Komputer">{{ $row->computer->code ?? '-' }}</td>
                                    <td class="px-4 py-3 text-gray-600 whitespace-nowrap" data-label="Tanggal">{{ \Illuminate\Support\Carbon::parse($row->borrow_date)->translatedFormat('d M Y') }}</td>
                                    <td class="px-4 py-3 text-gray-600 whitespace-nowrap" data-label="Jam">{{ $row->borrow_time_start }} - {{ $row->borrow_time_end }}</td>
                                    <td class="px-4 py-3" data-label="Status">
                                        @php
                                            $statusClass = match ($row->status) {
                                                'Approved' => 'bg-green-100 text-green-700',
                                                'Pending' => 'bg-yellow-100 text-yellow-700',
                                                'Rejected' => 'bg-red-100 text-red-700',
                                                default => 'bg-gray-100 text-gray-600',
                                            };
                                        @endphp
                                        <span class="px-2 py-1 text-xs font-medium rounded-full {{ $statusClass }}">{{ $row->status }}</span>
                                    </td>
                                    @break
                                @case('device_checks')
                                    @if($selectedLab->id === 'all')<td class="px-4 py-3 text-gray-600" data-label="Lab">{{ $row->laboratory->name ?? '-' }}</td>@endif
                                    <td class="px-4 py-3 text-gray-600 whitespace-nowrap" data-label="Tanggal Cek">{{ $row->check_date?->translatedFormat('d M Y') }}</td>
                                    <td class="px-4 py-3 font-medium text-gray-900" data-label="Petugas">{{ $row->officer_name ?? '-' }}</td>
                                    <td class="px-4 py-3 text-gray-600" data-label="Catatan">{{ $row->notes ?? '-' }}</td>
                                    @break
                                @case('card_control')
                                    @if($selectedLab->id === 'all')<td class="px-4 py-3 text-gray-600" data-label="Lab">{{ $row->laboratory->name ?? '-' }}</td>@endif
                                    <td class="px-4 py-3 font-medium text-gray-900" data-label="Kode Komputer">{{ $row->code }}</td>
                                    <td class="px-4 py-3 text-gray-600 whitespace-nowrap" data-label="Tanggal Cek">{{ $row->latestCheck?->created_at?->translatedFormat('d M Y') ?? '-' }}</td>
                                    <td class="px-4 py-3" data-label="Status">
                                        @if($row->is_baik)
                                            <span class="px-2 py-1 text-xs font-medium rounded-full bg-green-100 text-green-700">Baik</span>
                                        @elseif($row->latestCheck)
                                            <span class="px-2 py-1 text-xs font-medium rounded-full bg-red-100 text-red-700">Perlu Perbaikan</span>
                                        @else
                                            <span class="px-2 py-1 text-xs font-medium rounded-full bg-gray-100 text-gray-600">Belum Dicek</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-gray-600" data-label="Keterangan">{{ $row->latestCheck->notes ?? '-' }}</td>
                                    <td class="px-4 py-3 text-gray-600" data-label="PJ">{{ $row->latestCheck?->checkedBy->name ?? '-' }}</td>
                                    @break
                                @case('tickets')
                                    @if($selectedLab->id === 'all')<td class="px-4 py-3 text-gray-600" data-label="Lab">{{ $row->laboratory->name ?? '-' }}</td>@endif
                                    <td class="px-4 py-3 font-medium text-gray-900" data-label="Kode">{{ $row->tracking_code }}</td>
                                    <td class="px-4 py-3 text-gray-600" data-label="Judul">{{ $row->title }}</td>
                                    <td class="px-4 py-3 text-gray-600" data-label="Kategori">{{ $row->category }}</td>
                                    <td class="px-4 py-3" data-label="Prioritas">
                                        @php
                                            $priorityClass = match ($row->priority) {
                                                'Critical' => 'bg-red-100 text-red-700',
                                                'High' => 'bg-orange-100 text-orange-700',
                                                'Medium' => 'bg-yellow-100 text-yellow-700',
                                                default => 'bg-gray-100 text-gray-600',
                                            };
                                        @endphp
                                        <span class="px-2 py-1 text-xs font-medium rounded-full {{ $priorityClass }}">{{ $row->priority }}</span>
                                    </td>
                                    <td class="px-4 py-3" data-label="Status">
                                        @php
                                            $ticketStatusClass = match ($row->status) {
                                                'Open' => 'bg-red-100 text-red-700',
                                                'In Progress' => 'bg-yellow-100 text-yellow-700',
                                                'Resolved' => 'bg-green-100 text-green-700',
                                                default => 'bg-gray-100 text-gray-600',
                                            };
                                        @endphp
                                        <span class="px-2 py-1 text-xs font-medium rounded-full {{ $ticketStatusClass }}">{{ $row->status }}</span>
                                    </td>
                                    @break
                                @case('maintenances')
                                    @if($selectedLab->id === 'all')<td class="px-4 py-3 text-gray-600" data-label="Lab">{{ $row->laboratory->name ?? '-' }}</td>@endif
                                    <td class="px-4 py-3 text-gray-600 whitespace-nowrap" data-label="Tanggal">{{ \Illuminate\Support\Carbon::parse($row->maintenance_date)->translatedFormat('d M Y') }}</td>
                                    <td class="px-4 py-3 font-medium text-gray-900" data-label="Inspektur">{{ $row->inspector_name ?? '-' }}</td>
                                    <td class="px-4 py-3 text-gray-600" data-label="Catatan">{{ $row->notes_computer ?? $row->notes_mouse_keyboard ?? $row->notes_ups ?? $row->notes_monitor ?? '-' }}</td>
                                    @break
                                @case('pengadaan')
                                    <td class="px-4 py-3 font-medium text-gray-900" data-label="Kode">{{ $row->kode }}</td>
                                    <td class="px-4 py-3 text-gray-600" data-label="Nama Barang">{{ $row->nama_barang }}</td>
                                    <td class="px-4 py-3 text-gray-600" data-label="Kategori">{{ $row->kategori }}</td>
                                    <td class="px-4 py-3 text-gray-600" data-label="Jumlah">{{ $row->jumlah }}</td>
                                    <td class="px-4 py-3 text-gray-600 whitespace-nowrap" data-label="Tanggal">{{ $row->tanggal?->translatedFormat('d M Y') }}</td>
                                    <td class="px-4 py-3" data-label="Status">
                                        @php
                                            $pengadaanClass = match ($row->status) {
                                                'Acc' => 'bg-green-100 text-green-700',
                                                'Ditolak' => 'bg-red-100 text-red-700',
                                                default => 'bg-yellow-100 text-yellow-700',
                                            };
                                        @endphp
                                        <span class="px-2 py-1 text-xs font-medium rounded-full {{ $pengadaanClass }}">{{ $row->status }}</span>
                                    </td>
                                    @break
                            @endswitch
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="px-4 py-12 text-center text-gray-500">
                                <p class="font-medium">Belum ada data {{ strtolower($jenisOptions[$jenis]) }}</p>
                                <p class="text-sm mt-1">untuk filter yang dipilih</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
