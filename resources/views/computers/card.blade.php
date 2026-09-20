@extends('layouts.app')

@section('title', 'Kartu Kendali - ' . $computer->code)
@section('header', 'Kartu Kendali Komputer')

@section('content')
<div class="max-w-4xl">
    <div class="mb-6 flex items-center justify-between">
        <a href="{{ route('computers.show', $computer) }}" class="inline-flex items-center gap-1.5 text-sm text-gray-500 hover:text-green-600 transition-colors">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
            </svg>
            Kembali
        </a>
        <a href="{{ route('computers.card-print', $computer) }}" target="_blank" class="no-print inline-flex items-center gap-2 px-4 py-2 border border-green-600 text-green-600 text-sm font-medium rounded-lg hover:bg-green-50 transition-colors">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0110.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0l.229 2.523a1.125 1.125 0 01-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0021 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 00-1.913-.247M6.34 18H5.25A2.25 2.25 0 013 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 011.913-.247m10.5 0a48.536 48.536 0 00-10.5 0m10.5 0V3.375c0-.621-.504-1.125-1.125-1.125h-8.25c-.621 0-1.125.504-1.125 1.125v3.659" />
            </svg>
            Cetak
        </a>
    </div>

    @if(session('success'))
        <div class="mb-4 px-4 py-3 bg-green-50 border border-green-200 text-green-700 rounded-lg text-sm flex items-center gap-2">
            <svg class="w-5 h-5 text-green-600 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            {{ session('success') }}
        </div>
    @endif

    <div class="bg-white rounded-xl border border-gray-200 mb-6">
        <div class="bg-green-600 text-white px-6 py-4 rounded-t-xl">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 bg-white/20 rounded-lg flex items-center justify-center">
                        <svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 17.25v1.007a3 3 0 01-.879 2.122L7.5 21h9l-.621-.621A3 3 0 0115 18.257V17.25m6-12V15a2.25 2.25 0 01-2.25 2.25H5.25A2.25 2.25 0 013 15V5.25m18 0A2.25 2.25 0 0018.75 3H5.25A2.25 2.25 0 003 5.25m18 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 7.41A2.25 2.25 0 012.25 5.496V5.25" />
                        </svg>
                    </div>
                    <div>
                        <h1 class="text-xl font-bold">KARTU KENDALI KOMPUTER</h1>
                        <p class="text-green-100 text-sm">{{ $computer->laboratory?->name ?? 'Laboratorium' }}</p>
                    </div>
                </div>
                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-bold
                    {{ $computer->status === 'Aktif' ? 'bg-white text-green-700' : ($computer->status === 'Maintenance' ? 'bg-yellow-400 text-yellow-900' : 'bg-gray-300 text-gray-700') }}">
                    {{ $computer->status }}
                </span>
            </div>
        </div>

        <div class="p-6">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="bg-gray-50 rounded-lg p-3">
                    <p class="text-xs text-gray-400 mb-1">Kode Komputer</p>
                    <p class="text-lg font-bold text-green-700">{{ $computer->code }}</p>
                </div>
                <div class="bg-gray-50 rounded-lg p-3">
                    <p class="text-xs text-gray-400 mb-1">Lokasi</p>
                    <p class="text-sm font-semibold text-gray-800">{{ $computer->laboratory?->name ?? '-' }}</p>
                </div>
            </div>

            <div class="mt-4 flex items-center gap-4 p-4 bg-gray-50 rounded-lg border border-dashed border-gray-300">
                <div class="flex-shrink-0 bg-white p-2 rounded-lg border border-gray-200">
                    <div id="qrCode"></div>
                </div>
                <div class="flex-1">
                    <p class="text-sm font-semibold text-gray-800 mb-1">QR Code Kartu Kendali</p>
                    <p class="text-xs text-gray-500 mb-3">Scan QR Code untuk melihat riwayat pengecekan komputer ini.</p>
                    <button type="button" onclick="downloadQR()" class="no-print inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium text-green-600 bg-green-50 border border-green-200 rounded-lg hover:bg-green-100 transition-colors">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" />
                        </svg>
                        Download QR Code
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Form Pengecekan --}}
    <div class="bg-white rounded-xl border border-gray-200 mb-6">
        <div class="px-6 py-4 border-b border-gray-100">
            <h2 class="text-base font-semibold text-gray-800">Form Pengecekan Baru</h2>
            <p class="text-xs text-gray-500 mt-1">Centang status setiap komponen lalu simpan.</p>

            @if($currentYear)
                <p class="text-xs text-gray-500 mt-2">
                    Pengecekan ini akan dicatat pada tahun ajaran
                    <span class="font-semibold text-gray-700">{{ $currentYear->name }}</span>
                    <span class="text-gray-400">({{ $currentYear->periodLabel() }})</span>.
                </p>
            @else
                <p class="text-xs text-amber-600 mt-2 flex items-start gap-1.5">
                    <svg class="w-4 h-4 flex-shrink-0 mt-px" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
                    </svg>
                    Belum ada tahun ajaran berstatus Aktif. Pengecekan tetap tersimpan, tetapi tanpa tahun ajaran sehingga tidak muncul di laporan per tahun ajaran.
                </p>
            @endif
        </div>

        <form action="{{ route('computers.check', $computer) }}" method="POST" class="p-6">
            @csrf
            <div class="mb-6">
                <h4 class="text-sm font-semibold text-gray-700 mb-3 flex items-center gap-2">
                    <span class="w-5 h-5 bg-blue-100 rounded flex items-center justify-center">
                        <svg class="w-3 h-3 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 3v1.5M4.5 8.25H3m18 0h-1.5M4.5 12H3m18 0h-1.5m-15 3.75H3m18 0h-1.5M8.25 19.5V21M12 3v1.5m0 15V21m3.75-18v1.5m0 15V21m-9-1.5h10.5a2.25 2.25 0 002.25-2.25V6.75a2.25 2.25 0 00-2.25-2.25H6.75A2.25 2.25 0 004.5 6.75v10.5a2.25 2.25 0 002.25 2.25z" />
                        </svg>
                    </span>
                    Hardware
                </h4>
                @forelse($computer->hardware as $hw)
                    <div class="flex items-center gap-4 px-4 py-3 bg-gray-50 border border-gray-200 rounded-lg mb-2">
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-800">{{ $hw->name }}</p>
                            <p class="text-xs text-gray-500">{{ $hw->category }} {{ $hw->brand ? "- {$hw->brand}" : '' }}</p>
                        </div>
                        <select name="hardware_status[]" class="px-3 py-1.5 text-xs border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent">
                            <option value="Baik">Baik</option>
                            <option value="Rusak">Rusak</option>
                            <option value="Perlu Perbaikan">Perlu Perbaikan</option>
                            <option value="Usang">Usang</option>
                            <option value="Tidak Terdeteksi">Tidak Terdeteksi</option>
                        </select>
                        <input type="text" name="hardware_notes[]" placeholder="Catatan..." class="w-40 px-3 py-1.5 text-xs border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent">
                    </div>
                @empty
                    <p class="text-sm text-gray-400 italic">Belum ada hardware</p>
                @endforelse
            </div>

            <div class="mb-6">
                <h4 class="text-sm font-semibold text-gray-700 mb-3 flex items-center gap-2">
                    <span class="w-5 h-5 bg-purple-100 rounded flex items-center justify-center">
                        <svg class="w-3 h-3 text-purple-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 17.25v1.007a3 3 0 01-.879 2.122L7.5 21h9l-.621-.621A3 3 0 0115 18.257V17.25m6-12V15a2.25 2.25 0 01-2.25 2.25H5.25A2.25 2.25 0 013 15V5.25m18 0A2.25 2.25 0 0018.75 3H5.25A2.25 2.25 0 003 5.25m18 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 7.41A2.25 2.25 0 012.25 5.496V5.25" />
                        </svg>
                    </span>
                    Software
                </h4>
                @forelse($computer->software as $sw)
                    <div class="flex items-center gap-4 px-4 py-3 bg-gray-50 border border-gray-200 rounded-lg mb-2">
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-800">{{ $sw->name }}</p>
                            <p class="text-xs text-gray-500">{{ $sw->category }} {{ $sw->version ? "v{$sw->version}" : '' }}</p>
                        </div>
                        <select name="software_status[]" class="px-3 py-1.5 text-xs border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent">
                            <option value="Baik">Baik</option>
                            <option value="Rusak">Rusak</option>
                            <option value="Perlu Perbaikan">Perlu Perbaikan</option>
                            <option value="Usang">Usang</option>
                            <option value="Tidak Terdeteksi">Tidak Terdeteksi</option>
                        </select>
                        <input type="text" name="software_notes[]" placeholder="Catatan..." class="w-40 px-3 py-1.5 text-xs border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent">
                    </div>
                @empty
                    <p class="text-sm text-gray-400 italic">Belum ada software</p>
                @endforelse
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Status Keseluruhan <span class="text-red-500">*</span></label>
                    <select name="overall_status" required class="w-full px-4 py-2.5 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent">
                        <option value="Baik">Baik</option>
                        <option value="Perlu Perbaikan">Perlu Perbaikan</option>
                        <option value="Kritis">Kritis</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Catatan Umum</label>
                    <input type="text" name="notes" placeholder="Catatan tambahan..." class="w-full px-4 py-2.5 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent">
                </div>
            </div>

            <button type="submit" class="w-full px-4 py-2.5 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700 transition-colors">
                Simpan Pengecekan
            </button>
        </form>
    </div>

    {{-- Riwayat Pengecekan --}}
    <div class="bg-white rounded-xl border border-gray-200">
        <div class="px-6 py-4 border-b border-gray-100">
            <h2 class="text-base font-semibold text-gray-800">Riwayat Pengecekan</h2>
        </div>
        @include('computers.partials.riwayat-pengecekan', ['checks' => $checks, 'showItemNames' => true, 'years' => $years])
    </div>
</div>
@endsection

@push('styles')
<style>
    @media print {
        .no-print { display: none !important; }
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/qrcodejs@1.0.0/qrcode.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const qrUrl = "{{ route('kartu.show', $computer) }}";
        const qrContainer = document.getElementById('qrCode');
        qrContainer.innerHTML = '';
        
        new QRCode(qrContainer, {
            text: qrUrl,
            width: 120,
            height: 120,
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
