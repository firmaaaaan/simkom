@extends('layouts.app')

@section('title', 'Tanda Tangan Laporan')
@section('header', 'Tanda Tangan Laporan')

@section('content')
<div class="max-w-4xl">
    <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
        <a href="{{ route('reports.laporan') }}" class="inline-flex items-center gap-1.5 text-sm text-gray-500 hover:text-green-600 transition-colors">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
            </svg>
            Kembali
        </a>
        @if(auth()->check() && (auth()->user()->hasPermission('edit-reports') || auth()->user()->can('edit-reports')))
        <a href="{{ route('settings.signatures.create') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700 transition-colors">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
            </svg>
            Tambah TTD
        </a>
        @endif
    </div>

    @if(session('success'))
        <div class="mb-4 px-4 py-3 bg-green-50 border border-green-200 text-green-700 rounded-lg text-sm flex items-center gap-2">
            <svg class="w-5 h-5 text-green-600 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            {{ session('success') }}
        </div>
    @endif

    <div class="bg-white rounded-xl border border-gray-200 p-6 mb-4">
        <h2 class="text-base font-semibold text-gray-800 mb-1">Daftar TTD</h2>
        <p class="text-sm text-gray-500">
            Centang laporan yang ingin menerapkan TTD ini. Laporan yang belum ditugasi tetap memakai TTD default.
        </p>
    </div>

    @forelse($signatures as $signature)
        <div class="bg-white rounded-xl border border-gray-200 p-5 mb-3 flex flex-col sm:flex-row sm:items-center gap-4">
            <div class="flex-shrink-0 w-28 h-16 bg-gray-50 border border-gray-200 rounded-lg flex items-center justify-center overflow-hidden">
                @if($signature->imageSrc())
                    <img src="{{ $signature->imageSrc() }}" alt="TTD {{ $signature->name }}" class="max-h-14 max-w-full object-contain">
                @else
                    <span class="text-[10px] text-gray-400">tanpa gambar</span>
                @endif
            </div>

            <div class="min-w-0 flex-1">
                <p class="text-sm font-semibold text-gray-800">{{ $signature->name }}</p>
                <p class="text-xs text-gray-500 mt-0.5">{{ $signature->role }}</p>
                <div class="flex flex-wrap gap-1.5 mt-2">
                    @php($labels = $signature->reportLabels())
                    @if(empty($labels))
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full bg-gray-100 text-gray-500 text-[11px] font-medium">
                            Belum ditugaskan ke laporan
                        </span>
                    @else
                        @foreach($labels as $label)
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full bg-green-50 text-green-700 text-[11px] font-medium">
                                {{ $label }}
                            </span>
                        @endforeach
                    @endif
                </div>
            </div>

            @if(auth()->check() && (auth()->user()->hasPermission('edit-reports') || auth()->user()->can('edit-reports')))
            <div class="flex items-center gap-2 flex-shrink-0">
                <a href="{{ route('settings.signatures.edit', $signature) }}" class="px-3 py-1.5 border border-gray-300 text-gray-600 text-xs font-medium rounded-lg hover:bg-gray-50 transition-colors">
                    Edit
                </a>
                <form action="{{ route('settings.signatures.destroy', $signature) }}" method="POST" onsubmit="return confirm('Hapus TTD {{ $signature->name }}? Laporan terkait kembali ke TTD default.');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="px-3 py-1.5 border border-red-200 text-red-600 text-xs font-medium rounded-lg hover:bg-red-50 transition-colors">
                        Hapus
                    </button>
                </form>
            </div>
            @endif
        </div>
    @empty
        <div class="bg-white rounded-xl border border-gray-200 p-12 text-center">
            @if(auth()->check() && (auth()->user()->hasPermission('edit-reports') || auth()->user()->can('edit-reports')))
                <p class="text-sm text-gray-500">Belum ada TTD. Klik "Tambah TTD" untuk membuat.</p>
            @else
                <p class="text-sm text-gray-500">Belum ada TTD.</p>
            @endif
        </div>
    @endforelse
</div>
@endsection
