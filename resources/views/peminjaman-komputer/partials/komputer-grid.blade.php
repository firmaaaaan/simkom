@forelse($komputers as $komputer)
    @php
        $statusColor = match($komputer->status) {
            'aktif' => 'bg-green-50 border-green-200',
            'perbaikan' => 'bg-yellow-50 border-yellow-200',
            'rusak' => 'bg-red-50 border-red-200',
            'dipinjam' => 'bg-blue-50 border-blue-200',
            default => 'bg-gray-50 border-gray-200',
        };
        $statusBadge = match($komputer->status) {
            'aktif' => 'bg-label-success',
            'perbaikan' => 'bg-label-warning',
            'rusak' => 'bg-label-danger',
            'dipinjam' => 'bg-label-info',
            default => 'bg-label-secondary',
        };
    @endphp
    <div class="rounded-2xl border p-6 {{ $statusColor }} hover:shadow-lg transition-shadow">
        <div class="flex items-start justify-between mb-4">
            <div class="w-12 h-12 bg-white rounded-xl flex items-center justify-center shadow-sm">
                <svg class="w-6 h-6 text-slate-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z"/></svg>
            </div>
            <span class="badge {{ $statusBadge }} rounded-pill">{{ ucfirst($komputer->status) }}</span>
        </div>
        <h3 class="text-lg font-bold text-slate-900 mb-1">{{ $komputer->nama_komputer }}</h3>
        <p class="text-sm text-slate-500 mb-2">{{ $komputer->kode_komputer }}</p>
        <div class="text-sm text-slate-600 space-y-1 mb-4">
            <p><span class="text-slate-500">Laboratorium:</span> {{ $komputer->laboratorium->nama_laboratorium ?? '-' }}</p>
            <p><span class="text-slate-500">Spesifikasi:</span> {{ $komputer->spesifikasi ?: '-' }}</p>
        </div>
        @if($komputer->status === 'aktif')
            <a href="{{ route('peminjaman-komputer.create', $komputer) }}" class="block w-full text-center bg-primary-600 text-white px-4 py-2 rounded-lg hover:bg-primary-700 transition-colors font-medium">
                Pinjam Komputer
            </a>
        @else
            <button disabled class="block w-full text-center bg-slate-300 text-slate-500 px-4 py-2 rounded-lg font-medium cursor-not-allowed">
                Tidak Tersedia
            </button>
        @endif
    </div>
@empty
    <div class="col-span-full text-center text-slate-500 py-12">
        <p>Tidak ada data komputer untuk laboratorium ini.</p>
    </div>
@endforelse
