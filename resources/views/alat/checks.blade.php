@extends('layouts.app')

@section('title', 'Kartu Kendali - '.$alat->name)
@section('header', 'Kartu Kendali: '.$alat->name)

@section('content')
<div class="space-y-6">
    @if(session('success'))
        <div class="px-4 py-3 bg-green-50 border border-green-200 text-green-700 rounded-lg text-sm flex items-center gap-2">
            <svg class="w-5 h-5 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            {{ session('success') }}
        </div>
    @endif

    {{-- Identitas alat --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-8 gap-y-2 text-sm">
                <div><span class="font-semibold text-gray-700">Nama Alat</span><span class="text-gray-500 mx-2">:</span>{{ $alat->name }}</div>
                <div><span class="font-semibold text-gray-700">No Inventaris</span><span class="text-gray-500 mx-2">:</span>{{ $alat->code ?? '-' }}</div>
                <div><span class="font-semibold text-gray-700">Merk/Type</span><span class="text-gray-500 mx-2">:</span>{{ $alat->merk_type ?? '-' }}</div>
                <div><span class="font-semibold text-gray-700">Laboratorium</span><span class="text-gray-500 mx-2">:</span>{{ $alat->laboratory->name ?? '-' }}</div>
            </div>
            <div class="flex items-center gap-2 shrink-0">
                <a href="{{ route('alat.print-one', $alat) }}" target="_blank"
                    class="inline-flex items-center gap-2 px-4 py-2 border border-green-600 text-green-600 text-sm font-medium rounded-lg hover:bg-green-50 transition-colors">
                    Cetak Kartu
                </a>
                <a href="{{ route('alat.kartu') }}" class="px-4 py-2 text-sm text-gray-600 hover:underline">Kembali</a>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Form input entri baru --}}
        <div class="lg:col-span-1">
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <h2 class="text-base font-semibold text-gray-800 mb-4">Input Entri Baru</h2>
                <form action="{{ route('alat.checks.store', $alat) }}" method="POST" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal</label>
                        <input type="date" name="check_date" value="{{ old('check_date', $defaultDate) }}" required
                            class="w-full rounded-lg border-gray-300 border px-3 py-2 text-sm focus:ring-green-500 focus:border-green-500">
                        @error('check_date') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Fungsi</label>
                        <div class="flex gap-4 pt-1">
                            @foreach($statusList as $status)
                                <label class="inline-flex items-center gap-2 text-sm text-gray-700">
                                    <input type="radio" name="status" value="{{ $status }}"
                                        {{ old('status', 'Baik') === $status ? 'checked' : '' }} required
                                        class="text-green-600 focus:ring-green-500">
                                    {{ $status }}
                                </label>
                            @endforeach
                        </div>
                        @error('status') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Keterangan/Paraf</label>
                        <input type="text" name="notes" value="{{ old('notes') }}" maxlength="255"
                            placeholder="cth: oke, Tutup pecah 1"
                            class="w-full rounded-lg border-gray-300 border px-3 py-2 text-sm focus:ring-green-500 focus:border-green-500">
                        @error('notes') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <button type="submit"
                        class="w-full px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700 transition-colors">
                        Simpan Entri
                    </button>
                </form>
            </div>
        </div>

        {{-- Riwayat --}}
        <div class="lg:col-span-2">
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
                    <h2 class="text-base font-semibold text-gray-800">Riwayat Kartu Kendali</h2>
                    <span class="text-sm text-gray-500">{{ $checks->count() }} entri</span>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm table-responsive-cards">
                        <thead>
                            <tr class="bg-green-600 text-white">
                                <th class="px-4 py-3 text-center w-12">No</th>
                                <th class="px-4 py-3 text-left">Tanggal</th>
                                <th class="px-4 py-3 text-center">Baik</th>
                                <th class="px-4 py-3 text-center">Rusak</th>
                                <th class="px-4 py-3 text-left">Keterangan/Paraf</th>
                                <th class="px-4 py-3 text-left">Petugas</th>
                                <th class="px-4 py-3 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($checks as $index => $check)
                                <tr class="hover:bg-gray-50 transition-colors">
                                    <td class="px-4 py-3 text-center text-gray-600">{{ $index + 1 }}</td>
                                    <td class="px-4 py-3 text-gray-900 whitespace-nowrap">{{ $check->check_date->translatedFormat('d F Y') }}</td>
                                    <td class="px-4 py-3 text-center">
                                        @if($check->status === 'Baik')
                                            <span class="inline-flex items-center justify-center w-5 h-5 rounded border-2 border-green-500 bg-green-500">
                                                <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="3">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/>
                                                </svg>
                                            </span>
                                        @else
                                            <span class="inline-flex items-center justify-center w-5 h-5 rounded border border-gray-300 bg-white"></span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        @if($check->status === 'Rusak')
                                            <span class="inline-flex items-center justify-center w-5 h-5 rounded border-2 border-red-500 bg-red-500">
                                                <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="3">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/>
                                                </svg>
                                            </span>
                                        @else
                                            <span class="inline-flex items-center justify-center w-5 h-5 rounded border border-gray-300 bg-white"></span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-gray-600">{{ $check->notes ?? '-' }}</td>
                                    <td class="px-4 py-3 text-gray-900">{{ $check->checkedBy->name ?? '-' }}</td>
                                    <td class="px-4 py-3 text-center">
                                        <form action="{{ route('alat.checks.destroy', [$alat, $check]) }}" method="POST" onsubmit="return confirm('Hapus entri ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:underline text-sm">Hapus</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-4 py-10 text-center text-gray-500">
                                        Belum ada riwayat kartu kendali untuk alat ini
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
