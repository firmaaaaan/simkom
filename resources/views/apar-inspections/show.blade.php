@extends('layouts.app')

@section('title', 'Detail Lembar Periksa APAR')
@section('header', 'Detail Lembar Periksa APAR')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <a href="{{ route('apar-inspections.index') }}" class="inline-flex items-center gap-2 text-sm text-gray-600 hover:text-green-600 transition-colors">
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
                <h3 class="text-lg font-semibold text-gray-900">{{ $aparInspection->lokasi }}</h3>
                <p class="text-sm text-gray-500 mt-0.5">{{ $aparInspection->kode }}</p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('apar-inspections.print', $aparInspection) }}" target="_blank" class="px-3 py-1.5 rounded-lg bg-green-50 text-green-700 text-xs font-medium hover:bg-green-100 transition-colors">Cetak</a>
                @if(auth()->user()->hasPermission('edit-apar-inspections'))
                <a href="{{ route('apar-inspections.edit', $aparInspection) }}" class="px-3 py-1.5 rounded-lg bg-amber-50 text-amber-700 text-xs font-medium hover:bg-amber-100 transition-colors">Ubah</a>
                @endif
                @if(auth()->user()->hasPermission('delete-apar-inspections'))
                <form method="POST" action="{{ route('apar-inspections.destroy', $aparInspection) }}" onsubmit="return confirm('Hapus lembar periksa APAR ini?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="px-3 py-1.5 rounded-lg bg-red-50 text-red-700 text-xs font-medium hover:bg-red-100 transition-colors">Hapus</button>
                </form>
                @endif
            </div>
        </div>

        <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-5 text-sm">
            <div>
                <dt class="text-gray-500">No. Reg. APAR</dt>
                <dd class="mt-1 font-medium text-gray-900">{{ $aparInspection->nomor_registrasi }}</dd>
            </div>
            <div>
                <dt class="text-gray-500">Kondisi</dt>
                <dd class="mt-1">
                    @if($aparInspection->jumlah_tidak_baik === 0)
                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-green-100 text-green-700">Baik</span>
                    @else
                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-red-100 text-red-700">
                            {{ $aparInspection->jumlah_tidak_baik }} item tidak baik
                        </span>
                    @endif
                </dd>
            </div>
            <div>
                <dt class="text-gray-500">Hari, Tanggal</dt>
                <dd class="mt-1 font-medium text-gray-900">{{ $aparInspection->hari }}, {{ $aparInspection->tanggal_inspeksi->translatedFormat('d F Y') }}</dd>
            </div>
            <div>
                <dt class="text-gray-500">Lokasi</dt>
                <dd class="mt-1 font-medium text-gray-900">{{ $aparInspection->lokasi }}</dd>
            </div>
            <div>
                <dt class="text-gray-500">Tgl. Inspeksi / Oleh</dt>
                <dd class="mt-1 font-medium text-gray-900">{{ $aparInspection->diperiksa_oleh }}</dd>
            </div>
            <div>
                <dt class="text-gray-500">Dicatat Sistem Oleh</dt>
                <dd class="mt-1 font-medium text-gray-900">{{ $aparInspection->creator->name ?? '-' }}</dd>
            </div>
        </dl>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-sm font-semibold text-gray-900">Hasil Pemeriksaan</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm table-responsive-cards">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-4 py-3 text-left font-medium text-gray-600 w-8">No</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-600">Item</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-600">Hasil</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-600">Keterangan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($aparInspection->item_rows as $row)
                        <tr>
                            <td data-label="No" class="px-4 py-3 text-gray-500">{{ $loop->iteration }}</td>
                            <td data-label="Item" class="px-4 py-3">
                                <p class="font-medium text-gray-900">{{ $row['label'] }}</p>
                                <p class="text-xs text-gray-500">{{ $row['label_id'] }}</p>
                            </td>
                            <td data-label="Hasil" class="px-4 py-3">
                                @if($row['status'] === 'baik')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-700">Baik</span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-700">Tidak</span>
                                @endif
                            </td>
                            <td data-label="Keterangan" class="px-4 py-3 text-gray-600">{{ $row['keterangan'] ?: '-' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="px-6 py-3 bg-gray-50 border-t border-gray-200 text-xs text-gray-500">
            Sebelum dipasang pada tempatnya, Box Fire Extinguisher harus diberi "Tag" terlebih dahulu.
        </div>
    </div>
</div>
@endsection
