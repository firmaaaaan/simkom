@extends('layouts.public')

@section('title', 'Form Peminjaman - ' . $komputer->nama_komputer)

@section('content')
<div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden mb-6">
        <div class="bg-primary-600 px-6 py-5">
            <h1 class="text-xl font-bold text-white">Form Peminjaman Komputer</h1>
            <p class="text-primary-100 text-sm mt-1">Isi data berikut untuk meminjam komputer untuk tugas</p>
        </div>
        <div class="p-6">
            <div class="bg-slate-50 rounded-xl p-4 border border-slate-100 mb-6">
                <h2 class="font-semibold text-slate-900 mb-2">Informasi Komputer</h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-sm">
                    <div>
                        <span class="text-slate-500">Nama Komputer</span>
                        <div class="font-medium text-slate-900">{{ $komputer->nama_komputer }}</div>
                    </div>
                    <div>
                        <span class="text-slate-500">Kode Komputer</span>
                        <div class="font-medium text-slate-900">{{ $komputer->kode_komputer }}</div>
                    </div>
                    <div>
                        <span class="text-slate-500">Laboratorium</span>
                        <div class="font-medium text-slate-900">{{ $komputer->laboratorium->nama_laboratorium ?? '-' }}</div>
                    </div>
                    <div>
                            <span class="text-slate-500">Spesifikasi</span>
                            <div class="font-medium text-slate-900">{{ $komputer->spesifikasi ?: '-' }}</div>
                    </div>
                </div>
            </div>

            @if(session('error'))
                <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg mb-4">
                    {{ session('error') }}
                </div>
            @endif

            <div class="bg-yellow-50 border border-yellow-200 text-yellow-800 px-4 py-3 rounded-lg mb-4">
                <p class="font-medium">Perhatian:</p>
                <p class="text-sm">Peminjaman akan dibatalkan jika terjadi reschedule perkuliahan di laboratorium terkait.</p>
            </div>

            <form method="POST" action="{{ route('peminjaman-komputer.store') }}" class="space-y-4">
                @csrf
                <input type="hidden" name="komputer_id" value="{{ $komputer->id }}">

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="nama_peminjam" class="block text-sm font-medium text-slate-700 mb-1">Nama Lengkap <span class="text-red-500">*</span></label>
                        <input type="text" name="nama_peminjam" id="nama_peminjam" value="{{ old('nama_peminjam') }}" class="w-full rounded-lg border-slate-300 focus:border-primary-500 focus:ring-primary-500 px-3 py-2 border @error('nama_peminjam') border-red-500 @enderror" placeholder="Masukkan nama lengkap" required>
                        @error('nama_peminjam') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label for="npm_nim" class="block text-sm font-medium text-slate-700 mb-1">NPM/NIM <span class="text-red-500">*</span></label>
                        <input type="text" name="npm_nim" id="npm_nim" value="{{ old('npm_nim') }}" class="w-full rounded-lg border-slate-300 focus:border-primary-500 focus:ring-primary-500 px-3 py-2 border @error('npm_nim') border-red-500 @enderror" placeholder="Masukkan NPM atau NIM" required>
                        @error('npm_nim') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div>
                    <label for="nama_prodi" class="block text-sm font-medium text-slate-700 mb-1">Nama Prodi <span class="text-red-500">*</span></label>
                    <input type="text" name="nama_prodi" id="nama_prodi" value="{{ old('nama_prodi') }}" class="w-full rounded-lg border-slate-300 focus:border-primary-500 focus:ring-primary-500 px-3 py-2 border @error('nama_prodi') border-red-500 @enderror" placeholder="Contoh: Teknik Informatika" required>
                    @error('nama_prodi') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="tanggal_pinjam" class="block text-sm font-medium text-slate-700 mb-1">Tanggal Pinjam <span class="text-red-500">*</span></label>
                        <input type="date" name="tanggal_pinjam" id="tanggal_pinjam" value="{{ old('tanggal_pinjam', date('Y-m-d', strtotime('+1 day'))) }}" class="w-full rounded-lg border-slate-300 focus:border-primary-500 focus:ring-primary-500 px-3 py-2 border @error('tanggal_pinjam') border-red-500 @enderror" required>
                        @error('tanggal_pinjam') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
                        <p class="text-xs text-slate-500 mt-1">Minimal peminjaman H-1 (1 hari sebelum).</p>
                    </div>
                    <div>
                        <label for="jam_mulai" class="block text-sm font-medium text-slate-700 mb-1">Jam Mulai <span class="text-red-500">*</span></label>
                        <input type="time" name="jam_mulai" id="jam_mulai" value="{{ old('jam_mulai') }}" class="w-full rounded-lg border-slate-300 focus:border-primary-500 focus:ring-primary-500 px-3 py-2 border @error('jam_mulai') border-red-500 @enderror" required>
                        @error('jam_mulai') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="jam_selesai" class="block text-sm font-medium text-slate-700 mb-1">Jam Selesai <span class="text-red-500">*</span></label>
                        <input type="time" name="jam_selesai" id="jam_selesai" value="{{ old('jam_selesai') }}" class="w-full rounded-lg border-slate-300 focus:border-primary-500 focus:ring-primary-500 px-3 py-2 border @error('jam_selesai') border-red-500 @enderror" required>
                        @error('jam_selesai') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
                        <p class="text-xs text-slate-500 mt-1">Minimal durasi 2 jam.</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Durasi</label>
                        <input type="text" id="durasi" value="2 jam" class="w-full rounded-lg border-slate-200 bg-slate-50 px-3 py-2 border text-slate-500" readonly>
                    </div>
                </div>

                <div>
                    <label for="catatan" class="block text-sm font-medium text-slate-700 mb-1">Catatan (Opsional)</label>
                    <textarea name="catatan" id="catatan" rows="3" class="w-full rounded-lg border-slate-300 focus:border-primary-500 focus:ring-primary-500 px-3 py-2 border @error('catatan') border-red-500 @enderror" placeholder="Tambahkan catatan jika diperlukan">{{ old('catatan') }}</textarea>
                    @error('catatan') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
                </div>

                <div class="flex items-center gap-3 pt-2">
                    <button type="submit" class="bg-primary-600 text-white px-6 py-2.5 rounded-lg hover:bg-primary-700 transition-colors font-medium">
                        Ajukan Peminjaman
                    </button>
                    <a href="{{ route('peminjaman-komputer.index') }}" class="text-slate-600 hover:text-slate-900 transition-colors">Batal</a>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        var jamMulai = document.getElementById('jam_mulai');
        var jamSelesai = document.getElementById('jam_selesai');
        var durasi = document.getElementById('durasi');

        function hitungDurasi() {
            if (jamMulai.value && jamSelesai.value) {
                var mulai = jamMulai.value.split(':').map(Number);
                var selesai = jamSelesai.value.split(':').map(Number);
                var mulaiMenit = mulai[0] * 60 + mulai[1];
                var selesaiMenit = selesai[0] * 60 + selesai[1];
                var diff = selesaiMenit - mulaiMenit;

                if (diff >= 120) {
                    var jam = Math.floor(diff / 60);
                    var menit = diff % 60;
                    durasi.value = jam + ' jam ' + (menit > 0 ? menit + ' menit' : '');
                } else if (diff > 0) {
                    durasi.value = diff + ' menit';
                } else {
                    durasi.value = '-';
                }
            }
        }

        if (jamMulai && jamSelesai) {
            jamMulai.addEventListener('change', hitungDurasi);
            jamSelesai.addEventListener('change', hitungDurasi);
        }
    });
</script>
@endsection