@extends('layouts.app')

@section('title', 'Detail Komponen')
@section('header', 'Detail Komponen')

@section('content')
<div class="max-w-3xl">
    <a href="{{ route('components.index') }}" class="inline-flex items-center gap-2 text-sm text-gray-600 hover:text-green-600 transition-colors mb-6">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"/>
        </svg>
        Kembali ke Daftar Komponen
    </a>

    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
        @if($component->image)
            <div class="border-b border-gray-100">
                <img src="{{ asset('storage/' . $component->image) }}" alt="{{ $component->name }}" class="w-full h-64 object-cover">
            </div>
        @endif

        <div class="p-6">
            <div class="flex items-start justify-between mb-4">
                <div>
                    <h3 class="text-xl font-bold text-gray-900">{{ $component->name }}</h3>
                    <p class="text-sm text-gray-500 mt-1">Kode: {{ $component->code }}</p>
                </div>
                <div class="flex items-center gap-2">
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium {{ $component->category === 'IoT' ? 'bg-blue-100 text-blue-700' : ($component->category === 'Jaringan' ? 'bg-purple-100 text-purple-700' : 'bg-gray-100 text-gray-700') }}">
                        {{ $component->category }}
                    </span>
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium
                        {{ $component->status === 'Tersedia' ? 'bg-green-100 text-green-700' : ($component->status === 'Digunakan' ? 'bg-blue-100 text-blue-700' : ($component->status === 'Rusak' ? 'bg-red-100 text-red-700' : 'bg-yellow-100 text-yellow-700')) }}">
                        {{ $component->status }}
                    </span>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-4 border-t border-gray-100">
                <div>
                    <p class="text-xs text-gray-500 mb-1">Merk</p>
                    <p class="text-sm font-medium text-gray-900">{{ $component->brand ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-500 mb-1">Model</p>
                    <p class="text-sm font-medium text-gray-900">{{ $component->model ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-500 mb-1">Jumlah</p>
                    <p class="text-sm font-medium text-gray-900">{{ $component->quantity }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-500 mb-1">Terakhir Diperbarui</p>
                    <p class="text-sm font-medium text-gray-900">{{ $component->updated_at->format('d M Y H:i') }}</p>
                </div>
            </div>

            @if($component->description)
                <div class="pt-4 border-t border-gray-100">
                    <p class="text-xs text-gray-500 mb-1">Keterangan</p>
                    <p class="text-sm text-gray-700">{{ $component->description }}</p>
                </div>
            @endif

            <div class="flex items-center gap-3 pt-6 border-t border-gray-100">
                <a href="{{ route('components.edit', $component) }}" class="inline-flex items-center gap-2 px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700 transition-colors">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125" />
                    </svg>
                    Edit
                </a>
                <form action="{{ route('components.destroy', $component) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus komponen ini?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="inline-flex items-center gap-2 px-4 py-2 bg-red-600 text-white text-sm font-medium rounded-lg hover:bg-red-700 transition-colors">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                        </svg>
                        Hapus
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
