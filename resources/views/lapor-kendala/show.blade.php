<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <title>Detail Tiket {{ $ticket->tracking_code }} - {{ config('app.name', 'SimKom') }}</title>
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

    {{-- Navbar --}}
    <nav class="bg-white border-b border-gray-200 sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 bg-gradient-to-br from-green-500 to-emerald-600 rounded-xl flex items-center justify-center shadow-lg shadow-green-200">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 3.104v5.714a2.25 2.25 0 01-.659 1.591L5 14.5M9.75 3.104c-.251.023-.501.05-.75.082m.75-.082a24.301 24.301 0 014.5 0m0 0v5.714a2.25 2.25 0 00.659 1.591L19 14.5m-4.25-11.396c.251.023.501.05.75.082M12 21a8.966 8.966 0 005.982-2.275M12 21a8.966 8.966 0 01-5.982-2.275M15.75 3.186a24.284 24.284 0 012.038.443M8.25 3.186a24.284 24.284 0 00-2.038.443M18 14.5l-6 6-6-6" />
                        </svg>
                    </div>
                    <span class="text-xl font-extrabold text-gray-800">Sim<span class="text-green-600">Lab</span></span>
                </div>
                <div class="flex items-center gap-3">
                    <a href="{{ route('track.index') }}" class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-gray-600 hover:text-green-600 transition-colors">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                        </svg>
                        Lacak Laporan
                    </a>
                    <a href="{{ url('/') }}" class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-gray-600 hover:text-green-600 transition-colors">
                        Beranda
                    </a>
                </div>
            </div>
        </div>
    </nav>

    {{-- Content --}}
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

        {{-- Back Link --}}
        <a href="{{ route('track.index') }}" class="inline-flex items-center gap-2 text-sm text-gray-600 hover:text-green-600 transition-colors mb-6">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"/>
            </svg>
            Lacak Lain
        </a>

        {{-- Success Message --}}
        @if(session('success'))
            <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-xl text-sm mb-6 flex items-center gap-2">
                <svg class="w-5 h-5 text-green-600 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                {{ session('success') }}
            </div>
        @endif

        {{-- Tracking Code Banner --}}
        <div class="bg-gradient-to-br from-blue-600 to-indigo-700 rounded-2xl p-6 text-white mb-6 shadow-lg shadow-blue-200">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-blue-100 mb-1">Kode Tracking Anda</p>
                    <p class="text-3xl font-black tracking-wider">{{ $ticket->tracking_code }}</p>
                    <p class="text-xs text-blue-200 mt-2">Simpan kode ini untuk melacak status laporan Anda</p>
                </div>
                <div class="w-16 h-16 bg-white/20 rounded-2xl flex items-center justify-center">
                    <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 6v.75m0 3v.75m0 3v.75m0 3V18m-9-5.25h5.25M7.5 15h3M3.375 5.25c-.621 0-1.125.504-1.125 1.125v3.026a2.999 2.999 0 010 5.198v3.026c0 .621.504 1.125 1.125 1.125h17.25c.621 0 1.125-.504 1.125-1.125v-3.026a2.999 2.999 0 010-5.198V6.375c0-.621-.504-1.125-1.125-1.125H3.375z" />
                    </svg>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            {{-- Main Content --}}
            <div class="lg:col-span-2 space-y-6">
                {{-- Ticket Info --}}
                <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6">
                    <div class="flex items-start justify-between mb-4">
                        <div>
                            <h3 class="text-xl font-bold text-gray-900">{{ $ticket->title }}</h3>
                            <p class="text-sm text-gray-500 mt-1">Dibuat {{ $ticket->created_at->format('d M Y H:i') }}</p>
                        </div>
                        @php
                            $statusColors = [
                                'Open' => 'bg-blue-100 text-blue-700 border-blue-200',
                                'In Progress' => 'bg-yellow-100 text-yellow-700 border-yellow-200',
                                'Resolved' => 'bg-green-100 text-green-700 border-green-200',
                                'Closed' => 'bg-gray-100 text-gray-500 border-gray-200',
                            ];
                        @endphp
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium border {{ $statusColors[$ticket->status] ?? '' }}">
                            {{ $ticket->status }}
                        </span>
                    </div>

                    <div class="prose prose-sm max-w-none text-gray-700 mb-6">
                        {!! nl2br(e($ticket->description)) !!}
                    </div>

                    @if($ticket->images && count($ticket->images) > 0)
                        <div class="mb-6">
                            <p class="text-sm font-medium text-gray-700 mb-2">Foto Kendala</p>
                            <div class="flex flex-wrap gap-3">
                                @foreach($ticket->images as $image)
                                    <a href="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($image) }}" target="_blank" class="block w-24 h-24 rounded-lg overflow-hidden border border-gray-200 hover:ring-2 hover:ring-green-500 transition-all">
                                        <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($image) }}" alt="Foto kendala" class="w-full h-full object-cover">
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 pt-4 border-t border-gray-100">
                        <div>
                            <p class="text-xs text-gray-500 mb-1">Kategori</p>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                {{ $ticket->category }}
                            </span>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500 mb-1">Prioritas</p>
                            @php
                                $priorityColors = [
                                    'Rendah' => 'bg-gray-100 text-gray-700',
                                    'Sedang' => 'bg-blue-100 text-blue-700',
                                    'Tinggi' => 'bg-orange-100 text-orange-700',
                                    'Darurat' => 'bg-red-100 text-red-700',
                                ];
                            @endphp
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $priorityColors[$ticket->priority] ?? '' }}">
                                {{ $ticket->priority }}
                            </span>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500 mb-1">Laboratorium</p>
                            <p class="text-sm font-medium text-gray-900">{{ $ticket->laboratory->name ?? '-' }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500 mb-1">Komputer</p>
                            <p class="text-sm font-medium text-gray-900">{{ $ticket->computer->code ?? '-' }}</p>
                        </div>
                    </div>
                </div>

                {{-- Comments --}}
                <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6">
                    <h4 class="text-lg font-bold text-gray-900 mb-4">Komentar ({{ $ticket->comments->count() }})</h4>

                    @if($ticket->comments->count() > 0)
                        <div class="space-y-4">
                            @foreach($ticket->comments as $comment)
                                <div class="flex gap-3">
                                    <div class="w-8 h-8 bg-blue-100 text-blue-700 rounded-full flex items-center justify-center text-sm font-semibold flex-shrink-0">
                                        {{ strtoupper(substr($comment->user->name ?? 'A', 0, 1)) }}
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center gap-2 mb-1">
                                            <span class="text-sm font-medium text-gray-900">{{ $comment->user->name ?? 'Admin' }}</span>
                                            <span class="text-xs text-gray-400">{{ $comment->created_at->format('d M Y H:i') }}</span>
                                        </div>
                                        <p class="text-sm text-gray-700">{{ $comment->message }}</p>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-sm text-gray-500">Belum ada komentar dari admin.</p>
                    @endif
                </div>
            </div>

            {{-- Sidebar --}}
            <div class="space-y-6">
                {{-- Status Timeline --}}
                <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6">
                    <h4 class="text-sm font-bold text-gray-900 mb-4">Status Laporan</h4>
                    <div class="space-y-4">
                        @php
                            $statuses = ['Open', 'In Progress', 'Resolved', 'Closed'];
                            $currentIdx = array_search($ticket->status, $statuses);
                        @endphp
                        @foreach($statuses as $idx => $status)
                            <div class="flex items-start gap-3">
                                <div class="flex flex-col items-center">
                                    <div class="w-8 h-8 rounded-full flex items-center justify-center text-xs font-bold
                                        {{ $idx <= $currentIdx ? 'bg-green-500 text-white' : 'bg-gray-200 text-gray-400' }}">
                                        @if($idx < $currentIdx)
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                                            </svg>
                                        @else
                                            {{ $idx + 1 }}
                                        @endif
                                    </div>
                                    @if($idx < count($statuses) - 1)
                                        <div class="w-0.5 h-6 {{ $idx < $currentIdx ? 'bg-green-500' : 'bg-gray-200' }}"></div>
                                    @endif
                                </div>
                                <div class="pt-1">
                                    <p class="text-sm font-medium {{ $idx <= $currentIdx ? 'text-gray-900' : 'text-gray-400' }}">{{ $status }}</p>
                                    @if($idx === $currentIdx)
                                        <p class="text-xs text-green-600 font-medium">Status saat ini</p>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- Info --}}
                <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6">
                    <h4 class="text-sm font-bold text-gray-900 mb-4">Informasi Laporan</h4>
                    <div class="space-y-3 text-sm">
                        <div class="flex justify-between">
                            <span class="text-gray-500">Pelapor</span>
                            <span class="font-medium text-gray-900">{{ $ticket->reporter_name ?? '-' }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-500">NIM/NIDN</span>
                            <span class="font-medium text-gray-900">{{ $ticket->reporter_nim ?? '-' }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-500">Program Studi</span>
                            <span class="font-medium text-gray-900">{{ $ticket->reporter_prodi ?? '-' }}</span>
                        </div>
                        <hr class="border-gray-100">
                        <div class="flex justify-between">
                            <span class="text-gray-500">Tahun Ajaran</span>
                            <span class="font-medium text-gray-900">{{ $ticket->academicYear->name ?? '-' }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-500">Ditugaskan</span>
                            <span class="font-medium text-gray-900">{{ $ticket->assignee->name ?? 'Belum ditugaskan' }}</span>
                        </div>
                        <hr class="border-gray-100">
                        <div class="flex justify-between">
                            <span class="text-gray-500">Dibuat</span>
                            <span class="font-medium text-gray-900">{{ $ticket->created_at->format('d M Y H:i') }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-500">Diperbarui</span>
                            <span class="font-medium text-gray-900">{{ $ticket->updated_at->format('d M Y H:i') }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</body>
</html>
