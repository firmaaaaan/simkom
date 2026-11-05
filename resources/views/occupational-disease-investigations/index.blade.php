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
    </div>

    @if(session('success'))<div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg text-sm">{{ session('success') }}</div>@endif

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
