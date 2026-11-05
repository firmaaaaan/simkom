@extends('layouts.app')

@section('title', 'Penyelidikan Penyakit Akibat Kerja')
@section('header', 'Penyelidikan Penyakit Akibat Kerja')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <p class="text-sm text-gray-600">Formulir Penyelidikan Penyakit Akibat Kerja (PAK) (Form 008).</p>
        @if(auth()->user()->hasPermission('create-occupational-disease-investigations'))
        <a href="{{ route('occupational-disease-investigations.create') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700">Buat Penyelidikan PAK</a>
        @endif
        @if(auth()->user()->hasPermission('import-occupational-disease-investigations'))
        <a href="{{ route('occupational-disease-investigations.import') }}" class="inline-flex items-center gap-2 px-4 py-2 border border-blue-600 text-blue-600 text-sm font-medium rounded-lg hover:bg-blue-50 transition-colors">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5" />
            </svg>
            Import
        </a>
        @endif
    </div>

    @if(session('success'))<div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg text-sm">{{ session('success') }}</div>@endif

    @if(session('import_errors') && count(session('import_errors')) > 0)
    <div class="bg-amber-50 border border-amber-200 text-amber-800 px-4 py-3 rounded-lg text-sm">
        <p class="font-semibold mb-1">{{ count(session('import_errors')) }} baris saat import bermasalah:</p>
        <ul class="list-disc list-inside space-y-0.5">
            @foreach(session('import_errors') as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                    <th class="px-4 py-3 text-left font-medium text-gray-600">Kode</th>
                    <th class="px-4 py-3 text-left font-medium text-gray-600">Nama</th>
                    <th class="px-4 py-3 text-left font-medium text-gray-600">NIP</th>
                    <th class="px-4 py-3 text-left font-medium text-gray-600">Bagian/Jabatan</th>
                    <th class="px-4 py-3 text-center font-medium text-gray-600">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($investigations as $inv)
                <tr>
                    <td class="px-4 py-3"><a href="{{ route('occupational-disease-investigations.show', $inv) }}" class="text-green-700 font-medium">{{ $inv->kode }}</a></td>
                    <td class="px-4 py-3">{{ $inv->nama }}</td>
                    <td class="px-4 py-3">{{ $inv->nip ?: '-' }}</td>
                    <td class="px-4 py-3 text-xs text-gray-600">{{ $inv->bagian }} / {{ $inv->jabatan }}</td>
                    <td class="px-4 py-3 text-center">
                        <div class="flex items-center justify-center gap-1">
                            <a href="{{ route('occupational-disease-investigations.show', $inv) }}" class="p-1.5 text-blue-600 hover:bg-blue-50 rounded-lg">Lihat</a>
                            <a href="{{ route('occupational-disease-investigations.print', $inv) }}" target="_blank" class="p-1.5 text-gray-600 hover:bg-gray-100 rounded-lg">Cetak</a>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="5" class="px-4 py-10 text-center text-gray-500">Belum ada data</td></tr>
                @endforelse
            </tbody>
        </table>
        @if($investigations->hasPages())<div class="px-4 py-3 border-t border-gray-200">{{ $investigations->links() }}</div>@endif
    </div>
</div>
@endsection
