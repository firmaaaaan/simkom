<footer class="bg-gray-900 text-gray-300 py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid md:grid-cols-4 gap-8">
            <div class="col-span-2">
                <div class="flex items-center gap-2 mb-4">
                    <div class="w-8 h-8 bg-primary-600 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z"/>
                        </svg>
                    </div>
                    <span class="font-bold text-xl text-white">SimLabKom</span>
                </div>
                <p class="text-gray-400 max-w-md">Sistem laboratorium komputer yang terpadu untuk pengelolaan aset yang lebih efektif dan efisien.</p>
            </div>
            <div>
                <h4 class="font-semibold text-white mb-4">Menu</h4>
                <ul class="space-y-2">
                    <li><a href="{{ url('/') }}" class="text-gray-400 hover:text-white transition-colors">Beranda</a></li>
                    <li><a href="{{ route('peminjaman-komputer.index') }}" class="text-gray-400 hover:text-white transition-colors">Peminjaman Komputer</a></li>
                    <li><a href="{{ route('laporan-kendala-komputer.create') }}" class="text-gray-400 hover:text-white transition-colors">Lapor Kendala</a></li>
                    <li><a href="{{ route('laporan-kendala-komputer.track') }}" class="text-gray-400 hover:text-white transition-colors">Lacak</a></li>
                    <li><a href="{{ route('jadwal-kuliah.index') }}" class="text-gray-400 hover:text-white transition-colors">Jadwal Kuliah</a></li>
                    @auth
                        <li><a href="{{ route('admin.dashboard') }}" class="text-gray-400 hover:text-white transition-colors">Dashboard</a></li>
                    @else
                        <li><a href="{{ route('login') }}" class="text-gray-400 hover:text-white transition-colors">Masuk</a></li>
                    @endauth
                </ul>
            </div>
            <div>
                <h4 class="font-semibold text-white mb-4">Kontak</h4>
                <ul class="space-y-2 text-gray-400">
                    <li>Email: info@simlabkom.id</li>
                    <li>Telepon: (021) 1234-5678</li>
                </ul>
            </div>
        </div>
        <div class="border-t border-gray-800 mt-12 pt-8 text-center text-gray-500 text-sm">
            <p>&copy; {{ date('Y') }} SimLabKom. All rights reserved.</p>
        </div>
    </div>
</footer>
