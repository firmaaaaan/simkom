<nav class="bg-white border-b border-gray-200 sticky top-0 z-40">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center h-16 gap-3">
            <a href="{{ route('daftar-komputer') }}" class="flex items-center gap-3 shrink-0 min-w-0">
                <img src="{{ asset('kop/cropped-logo-unisa-crop.png') }}" alt="Logo Aisyiyah" class="w-9 h-9 object-contain">
                <span class="flex flex-col justify-center leading-none min-w-0">
                    <span class="text-xl font-extrabold text-gray-800">Sim<span class="text-green-600">Kom</span></span>
                    <span class="mt-1 text-[10px] font-semibold uppercase tracking-[0.14em] text-gray-400 truncate">BY UPT LABORATORIUM TERPADU</span>
                </span>
            </a>

            {{-- Desktop links --}}
            <div class="hidden sm:flex items-center gap-1.5">
                <a href="{{ route('daftar-komputer') }}"
                    class="inline-flex items-center gap-2 px-3 py-2 text-sm font-medium rounded-lg transition-colors whitespace-nowrap {{ request()->routeIs('daftar-komputer') ? 'bg-green-600 text-white shadow-sm' : 'text-gray-600 hover:text-green-600 hover:bg-green-50' }}">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 17.25v1.007a3 3 0 01-.879 2.122L7.5 21h9l-.621-.621A3 3 0 0115 18.257V17.25m6-12V15a2.25 2.25 0 01-2.25 2.25H5.25A2.25 2.25 0 013 15V5.25m18 0A2.25 2.25 0 0018.75 3H5.25A2.25 2.25 0 003 5.25m18 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 7.41A2.25 2.25 0 012.25 5.496V5.25" />
                    </svg>
                    Pinjam Komputer
                </a>
                <a href="{{ route('jadwal.index') }}"
                    class="inline-flex items-center gap-2 px-3 py-2 text-sm font-medium rounded-lg transition-colors whitespace-nowrap {{ request()->routeIs('jadwal.index') ? 'bg-green-600 text-white shadow-sm' : 'text-gray-600 hover:text-green-600 hover:bg-green-50' }}">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" />
                    </svg>
                    Jadwal Laboratorium
                </a>
                <a href="{{ route('track.index') }}"
                    class="inline-flex items-center gap-2 px-3 py-2 text-sm font-medium rounded-lg transition-colors whitespace-nowrap {{ request()->routeIs('track.index') ? 'bg-green-600 text-white shadow-sm' : 'text-gray-600 hover:text-green-600 hover:bg-green-50' }}">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                    </svg>
                    Lacak Laporan
                </a>
            </div>

            {{-- Mobile hamburger --}}
            <button type="button" id="publicNavToggle" onclick="togglePublicNav()"
                class="sm:hidden inline-flex items-center justify-center p-2 rounded-lg text-gray-600 hover:text-green-600 hover:bg-green-50 transition-colors"
                aria-controls="publicNavMenu" aria-expanded="false" aria-label="Buka menu">
                <svg id="publicNavIconOpen" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                </svg>
                <svg id="publicNavIconClose" class="w-6 h-6 hidden" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
    </div>

    {{-- Mobile menu --}}
    <div id="publicNavMenu" class="sm:hidden hidden border-t border-gray-200 bg-white">
        <div class="px-4 py-3 space-y-1">
            <a href="{{ route('daftar-komputer') }}"
                class="flex items-center gap-3 px-3 py-2.5 text-sm font-medium rounded-lg transition-colors {{ request()->routeIs('daftar-komputer') ? 'bg-green-600 text-white' : 'text-gray-700 hover:text-green-600 hover:bg-green-50' }}">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 17.25v1.007a3 3 0 01-.879 2.122L7.5 21h9l-.621-.621A3 3 0 0115 18.257V17.25m6-12V15a2.25 2.25 0 01-2.25 2.25H5.25A2.25 2.25 0 013 15V5.25m18 0A2.25 2.25 0 0018.75 3H5.25A2.25 2.25 0 003 5.25m18 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 7.41A2.25 2.25 0 012.25 5.496V5.25" />
                </svg>
                Pinjam Komputer
            </a>
            <a href="{{ route('jadwal.index') }}"
                class="flex items-center gap-3 px-3 py-2.5 text-sm font-medium rounded-lg transition-colors {{ request()->routeIs('jadwal.index') ? 'bg-green-600 text-white' : 'text-gray-700 hover:text-green-600 hover:bg-green-50' }}">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" />
                </svg>
                Jadwal Laboratorium
            </a>
            <a href="{{ route('track.index') }}"
                class="flex items-center gap-3 px-3 py-2.5 text-sm font-medium rounded-lg transition-colors {{ request()->routeIs('track.index') ? 'bg-green-600 text-white' : 'text-gray-700 hover:text-green-600 hover:bg-green-50' }}">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                </svg>
                Lacak Laporan
            </a>
        </div>
    </div>
</nav>

<script>
    function togglePublicNav() {
        const menu = document.getElementById('publicNavMenu');
        const toggle = document.getElementById('publicNavToggle');
        const openIcon = document.getElementById('publicNavIconOpen');
        const closeIcon = document.getElementById('publicNavIconClose');
        if (!menu || !toggle) return;
        const expanded = toggle.getAttribute('aria-expanded') === 'true';
        toggle.setAttribute('aria-expanded', expanded ? 'false' : 'true');
        toggle.setAttribute('aria-label', expanded ? 'Buka menu' : 'Tutup menu');
        menu.classList.toggle('hidden', expanded);
        if (openIcon && closeIcon) {
            openIcon.classList.toggle('hidden', !expanded);
            closeIcon.classList.toggle('hidden', expanded);
        }
    }
</script>
