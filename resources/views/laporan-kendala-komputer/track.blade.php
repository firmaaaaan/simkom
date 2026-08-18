@extends('layouts.public')

@section('title', 'Lacak Laporan & Peminjaman')

@section('content')
<div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden mb-6">
        <div class="bg-primary-600 px-6 py-5">
            <h1 class="text-xl font-bold text-white">Lacak Laporan & Peminjaman</h1>
            <p class="text-primary-100 text-sm mt-1">Masukkan kode tracker untuk melihat status laporan kendala atau peminjaman komputer</p>
        </div>
        <div class="p-6">
            @if($error)
                <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg mb-4">
                    {{ $error }}
                </div>
            @endif

            <form method="GET" action="{{ route('laporan-kendala-komputer.track') }}" class="space-y-4">
                @csrf
                <div>
                    <label for="kode_tracker" class="block text-sm font-medium text-slate-700 mb-1">Kode Tracker <span class="text-red-500">*</span></label>
                    <input type="text" name="kode_tracker" id="kode_tracker" value="{{ old('kode_tracker', $kodeTracker ?? request('kode_tracker')) }}" class="w-full rounded-lg border-slate-300 focus:border-primary-500 focus:ring-primary-500 px-3 py-2 border @error('kode_tracker') border-red-500 @enderror" placeholder="Contoh: LKD-20260809-ABCD12 atau PKM-20260809-ABCD12" required>
                    @error('kode_tracker') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
                    <p class="text-xs text-slate-500 mt-1">Gunakan prefix <strong>LKD-</strong> untuk laporan kendala atau <strong>PKM-</strong> untuk peminjaman komputer.</p>
                </div>

                <div class="flex items-center gap-3 pt-2">
                    <button type="submit" class="bg-primary-600 text-white px-6 py-2.5 rounded-lg hover:bg-primary-700 transition-colors font-medium">
                        Lacak
                    </button>
                    <a href="{{ url('/') }}" class="text-slate-600 hover:text-slate-900 transition-colors">Batal</a>
                </div>
            </form>

            @if($laporan)
                <div class="mt-8 border-t border-slate-200 pt-6">
                    <h2 class="text-lg font-bold text-slate-900 mb-4">Hasil Pencarian - Laporan Kendala</h2>
                    <div class="bg-slate-50 rounded-xl p-4 border border-slate-100">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-sm">
                            <div>
                                <span class="text-slate-500">Kode Tracker</span>
                                <div class="font-medium text-slate-900">{{ $laporan->kode_tracker }}</div>
                            </div>
                            <div>
                                <span class="text-slate-500">Status</span>
                                <div class="font-medium text-slate-900">
                                    @php
                                        $badgeClass = $laporan->status_kendala == 'menunggu' ? 'bg-label-warning' : ($laporan->status_kendala == 'diperbaiki' ? 'bg-label-info' : 'bg-label-success');
                                    @endphp
                                    <span class="badge {{ $badgeClass }} rounded-pill">{{ ucfirst($laporan->status_kendala) }}</span>
                                </div>
                            </div>
                            <div>
                                <span class="text-slate-500">Komputer</span>
                                <div class="font-medium text-slate-900">{{ $laporan->komputer->nama_komputer ?? '-' }}</div>
                            </div>
                            <div>
                                <span class="text-slate-500">Tanggal Lapor</span>
                                <div class="font-medium text-slate-900">{{ $laporan->tanggal_lapor->translatedFormat('d F Y') }}</div>
                            </div>
                            <div class="sm:col-span-2">
                                <span class="text-slate-500">Deskripsi Kendala</span>
                                <div class="font-medium text-slate-900">{{ $laporan->deskripsi_kendala }}</div>
                            </div>
                            @if($laporan->catatan_admin)
                                <div class="sm:col-span-2">
                                    <span class="text-slate-500">Catatan Admin</span>
                                    <div class="font-medium text-slate-900">{{ $laporan->catatan_admin }}</div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @endif

            @if($peminjaman)
                <div class="mt-8 border-t border-slate-200 pt-6">
                    <h2 class="text-lg font-bold text-slate-900 mb-4">Hasil Pencarian - Peminjaman Komputer</h2>
                    <div class="bg-slate-50 rounded-xl p-4 border border-slate-100">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-sm">
                            <div>
                                <span class="text-slate-500">Kode Tracker</span>
                                <div class="font-medium text-slate-900">{{ $peminjaman->kode_tracker }}</div>
                            </div>
                            <div>
                                <span class="text-slate-500">Status Peminjaman</span>
                                <div class="font-medium text-slate-900">
                                    @php
                                        $badgeClass = match($peminjaman->status_peminjaman) {
                                            'menunggu' => 'bg-label-warning',
                                            'disetujui' => 'bg-label-info',
                                            'ditolak' => 'bg-label-danger',
                                            'dipinjam' => 'bg-label-primary',
                                            'dikembalikan' => 'bg-label-success',
                                            default => 'bg-label-secondary',
                                        };
                                    @endphp
                                    <span class="badge {{ $badgeClass }} rounded-pill">{{ ucfirst($peminjaman->status_peminjaman) }}</span>
                                </div>
                            </div>
                            <div>
                                <span class="text-slate-500">Komputer</span>
                                <div class="font-medium text-slate-900">{{ $peminjaman->komputer->nama_komputer ?? '-' }}</div>
                            </div>
                            <div>
                                <span class="text-slate-500">Peminjam</span>
                                <div class="font-medium text-slate-900">{{ $peminjaman->nama_peminjam }}</div>
                            </div>
                            <div>
                                <span class="text-slate-500">Tanggal Pinjam</span>
                                <div class="font-medium text-slate-900">{{ $peminjaman->tanggal_pinjam->translatedFormat('d F Y') }}</div>
                            </div>
                            <div>
                                <span class="text-slate-500">Jam</span>
                                <div class="font-medium text-slate-900">{{ $peminjaman->jam_mulai }} - {{ $peminjaman->jam_selesai }}</div>
                            </div>
                            <div>
                                <span class="text-slate-500">NPM/NIM</span>
                                <div class="font-medium text-slate-900">{{ $peminjaman->npm_nim }}</div>
                            </div>
                            <div>
                                <span class="text-slate-500">Prodi</span>
                                <div class="font-medium text-slate-900">{{ $peminjaman->nama_prodi ?? '-' }}</div>
                            </div>
                            @if($peminjaman->catatan)
                                <div class="sm:col-span-2">
                                    <span class="text-slate-500">Catatan</span>
                                    <div class="font-medium text-slate-900">{{ $peminjaman->catatan }}</div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection