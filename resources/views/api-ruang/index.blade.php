@extends('layouts.app')

@section('title', 'Master Ruangan SB')
@section('header', 'Master Ruangan SB (API SIMPTT)')

@section('content')
<div class="space-y-6">
    @if(session('success'))
        <div class="px-4 py-3 bg-green-50 border border-green-200 text-green-700 rounded-lg text-sm">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="px-4 py-3 bg-red-50 border border-red-200 text-red-700 rounded-lg text-sm">{{ session('error') }}</div>
    @endif

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-base font-semibold text-gray-800">Sinkron Master Ruangan</h2>
            <p class="text-sm text-gray-500 mt-1">
                Data diambil dari API SIMPTT <code class="text-xs">{{ config('jadwal.ruang_api_url') }}</code> — hanya ruangan berawalan kode SB yang disimpan.
            </p>
        </div>
        @if(auth()->user()->hasPermission('edit-laboratories'))
            <form action="{{ route('api-ruang.sync') }}" method="POST">
                @csrf
                <button type="submit" class="px-6 py-2 bg-emerald-600 text-white text-sm font-medium rounded-lg hover:bg-emerald-700 transition-colors">Sinkronkan API SB</button>
            </form>
        @endif
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <table class="w-full text-sm table-responsive-cards">
            <thead>
                <tr class="bg-green-600 text-white">
                    <th class="px-4 py-3 text-center w-12">No</th>
                    <th class="px-4 py-3 text-left">Kode</th>
                    <th class="px-4 py-3 text-left">Nama Ruangan</th>
                    <th class="px-4 py-3 text-center">Kapasitas</th>
                    <th class="px-4 py-3 text-left">Kode API</th>
                    <th class="px-4 py-3 text-left">Data Asli</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($ruangs as $index => $ruang)
                    <tr>
                        <td class="px-4 py-3 text-center">{{ $ruangs->firstItem() + $index }}</td>
                        <td class="px-4 py-3 font-medium">{{ $ruang->kode }}</td>
                        <td class="px-4 py-3">{{ $ruang->nama }}</td>
                        <td class="px-4 py-3 text-center">{{ $ruang->kapasitas }}</td>
                        <td class="px-4 py-3">{{ $ruang->kdruang }}</td>
                        <td class="px-4 py-3 text-gray-500">{{ $ruang->ruang_asli }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-8 text-center text-gray-500">Belum ada data ruangan SB. Klik "Sinkronkan API SB" untuk mengambil data.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div>{{ $ruangs->links() }}</div>
</div>
@endsection
