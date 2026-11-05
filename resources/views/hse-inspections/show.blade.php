@extends('layouts.app')

@section('title', 'Detail Inspeksi HSE')
@section('header', 'Detail Inspeksi HSE')

@section('content')
<div class="max-w-6xl mx-auto space-y-6">
    <a href="{{ route('hse-inspections.index') }}" class="inline-flex items-center gap-2 text-sm text-gray-600 hover:text-green-600 transition-colors">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"/>
        </svg>
        Kembali
    </a>

    @if(session('success'))
        <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg text-sm">
            {{ session('success') }}
        </div>
    @endif

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-4 mb-6">
            <div>
                <h3 class="text-lg font-semibold text-gray-900">{{ $hseInspection->lokasi }}</h3>
                <p class="text-sm text-gray-500 mt-0.5">{{ $hseInspection->kode }}</p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('hse-inspections.print', $hseInspection) }}" target="_blank" class="px-3 py-1.5 rounded-lg bg-green-50 text-green-700 text-xs font-medium hover:bg-green-100 transition-colors">Cetak</a>
                @if(auth()->user()->hasPermission('edit-hse-inspections'))
                <a href="{{ route('hse-inspections.edit', $hseInspection) }}" class="px-3 py-1.5 rounded-lg bg-amber-50 text-amber-700 text-xs font-medium hover:bg-amber-100 transition-colors">Ubah</a>
                @endif
                @if(auth()->user()->hasPermission('delete-hse-inspections'))
                <form method="POST" action="{{ route('hse-inspections.destroy', $hseInspection) }}" onsubmit="return confirm('Hapus inspeksi HSE ini?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="px-3 py-1.5 rounded-lg bg-red-50 text-red-700 text-xs font-medium hover:bg-red-100 transition-colors">Hapus</button>
                </form>
                @endif
            </div>
        </div>

        <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-5 text-sm">
            <div>
                <dt class="text-gray-500">Lokasi</dt>
                <dd class="mt-1 font-medium text-gray-900">{{ $hseInspection->lokasi }}</dd>
            </div>
            <div>
                <dt class="text-gray-500">Tanggal Inspeksi</dt>
                <dd class="mt-1 font-medium text-gray-900">{{ $hseInspection->tanggal_inspeksi->translatedFormat('d F Y') }}</dd>
            </div>
            <div>
                <dt class="text-gray-500">Inspeksi Oleh</dt>
                <dd class="mt-1 font-medium text-gray-900">{{ $hseInspection->diperiksa_oleh }}</dd>
            </div>
            <div>
                <dt class="text-gray-500">Dicatat Sistem Oleh</dt>
                <dd class="mt-1 font-medium text-gray-900">{{ $hseInspection->creator->name ?? '-' }}</dd>
            </div>
        </dl>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
            <h3 class="text-sm font-semibold text-gray-900">Temuan &amp; Dokumentasi</h3>
            <span class="text-xs text-gray-500">{{ $hseInspection->jumlah_temuan }} temuan</span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm" style="min-width: 900px;">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-3 py-3 text-center font-medium text-gray-600 w-10">No</th>
                        <th class="px-3 py-3 text-left font-medium text-gray-600">Lokasi</th>
                        <th class="px-3 py-3 text-left font-medium text-gray-600">Temuan &amp; Dokumentasi</th>
                        <th class="px-3 py-3 text-center font-medium text-gray-600">Unsafe<br>Condition</th>
                        <th class="px-3 py-3 text-center font-medium text-gray-600">Unsafe<br>Action</th>
                        <th class="px-3 py-3 text-left font-medium text-gray-600">Tanggal Kejadian</th>
                        <th class="px-3 py-3 text-left font-medium text-gray-600">Tindakan Perbaikan</th>
                        <th class="px-3 py-3 text-left font-medium text-gray-600">Tanggal Perbaikan</th>
                        <th class="px-3 py-3 text-left font-medium text-gray-600">PIC</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($hseInspection->finding_rows as $row)
                        <tr class="align-top">
                            <td class="px-3 py-3 text-center text-gray-500">{{ $loop->iteration }}</td>
                            <td class="px-3 py-3 text-gray-900">{{ $row['lokasi'] ?: '-' }}</td>
                            <td class="px-3 py-3 text-gray-900 whitespace-pre-line">{{ $row['temuan'] ?: '-' }}</td>
                            <td class="px-3 py-3 text-center">
                                @if($row['unsafe_condition'])
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-700">Ya</span>
                                @else
                                    <span class="text-xs text-gray-400">-</span>
                                @endif
                            </td>
                            <td class="px-3 py-3 text-center">
                                @if($row['unsafe_action'])
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-700">Ya</span>
                                @else
                                    <span class="text-xs text-gray-400">-</span>
                                @endif
                            </td>
                            <td class="px-3 py-3 text-gray-600">{{ $row['tanggal_kejadian_label'] }}</td>
                            <td class="px-3 py-3 text-gray-900 whitespace-pre-line">{{ $row['tindakan_perbaikan'] ?: '-' }}</td>
                            <td class="px-3 py-3 text-gray-600">{{ $row['tanggal_perbaikan_label'] }}</td>
                            <td class="px-3 py-3 text-gray-900">{{ $row['pic'] ?: '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="px-4 py-10 text-center text-sm text-gray-500">
                                Tidak ada temuan tercatat pada inspeksi ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
