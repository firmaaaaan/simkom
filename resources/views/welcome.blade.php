@extends('layouts.public')
@section('content')
<!-- Hero Section -->
    <section class="hero-gradient pt-32 pb-20 lg:pt-40 lg:pb-28 relative overflow-hidden">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid lg:grid-cols-2 gap-12 items-center">
                <div>
                    <div class="inline-flex items-center gap-2 bg-primary-50 text-primary-700 border border-primary-100 px-4 py-2 rounded-full text-sm font-medium mb-6">
                        <span class="w-2 h-2 bg-primary-500 rounded-full animate-pulse"></span>
                        Sistem Laboratorium Komputer Terpadu
                    </div>
                    <h1 class="text-4xl lg:text-6xl font-bold leading-tight mb-6">
                        Kelola Laboratorium Komputer dengan <br>
                        <span class="gradient-text">Mudah & Efisien</span>
                    </h1>
                    <p class="text-lg text-gray-600 mb-8 leading-relaxed">
                        Platform digital untuk mengelola aset laboratorium komputer, melaporkan kendala, meminjam komputer untuk tugas, dan memantau kondisi perangkat secara real-time.
                    </p>
                    <div class="flex flex-col sm:flex-row gap-4 mb-10">
                        <a href="{{ route('peminjaman-komputer.index') }}" class="inline-flex items-center justify-center gap-2 bg-primary-600 text-white px-8 py-3.5 rounded-xl hover:bg-primary-700 transition-all font-semibold shadow-lg shadow-primary-600/25">
                            Pinjam Komputer
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                            </svg>
                        </a>
                        <a href="{{ route('laporan-kendala-komputer.create') }}" class="inline-flex items-center justify-center gap-2 bg-white text-gray-700 border border-gray-200 px-8 py-3.5 rounded-xl hover:border-primary-300 hover:text-primary-700 transition-all font-semibold">
                            Lapor Kendala
                        </a>
                        <a href="{{ route('jadwal-kuliah.index') }}" class="inline-flex items-center justify-center gap-2 text-primary-700 px-4 py-3.5 rounded-xl hover:text-primary-900 transition-all font-semibold">
                            Lihat Jadwal
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </a>
                    </div>
                    <!-- Quick Stats -->
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-6 border-t border-primary-100 pt-8">
                        <div>
                            <p class="text-3xl font-bold text-primary-700">{{ $stats['komputer'] }}</p>
                            <p class="text-sm text-gray-500 mt-1">Komputer Terdaftar</p>
                        </div>
                        <div>
                            <p class="text-3xl font-bold text-primary-700">{{ $stats['laboratorium'] }}</p>
                            <p class="text-sm text-gray-500 mt-1">Laboratorium</p>
                        </div>
                        <div>
                            <p class="text-3xl font-bold text-primary-700">{{ $stats['peminjaman'] }}</p>
                            <p class="text-sm text-gray-500 mt-1">Peminjaman</p>
                        </div>
                        <div>
                            <p class="text-3xl font-bold text-primary-700">{{ $stats['laporan'] }}</p>
                            <p class="text-sm text-gray-500 mt-1">Laporan Kendala</p>
                        </div>
                    </div>
                </div>
                <div class="relative">
                    <div class="relative z-10">
                        <svg viewBox="0 0 500 400" fill="none" xmlns="http://www.w3.org/2000/svg" class="w-full max-w-lg mx-auto drop-shadow-xl">
                            <rect x="50" y="80" width="400" height="280" rx="12" fill="white" stroke="#d1fae5" stroke-width="2"/>
                            <rect x="50" y="80" width="400" height="50" rx="12" fill="#f0fdf4"/>
                            <rect x="50" y="110" width="400" height="20" fill="#f0fdf4"/>
                            <circle cx="75" cy="105" r="6" fill="#ef4444"/>
                            <circle cx="95" cy="105" r="6" fill="#f59e0b"/>
                            <circle cx="115" cy="105" r="6" fill="#10b981"/>
                            <rect x="50" y="140" width="120" height="220" rx="8" fill="#ecfdf5"/>
                            <rect x="180" y="140" width="270" height="100" rx="8" fill="#f8fafc"/>
                            <rect x="180" y="250" width="130" height="110" rx="8" fill="#f8fafc"/>
                            <rect x="320" y="250" width="130" height="110" rx="8" fill="#f8fafc"/>
                            <rect x="70" y="160" width="80" height="8" rx="4" fill="#cbd5e1"/>
                            <rect x="70" y="180" width="60" height="8" rx="4" fill="#cbd5e1"/>
                            <rect x="70" y="200" width="90" height="8" rx="4" fill="#cbd5e1"/>
                            <rect x="70" y="220" width="70" height="8" rx="4" fill="#10b981"/>
                            <rect x="70" y="240" width="80" height="8" rx="4" fill="#cbd5e1"/>
                            <rect x="70" y="260" width="60" height="8" rx="4" fill="#cbd5e1"/>
                            <rect x="70" y="280" width="90" height="8" rx="4" fill="#cbd5e1"/>
                            <rect x="70" y="300" width="70" height="8" rx="4" fill="#34d399"/>
                            <rect x="200" y="160" width="100" height="8" rx="4" fill="#cbd5e1"/>
                            <rect x="200" y="180" width="220" height="8" rx="4" fill="#cbd5e1"/>
                            <rect x="200" y="200" width="180" height="8" rx="4" fill="#cbd5e1"/>
                            <rect x="200" y="220" width="230" height="8" rx="4" fill="#cbd5e1"/>
                            <rect x="200" y="270" width="100" height="8" rx="4" fill="#cbd5e1"/>
                            <rect x="200" y="290" width="110" height="8" rx="4" fill="#cbd5e1"/>
                            <rect x="200" y="310" width="80" height="8" rx="4" fill="#10b981"/>
                            <rect x="200" y="330" width="100" height="8" rx="4" fill="#cbd5e1"/>
                            <rect x="340" y="270" width="100" height="8" rx="4" fill="#cbd5e1"/>
                            <rect x="340" y="290" width="90" height="8" rx="4" fill="#cbd5e1"/>
                            <rect x="340" y="310" width="110" height="8" rx="4" fill="#cbd5e1"/>
                            <rect x="340" y="330" width="80" height="8" rx="4" fill="#cbd5e1"/>
                        </svg>
                    </div>
                    <!-- Floating badge: status -->
                    <div class="absolute top-8 -right-2 lg:right-0 bg-white rounded-2xl shadow-lg border border-primary-100 px-4 py-3 flex items-center gap-3 z-20">
                        <span class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center">
                            <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                        </span>
                        <div>
                            <p class="text-sm font-semibold text-gray-900">Sistem Normal</p>
                            <p class="text-xs text-gray-500">Komputer siap dipinjam</p>
                        </div>
                    </div>
                    <!-- Floating badge: tracker -->
                    <div class="absolute -bottom-6 left-4 bg-white rounded-2xl shadow-lg border border-primary-100 px-4 py-3 flex items-center gap-3 z-20">
                        <span class="w-10 h-10 bg-primary-100 rounded-full flex items-center justify-center">
                            <svg class="w-5 h-5 text-primary-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                            </svg>
                        </span>
                        <div>
                            <p class="text-sm font-semibold text-gray-900">Lacak Kendala</p>
                            <p class="text-xs text-gray-500">Kode tracker unik</p>
                        </div>
                    </div>
                    <div class="absolute -top-4 -right-4 w-72 h-72 bg-primary-200 rounded-full mix-blend-multiply filter blur-xl opacity-70 animate-blob"></div>
                    <div class="absolute -bottom-8 -left-4 w-72 h-72 bg-emerald-200 rounded-full mix-blend-multiply filter blur-xl opacity-70 animate-blob animation-delay-2000"></div>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section id="fitur" class="py-20 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <span class="inline-block bg-primary-50 text-primary-700 text-sm font-semibold px-4 py-1.5 rounded-full mb-4">Fitur Unggulan</span>
                <h2 class="text-3xl lg:text-4xl font-bold mb-4">Semua Kebutuhan Laboratorium dalam Satu Platform</h2>
                <p class="text-lg text-gray-600 max-w-2xl mx-auto">Kelola seluruh aset laboratorium dengan fitur-fitur lengkap yang dirancang untuk memudahkan administrasi dan pemantauan.</p>
            </div>
            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
                <div class="card-hover bg-white border border-gray-100 rounded-2xl p-8">
                    <div class="w-12 h-12 bg-green-50 rounded-xl flex items-center justify-center mb-6">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z"/>
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold mb-3">Peminjaman Komputer</h3>
                    <p class="text-gray-600 leading-relaxed mb-4">Pinjam komputer untuk tugas kuliah dengan sistem booking online dan verifikasi via kode tracker.</p>
                    <div class="flex flex-wrap gap-2">
                        <span class="inline-flex items-center gap-1.5 text-xs font-medium text-green-700 bg-green-50 px-2.5 py-1 rounded-full"><span class="w-1.5 h-1.5 bg-green-500 rounded-full"></span>Normal</span>
                        <span class="inline-flex items-center gap-1.5 text-xs font-medium text-yellow-700 bg-yellow-50 px-2.5 py-1 rounded-full"><span class="w-1.5 h-1.5 bg-yellow-500 rounded-full"></span>Perbaikan</span>
                        <span class="inline-flex items-center gap-1.5 text-xs font-medium text-red-700 bg-red-50 px-2.5 py-1 rounded-full"><span class="w-1.5 h-1.5 bg-red-500 rounded-full"></span>Rusak</span>
                    </div>
                </div>
                <div class="card-hover bg-white border border-gray-100 rounded-2xl p-8">
                    <div class="w-12 h-12 bg-amber-50 rounded-xl flex items-center justify-center mb-6">
                        <svg class="w-6 h-6 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold mb-3">Lapor Kendala</h3>
                    <p class="text-gray-600 leading-relaxed">Laporkan kendala komputer di laboratorium dengan mudah, lengkapi dengan foto dan kategori kerusakan.</p>
                </div>
                <div class="card-hover bg-white border border-gray-100 rounded-2xl p-8">
                    <div class="w-12 h-12 bg-blue-50 rounded-xl flex items-center justify-center mb-6">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold mb-3">Lacak Status</h3>
                    <p class="text-gray-600 leading-relaxed">Pantau status laporan kendala dan peminjaman komputer dengan kode tracker yang unik, tanpa perlu login.</p>
                </div>
                <div class="card-hover bg-white border border-gray-100 rounded-2xl p-8">
                    <div class="w-12 h-12 bg-purple-50 rounded-xl flex items-center justify-center mb-6">
                        <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold mb-3">Jadwal Kuliah</h3>
                    <p class="text-gray-600 leading-relaxed">Lihat jadwal kuliah dan praktikum laboratorium dengan kalender interaktif yang terintegrasi.</p>
                </div>
                <div class="card-hover bg-white border border-gray-100 rounded-2xl p-8">
                    <div class="w-12 h-12 bg-teal-50 rounded-xl flex items-center justify-center mb-6">
                        <svg class="w-6 h-6 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M12 20h4.01M5.6 5.6l.01.01M20 12h.01M5.6 19.4l.01.01M12 12H5.6M12 12h.01M12 20H5.6M12 20h.01M19.4 5.6l.01.01"/>
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold mb-3">QR Code & Kartu Kendali</h3>
                    <p class="text-gray-600 leading-relaxed">Setiap komputer dilengkapi QR code dan kartu kendali untuk pelacakan riwayat pemeliharaan dengan cepat.</p>
                </div>
                <div class="card-hover bg-white border border-gray-100 rounded-2xl p-8">
                    <div class="w-12 h-12 bg-indigo-50 rounded-xl flex items-center justify-center mb-6">
                        <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold mb-3">Dashboard Analytics</h3>
                    <p class="text-gray-600 leading-relaxed">Pantau statistik penggunaan laboratorium, jumlah komputer per lab, dan notifikasi real-time untuk admin.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- How It Works Section -->
    <section id="cara-kerja" class="py-20 bg-gray-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <span class="inline-block bg-primary-50 text-primary-700 text-sm font-semibold px-4 py-1.5 rounded-full mb-4">Cara Kerja</span>
                <h2 class="text-3xl lg:text-4xl font-bold mb-4">Mudah Digunakan dalam 3 Langkah</h2>
                <p class="text-lg text-gray-600 max-w-2xl mx-auto">Tidak perlu akun, langsung gunakan layanan yang tersedia untuk mahasiswa dan dosen.</p>
            </div>
            <div class="grid md:grid-cols-3 gap-8">
                <div class="relative bg-white rounded-2xl p-8 border border-gray-100 shadow-sm">
                    <div class="w-14 h-14 bg-primary-600 rounded-2xl flex items-center justify-center mb-6 text-white text-2xl font-bold shadow-lg shadow-primary-600/25">1</div>
                    <h3 class="text-xl font-bold mb-3">Pilih Komputer</h3>
                    <p class="text-gray-600 leading-relaxed">Cari komputer yang tersedia berdasarkan laboratorium dan statusnya. Filter berdasarkan lab atau kata kunci.</p>
                </div>
                <div class="relative bg-white rounded-2xl p-8 border border-gray-100 shadow-sm">
                    <div class="w-14 h-14 bg-primary-600 rounded-2xl flex items-center justify-center mb-6 text-white text-2xl font-bold shadow-lg shadow-primary-600/25">2</div>
                    <h3 class="text-xl font-bold mb-3">Isi Data & Ajukan</h3>
                    <p class="text-gray-600 leading-relaxed">Lengkapi formulir peminjaman atau laporan kendala. Cukup isi nama, NPM/NIM, dan detail yang dibutuhkan.</p>
                </div>
                <div class="relative bg-white rounded-2xl p-8 border border-gray-100 shadow-sm">
                    <div class="w-14 h-14 bg-primary-600 rounded-2xl flex items-center justify-center mb-6 text-white text-2xl font-bold shadow-lg shadow-primary-600/25">3</div>
                    <h3 class="text-xl font-bold mb-3">Dapatkan Kode Tracker</h3>
                    <p class="text-gray-600 leading-relaxed">Terima kode tracker unik untuk memantau status peminjaman atau perbaikan secara real-time kapan saja.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Status Komputer Section -->
    <section id="status" class="py-20 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <span class="inline-block bg-primary-50 text-primary-700 text-sm font-semibold px-4 py-1.5 rounded-full mb-4">Status Komputer</span>
                <h2 class="text-3xl lg:text-4xl font-bold mb-4">Ketahui Kondisi Sebelum Meminjam</h2>
                <p class="text-lg text-gray-600 max-w-2xl mx-auto">Setiap komputer dilengkapi kartu status yang jelas untuk memudahkan peminjaman.</p>
            </div>
            <div class="grid md:grid-cols-3 gap-8">
                <div class="card-hover bg-white rounded-2xl p-8 border border-green-200 shadow-sm">
                    <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-6">
                        <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-center mb-2 text-green-700">Normal</h3>
                    <p class="text-gray-600 text-center">Komputer siap pakai untuk peminjaman. Status aktif dan dalam kondisi baik.</p>
                </div>
                <div class="card-hover bg-white rounded-2xl p-8 border border-yellow-200 shadow-sm">
                    <div class="w-16 h-16 bg-yellow-100 rounded-full flex items-center justify-center mx-auto mb-6">
                        <svg class="w-8 h-8 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-center mb-2 text-yellow-700">Perbaikan</h3>
                    <p class="text-gray-600 text-center">Komputer sedang dalam perbaikan. Tidak tersedia untuk dipinjam sementara waktu.</p>
                </div>
                <div class="card-hover bg-white rounded-2xl p-8 border border-red-200 shadow-sm">
                    <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-6">
                        <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-center mb-2 text-red-700">Rusak</h3>
                    <p class="text-gray-600 text-center">Komputer mengalami kerusakan. Menunggu pemeliharaan atau penggantian.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="py-20 relative overflow-hidden bg-primary-700">
        <div class="absolute inset-0 opacity-20">
            <div class="absolute -top-24 -left-24 w-96 h-96 bg-primary-400 rounded-full mix-blend-multiply filter blur-3xl"></div>
            <div class="absolute -bottom-24 -right-24 w-96 h-96 bg-emerald-400 rounded-full mix-blend-multiply filter blur-3xl"></div>
        </div>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center relative">
            <h2 class="text-3xl lg:text-4xl font-bold text-white mb-4">Siap Memulai?</h2>
            <p class="text-lg text-primary-100 mb-8 max-w-2xl mx-auto">Kelola laboratorium Anda dengan lebih efisien menggunakan SimLabKom. Gratis untuk seluruh sivitas akademika.</p>
            <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
                <a href="{{ route('peminjaman-komputer.index') }}" class="inline-flex items-center gap-2 bg-white text-primary-700 px-8 py-3.5 rounded-xl hover:bg-gray-100 transition-all font-semibold shadow-lg">
                    Pinjam Komputer
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                    </svg>
                </a>
                <a href="{{ route('laporan-kendala-komputer.create') }}" class="inline-flex items-center gap-2 bg-primary-800 text-white px-8 py-3.5 rounded-xl hover:bg-primary-900 transition-all font-semibold">
                    Lapor Kendala
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </a>
            </div>
        </div>
    </section>
@endsection
