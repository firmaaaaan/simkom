@extends('layouts.app')

@section('title', 'Alat Laboratorium')
@section('header', 'Alat Laboratorium')

@section('content')
<div class="space-y-4">
    @if(session('success'))
        <div class="px-4 py-3 bg-green-50 border border-green-200 text-green-700 rounded-lg text-sm">{{ session('success') }}</div>
    @endif

    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <p class="text-sm text-gray-500">Kelola data alat laboratorium per ruangan</p>
        <div class="flex items-center gap-2">
            <a href="{{ route('alat.labels', ['laboratory_id' => request('laboratory_id') ?: 'all']) }}" class="inline-flex items-center gap-2 px-4 py-2 border border-emerald-600 text-emerald-600 text-sm font-medium rounded-lg hover:bg-emerald-50 transition-colors">
                Label QR
            </a>
            <a href="{{ route('alat.kartu') }}" class="inline-flex items-center gap-2 px-4 py-2 border border-green-600 text-green-600 text-sm font-medium rounded-lg hover:bg-green-50 transition-colors">
                Kartu Kendali
            </a>
            <a href="{{ route('alat.create') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700 transition-colors">
                Tambah Alat
            </a>
        </div>
    </div>

    <form action="{{ route('alat.index') }}" method="GET" class="bg-white rounded-xl border border-gray-200 p-4 flex flex-col sm:flex-row gap-3 items-end">
        <div class="flex-1">
            <label class="block text-sm font-medium text-gray-700 mb-1">Laboratorium</label>
            <select name="laboratory_id" class="w-full rounded-lg border-gray-300 border px-3 py-2 text-sm">
                <option value="">Semua</option>
                <option value="all" {{ request('laboratory_id') === 'all' ? 'selected' : '' }}>Semua Laboratorium</option>
                @foreach($laboratories as $lab)
                    <option value="{{ $lab->id }}" {{ request('laboratory_id') == $lab->id ? 'selected' : '' }}>{{ $lab->name }}</option>
                @endforeach
            </select>
        </div>
        <button type="submit" class="px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700">Tampilkan</button>
    </form>

    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <table class="w-full text-sm table-responsive-cards">
            <thead>
                <tr class="border-b border-gray-100">
                    <th class="text-left px-4 py-3 font-medium text-gray-500">No</th>
                    <th class="text-left px-4 py-3 font-medium text-gray-500">Nama Alat</th>
                    <th class="text-left px-4 py-3 font-medium text-gray-500">No Inventaris</th>
                    <th class="text-left px-4 py-3 font-medium text-gray-500">Merk/Type</th>
                    <th class="text-left px-4 py-3 font-medium text-gray-500">Laboratorium</th>
                    <th class="text-left px-4 py-3 font-medium text-gray-500">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($alat as $index => $item)
                    <tr class="border-b border-gray-50 hover:bg-gray-50">
                        <td class="px-4 py-3">{{ $alat->firstItem() + $index }}</td>
                        <td class="px-4 py-3 font-medium">{{ $item->name }}</td>
                        <td class="px-4 py-3">{{ $item->code ?? '-' }}</td>
                        <td class="px-4 py-3">{{ $item->merk_type ?? '-' }}</td>
                        <td class="px-4 py-3">{{ $item->laboratory->name ?? '-' }}</td>
                        <td class="px-4 py-3" data-label="Aksi">
                            <div class="flex items-center gap-1 flex-wrap">
                                <a href="{{ route('alat.checks', $item) }}" class="p-1.5 text-green-600 hover:bg-green-50 rounded-lg transition-colors" title="Kartu Kendali">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                    </svg>
                                </a>
                                <a href="{{ route('alat.show', $item) }}" class="p-1.5 text-blue-600 hover:bg-blue-50 rounded-lg transition-colors" title="Detail">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    </svg>
                                </a>
                                <a href="{{ route('alat.edit', $item) }}" class="p-1.5 text-amber-600 hover:bg-amber-50 rounded-lg transition-colors" title="Ubah">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125" />
                                    </svg>
                                </a>
                                <form action="{{ route('alat.destroy', $item) }}" method="POST" onsubmit="return confirm('Hapus alat ini?')" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-1.5 text-red-600 hover:bg-red-50 rounded-lg transition-colors" title="Hapus">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                                        </svg>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-8 text-center text-gray-500">Belum ada data alat.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div>{{ $alat->links() }}</div>
</div>
@endsection
