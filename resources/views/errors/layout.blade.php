<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Error') - SimLabKom</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'system-ui', 'sans-serif'],
                    },
                    colors: {
                        primary: {
                            50: '#ecfdf5',
                            100: '#d1fae5',
                            200: '#a7f3d0',
                            300: '#6ee7b7',
                            400: '#34d399',
                            500: '#10b981',
                            600: '#059669',
                            700: '#047857',
                            800: '#065f46',
                            900: '#064e3b',
                        }
                    }
                }
            }
        }
    </script>
</head>
<body class="font-sans antialiased bg-gray-50 min-h-screen flex flex-col">
    <nav class="bg-white border-b border-gray-100">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16 items-center">
                <a href="{{ url('/') }}" class="flex items-center gap-2">
                    <div class="w-8 h-8 bg-primary-600 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                        </svg>
                    </div>
                    <span class="text-lg font-bold text-gray-900">SimLabKom</span>
                </a>
                <a href="{{ url('/') }}" class="text-sm font-medium text-primary-600 hover:text-primary-700 transition-colors">
                    Kembali ke Beranda
                </a>
            </div>
        </div>
    </nav>

    <main class="flex-1 flex items-center justify-center px-4">
        @yield('content')
    </main>

    <footer class="bg-white border-t border-gray-100 py-4">
        <div class="max-w-5xl mx-auto px-4 text-center text-sm text-gray-500">
            &copy; {{ date('Y') }} SimLabKom - Sistem Inventaris Laboratorium Komputer
        </div>
    </footer>
</body>
</html>
