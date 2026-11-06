<nav class="bg-white border-b border-gray-200 sticky top-0 z-40">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center h-16 gap-3">
            <a href="{{ route('daftar-komputer') }}" class="flex items-center gap-3 shrink-0">
                <img src="{{ asset('kop/cropped-logo-unisa-crop.png') }}" alt="Logo Aisyiyah" class="w-9 h-9 object-contain">
                <span class="flex flex-col justify-center leading-none">
                        <span class="text-xl font-extrabold text-gray-800">Sim<span class="text-green-600">Kom</span></span>
                        <span class="mt-1 text-[10px] font-semibold uppercase tracking-[0.14em] text-gray-400">BY UPT LABORATORIUM TERPADU</span>
                    </span>
            </a>

            <div class="flex items-center gap-1.5 min-w-0 overflow-x-auto">
                <a href="{{ route('daftar-komputer') }}"
                    class="inline-flex items-center gap-2 px-3 py-2 text-sm font-medium rounded-lg transition-colors whitespace-nowrap {{ request()->routeIs('daftar-komputer') ? 'bg-green-600 text-white shadow-sm' : 'text-gray-600 hover:text-green-600 hover:bg-green-50' }}">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 17.25v1.007a3 3 0 01-.879 2.122L7.5 21h9l-.621-.621A3 3 0 0115 18.257V17.25m6-12V15a2.25 2.25 0 01-2.25 2.25H5.25A2.25 2.25 0 013 15V5.25m18 0A2.25 2.25 0 0018.75 3H5.25A2.25 2.25 0 003 5.25m18 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 7.41A2.25 2.25 0 012.25 5.496V5.25" />
                    </svg>
                    <span class="hidden sm:inline">Pinjam Komputer</span>
                </a>
                <a href="{{ route('jadwal.index') }}"
                    class="inline-flex items-center gap-2 px-3 py-2 text-sm font-medium rounded-lg transition-colors whitespace-nowrap {{ request()->routeIs('jadwal.index') ? 'bg-green-600 text-white shadow-sm' : 'text-gray-600 hover:text-green-600 hover:bg-green-50' }}">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" />
                    </svg>
                    <span class="hidden sm:inline">Jadwal Laboratorium</span>
                </a>
                <a href="{{ route('track.index') }}"
                    class="inline-flex items-center gap-2 px-3 py-2 text-sm font-medium rounded-lg transition-colors whitespace-nowrap {{ request()->routeIs('track.index') ? 'bg-green-600 text-white shadow-sm' : 'text-gray-600 hover:text-green-600 hover:bg-green-50' }}">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                    </svg>
                    <span class="hidden sm:inline">Lacak Laporan</span>
                </a>
            </div>
        </div>
    </div>
</nav>
