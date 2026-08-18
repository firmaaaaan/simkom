<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Peminjaman Berhasil - SimLabKom</title>
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
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
                <div class="p-8">
                    <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    </div>
                    <h1 class="text-2xl font-bold text-slate-900 mb-2">Peminjaman Berhasil Dicatat</h1>
                    <p class="text-slate-600 mb-6">Silakan tunjukkan bukti peminjaman kepada admin di laboratorium.</p>

                    @php
                        $data = session('peminjaman_data', []);
                    @endphp

                    @if($data)
                        <div class="bg-slate-50 rounded-xl p-4 border border-slate-100 text-left mb-6">
                            <h2 class="font-semibold text-slate-900 mb-2">Detail Peminjaman</h2>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-sm">
                                <div>
                                    <span class="text-slate-500">Nama Peminjam</span>
                                    <div class="font-medium text-slate-900">{{ $data['nama_peminjam'] ?? '-' }}</div>
                                </div>
                                <div>
                                    <span class="text-slate-500">NPM/NIM</span>
                                    <div class="font-medium text-slate-900">{{ $data['npm_nim'] ?? '-' }}</div>
                                </div>
                                <div>
                                    <span class="text-slate-500">Tanggal Pinjam</span>
                                    <div class="font-medium text-slate-900">{{ \Carbon\Carbon::parse($data['tanggal_pinjam'] ?? now())->translatedFormat('d F Y') }}</div>
                                </div>
                                <div>
                                    <span class="text-slate-500">Estimasi Kembali</span>
                                    <div class="font-medium text-slate-900">{{ \Carbon\Carbon::parse($data['tanggal_kembali_direncanakan'] ?? now())->translatedFormat('d F Y') }}</div>
                                </div>
                            </div>
                        </div>
                    @endif

                    <a href="{{ url('/') }}" class="bg-primary-600 text-white px-6 py-2.5 rounded-lg hover:bg-primary-700 transition-colors font-medium">
                        Kembali ke Beranda
                    </a>
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
