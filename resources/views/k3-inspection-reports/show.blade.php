@extends('layouts.app')

@section('title', 'Detail Laporan Inspeksi K3')
@section('header', 'Detail Laporan Inspeksi K3')

@section('content')
<div class="max-w-6xl mx-auto space-y-6">
    <a href="{{ route('k3-inspection-reports.index') }}" class="inline-flex items-center gap-2 text-sm text-gray-600 hover:text-green-600 transition-colors">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"/>
        </svg>
        Kembali
    </a>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-4 mb-6">
            <div>
                <h3 class="text-lg font-semibold text-gray-900">{{ $k3InspectionReport->lokasi }}</h3>
                <p class="text-sm text-gray-500 mt-0.5">{{ $k3InspectionReport->kode }} &middot; Inspeksi Bulan {{ $k3InspectionReport->bulan_inspeksi }}</p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('k3-inspection-reports.print', $k3InspectionReport) }}" target="_blank" class="px-3 py-1.5 rounded-lg bg-green-50 text-green-700 text-xs font-medium hover:bg-green-100 transition-colors">Cetak</a>
                @if(auth()->user()->hasPermission('edit-k3-inspection-reports'))
                <a href="{{ route('k3-inspection-reports.edit', $k3InspectionReport) }}" class="px-3 py-1.5 rounded-lg bg-amber-50 text-amber-700 text-xs font-medium hover:bg-amber-100 transition-colors">Ubah</a>
                @endif
                @if(auth()->user()->hasPermission('delete-k3-inspection-reports'))
                <form method="POST" action="{{ route('k3-inspection-reports.destroy', $k3InspectionReport) }}" onsubmit="return confirm('Hapus laporan inspeksi K3 ini?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="px-3 py-1.5 rounded-lg bg-red-50 text-red-700 text-xs font-medium hover:bg-red-100 transition-colors">Hapus</button>
                </form>
                @endif
            </div>
        </div>
        <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-4 text-sm">
            <div>
                <dt class="text-gray-500">Berita Acara</dt>
                <dd class="mt-1 font-medium text-gray-900">{{ $k3InspectionReport->hari }}, {{ $k3InspectionReport->tanggal_ba->translatedFormat('d F Y') }}</dd>
            </div>
            <div>
                <dt class="text-gray-500">Pemeriksa</dt>
                <dd class="mt-1 font-medium text-gray-900">{{ $k3InspectionReport->pemeriksa_nama }} - {{ $k3InspectionReport->pemeriksa_jabatan }}</dd>
            </div>
        </dl>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-sm font-semibold text-gray-900">Temuan Inspeksi</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm" style="min-width: 900px;">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-3 py-3 text-center font-medium text-gray-600 w-10">No</th>
                        <th class="px-3 py-3 text-left font-medium text-gray-600">Temuan</th>
                        <th class="px-3 py-3 text-left font-medium text-gray-600">Saran Perbaikan</th>
                        <th class="px-3 py-3 text-left font-medium text-gray-600">Pelaksana</th>
                        <th class="px-3 py-3 text-left font-medium text-gray-600">Target</th>
                        <th class="px-3 py-3 text-left font-medium text-gray-600">Catatan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($k3InspectionReport->finding_rows as $row)
                        <tr class="align-top">
                            <td class="px-3 py-3 text-center text-gray-500">{{ $loop->iteration }}</td>
                            <td class="px-3 py-3 text-gray-900 whitespace-pre-line">{{ $row['temuan'] ?: '-' }}</td>
                            <td class="px-3 py-3 text-gray-900 whitespace-pre-line">{{ $row['saran_perbaikan'] ?: '-' }}</td>
                            <td class="px-3 py-3 text-gray-900">{{ $row['pelaksana'] ?: '-' }}</td>
                            <td class="px-3 py-3 text-gray-900">{{ $row['target'] ?: '-' }}</td>
                            <td class="px-3 py-3 text-gray-900 whitespace-pre-line">{{ $row['catatan'] ?: '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-10 text-center text-sm text-gray-500">Tidak ada temuan.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
