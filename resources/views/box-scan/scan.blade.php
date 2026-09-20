<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Scan Box - {{ $box->code }}</title>
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
                            <path stroke-linecap="round" stroke-linejoin="round" d="m21 7.5-9-5.25L3 7.5m18 0-9 5.25m9-5.25v9l-9 5.25M3 7.5l9 5.25M3 7.5v9l9 5.25m0-9v9" />
                        </svg>
                    </div>
                    <div>
                        <h1 class="text-xl font-bold">{{ $box->code }}</h1>
                        <p class="text-green-100 text-sm">{{ $box->name }}</p>
                    </div>
                </div>
            </div>

            <div class="p-6 space-y-5">
                <div class="flex items-center gap-2 text-sm text-gray-600">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z" />
                    </svg>
                    <span>{{ $box->location ?? 'Lokasi tidak ditentukan' }}</span>
                </div>

                @if($box->boxComponents->isNotEmpty())
                    <div>
                        <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Komponen dalam Box</h3>
                        <div class="bg-gray-50 rounded-xl p-3 space-y-1">
                            @foreach($box->boxComponents as $bc)
                                <div class="flex items-center justify-between text-sm">
                                    <span class="text-gray-700">{{ $bc->component->name }}</span>
                                    <span class="font-medium text-gray-900">x{{ $bc->quantity }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                <div class="border-t border-gray-100 pt-5">
                    @if($activeUsage)
                        <div class="bg-yellow-50 border border-yellow-200 rounded-xl p-4">
                            <div class="flex items-center gap-2 mb-2">
                                <svg class="w-5 h-5 text-yellow-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
                                </svg>
                                <span class="text-sm font-semibold text-yellow-800">Box Sedang Digunakan</span>
                            </div>
                            <p class="text-sm text-yellow-700">
                                Oleh: <strong>{{ $activeUsage->user_name }}</strong> ({{ $activeUsage->user_nim }})
                            </p>
                            @if($activeUsage->user_kelas)
                                <p class="text-xs text-yellow-600 mt-1">Kelas: {{ $activeUsage->user_kelas }}</p>
                            @endif
                            <p class="text-xs text-yellow-600 mt-1">
                                Sejak {{ $activeUsage->used_at->format('d M Y H:i') }}
                            </p>
                            <p class="text-xs text-yellow-600 mt-2 italic">Hubungi admin untuk pengembalian</p>
                        </div>
                    @else
                        <div class="bg-green-50 border border-green-200 rounded-xl p-4 mb-4">
                            <div class="flex items-center gap-2">
                                <svg class="w-5 h-5 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <span class="text-sm font-semibold text-green-800">Tersedia</span>
                            </div>
                        </div>

                        @if($lastUsageByNim && !$activeUsage)
                            <div class="bg-blue-50 border border-blue-200 rounded-xl p-4">
                                <p class="text-sm text-blue-700 mb-3">
                                    Halo <strong>{{ $lastUsageByNim->user_name }}</strong> ({{ $lastUsageByNim->user_nim }})
                                    <br><span class="text-xs text-blue-600">Anda telah menggunakan box ini sebelumnya.</span>
                                </p>
                                <div id="quickUseError" class="hidden bg-red-50 border border-red-200 text-red-700 text-sm px-4 py-3 rounded-xl mb-3"></div>
                                <button onclick="quickUse()" id="quickUseBtn"
                                    class="w-full px-4 py-2.5 bg-blue-600 text-white text-sm font-semibold rounded-lg hover:bg-blue-700 transition-colors">
                                    Gunakan
                                </button>
                            </div>
                        @else
                            <div id="useFormSection">
                                <h3 class="text-sm font-semibold text-gray-700 mb-3">Isi Data Diri</h3>
                                <form id="useForm" class="space-y-3">
                                    <div>
                                        <label class="block text-xs font-medium text-gray-500 mb-1">Nama Lengkap</label>
                                        <input type="text" name="user_name" required placeholder="Contoh: Budi Santoso"
                                            class="w-full px-4 py-2.5 text-sm border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-medium text-gray-500 mb-1">NIM</label>
                                        <input type="text" name="user_nim" required placeholder="Contoh: 12345678"
                                            class="w-full px-4 py-2.5 text-sm border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-medium text-gray-500 mb-1">Kelas</label>
                                        <input type="text" name="user_kelas" required placeholder="Contoh: TI-2A"
                                            class="w-full px-4 py-2.5 text-sm border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                                    </div>
                                    <div id="useError" class="hidden bg-red-50 border border-red-200 text-red-700 text-sm px-4 py-3 rounded-xl"></div>
                                    <button type="submit" id="useBtn"
                                        class="w-full px-4 py-2.5 bg-green-600 text-white text-sm font-semibold rounded-lg hover:bg-green-700 transition-colors">
                                        Gunakan
                                    </button>
                                </form>
                            </div>
                        @endif
                    @endif
                </div>
            </div>
        </div>

        <p class="text-center text-xs text-gray-400 mt-4">SimKom - Sistem Informasi Laboratorium</p>
    </div>

    <script>
        const boxCode = '{{ $box->code }}';

        function getCsrfToken() {
            return document.querySelector('meta[name="csrf-token"]').content;
        }

        document.getElementById('useForm')?.addEventListener('submit', async function(e) {
            e.preventDefault();
            const errorEl = document.getElementById('useError');
            const btn = document.getElementById('useBtn');
            errorEl.classList.add('hidden');
            btn.disabled = true;
            btn.textContent = 'Memproses...';

            const formData = new FormData(this);

            try {
                const response = await fetch(`/box-scan/${boxCode}/use`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': getCsrfToken(),
                        'Accept': 'application/json',
                    },
                    body: formData,
                });

                const data = await response.json();

                if (data.success) {
                    location.reload();
                } else {
                    errorEl.textContent = data.message || 'Terjadi kesalahan';
                    errorEl.classList.remove('hidden');
                    btn.disabled = false;
                    btn.textContent = 'Gunakan';
                }
            } catch (err) {
                errorEl.textContent = 'Terjadi kesalahan jaringan';
                errorEl.classList.remove('hidden');
                btn.disabled = false;
                btn.textContent = 'Gunakan';
            }
        });

        async function quickUse() {
            const btn = document.getElementById('quickUseBtn');
            const errorEl = document.getElementById('quickUseError');
            btn.disabled = true;
            btn.textContent = 'Memproses...';
            errorEl.classList.add('hidden');

            try {
                const response = await fetch(`/box-scan/${boxCode}/quick-use`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': getCsrfToken(),
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ nim: '{{ $nim ?? "" }}' }),
                });

                const data = await response.json();

                if (data.success) {
                    location.reload();
                } else {
                    errorEl.textContent = data.message || 'Terjadi kesalahan';
                    errorEl.classList.remove('hidden');
                    btn.disabled = false;
                    btn.textContent = 'Gunakan';
                }
            } catch (err) {
                errorEl.textContent = 'Terjadi kesalahan jaringan';
                errorEl.classList.remove('hidden');
                btn.disabled = false;
                btn.textContent = 'Gunakan';
            }
        }
    </script>
</body>
</html>
