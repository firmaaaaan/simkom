<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Check-in Lab - {{ $lab->name }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body { font-family: 'Inter', system-ui, -apple-system, sans-serif; }
    </style>
</head>
<body class="bg-gray-50 min-h-screen flex items-center justify-center p-4">
    <div class="w-full max-w-md">
        <div class="bg-white rounded-2xl shadow-lg border border-gray-200 overflow-hidden">
            <div class="bg-gradient-to-r from-green-600 to-green-700 px-6 py-4 text-white">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 bg-white/20 rounded-xl flex items-center justify-center">
                        <svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21" />
                        </svg>
                    </div>
                    <div>
                        <h1 class="text-xl font-bold">{{ $lab->name }}</h1>
                        <p class="text-green-100 text-sm">{{ $lab->code }} &middot; {{ $lab->location ?? 'Lokasi tidak ditentukan' }}</p>
                    </div>
                </div>
            </div>

            <div class="p-6 space-y-5">
                <div class="bg-green-50 border border-green-200 rounded-xl p-4">
                    <p class="text-sm font-semibold text-green-800 mb-1">Check-in Penggunaan Laboratorium</p>
                    <p class="text-xs text-green-600">Isi data diri Anda untuk mencatat kehadiran di laboratorium.</p>
                </div>

                @if(session('success'))
                    <div class="bg-blue-50 border border-blue-200 rounded-xl p-4">
                        <div class="flex items-center gap-2 mb-1">
                            <svg class="w-5 h-5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span class="text-sm font-semibold text-blue-800">Berhasil</span>
                        </div>
                        <p class="text-sm text-blue-700">{{ session('success') }}</p>
                    </div>
                @endif

                <div class="bg-gray-50 rounded-xl p-4 space-y-2">
                    <div class="flex items-center justify-between text-sm">
                        <span class="text-gray-500">Hari</span>
                        <span class="font-medium text-gray-900">{{ $day }}</span>
                    </div>
                    <div class="flex items-center justify-between text-sm">
                        <span class="text-gray-500">Tanggal</span>
                        <span class="font-medium text-gray-900">{{ $date }}</span>
                    </div>
                    <div class="flex items-center justify-between text-sm">
                        <span class="text-gray-500">Waktu</span>
                        <span class="font-medium text-gray-900">{{ $time }} WIB</span>
                    </div>
                    <p class="text-[11px] text-gray-400 italic pt-1">Hari & waktu terisi otomatis dari sistem.</p>
                </div>

                <div class="border-t border-gray-100 pt-5">
                    <h3 class="text-sm font-semibold text-gray-700 mb-3">Isi Data Diri</h3>

                    @if($errors->any())
                        <div class="bg-red-50 border border-red-200 text-red-700 text-sm px-4 py-3 rounded-xl mb-3">
                            @foreach($errors->all() as $error)
                                <p>{{ $error }}</p>
                            @endforeach
                        </div>
                    @endif

                    <form method="POST" action="{{ route('lab-scan.check-in', $lab->code) }}" class="space-y-3">
                        @csrf
                        <p class="text-xs text-gray-500 -mb-1">Semua kolom wajib diisi.</p>
                        <div>
                            <label class="block text-xs font-medium text-gray-500 mb-1">Nama Lengkap <span class="text-red-500">*</span></label>
                            <input type="text" name="user_name" value="{{ old('user_name') }}" required placeholder="Contoh: Budi Santoso"
                                class="w-full px-4 py-2.5 text-sm border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-500 mb-1">Prodi <span class="text-red-500">*</span></label>
                            <input type="text" name="user_prodi" value="{{ old('user_prodi') }}" required placeholder="Contoh: Teknik Informatika"
                                class="w-full px-4 py-2.5 text-sm border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-500 mb-1">Keperluan <span class="text-red-500">*</span></label>
                            <input type="text" name="purpose" value="{{ old('purpose') }}" required placeholder="Contoh: Praktikum Jaringan"
                                class="w-full px-4 py-2.5 text-sm border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                        </div>
                        <button type="submit"
                            class="w-full px-4 py-2.5 bg-green-600 text-white text-sm font-semibold rounded-lg hover:bg-green-700 transition-colors">
                            Check-in
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <p class="text-center text-xs text-gray-400 mt-4">SimKom - Sistem Informasi Laboratorium</p>
    </div>
</body>
</html>
