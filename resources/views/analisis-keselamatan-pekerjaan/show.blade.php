@extends('layouts.app')

@section('title', 'Detail Analisis Keselamatan Pekerjaan')
@section('header', 'Detail Analisis Keselamatan Pekerjaan (JSA)')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <a href="{{ route('analisis-keselamatan-pekerjaan.index') }}" class="inline-flex items-center gap-2 text-sm text-gray-600 hover:text-green-600 transition-colors">
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
                <h3 class="text-lg font-semibold text-gray-900">{{ $jsa->nama_pekerjaan }}</h3>
                <p class="text-sm text-gray-500 mt-0.5">{{ $jsa->kode }}</p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('analisis-keselamatan-pekerjaan.print', $jsa) }}" target="_blank" class="px-3 py-1.5 rounded-lg bg-green-50 text-green-700 text-xs font-medium hover:bg-green-100 transition-colors">Cetak</a>
                @if(auth()->user()->hasPermission('edit-jsa'))
                <a href="{{ route('analisis-keselamatan-pekerjaan.edit', $jsa) }}" class="px-3 py-1.5 rounded-lg bg-amber-50 text-amber-700 text-xs font-medium hover:bg-amber-100 transition-colors">Ubah</a>
                @endif
                @if(auth()->user()->hasPermission('delete-jsa'))
                <form method="POST" action="{{ route('analisis-keselamatan-pekerjaan.destroy', $jsa) }}" onsubmit="return confirm('Hapus formulir JSA ini?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="px-3 py-1.5 rounded-lg bg-red-50 text-red-700 text-xs font-medium hover:bg-red-100 transition-colors">Hapus</button>
                </form>
                @endif
            </div>
        </div>

        <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-5 text-sm">
            <div>
                <dt class="text-gray-500">Nama Pekerja</dt>
                <dd class="mt-1 font-medium text-gray-900">{{ $jsa->nama_pekerja }}</dd>
            </div>
            <div>
                <dt class="text-gray-500">Tanggal Pekerjaan</dt>
                <dd class="mt-1 font-medium text-gray-900">{{ $jsa->hari }}, {{ $jsa->tanggal_pekerjaan->translatedFormat('d F Y') }}</dd>
            </div>
            <div>
                <dt class="text-gray-500">Tempat</dt>
                <dd class="mt-1 font-medium text-gray-900">{{ $jsa->tempat }}</dd>
            </div>
            <div>
                <dt class="text-gray-500">Dicatat Sistem Oleh</dt>
                <dd class="mt-1 font-medium text-gray-900">{{ $jsa->creator->name ?? '-' }}</dd>
            </div>
        </dl>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <h3 class="text-sm font-semibold text-gray-900 mb-3">Alat Pelindung Diri yang Diperlukan</h3>
        @if($jsa->apd)
            <div class="flex flex-wrap gap-2">
                @foreach(\App\Models\JobSafetyAnalysis::APD_ITEMS as $key => $item)
                    @if(! empty($jsa->apd[$key]))
                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-green-100 text-green-700">
                            {{ $item['label_id'] }}
                        </span>
                    @endif
                @endforeach
            </div>
        @else
            <p class="text-sm text-gray-400">Tidak ada APD yang dicentang.</p>
        @endif
        @if($jsa->apd_lain)
            <p class="text-sm text-gray-700 mt-3"><span class="font-medium">Lain-lain:</span> {{ $jsa->apd_lain }}</p>
        @endif
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-sm font-semibold text-gray-900">Langkah Pekerjaan</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm table-responsive-cards">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-4 py-3 text-left font-medium text-gray-600 w-10">No</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-600">Urutan Langkah Pekerjaan</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-600">Identifikasi Bahaya</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-600">Pengendalian</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-600">Penanggung Jawab</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($jsa->step_rows as $row)
                        <tr>
                            <td data-label="No" class="px-4 py-3 text-gray-500">{{ $loop->iteration }}</td>
                            <td data-label="Urutan Langkah Pekerjaan" class="px-4 py-3 text-gray-900">{{ $row['langkah'] ?: '-' }}</td>
                            <td data-label="Identifikasi Bahaya" class="px-4 py-3 text-gray-600">{{ $row['bahaya'] ?: '-' }}</td>
                            <td data-label="Pengendalian" class="px-4 py-3 text-gray-600">{{ $row['pengendalian'] ?: '-' }}</td>
                            <td data-label="Penanggung Jawab" class="px-4 py-3 text-gray-600">{{ $row['penanggung_jawab'] ?: '-' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="px-6 py-3 bg-gray-50 border-t border-gray-200 text-xs text-gray-500 space-y-1">
            <p>*Untuk pekerjaan yang memerlukan perpanjangan waktu dengan kasus yang sama dengan hasil identifikasi dan pengendalian yang sama, maka dapat diperpanjang satu kali perpanjangan.</p>
            <p>- Ahli Teknik terkait merupakan Ahli Teknik sesuai bidangnya/Penanggungjawab Proses.</p>
            <p>- Pengendalian bersifat teknis, perlengkapan APK, APD, harus berdasarkan standar dan/atau Peraturan perundangan sesuai dengan tingkat risiko hasil identifikasi bahaya.</p>
        </div>
    </div>
</div>
@endsection
