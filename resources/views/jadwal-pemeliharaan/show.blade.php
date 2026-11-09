@extends('layouts.app')

@section('title', 'Detail Jadwal Pemeliharaan')
@section('header', 'Detail Jadwal Pemeliharaan')

@section('content')
<div class="max-w-6xl mx-auto space-y-6">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3">
        <a href="{{ route('jadwal-pemeliharaan.index') }}" class="inline-flex items-center gap-2 text-sm text-gray-600 hover:text-green-600 transition-colors">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"/>
            </svg>
            Kembali
        </a>
        <div class="flex items-center gap-2">
            @if(auth()->user()->hasPermission('print-jadwal-pemeliharaan'))
            <a href="{{ route('jadwal-pemeliharaan.print', $jadwalPemeliharaan) }}" target="_blank" class="inline-flex items-center gap-2 px-4 py-2 border border-gray-300 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-50 transition-colors">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0110.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0l.229 2.523a1.125 1.125 0 01-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0021 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 00-1.913-.247M6.34 18H5.25A2.25 2.25 0 013 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 011.913-.247m10.5 0a48.536 48.536 0 00-10.5 0m10.5 0V3.375c0-.621-.504-1.125-1.125-1.125h-8.25c-.621 0-1.125.504-1.125 1.125v3.659" />
                </svg>
                Cetak FM-02
            </a>
            @endif
            @if(auth()->user()->hasPermission('edit-jadwal-pemeliharaan'))
            <a href="{{ route('jadwal-pemeliharaan.edit', $jadwalPemeliharaan) }}" class="inline-flex items-center gap-2 px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700 transition-colors">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125" />
                </svg>
                Ubah
            </a>
            @endif
            @if(auth()->user()->hasPermission('delete-jadwal-pemeliharaan'))
            <form action="{{ route('jadwal-pemeliharaan.destroy', $jadwalPemeliharaan) }}" method="POST" class="inline" onsubmit="return confirm('Hapus jadwal ini?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="inline-flex items-center gap-2 px-4 py-2 bg-red-600 text-white text-sm font-medium rounded-lg hover:bg-red-700 transition-colors">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                    </svg>
                    Hapus
                </button>
            </form>
            @endif
        </div>
    </div>

    @if(session('success'))
        <div class="px-4 py-3 bg-green-50 border border-green-200 text-green-700 rounded-lg text-sm">
            {{ session('success') }}
        </div>
    @endif

    <div class="bg-white rounded-xl border border-gray-200 p-6">
        <h2 class="text-base font-semibold text-gray-800 mb-1">Jadwal Pemeliharaan Alat dan Bahan Laboratorium</h2>
        <p class="text-xs text-gray-500 mb-4">No. Dokumen: {{ $jadwalPemeliharaan->kode_dokumen }}</p>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
            <p><span class="font-medium text-gray-500">Laboratorium/Unit:</span> <span class="text-gray-900 font-medium">{{ $jadwalPemeliharaan->nama_lab }}</span></p>
            <p><span class="font-medium text-gray-500">Periode:</span> <span class="text-gray-900">{{ $jadwalPemeliharaan->periode_label }}</span></p>
            <p><span class="font-medium text-gray-500">PIC/Petugas:</span> <span class="text-gray-900">{{ $jadwalPemeliharaan->pic }}</span></p>
            <p><span class="font-medium text-gray-500">Tanggal Penyusunan:</span> <span class="text-gray-900">{{ $jadwalPemeliharaan->tanggal_penyusunan->translatedFormat('d F Y') }}</span></p>
            <p><span class="font-medium text-gray-500">Disusun:</span> <span class="text-gray-900">{{ $jadwalPemeliharaan->disusun_oleh ?? $jadwalPemeliharaan->pic }}</span></p>
            <p><span class="font-medium text-gray-500">Dibuat oleh:</span> <span class="text-gray-900">{{ $jadwalPemeliharaan->creator?->name ?? '-' }}</span></p>
        </div>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
            <h3 class="text-base font-semibold text-gray-800">Rincian ({{ $jadwalPemeliharaan->items->count() }} item)</h3>
            @php $counts = $jadwalPemeliharaan->items->countBy('status'); @endphp
            <div class="flex flex-wrap gap-2">
                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-700">{{ $counts['Terjadwal'] ?? 0 }} Terjadwal</span>
                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-700">{{ $counts['Selesai'] ?? 0 }} Selesai</span>
                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-700">{{ $counts['Tunda'] ?? 0 }} Tunda</span>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-3 py-2 text-center font-medium text-gray-500 w-10">No</th>
                        <th class="px-3 py-2 text-left font-medium text-gray-500">Nama Alat/Bahan</th>
                        <th class="px-3 py-2 text-left font-medium text-gray-500">Kode/No. Inv.</th>
                        <th class="px-3 py-2 text-left font-medium text-gray-500">Lokasi</th>
                        <th class="px-3 py-2 text-left font-medium text-gray-500">Jenis</th>
                        <th class="px-3 py-2 text-left font-medium text-gray-500">Frekuensi</th>
                        <th class="px-3 py-2 text-left font-medium text-gray-500">Jadwal</th>
                        <th class="px-3 py-2 text-left font-medium text-gray-500">PIC</th>
                        <th class="px-3 py-2 text-left font-medium text-gray-500">Status</th>
                        <th class="px-3 py-2 text-left font-medium text-gray-500">Catatan/Hasil</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($jadwalPemeliharaan->items as $item)
                        <tr class="hover:bg-gray-50">
                            <td class="px-3 py-2 text-center text-gray-500">{{ $loop->iteration }}</td>
                            <td class="px-3 py-2 font-medium text-gray-900">{{ $item->nama_alat_bahan }}</td>
                            <td class="px-3 py-2">
                                @if(count($item->kode_list))
                                    <div class="flex flex-col items-start gap-1">
                                        @foreach($item->kode_list as $code)
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full bg-gray-100 text-gray-700 text-[11px] font-medium">{{ $code }}</span>
                                        @endforeach
                                        <span class="text-[10px] text-gray-400">{{ count($item->kode_list) }} kode</span>
                                    </div>
                                @else
                                    <span class="text-gray-400">-</span>
                                @endif
                            </td>
                            <td class="px-3 py-2 text-gray-600">{{ $item->lokasi ?? '-' }}</td>
                            <td class="px-3 py-2 text-gray-600">{{ $item->jenis_pemeliharaan ?? '-' }}</td>
                            <td class="px-3 py-2 text-gray-600">{{ $item->frekuensi ?? '-' }}</td>
                            <td class="px-3 py-2 text-gray-600">{{ $item->jadwal?->translatedFormat('d/m/Y') ?? '-' }}</td>
                            <td class="px-3 py-2 text-gray-600">{{ $item->pic ?? '-' }}</td>
                            <td class="px-3 py-2"><span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ \App\Models\JadwalPemeliharaanItem::statusBadge($item->status) }}">{{ $item->status }}</span></td>
                            <td class="px-3 py-2 text-gray-600">{{ $item->catatan_hasil ?? '-' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
