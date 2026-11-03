@extends('layouts.app')

@section('title', 'Detail Laporan Insiden')
@section('header', 'Detail Laporan Insiden')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <a href="{{ route('incident-reports.index') }}" class="inline-flex items-center gap-2 text-sm text-gray-600 hover:text-green-600 transition-colors">
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
                <h3 class="text-lg font-semibold text-gray-900">{{ $incidentReport->lokasi }}</h3>
                <p class="text-sm text-gray-500 mt-0.5">{{ $incidentReport->kode }}</p>
            </div>
            <div class="flex items-center gap-2">
                @if(auth()->user()->hasPermission('edit-incident-reports'))
                <a href="{{ route('incident-reports.edit', $incidentReport) }}" class="px-3 py-1.5 rounded-lg bg-amber-50 text-amber-700 text-xs font-medium hover:bg-amber-100 transition-colors">Ubah</a>
                @endif
                @if(auth()->user()->hasPermission('delete-incident-reports'))
                <form method="POST" action="{{ route('incident-reports.destroy', $incidentReport) }}" onsubmit="return confirm('Hapus laporan insiden ini?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="px-3 py-1.5 rounded-lg bg-red-50 text-red-700 text-xs font-medium hover:bg-red-100 transition-colors">Hapus</button>
                </form>
                @endif
            </div>
        </div>

        <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-5 text-sm">
            <div class="sm:col-span-2">
                <dt class="text-gray-500">Telah Terjadi</dt>
                <dd class="mt-1">
                    @php
                        $jenisColors = [
                            'Kecelakaan' => 'bg-red-100 text-red-700',
                            'Insiden' => 'bg-amber-100 text-amber-700',
                            'Nearmiss' => 'bg-yellow-100 text-yellow-700',
                            'Duga Bahaya' => 'bg-blue-100 text-blue-700',
                        ];
                    @endphp
                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium {{ $jenisColors[$incidentReport->telah_terjadi] ?? 'bg-gray-100 text-gray-700' }}">
                        {{ $incidentReport->telah_terjadi_label }}
                    </span>
                </dd>
            </div>
            <div>
                <dt class="text-gray-500">Hari, Tanggal</dt>
                <dd class="mt-1 font-medium text-gray-900">{{ $incidentReport->hari }}, {{ $incidentReport->tanggal_kejadian->translatedFormat('d F Y') }}</dd>
            </div>
            <div>
                <dt class="text-gray-500">Waktu</dt>
                <dd class="mt-1 font-medium text-gray-900">{{ $incidentReport->waktu }} WIB</dd>
            </div>
            <div>
                <dt class="text-gray-500">Lokasi Kejadian</dt>
                <dd class="mt-1 font-medium text-gray-900">{{ $incidentReport->lokasi }}</dd>
            </div>
            <div>
                <dt class="text-gray-500">Kecelakaan atas Nama</dt>
                <dd class="mt-1 font-medium text-gray-900">{{ $incidentReport->nama_korban }}</dd>
            </div>
            <div>
                <dt class="text-gray-500">Jabatan</dt>
                <dd class="mt-1 font-medium text-gray-900">{{ $incidentReport->jabatan ?: '-' }}</dd>
            </div>
            <div>
                <dt class="text-gray-500">Asal Institusi/Fakultas/Prodi/Unit</dt>
                <dd class="mt-1 font-medium text-gray-900">{{ $incidentReport->asal_institusi ?: '-' }}</dd>
            </div>
            <div>
                <dt class="text-gray-500">Dilaporkan Oleh</dt>
                <dd class="mt-1 font-medium text-gray-900">{{ $incidentReport->pelapor }}</dd>
            </div>
            <div>
                <dt class="text-gray-500">Dicatat Sistem Oleh</dt>
                <dd class="mt-1 font-medium text-gray-900">{{ $incidentReport->creator->name ?? '-' }}</dd>
            </div>
            <div>
                <dt class="text-gray-500">Kemungkinan (Likelihood)</dt>
                <dd class="mt-1 font-medium text-gray-900">
                    {{ \App\Models\IncidentReport::LIKELIHOODS[$incidentReport->likelihood] ?? '-' }}
                    @if($incidentReport->likelihood)<span class="text-gray-500 font-normal">({{ $incidentReport->likelihood }})</span>@endif
                </dd>
            </div>
            <div>
                <dt class="text-gray-500">Dampak (Impact)</dt>
                <dd class="mt-1 font-medium text-gray-900">
                    {{ \App\Models\IncidentReport::IMPACTS[$incidentReport->impact] ?? '-' }}
                    @if($incidentReport->impact)<span class="text-gray-500 font-normal">({{ $incidentReport->impact }})</span>@endif
                </dd>
            </div>
            <div>
                <dt class="text-gray-500">Risk Score</dt>
                <dd class="mt-1">
                    <span class="text-lg font-bold text-gray-900">{{ $incidentReport->risk_score ?? '-' }}</span>
                    @if($incidentReport->risk_level)
                        <span class="ml-2 inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold {{ \App\Models\IncidentReport::riskLevelColor($incidentReport->risk_level) }}">
                            {{ $incidentReport->risk_level }}
                        </span>
                    @endif
                </dd>
            </div>
            <div class="sm:col-span-2">
                <dt class="text-gray-500">Sebab-sebab Kecelakaan</dt>
                <dd class="mt-1 text-gray-900 whitespace-pre-line">{{ $incidentReport->sebab ?: '-' }}</dd>
            </div>
            <div class="sm:col-span-2">
                <dt class="text-gray-500">Riwayat Kejadian</dt>
                <dd class="mt-1 text-gray-900 whitespace-pre-line">{{ $incidentReport->riwayat ?: '-' }}</dd>
            </div>
            <div>
                <dt class="text-gray-500">Saksi</dt>
                <dd class="mt-1 text-gray-900 whitespace-pre-line">{{ $incidentReport->saksi ?: '-' }}</dd>
            </div>
            <div>
                <dt class="text-gray-500">Akibat Kejadian</dt>
                <dd class="mt-1 text-gray-900 whitespace-pre-line">{{ $incidentReport->akibat ?: '-' }}</dd>
            </div>
        </dl>
    </div>

    {{-- Bukti dokumentasi --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <h3 class="text-sm font-semibold text-gray-900 mb-3">Bukti Dokumentasi Kejadian</h3>
        @if(count($incidentReport->images ?? []))
            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-4">
                @foreach($incidentReport->images as $image)
                    <a href="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($image) }}" target="_blank" class="block aspect-square rounded-lg overflow-hidden border border-gray-200 hover:ring-2 hover:ring-green-500 transition-all">
                        <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($image) }}" alt="Bukti dokumentasi {{ $incidentReport->kode }}" class="w-full h-full object-cover">
                    </a>
                @endforeach
            </div>
        @else
            <p class="text-sm text-gray-500">Belum ada bukti dokumentasi yang diunggah.</p>
        @endif
    </div>
</div>
@endsection
