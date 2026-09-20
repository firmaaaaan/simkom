<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Lacak Tiket - {{ config('app.name', 'SimKom') }}</title>
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
<body class="bg-gray-50 font-sans antialiased min-h-screen flex flex-col">

    {{-- Navbar --}}
    <nav class="bg-white border-b border-gray-200 sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 bg-gradient-to-br from-green-500 to-emerald-600 rounded-xl flex items-center justify-center shadow-center-green-green shadow shadow2>
:">
- shadow0 box shadow2--- shadow shadow
 shadow                     </2>

                       </                   
>
="</est <               -items                   0</">
 route '>>
<strong->="(result="...kg--to                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 3.104v5.714a2.25 2.25 0 01-.659 1.591L5 14.5M9.75 3.104c-.251.023-.501.05-.75.082m.75-.082a24.301 24.301 0 014.5 0m0 0v5.714a2.25 2.25 0 00.659 1.591L19 14.5m-4.25-11.396c.251.023.501.05.75.082M12 21a8.966 8.966 0 005.982-2.275M12 21a8.966 8.966 0 01-5.982-2.275M15.75 3.186a24.284 24.284 0 012.038.443M8.25 3.186a24.284 24.284 0 00-2.038.443M18 14.5l-6 6-6-6" />
                    </svg>
                </div>
                <span class="text-xl font-extrabold text-gray-800">Sim<span class="text-green-600">Lab</span></span>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('lapor-kendala.index') }}" class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-gray-600 hover:text-green-600 transition-colors">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                    Lapor Kendala
                </a>
                <a href="{{ url('/') }}" class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-gray-600 hover:text-green-600 transition-colors">
                    Beranda
                </a>
            </div>
        </div>
    </nav>

    {{-- Content --}}
    <div class="flex-1 flex items-center justify-center px-4 sm:px-6 lg:px-8 py-12">
        <div class="w-full max-w-md">

            {{-- Success Message --}}
            @if(session('success'))
                <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-xl text-sm mb-6 flex items-center gap-2">
                    <svg class="w-5 h-5 text-green-600 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    {{ session('success') }}
                </div>
            @endif

            <div class="text-center mb-8">
                <div class="w-16 h-16 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-2xl flex items-center justify-center mx-auto mb-4 shadow-lg shadow-blue-200">
                    <svg class="w-8 h-8 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                    </svg>
                </div>
                <h1 class="text-2xl font-black text-gray-900 mb-2">Lacak Status Tiket</h1>
                <p class="text-gray-500">Masukkan kode tracking yang Anda dapatkan saat membuat laporan</p>
            </div>

            <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6">
                <form action="" method="GET" id="trackForm" class="space-y-4">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Kode Tracking</label>
                        <input type="text" name="tracking_code" id="trackingCode" required
                            placeholder="TKT-XXXXXXXX"
                            class="w-full px-4 py-3 text-center text-lg font-mono font-bold tracking-wider border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 uppercase"
                            maxlength="13">
                        <p class="text-xs text-gray-400 mt-2 text-center">Format: TKT- diikuti 8 karakter (contoh: TKT-ABC12345)</p>
                    </div>
                    <button type="submit" id="trackBtn"
                        class="w-full px-6 py-3 bg-gradient-to-r from-blue-600 to-indigo-600 text-white font-semibold rounded-xl hover:from-blue-700 hover:to-indigo-700 transition-all shadow-lg shadow-blue-200 hover:shadow-blue-300">
                        Lacak Sekarang
                    </button>
                </form>
            </div>

            <div class="text-center mt-6">
                <a href="{{ route('lapor-kendala.index') }}" class="inline-flex items-center gap-2 text-sm text-gray-500 hover:text-green-600 transition-colors">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"/>
                    </svg>
                    Buat Laporan Baru
                </a>
            </div>
        </div>
    </div>

    <script>
        document.getElementById('trackForm').addEventListener('submit', function(e) {
            e.preventDefault();
            var code = document.getElementById('trackingCode').value.trim().toUpperCase();
            if (code) {
                window.location.href = '/lapor-kendala/track/' + code;
            }
        });
    </script>

</body>
</html>
