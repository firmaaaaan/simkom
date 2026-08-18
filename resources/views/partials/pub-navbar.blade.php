<nav class="fixed top-0 left-0 right-0 bg-white/80 backdrop-blur-md border-b border-gray-100 z-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center h-16">
            <div class="flex items-center">
                <div class="flex-shrink-0 flex items-center gap-2">
                    <div class="w-8 h-8 bg-primary-600 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z"/>
                        </svg>
                    </div>
                    <span class="font-bold text-xl text-gray-900">Simlabkom</span>
                </div>
            </div>
            <div class="hidden md:flex items-center gap-6">
                <a href="{{ url('/') }}" class="text-sm {{ request()->routeIs('home') ? 'text-primary-600' : 'text-gray-600' }} hover:text-gray-900 transition-colors">Beranda</a>
                <a href="{{ route('peminjaman-komputer.index') }}" class="text-sm {{ request()->routeIs('peminjaman-komputer.*') ? 'text-primary-600' : 'text-gray-600' }} hover:text-primary-700 font-medium transition-colors">Peminjaman Komputer</a>
                <a href="{{ route('laporan-kendala-komputer.create') }}" class="text-sm {{ request()->routeIs('laporan-kendala-komputer.create') ? 'text-primary-600' : 'text-gray-600' }} hover:text-gray-900 font-medium transition-colors">Lapor Kendala</a>
                <a href="{{ route('laporan-kendala-komputer.track') }}" class="text-sm {{ request()->routeIs('laporan-kendala-komputer.track') ? 'text-primary-600' : 'text-gray-600' }} hover:text-gray-900 font-medium transition-colors">Lacak</a>
                <a href="{{ route('jadwal-kuliah.index') }}" class="text-sm {{ request()->routeIs('jadwal-kuliah.*') ? 'text-primary-600' : 'text-gray-600' }} hover:text-gray-900 font-medium transition-colors">Jadwal Kuliah</a>
                @auth
                    <a href="{{ route('admin.dashboard') }}" class="bg-primary-600 text-white px-5 py-2 rounded-lg hover:bg-primary-700 transition-colors font-medium">Dashboard</a>
                @else
                    <a href="{{ route('login') }}" class="bg-primary-600 text-white px-5 py-2 rounded-lg hover:bg-primary-700 transition-colors font-medium">Masuk</a>
                @endauth
            </div>
            <div class="md:hidden">
                <button id="mobile-menu-btn" class="text-gray-600 hover:text-gray-900 focus:outline-none">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                </button>
            </div>
        </div>
    </div>
    <div id="mobile-menu" class="hidden md:hidden bg-white border-t border-gray-100">
        <div class="px-4 pt-2 pb-4 space-y-1">
            <a href="{{ url('/') }}" class="block px-3 py-2 rounded-md text-base font-medium {{ request()->routeIs('home') ? 'text-primary-600 bg-primary-50' : 'text-gray-700 hover:text-gray-900 hover:bg-gray-50' }}">Beranda</a>
            <a href="{{ route('peminjaman-komputer.index') }}" class="block px-3 py-2 rounded-md text-base font-medium {{ request()->routeIs('peminjaman-komputer.*') ? 'text-primary-600 bg-primary-50' : 'text-gray-700 hover:text-gray-900 hover:bg-gray-50' }}">Peminjaman Komputer</a>
            <a href="{{ route('laporan-kendala-komputer.create') }}" class="block px-3 py-2 rounded-md text-base font-medium {{ request()->routeIs('laporan-kendala-komputer.create') ? 'text-primary-600 bg-primary-50' : 'text-gray-700 hover:text-gray-900 hover:bg-gray-50' }}">Lapor Kendala</a>
            <a href="{{ route('laporan-kendala-komputer.track') }}" class="block px-3 py-2 rounded-md text-base font-medium {{ request()->routeIs('laporan-kendala-komputer.track') ? 'text-primary-600 bg-primary-50' : 'text-gray-700 hover:text-gray-900 hover:bg-gray-50' }}">Lacak</a>
            <a href="{{ route('jadwal-kuliah.index') }}" class="block px-3 py-2 rounded-md text-base font-medium {{ request()->routeIs('jadwal-kuliah.*') ? 'text-primary-600 bg-primary-50' : 'text-gray-700 hover:text-gray-900 hover:bg-gray-50' }}">Jadwal Kuliah</a>
            @auth
                <a href="{{ route('admin.dashboard') }}" class="block px-3 py-2 rounded-md text-base font-medium text-primary-600 hover:text-primary-700 hover:bg-gray-50">Dashboard</a>
            @else
                <a href="{{ route('login') }}" class="block px-3 py-2 rounded-md text-base font-medium text-primary-600 hover:text-primary-700 hover:bg-gray-50">Masuk</a>
            @endauth
        </div>
    </div>
</nav>
