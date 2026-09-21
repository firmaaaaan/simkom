<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <title>Peminjaman {{ $borrowing->tracking_code }} - SimKom - UPT Lab Terpadu</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700" rel="stylesheet" />
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'ui-sans-serif', 'system-ui', 'sans-serif'],
                    },
                }
            }
        }
    </script>
</head>
<body class="bg-gray-50 font-sans antialiased">

    <nav class="bg-white border-b border-gray-200 sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <a href="{{ url('/') }}" class="flex items-center gap-3">
                    <div class="w-9 h-9 bg-gradient-to-br from-green-500 to-emerald-600 rounded-xl flex items-center justify-center shadow-lg shadow-green-200">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 3.104v5.714a2.25 2.25 0 01-.659 1.591L5 14.5M9.75 3.104c-.251.023-.501.05-.75.082m.75-.082a24.301 24.301 0 014.5 0m0 0v5.714a2.25 2.25 0 00.659 1.591L19 14.5m-4.25-11.396c.251.023.501.05.75.082M12 21a8.966 8.966 0 005.982-2.275M12 21a8.966 8.966 0 01-5.982-2.275M15.75 3.186a24.284 24.284 0 012.038.443M8.25 3.186a24.284 24.284 0 00-2.038.443M18 14.5l-6 6-6-6" />
                        </svg>
                    </div>
                    <span class="text-xl font-extrabold text-gray-800">Sim<span class="text-green-600">Lab</span></span>
                </a>
                <div class="flex items-center gap-3">
                    <a href="{{ route('track.index') }}" class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-gray-600 hover:text-green-600 transition-colors">
                        Lacak Lain
                    </a>
                    <a href="{{ url('/') }}" class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-gray-600 hover:text-green-600 transition-colors">
                        Beranda
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

        <a href="{{ route('track.index') }}" class="inline-flex items-center gap-2 text-sm text-gray-600 hover:text-green-600 transition-colors mb-6">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"/>
            </svg>
            Lacak Lain
        </a>

        {{-- Tracking Code Banner --}}
        <div class="bg-gradient-to-br from-blue-600 to-indigo-700 rounded-2xl p-6 text-white mb-6 shadow-lg shadow-blue-200">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-blue-100 mb-1">Kode Tracking Peminjaman</p>
                    <p class="text-3xl font-black tracking-wider">{{ $borrowing->tracking_code }}</p>
                    <p class="text-xs text-blue-200 mt-2">Simpan kode ini untuk melacak status peminjaman Anda</p>
                </div>
                <div class="flex flex-col items-center gap-2">
                    <div class="w-16 h-16 bg-white/20 rounded-2xl flex items-center justify-center">
                        <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 6v.75m0 3v.75m0 3v.75m0 3V18m-9-5.25h5.25M7.5 15h3M3.375 5.25c-.621 0-1.125.504-1.125 1.125v3.026a2.999 2.999 0 010 5.198v3.026c0 .621.504 1.125 1.125 1.125h17.25c.621 0 1.125-.504 1.125-1.125v-3.026a2.999 2.999 0 010-5.198V6.375c0-.621-.504-1.125-1.125-1.125H3.375z" />
                        </svg>
                    </div>
                    <button type="button" onclick="copyCode(this, '{{ $borrowing->tracking_code }}')"
                        class="px-3 py-1.5 bg-white/20 hover:bg-white/30 rounded-lg text-xs font-medium transition-colors flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.666 3.888A2.25 2.25 0 0013.5 2.25h-3c-1.03 0-1.9.693-2.166 1.638m7.332 0c.055.194.084.4.084.612v0a.75.75 0 01-.75.75H9.75a.75.75 0 01-.75-.75v0c0-.212.03-.418.084-.612m7.332 0c.646.049 1.288.11 1.927.184 1.1.128 1.907 1.077 1.907 2.185V19.5a2.25 2.25 0 01-2.25 2.25H6.75A2.25 2.25 0 014.5 19.5V6.257c0-1.108.806-2.057 1.907-2.185a48.208 48.208 0 011.927-.184" />
                        </svg>
                        <span>Salin Kode</span>
                    </button>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6 mb-6">
            <div class="flex items-start justify-between mb-4">
                <div>
                    <h3 class="text-xl font-bold text-gray-900">{{ $borrowing->purpose }}</h3>
                    <p class="text-sm text-gray-500 mt-1">Diajukan {{ $borrowing->created_at->format('d M Y H:i') }}</p>
                </div>
                @php
                    $statusColors = [
                        'Pending' => 'bg-yellow-100 text-yellow-700 border-yellow-200',
                        'Approved' => 'bg-green-100 text-green-700 border-green-200',
                        'Rejected' => 'bg-red-100 text-red-700 border-red-200',
                        'Returned' => 'bg-gray-100 text-gray-500 border-gray-200',
                    ];
                @endphp
                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium border {{ $statusColors[$borrowing->status] ?? '' }}">
                    {{ $borrowing->status }}
                </span>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-4 border-t border-gray-100">
                <div>
                    <p class="text-xs text-gray-500 mb-1">Komputer</p>
                    <p class="text-sm font-bold text-gray-900">{{ $borrowing->computer->code ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-500 mb-1">Laboratorium</p>
                    <p class="text-sm font-medium text-gray-900">{{ $borrowing->laboratory->name ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-500 mb-1">Tanggal</p>
                    <p class="text-sm font-medium text-gray-900">{{ \Carbon\Carbon::parse($borrowing->borrow_date)->format('d M Y') }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-500 mb-1">Jam</p>
                    <p class="text-sm font-medium text-gray-900">{{ $borrowing->borrow_time_start }} - {{ $borrowing->borrow_time_end }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6">
            <h4 class="text-sm font-bold text-gray-900 mb-4">Informasi Peminjam</h4>
            <div class="space-y-3 text-sm">
                <div class="flex justify-between">
                    <span class="text-gray-500">Nama</span>
                    <span class="font-medium text-gray-900">{{ $borrowing->borrower_name }}</span>
                </div>
                @if($borrowing->borrower_nim)
                    <div class="flex justify-between">
                        <span class="text-gray-500">NIM/NIDN</span>
                        <span class="font-medium text-gray-900">{{ $borrowing->borrower_nim }}</span>
                    </div>
                @endif
                @if($borrowing->borrower_prodi)
                    <div class="flex justify-between">
                        <span class="text-gray-500">Program Studi</span>
                        <span class="font-medium text-gray-900">{{ $borrowing->borrower_prodi }}</span>
                    </div>
                @endif
                @if($borrowing->admin_notes)
                    <hr class="border-gray-100">
                    <div>
                        <span class="text-gray-500">Catatan Admin</span>
                        <p class="mt-1 text-gray-700">{{ $borrowing->admin_notes }}</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <script>
        function copyCode(btn, code) {
            navigator.clipboard.writeText(code).then(() => {
                const span = btn.querySelector('span');
                span.textContent = 'Tersalin!';
                btn.classList.add('bg-white/40');
                setTimeout(() => {
                    span.textContent = 'Salin Kode';
                    btn.classList.remove('bg-white/40');
                }, 2000);
            });
        }
    </script>
</body>
</html>
