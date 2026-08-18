<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Pemeliharaan - {{ $komputer->nama_komputer }}</title>
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
    <style>
        .glass-nav { background: rgba(255,255,255,0.85); backdrop-filter: blur(12px); border-bottom: 1px solid rgba(0,0,0,0.05); }
    </style>
</head>
<body class="font-sans antialiased bg-slate-50 text-slate-900">
    <nav class="glass-nav fixed top-0 left-0 right-0 z-50">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-14">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 bg-primary-600 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z"/></svg>
                    </div>
                    <span class="font-bold text-lg text-slate-900">SimLabKom</span>
                </div>
                <a href="{{ route('admin.komputer.index') }}" class="text-sm text-slate-600 hover:text-slate-900 transition-colors">Kembali ke Dashboard</a>
            </div>
        </div>
    </nav>

    <main class="pt-24 pb-16">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden mb-6">
                <div class="bg-primary-600 px-6 py-5">
                    <h1 class="text-xl font-bold text-white">Riwayat Pemeliharaan</h1>
                    <p class="text-primary-100 text-sm mt-1">Data pemeliharaan untuk komputer ini</p>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div class="bg-slate-50 rounded-xl p-4 border border-slate-100">
                            <div class="text-xs text-slate-500 mb-1">Nama Komputer</div>
                            <div class="font-semibold text-slate-900">{{ $komputer->nama_komputer }}</div>
                        </div>
                        <div class="bg-slate-50 rounded-xl p-4 border border-slate-100">
                            <div class="text-xs text-slate-500 mb-1">Kode Komputer</div>
                            <div class="font-semibold text-slate-900">{{ $komputer->kode_komputer }}</div>
                        </div>
                        <div class="bg-slate-50 rounded-xl p-4 border border-slate-100">
                            <div class="text-xs text-slate-500 mb-1">Laboratorium</div>
                            <div class="font-semibold text-slate-900">{{ $komputer->laboratorium->nama_laboratorium ?? '-' }}</div>
                        </div>
                        <div class="bg-slate-50 rounded-xl p-4 border border-slate-100">
                            <div class="text-xs text-slate-500 mb-1">Total Pemeliharaan</div>
                            <div class="font-semibold text-slate-900">{{ $pemeliharaan->count() }} kali</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
                    <h2 class="font-semibold text-slate-900">Daftar Riwayat Pemeliharaan</h2>
                    @if($pemeliharaan->count() > 0)
                        <span class="text-xs text-slate-500">Menampilkan {{ $pemeliharaan->count() }} data</span>
                    @endif
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-slate-50 text-slate-600">
                            <tr>
                                <th class="text-left px-6 py-3 font-medium">No</th>
                                <th class="text-left px-6 py-3 font-medium">Tanggal</th>
                                <th class="text-left px-6 py-3 font-medium">Tahun Ajaran</th>
                                <th class="text-left px-6 py-3 font-medium">Jenis</th>
                                <th class="text-left px-6 py-3 font-medium">Deskripsi</th>
                                <th class="text-left px-6 py-3 font-medium">Biaya</th>
                                <th class="text-left px-6 py-3 font-medium">PIC</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($pemeliharaan as $index => $item)
                                <tr class="hover:bg-slate-50/60 transition-colors">
                                    <td class="px-6 py-4 text-slate-500">{{ $index + 1 }}</td>
                                    <td class="px-6 py-4 text-slate-900 font-medium">{{ \Carbon\Carbon::parse($item->tanggal_pemeliharaan)->translatedFormat('d F Y') }}</td>
                                    <td class="px-6 py-4 text-slate-600">{{ $item->tahunAjaran->nama ?? '-' }}</td>
                                    <td class="px-6 py-4">
                                        @php
                                            $badgeClass = match($item->jenis_pemeliharaan) {
                                                'preventif' => 'bg-sky-50 text-sky-700 border border-sky-200',
                                                'korektif' => 'bg-amber-50 text-amber-700 border border-amber-200',
                                                'upgrade' => 'bg-indigo-50 text-indigo-700 border border-indigo-200',
                                                'penggantian' => 'bg-rose-50 text-rose-700 border border-rose-200',
                                                default => 'bg-slate-100 text-slate-700 border border-slate-200',
                                            };
                                        @endphp
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $badgeClass }}">
                                            {{ ucfirst($item->jenis_pemeliharaan) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-slate-600 max-w-xs">{{ $item->deskripsi }}</td>
                                    <td class="px-6 py-4 text-slate-900 font-medium">{{ $item->biaya ? 'Rp ' . number_format($item->biaya, 0, ',', '.') : '-' }}</td>
                                    <td class="px-6 py-4 text-slate-600">{{ $item->pic ?: '-' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-6 py-12 text-center text-slate-500">
                                        <div class="flex flex-col items-center gap-2">
                                            <svg class="w-10 h-10 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 012 2"/></svg>
                                            <span>Belum ada data pemeliharaan untuk komputer ini.</span>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>

    <footer class="bg-white border-t border-slate-200 py-6">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 text-center text-xs text-slate-500">
            <p>&copy; {{ date('Y') }} SimLabKom. Sistem Inventaris Laboratorium.</p>
        </div>
    </footer>
</body>
</html>
