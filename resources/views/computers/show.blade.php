@extends('layouts.app')

@section('title', 'Detail Komputer')
@section('header', 'Detail Komputer')

@section('content')
<div class="max-w-2xl">
    <div class="mb-6">
        <a href="{{ route('computers.index') }}" class="inline-flex items-center gap-1.5 text-sm text-gray-500 hover:text-green-600 transition-colors">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
            </svg>
            Kembali
        </a>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 p-6">
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-base font-semibold text-gray-800">Detail Komputer</h2>
            <div class="flex items-center gap-2">
                <a href="{{ route('computers.card', $computer) }}" class="px-4 py-2 border border-green-600 text-green-600 text-sm font-medium rounded-lg hover:bg-green-50 transition-colors">
                    Kartu Kendali
                </a>
                <a href="{{ route('computers.edit', $computer) }}" class="px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700 transition-colors">
                    Edit
                </a>
            </div>
        </div>

        <div class="space-y-4">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <p class="text-xs text-gray-400 mb-1">Kode Komputer</p>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-sm font-medium bg-green-100 text-green-800">
                        {{ $computer->code }}
                    </span>
                </div>
                <div>
                    <p class="text-xs text-gray-400 mb-1">Status</p>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-sm font-medium
                        {{ $computer->status === 'Aktif' ? 'bg-green-100 text-green-800' : ($computer->status === 'Maintenance' ? 'bg-yellow-100 text-yellow-800' : 'bg-gray-100 text-gray-600') }}">
                        {{ $computer->status }}
                    </span>
                </div>
            </div>

            <div>
                <p class="text-xs text-gray-400 mb-1">Laboratorium</p>
                <p class="text-sm text-gray-800">{{ $computer->laboratory?->name ?? '-' }}</p>
            </div>

            <div>
                <p class="text-xs text-gray-400 mb-1">Keterangan</p>
                <p class="text-sm text-gray-800">{{ $computer->description ?? '-' }}</p>
            </div>

            <div class="pt-4 border-t border-gray-100">
                <p class="text-xs text-gray-400 mb-2">Hardware</p>
                <div class="flex flex-wrap gap-2">
                    @forelse($computer->hardware as $hw)
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-50 text-blue-700">
                            {{ $hw->name }}
                            <span class="ml-1 text-blue-400">({{ $hw->category }})</span>
                        </span>
                    @empty
                        <p class="text-sm text-gray-400">Belum ada hardware</p>
                    @endforelse
                </div>
            </div>

            <div>
                <p class="text-xs text-gray-400 mb-2">Software</p>
                <div class="flex flex-wrap gap-2">
                    @forelse($computer->software as $sw)
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-50 text-purple-700">
                            {{ $sw->name }}
                            <span class="ml-1 text-purple-400">({{ $sw->version ?? $sw->category }})</span>
                        </span>
                    @empty
                        <p class="text-sm text-gray-400">Belum ada software</p>
                    @endforelse
                </div>
            </div>

            {{-- Perbandingan spesifikasi vs inventaris agent (hostname = kode komputer) --}}
            <div class="pt-4 border-t border-gray-100">
                <div class="flex flex-wrap items-center justify-between gap-2 mb-2">
                    <p class="text-xs text-gray-400">Software Terdeteksi (Agent)</p>
                    @if($pcMonitor)
                        <div class="flex items-center gap-2 text-xs">
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full font-medium {{ $pcMonitor->status === 'online' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                <span class="w-1.5 h-1.5 rounded-full {{ $pcMonitor->status === 'online' ? 'bg-green-500' : 'bg-red-500' }}"></span>
                                {{ $pcMonitor->status === 'online' ? 'Online' : 'Offline' }}
                            </span>
                            @if($pcMonitor->software_synced_at)
                                <span class="text-gray-400">sync {{ $pcMonitor->software_synced_at->diffForHumans(short: true) }}</span>
                            @else
                                <span class="text-amber-600">belum pernah sync</span>
                            @endif
                        </div>
                    @endif
                </div>

                @if(! $pcMonitor)
                    <p class="text-sm text-gray-400">Belum ada data monitoring untuk hostname <span class="font-medium text-gray-600">{{ $computer->code }}</span> (agent belum pernah terhubung / hostname PC belum disesuaikan).</p>
                @elseif(! $pcMonitor->software_synced_at)
                    <p class="text-sm text-gray-400">PC ini sudah terhubung agent, tetapi inventaris software belum pernah terkirim.</p>
                @else
                    <div class="space-y-4">
                        <div>
                            <p class="text-xs font-semibold text-green-700 mb-1.5">Sesuai spesifikasi ({{ count($softwareComparison['matched']) }})</p>
                            @if(count($softwareComparison['matched']))
                                <div class="flex flex-wrap gap-2">
                                    @foreach($softwareComparison['matched'] as $match)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-50 text-green-700" title="Terdeteksi: {{ $match['detected'] }}">
                                            {{ $match['catalog'] }}
                                            @if($match['version'])
                                                <span class="ml-1 text-green-500">v{{ $match['version'] }}</span>
                                            @endif
                                        </span>
                                    @endforeach
                                </div>
                            @else
                                <p class="text-sm text-gray-400">Tidak ada software spesifikasi yang terdeteksi.</p>
                            @endif
                        </div>

                        <div>
                            <p class="text-xs font-semibold text-amber-700 mb-1.5">Terdeteksi tambahan ({{ count($softwareComparison['extra']) }})</p>
                            @if(count($softwareComparison['extra']))
                                <div class="flex flex-wrap gap-2">
                                    @foreach(array_slice($softwareComparison['extra'], 0, 15) as $extra)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-amber-50 text-amber-700">
                                            {{ $extra['name'] }}
                                            @if($extra['version'])
                                                <span class="ml-1 text-amber-500">v{{ $extra['version'] }}</span>
                                            @endif
                                        </span>
                                    @endforeach
                                    @if(count($softwareComparison['extra']) > 15)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-amber-50 text-amber-600">+{{ count($softwareComparison['extra']) - 15 }} lainnya</span>
                                    @endif
                                </div>
                            @else
                                <p class="text-sm text-gray-400">Tidak ada software di luar spesifikasi.</p>
                            @endif
                        </div>

                        <div>
                            <p class="text-xs font-semibold text-red-700 mb-1.5">Tidak terdeteksi ({{ count($softwareComparison['missing']) }})</p>
                            @if(count($softwareComparison['missing']))
                                <div class="flex flex-wrap gap-2">
                                    @foreach($softwareComparison['missing'] as $missing)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-50 text-red-700">
                                            {{ $missing }}
                                        </span>
                                    @endforeach
                                </div>
                            @else
                                <p class="text-sm text-gray-400">Semua software spesifikasi terdeteksi di PC ini.</p>
                            @endif
                        </div>
                    </div>
                @endif
            </div>

            <div class="pt-4 border-t border-gray-100 grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs text-gray-400">
                <div>
                    <p>Dibuat: {{ $computer->created_at->format('d M Y H:i') }}</p>
                </div>
                <div>
                    <p>Diperbarui: {{ $computer->updated_at->format('d M Y H:i') }}</p>
                </div>
            </div>

            <div class="pt-4 border-t border-gray-100">
                <div class="flex items-center gap-4 p-4 bg-gray-50 rounded-lg">
                    <div class="flex-shrink-0 bg-white p-2 rounded-lg border border-gray-200">
                        <div id="qrCode"></div>
                    </div>
                    <div class="flex-1">
                        <p class="text-sm font-semibold text-gray-800 mb-1">QR Code</p>
                        <p class="text-xs text-gray-500 mb-2">Scan untuk melihat riwayat pengecekan (tanpa login)</p>
                        <button type="button" onclick="downloadQR()" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium text-green-600 bg-green-50 border border-green-200 rounded-lg hover:bg-green-100 transition-colors">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" />
                            </svg>
                            Download QR
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/qrcodejs@1.0.0/qrcode.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const qrUrl = "{{ route('computers.card', $computer) }}";
        const qrContainer = document.getElementById('qrCode');
        qrContainer.innerHTML = '';
        
        new QRCode(qrContainer, {
            text: qrUrl,
            width: 100,
            height: 100,
            colorDark: '#000000',
            colorLight: '#ffffff',
            correctLevel: QRCode.CorrectLevel.H
        });
    });

    function downloadQR() {
        const canvas = document.querySelector('#qrCode canvas');
        const img = document.querySelector('#qrCode img');
        
        if (canvas) {
            const link = document.createElement('a');
            link.download = 'QR-{{ $computer->code }}.png';
            link.href = canvas.toDataURL('image/png');
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        } else if (img) {
            const link = document.createElement('a');
            link.download = 'QR-{{ $computer->code }}.png';
            link.href = img.src;
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        }
    }
</script>
@endpush
