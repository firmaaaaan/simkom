<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <title>@yield('title', 'Dashboard') - SimKom - UPT Lab Terpadu</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700" rel="stylesheet" />
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @else
        <script src="https://cdn.tailwindcss.com"></script>
    @endif
    <style>
        body { font-family: 'Inter', ui-sans-serif, system-ui, sans-serif; }
        [x-cloak] { display: none !important; }
        @media (max-width: 767.98px) {
            .table-responsive-cards thead { display: none; }
            .table-responsive-cards tbody tr { display: block; background: #fff; border: 1px solid #e5e7eb; border-radius: 0.75rem; margin-bottom: 0.75rem; padding: 0.75rem; box-shadow: 0 1px 2px rgba(0,0,0,0.05); }
            .table-responsive-cards tbody td { display: flex; justify-content: space-between; align-items: center; padding: 0.5rem 0; border-bottom: 1px solid #f3f4f6; }
            .table-responsive-cards tbody td:last-child { border-bottom: none; }
            .table-responsive-cards tbody td::before { content: attr(data-label); font-weight: 600; color: #6b7280; font-size: 0.75rem; margin-right: 1rem; flex-shrink: 0; }
        }
    </style>
    @stack('styles')
</head>
<body class="bg-gray-50 text-gray-800 antialiased">
    <div class="flex min-h-screen">

        {{-- SIDEBAR --}}
        <aside class="fixed inset-y-0 left-0 z-30 w-64 bg-green-800 text-white flex flex-col transition-transform duration-300 -translate-x-full lg:translate-x-0" id="sidebar">
            <div class="flex items-center gap-3 px-6 py-5 border-b border-green-700">
                <div class="w-9 h-9 bg-white rounded-lg flex items-center justify-center">
                    <svg class="w-5 h-5 text-green-800" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9.75 3.104v5.714a2.25 2.25 0 01-.659 1.591L5 14.5M9.75 3.104c-.251.023-.501.05-.75.082m.75-.082a24.301 24.301 0 014.5 0m0 0v5.714a2.25 2.25 0 00.659 1.591L19 14.5m-4.25-11.396c.251.023.501.05.75.082M12 21a8.966 8.966 0 005.982-2.275M12 21a8.966 8.966 0 01-5.982-2.275M15.75 3.186a24.284 24.284 0 012.038.443M8.25 3.186a24.284 24.284 0 00-2.038.443M18 14.5l-6 6-6-6" />
                    </svg>
                </div>
                <span class="text-lg font-bold tracking-tight">SimKom</span>
            </div>

            <nav class="flex-1 px-3 py-4 space-y-1 overflow-y-auto">
                <p class="text-[10px] font-bold text-green-400 uppercase tracking-wider px-3 pt-1 pb-1">Menu</p>
                <a href="{{ url('/dashboard') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium {{ request()->routeIs('dashboard') ? 'bg-green-700/50 text-white' : 'text-green-200 hover:bg-green-700/30' }} transition-colors">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z" />
                    </svg>
                    Dashboard
                </a>

                @if(auth()->user()->hasPermission('manage-users') || auth()->user()->hasPermission('manage-roles'))
                <p class="text-[10px] font-bold text-green-400 uppercase tracking-wider px-3 pt-4 pb-1">Pengaturan</p>
                @endif

                @if(auth()->user()->hasPermission('manage-users'))
                <a href="{{ route('users.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium {{ request()->routeIs('users.*') ? 'bg-green-700/50 text-white' : 'text-green-200 hover:bg-green-700/30' }} transition-colors">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" />
                    </svg>
                    User
                </a>
                @endif

                @if(auth()->user()->hasPermission('manage-roles'))
                <a href="{{ route('roles.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium {{ request()->routeIs('roles.*') ? 'bg-green-700/50 text-white' : 'text-green-200 hover:bg-green-700/30' }} transition-colors">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z" />
                    </svg>
                    Role
                </a>
                @endif

                @if(auth()->user()->hasPermission('manage-laboratories') || auth()->user()->hasPermission('manage-hardware') || auth()->user()->hasPermission('manage-software') || auth()->user()->hasPermission('manage-computers'))
                <p class="text-[10px] font-bold text-green-400 uppercase tracking-wider px-3 pt-4 pb-1">Data Master</p>
                @if(auth()->user()->hasPermission('manage-laboratories'))
                <a href="{{ route('laboratories.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium {{ request()->routeIs('laboratories.*') ? 'bg-green-700/50 text-white' : 'text-green-200 hover:bg-green-700/30' }} transition-colors">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9.75 3.104v5.714a2.25 2.25 0 01-.659 1.591L5 14.5M9.75 3.104c-.251.023-.501.05-.75.082m.75-.082a24.301 24.301 0 014.5 0m0 0v5.714a2.25 2.25 0 00.659 1.591L19 14.5m-4.25-11.396c.251.023.501.05.75.082M12 21a8.966 8.966 0 005.982-2.275M12 21a8.966 8.966 0 01-5.982-2.275M15.75 3.186a24.284 24.284 0 012.038.443M8.25 3.186a24.284 24.284 0 00-2.038.443M18 14.5l-6 6-6-6" />
                    </svg>
                    Laboratorium
                </a>
                @endif
                @if(auth()->user()->hasPermission('manage-hardware'))
                <a href="{{ route('hardware.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium {{ request()->routeIs('hardware.*') ? 'bg-green-700/50 text-white' : 'text-green-200 hover:bg-green-700/30' }} transition-colors">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 3v1.5M4.5 8.25H3m18 0h-1.5M4.5 12H3m18 0h-1.5m-15 3.75H3m18 0h-1.5M8.25 19.5V21M12 3v1.5m0 15V21m3.75-18v1.5m0 15V21m-9-1.5h10.5a2.25 2.25 0 002.25-2.25V6.75a2.25 2.25 0 00-2.25-2.25H6.75A2.25 2.25 0 004.5 6.75v10.5a2.25 2.25 0 002.25 2.25z" />
                    </svg>
                    Hardware
                </a>
                @endif
                @if(auth()->user()->hasPermission('manage-software'))
                <a href="{{ route('software.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium {{ request()->routeIs('software.*') ? 'bg-green-700/50 text-white' : 'text-green-200 hover:bg-green-700/30' }} transition-colors">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 17.25v1.007a3 3 0 01-.879 2.122L7.5 21h9l-.621-.621A3 3 0 0115 18.257V17.25m6-12V15a2.25 2.25 0 01-2.25 2.25H5.25A2.25 2.25 0 013 15V5.25m18 0A2.25 2.25 0 0018.75 3H5.25A2.25 2.25 0 003 5.25m18 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 7.41A2.25 2.25 0 012.25 5.496V5.25" />
                    </svg>
                    Software
                </a>
                @endif
                @if(auth()->user()->hasPermission('manage-computers'))
                <a href="{{ route('computers.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium {{ request()->routeIs('computers.*') ? 'bg-green-700/50 text-white' : 'text-green-200 hover:bg-green-700/30' }} transition-colors">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 17.25v1.007a3 3 0 01-.879 2.122L7.5 21h9l-.621-.621A3 3 0 0115 18.257V17.25m6-12V15a2.25 2.25 0 01-2.25 2.25H5.25A2.25 2.25 0 013 15V5.25m18 0A2.25 2.25 0 0018.75 3H5.25A2.25 2.25 0 003 5.25m18 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 7.41A2.25 2.25 0 012.25 5.496V5.25m-6 6h.008v.008H9v-.008zm3 0h.008v.008H12v-.008zm3 0h.008v.008H15v-.008z" />
                    </svg>
                    Komputer
                </a>
                @endif
                @if(auth()->user()->hasPermission('manage-components'))
                <a href="{{ route('components.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium {{ request()->routeIs('components.*') ? 'bg-green-700/50 text-white' : 'text-green-200 hover:bg-green-700/30' }} transition-colors">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 3v1.5M4.5 8.25H3m18 0h-1.5M4.5 12H3m18 0h-1.5m-15 3.75H3m18 0h-1.5M8.25 19.5V21M12 3v1.5m0 15V21m3.75-18v1.5m0 15V21m-9-1.5h10.5a2.25 2.25 0 002.25-2.25V6.75a2.25 2.25 0 00-2.25-2.25H6.75A2.25 2.25 0 004.5 6.75v10.5a2.25 2.25 0 002.25 2.25z" />
                    </svg>
                    Komponen
                </a>
                @endif
                @endif

                @if(auth()->user()->hasPermission('manage-academic-years') || auth()->user()->hasPermission('manage-maintenance') || auth()->user()->hasPermission('view-reports') || auth()->user()->hasPermission('manage-tickets') || auth()->user()->hasPermission('manage-borrowings'))
                <p class="text-[10px] font-bold text-green-400 uppercase tracking-wider px-3 pt-4 pb-1">Transaksi</p>
                @if(auth()->user()->hasPermission('manage-academic-years'))
                <a href="{{ route('academic-years.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium {{ request()->routeIs('academic-years.*') ? 'bg-green-700/50 text-white' : 'text-green-200 hover:bg-green-700/30' }} transition-colors">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5m-9-6h.008v.008H12v-.008zM12 15h.008v.008H12V15zm0 2.25h.008v.008H12v-.008zM9.75 15h.008v.008H9.75V15zm0 2.25h.008v.008H9.75v-.008zM7.5 15h.008v.008H7.5V15zm0 2.25h.008v.008H7.5v-.008zm6.75-4.5h.008v.008h-.008v-.008zm0 2.25h.008v.008h-.008V15zm0 2.25h.008v.008h-.008v-.008zm2.25-4.5h.008v.008H16.5v-.008zm0 2.25h.008v.008H16.5V15z" />
                    </svg>
                    Tahun Ajaran
                </a>
                @endif
                @if(auth()->user()->hasPermission('manage-lab-schedules'))
                <a href="{{ route('lab-schedules.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium {{ request()->routeIs('lab-schedules.*') ? 'bg-green-700/50 text-white' : 'text-green-200 hover:bg-green-700/30' }} transition-colors">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z" />
                    </svg>
                    Jadwal Lab
                </a>
                @endif
                @if(auth()->user()->hasPermission('manage-maintenance'))
                <a href="{{ route('maintenance.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium {{ request()->routeIs('maintenance.*') ? 'bg-green-700/50 text-white' : 'text-green-200 hover:bg-green-700/30' }} transition-colors">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M11.42 15.17l-5.1-5.1m0 0L11.42 4.97m-5.1 5.1H21M3 3v18" />
                    </svg>
                    Pemeliharaan
                </a>
                <a href="{{ route('device-checks.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium {{ request()->routeIs('device-checks.*') && ! request()->routeIs('device-checks.report', 'device-checks.report-print') ? 'bg-green-700/50 text-white' : 'text-green-200 hover:bg-green-700/30' }} transition-colors">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M11.42 15.17L17.25 21A2.652 2.652 0 0021 17.25l-5.877-5.877M11.42 15.17l2.496-3.03c.317-.384.74-.626 1.208-.766M11.42 15.17l-4.655 5.653a2.548 2.548 0 11-3.586-3.586l6.837-5.63m5.108-.233c.55-.164 1.163-.188 1.743-.14a4.5 4.5 0 004.486-6.336l-3.276 3.277a3.004 3.004 0 01-2.25-2.25l3.276-3.276a4.5 4.5 0 00-6.336 4.486c.091 1.076-.071 2.264-.904 2.95l-.102.085m-1.745 1.437L5.909 7.5H4.5L2.25 3.75l1.5-1.5L7.5 4.5v1.409l4.26 4.26m-1.745 1.437l1.745-1.437m6.615 8.206L15.75 15.75M4.867 19.125h.008v.008h-.008v-.008z" />
                    </svg>
                    Pengecekan Perangkat
                </a>
                <a href="{{ route('device-checks.report') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium {{ request()->routeIs('device-checks.report', 'device-checks.report-print') ? 'bg-green-700/50 text-white' : 'text-green-200 hover:bg-green-700/30' }} transition-colors">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25z" />
                    </svg>
                    Laporan Pengecekan
                </a>
                @endif
                @if(auth()->user()->hasPermission('view-reports'))
                <a href="{{ route('reports.card-control') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium {{ request()->routeIs('reports.*') ? 'bg-green-700/50 text-white' : 'text-green-200 hover:bg-green-700/30' }} transition-colors">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                    </svg>
                    Kartu Kendali
                </a>
                @endif
                @if(auth()->user()->hasPermission('manage-tickets'))
                <a href="{{ route('tickets.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium {{ request()->routeIs('tickets.*') ? 'bg-green-700/50 text-white' : 'text-green-200 hover:bg-green-700/30' }} transition-colors">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 6v.75m0 3v.75m0 3v.75m0 3V18m-9-5.25h5.25M7.5 15h3M3.375 5.25c-.621 0-1.125.504-1.125 1.125v3.026a2.999 2.999 0 010 5.198v3.026c0 .621.504 1.125 1.125 1.125h17.25c.621 0 1.125-.504 1.125-1.125v-3.026a2.999 2.999 0 010-5.198V6.375c0-.621-.504-1.125-1.125-1.125H3.375z" />
                    </svg>
                    Kendala Praktikum
                </a>
                @endif
                @if(auth()->user()->hasPermission('manage-borrowings'))
                <a href="{{ route('borrowings.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium {{ request()->routeIs('borrowings.*') ? 'bg-green-700/50 text-white' : 'text-green-200 hover:bg-green-700/30' }} transition-colors">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 01-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0H21M3.375 14.25h3.75l1.125 6.75h10.5l1.125-6.75h3.75M3.375 14.25V5.625c0-.621.504-1.125 1.125-1.125h15c.621 0 1.125.504 1.125 1.125v8.625" />
                    </svg>
                    Peminjaman
                </a>
                @endif
                @endif
            </nav>

            <div class="px-4 py-4 border-t border-green-700">
                <a href="{{ route('profile.edit') }}" class="flex items-center gap-3 rounded-lg p-1 -m-1 hover:bg-green-700/30 transition-colors" title="Pengaturan Akun">
                    <div class="w-9 h-9 bg-green-600 rounded-full flex items-center justify-center text-sm font-semibold">
                        {{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 1)) }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium truncate">{{ auth()->user()->name ?? 'User' }}</p>
                        <p class="text-xs text-green-300 truncate">{{ auth()->user()->role_label }}</p>
                    </div>
                </a>
            </div>
        </aside>

        {{-- OVERLAY (mobile) --}}
        <div class="fixed inset-0 bg-black/50 z-20 hidden" id="sidebarOverlay" onclick="toggleSidebar()"></div>

        {{-- MAIN CONTENT --}}
        <div class="flex-1 lg:ml-64 flex flex-col min-h-screen">

            {{-- HEADER --}}
            <header class="sticky top-0 z-10 bg-white border-b border-gray-200 px-4 sm:px-6 py-4 flex items-center justify-between">
                <div class="flex items-center gap-4 min-w-0">
                    <button onclick="toggleSidebar()" class="lg:hidden p-2 rounded-lg hover:bg-gray-100 transition-colors flex-shrink-0">
                        <svg class="w-5 h-5 text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                        </svg>
                    </button>
                    <h1 class="text-base sm:text-lg font-semibold text-gray-800 truncate">@yield('header', 'Dashboard')</h1>
                </div>
                <div class="flex items-center gap-3">
                    <div x-data="notifBell" class="relative">
                        <button @click="open = !open" @click.outside="open = false;" class="p-2 rounded-lg hover:bg-gray-100 relative transition-colors" aria-label="Notifikasi">
                            <svg class="w-5 h-5 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0" />
                            </svg>
                            <span x-show="unreadCount > 0" x-cloak
                                class="absolute top-0.5 right-0.5 min-w-[18px] h-[18px] px-1 bg-red-500 text-white text-[10px] font-bold rounded-full flex items-center justify-center"
                                x-text="unreadCount > 99 ? '99+' : unreadCount"></span>
                        </button>

                        {{-- Dropdown notifikasi --}}
                        <div x-show="open" x-cloak x-transition.opacity.duration.150ms
                            class="absolute right-0 mt-2 w-80 max-w-[calc(100vw-2rem)] bg-white rounded-xl border border-gray-200 shadow-lg z-50 overflow-hidden">
                            <div class="flex items-center justify-between px-4 py-3 border-b border-gray-100">
                                <p class="text-sm font-semibold text-gray-800">Notifikasi</p>
                                <button @click="markAllRead()" class="text-xs text-green-600 hover:text-green-700 font-medium">Tandai dibaca</button>
                            </div>
                            <div class="max-h-80 overflow-y-auto divide-y divide-gray-50">
                                <p x-show="notifications.length === 0" class="px-4 py-8 text-center text-sm text-gray-400">Tidak ada notifikasi</p>
                                <template x-for="n in notifications" :key="n.id">
                                    <a :href="n.url || '#'" class="flex gap-3 px-4 py-3 hover:bg-gray-50 transition-colors" :class="n.is_unread ? 'bg-green-50/50' : ''">
                                        <div class="w-8 h-8 rounded-full flex items-center justify-center flex-shrink-0"
                                            :class="n.type === 'ticket' ? 'bg-amber-100 text-amber-600' : 'bg-blue-100 text-blue-600'">
                                            <svg x-show="n.type === 'ticket'" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                                            </svg>
                                            <svg x-show="n.type !== 'ticket'" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 01-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0H21M3.375 14.25h3.75l1.125 6.75h10.5l1.125-6.75h3.75M3.375 14.25V5.625c0-.621.504-1.125 1.125-1.125h15c.621 0 1.125.504 1.125 1.125v8.625" />
                                            </svg>
                                        </div>
                                        <div class="min-w-0 flex-1">
                                            <p class="text-sm font-medium text-gray-800 truncate" x-text="n.title"></p>
                                            <p class="text-xs text-gray-500 truncate" x-text="n.message"></p>
                                            <p class="text-[11px] text-gray-400 mt-0.5" x-text="n.time"></p>
                                        </div>
                                        <span x-show="n.is_unread" class="w-2 h-2 bg-green-500 rounded-full flex-shrink-0 mt-1.5"></span>
                                    </a>
                                </template>
                            </div>
                        </div>
                    </div>
                    <div class="w-px h-6 bg-gray-200"></div>
                    <div class="flex items-center gap-2">
                        <div class="w-8 h-8 bg-green-600 rounded-full flex items-center justify-center text-white text-sm font-semibold">
                            {{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 1)) }}
                        </div>
                        <span class="text-sm font-medium text-gray-700 hidden sm:block">{{ auth()->user()->name ?? 'User' }}</span>
                    </div>
                    <button onclick="document.getElementById('logoutForm').submit()" class="p-2 rounded-lg hover:bg-gray-100 transition-colors" title="Logout">
                        <svg class="w-5 h-5 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15m3 0l3-3m0 0l-3-3m3 3H9" />
                        </svg>
                    </button>
                    <form id="logoutForm" action="{{ route('logout') }}" method="POST" class="hidden">
                        @csrf
                    </form>
                </div>
            </header>

            {{-- PAGE CONTENT --}}
            <main class="flex-1 p-4 sm:p-6 overflow-x-hidden">
                @yield('content')
            </main>
        </div>
    </div>

    <script>
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebarOverlay');
            sidebar.classList.toggle('-translate-x-full');
            overlay.classList.toggle('hidden');
        }

        document.addEventListener('alpine:init', () => {
            Alpine.data('notifBell', () => ({
                open: false,
                unreadCount: 0,
                notifications: [],
                timer: null,
                prevUnread: 0,
                audioCtx: null,
                init() {
                    this.poll();
                    this.timer = setInterval(() => this.poll(), 2000);
                },
                destroy() {
                    clearInterval(this.timer);
                },
                playNotifSound() {
                    try {
                        if (!this.audioCtx) this.audioCtx = new (window.AudioContext || window.webkitAudioContext)();
                        const ctx = this.audioCtx;
                        const osc = ctx.createOscillator();
                        const gain = ctx.createGain();
                        osc.connect(gain);
                        gain.connect(ctx.destination);
                        osc.type = 'sine';
                        osc.frequency.setValueAtTime(880, ctx.currentTime);
                        osc.frequency.setValueAtTime(1100, ctx.currentTime + 0.1);
                        gain.gain.setValueAtTime(0.3, ctx.currentTime);
                        gain.gain.exponentialRampToValueAtTime(0.01, ctx.currentTime + 0.4);
                        osc.start(ctx.currentTime);
                        osc.stop(ctx.currentTime + 0.4);
                    } catch (e) {}
                },
                async poll() {
                    try {
                        const res = await fetch('{{ route('notifications.poll') }}');
                        if (!res.ok) return;
                        const data = await res.json();
                        if (data.unread_count > this.prevUnread && this.prevUnread > 0) {
                            this.playNotifSound();
                        }
                        this.prevUnread = data.unread_count;
                        this.unreadCount = data.unread_count;
                        this.notifications = data.notifications;
                    } catch (e) {}
                },
                async markAllRead() {
                    try {
                        await fetch('{{ route('notifications.read-all') }}', {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                'Accept': 'application/json',
                            },
                        });
                        this.unreadCount = 0;
                        this.prevUnread = 0;
                        this.poll();
                    } catch (e) {}
                },
            }));
        });
    </script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    @stack('scripts')
</body>
</html>
