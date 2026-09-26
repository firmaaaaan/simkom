@extends('layouts.app')

@section('title', 'Detail Tahun Ajaran')
@section('header', 'Detail Tahun Ajaran')

@section('content')
<div class="max-w-2xl">
    <div class="mb-6">
        <a href="{{ route('academic-years.index') }}" class="inline-flex items-center gap-1.5 text-sm text-gray-500 hover:text-green-600 transition-colors">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
            </svg>
            Kembali
        </a>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 p-6">
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-lg font-bold text-gray-800">Detail Tahun Ajaran</h2>
            <div class="flex items-center gap-2">
                <a href="{{ route('academic-years.edit', $academicYear) }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-sm border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                    <svg class="w-4 h-4 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125" />
                    </svg>
                    Edit
                </a>
                <form action="{{ route('academic-years.destroy', $academicYear) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus tahun ajaran ini?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-sm border border-red-300 text-red-600 rounded-lg hover:bg-red-50 transition-colors">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                        </svg>
                        Hapus
                    </button>
                </form>
            </div>
        </div>

        <div class="space-y-4">
            <div class="flex items-center gap-4 pb-4 border-b border-gray-100">
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $academicYear->status === 'Aktif' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                    {{ $academicYear->status }}
                </span>
                <span class="text-sm text-gray-600">{{ $academicYear->start_year }}/{{ $academicYear->end_year }}</span>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <p class="text-xs text-gray-400 mb-1">Nama</p>
                    <p class="text-sm text-gray-800 font-medium">{{ $academicYear->name }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-400 mb-1">Tahun</p>
                    <p class="text-sm text-gray-800">{{ $academicYear->start_year }} - {{ $academicYear->end_year }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-400 mb-1">Tanggal Mulai</p>
                    <p class="text-sm text-gray-800">{{ $academicYear->start_date?->format('d-m-Y') ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-400 mb-1">Tanggal Selesai</p>
                    <p class="text-sm text-gray-800">{{ $academicYear->end_date?->format('d-m-Y') ?? '-' }}</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
