<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Form Peminjaman - {{ $inventaris_iot_jaringan->nama_inventaris }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ['Inter', 'system-ui', 'sans-serif'] },
                    colors: {
                        primary: { 50: '#ecfdf5', 100: '#d1fae5', 200: '#a7f3d0', 300: '#6ee7b7', 400: '#34d399', 500: '#10b981', 600: '#059669', 700: '#047857', 800: '#065f46', 900: '#064e3b' }
                    }
                }
            }
        }
    </script>
</head>
<body class="font-sans antialiased bg-slate-50 text-slate-900">
    <nav class="bg-white border-b border-slate-200">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-14">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 bg-primary-600 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z"/></svg>
                    </div>
                    <span class="font-bold text-lg text-slate-900">SimLabKom</span>
                </div>
                <a href="{{ url('/') }}" class="text-sm text-slate-600 hover:text-slate-900 transition-colors">Kembali</a>
            </div>
        </div>
    </nav>

    <main class="py-10">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden mb-6">
                <div class="bg-primary-600 px-6 py-5">
                    <h1 class="text-xl font-bold text-white">Form Peminjaman</h1>
                    <p class="text-primary-100 text-sm mt-1">Isi data berikut untuk meminjam perangkat</p>
                </div>
                <div class="p-6">
                    <div class="bg-slate-50 rounded-xl p-4 border border-slate-100 mb-6">
                        <h2 class="font-semibold text-slate-900 mb-2">Informasi Barang</h2>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-sm">
                            <div>
                                <span class="text-slate-500">Nama Inventaris</span>
                                <div class="font-medium text-slate-900">{{ $inventaris_iot_jaringan->nama_inventaris }}</div>
                            </div>
                            <div>
                                <span class="text-slate-500">Kode Inventaris</span>
                                <div class="font-medium text-slate-900">{{ $inventaris_iot_jaringan->kode_inventaris ?? $inventaris_iot_jaringan->id }}</div>
                            </div>
                            <div>
                                <span class="text-slate-500">Kategori</span>
                                <div class="font-medium text-slate-900">{{ $inventaris_iot_jaringan->kategori }}</div>
                            </div>
                            <div>
                                <span class="text-slate-500">Jenis</span>
                                <div class="font-medium text-slate-900">{{ $inventaris_iot_jaringan->jenis }}</div>
                            </div>
                            <div>
                                <span class="text-slate-500">Lokasi</span>
                                <div class="font-medium text-slate-900">{{ $inventaris_iot_jaringan->lokasi ?: '-' }}</div>
                            </div>
                            <div>
                                <span class="text-slate-500">Status</span>
                                <div class="font-medium text-slate-900">{{ ucfirst($inventaris_iot_jaringan->status_ketersediaan) }}</div>
                            </div>
                        </div>
                    </div>

                    @if(session('error'))
                        <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg mb-4">
                            {{ session('error') }}
                        </div>
                    @endif

                    <form method="POST" action="{{ route('inventaris-iot-jaringan.peminjaman.store', $inventaris_iot_jaringan) }}" class="space-y-4">
                        @csrf
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

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label for="tanggal_pinjam" class="block text-sm font-medium text-slate-700 mb-1">Tanggal Pinjam <span class="text-red-500">*</span></label>
                                <input type="date" name="tanggal_pinjam" id="tanggal_pinjam" value="{{ old('tanggal_pinjam', date('Y-m-d')) }}" class="w-full rounded-lg border-slate-300 focus:border-primary-500 focus:ring-primary-500 px-3 py-2 border @error('tanggal_pinjam') border-red-500 @enderror" required>
                                @error('tanggal_pinjam') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label for="tanggal_kembali_direncanakan" class="block text-sm font-medium text-slate-700 mb-1">Tanggal Kembali Direncanakan <span class="text-red-500">*</span></label>
                                <input type="date" name="tanggal_kembali_direncanakan" id="tanggal_kembali_direncanakan" value="{{ old('tanggal_kembali_direncanakan') }}" class="w-full rounded-lg border-slate-300 focus:border-primary-500 focus:ring-primary-500 px-3 py-2 border @error('tanggal_kembali_direncanakan') border-red-500 @enderror" required>
                                @error('tanggal_kembali_direncanakan') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
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
                            <a href="{{ url('/') }}" class="text-slate-600 hover:text-slate-900 transition-colors">Batal</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </main>

    <footer class="bg-white border-t border-slate-200 py-6">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 text-center text-xs text-slate-500">
            <p>&copy; {{ date('Y') }} SimLabKom. Sistem Inventaris Laboratorium.</p>
        </div>
    </footer>
</body>
</html>
