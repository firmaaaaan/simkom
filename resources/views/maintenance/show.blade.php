@extends('layouts.app')

@section('title', 'Detail Pemeliharaan')
@section('header', 'Detail Pemeliharaan')

@section('content')
<div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
    <a href="{{ route('maintenance.index') }}" class="inline-flex items-center gap-1.5 text-sm text-gray-500 hover:text-green-600 transition-colors">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
        </svg>
        Kembali
    </a>
    <div class="flex items-center gap-2">
        <a href="{{ route('maintenance.edit', $maintenance) }}" class="no-print inline-flex items-center gap-2 px-4 py-2 border border-gray-300 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-50 transition-colors">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125" />
            </svg>
            Edit
        </a>
        <a href="{{ route('maintenance.print', $maintenance) }}" target="_blank" class="no-print inline-flex items-center gap-2 px-4 py-2 border border-green-600 text-green-600 text-sm font-medium rounded-lg hover:bg-green-50 transition-colors">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0110.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0l.229 2.523a1.125 1.125 0 01-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0021 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 00-1.913-.247M6.34 18H5.25A2.25 2.25 0 013 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 011.913-.247m10.5 0a48.536 48.536 0 00-10.5 0m10.5 0V3.375c0-.621-.504-1.125-1.125-1.125h-8.25c-.621 0-1.125.504-1.125 1.125v3.659" />
            </svg>
            Cetak
        </a>
        <form action="{{ route('maintenance.destroy', $maintenance) }}" method="POST" class="no-print" onsubmit="return confirm('Yakin ingin menghapus data pemeliharaan ini?')">
            @csrf
            @method('DELETE')
            <button type="submit" class="inline-flex items-center gap-2 px-4 py-2 border border-red-300 text-red-600 text-sm font-medium rounded-lg hover:bg-red-50 transition-colors">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                </svg>
                Hapus
            </button>
        </form>
    </div>
</div>

<div class="bg-white rounded-xl border border-gray-200 p-6 mb-6">
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        <div>
            <p class="text-xs text-gray-400 mb-1">Laboratorium</p>
            <p class="text-sm font-semibold text-gray-800">{{ $maintenance->laboratory->name ?? '-' }}</p>
        </div>
        <div>
            <p class="text-xs text-gray-400 mb-1">Tahun Ajaran</p>
            <p class="text-sm font-semibold text-gray-800">{{ $maintenance->academicYear->name ?? '-' }}</p>
        </div>
        <div>
            <p class="text-xs text-gray-400 mb-1">Tanggal</p>
            <p class="text-sm font-semibold text-gray-800">{{ \Carbon\Carbon::parse($maintenance->maintenance_date)->format('d M Y') }}</p>
        </div>
        <div>
            <p class="text-xs text-gray-400 mb-1">Petugas</p>
            <p class="text-sm font-semibold text-gray-800">{{ $maintenance->inspector_name ?? '-' }}</p>
        </div>
    </div>
</div>

<div class="bg-white rounded-xl border border-gray-200 overflow-hidden mb-6">
    <div class="overflow-x-auto">
        <table class="w-full text-sm border-collapse">
            <thead>
                <tr class="bg-gray-50">
                    <th class="text-left px-4 py-2 font-medium text-gray-600 border border-gray-200 w-10">No</th>
                    <th class="text-left px-4 py-2 font-medium text-gray-600 border border-gray-200 min-w-[300px]">Item</th>
                    @foreach($computers as $computer)
                        <th class="text-center px-2 py-2 font-medium text-gray-600 border border-gray-200 w-14 text-xs">
                            {{ $computer->code }}
                        </th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @foreach($checklistItems as $category => $categoryData)
                    <tr class="bg-green-50">
                        <td colspan="{{ 2 + $computers->count() }}" class="px-4 py-2 font-bold text-gray-800 border border-gray-200">
                            {{ $category }}. {{ $categoryData['name'] }}
                        </td>
                    </tr>
                    @foreach($categoryData['items'] as $itemIndex => $question)
                        @php $itemNum = $itemIndex + 1; @endphp
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-2 text-gray-500 border border-gray-200 text-center">{{ $itemNum }}</td>
                            <td class="px-4 py-2 text-gray-800 border border-gray-200">{{ $question }}</td>
                            @foreach($computers as $computer)
                                @php $key = "{$category}_{$itemNum}_{$computer->id}"; @endphp
                                <td class="text-center border border-gray-200">
                                    @if(isset($items[$key]) && $items[$key]->is_checked)
                                        <span class="inline-flex items-center justify-center w-5 h-5 rounded bg-green-100">
                                            <svg class="w-3 h-3 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                                            </svg>
                                        </span>
                                    @else
                                        <span class="inline-flex items-center justify-center w-5 h-5 rounded bg-red-100">
                                            <svg class="w-3 h-3 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                            </svg>
                                        </span>
                                    @endif
                                </td>
                            @endforeach
                        </tr>
                    @endforeach
                @endforeach
            </tbody>
        </table>
    </div>
</div>

@if($maintenance->notes_computer || $maintenance->notes_mouse_keyboard || $maintenance->notes_ups || $maintenance->notes_monitor)
    <div class="bg-white rounded-xl border border-gray-200 p-6">
        <h2 class="text-lg font-bold text-gray-800 mb-4">Catatan Lain</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            @if($maintenance->notes_computer)
                <div class="p-3 bg-gray-50 rounded-lg">
                    <p class="text-xs font-medium text-gray-500 mb-1">Pemeriksaan Komputer</p>
                    <p class="text-sm text-gray-800">{{ $maintenance->notes_computer }}</p>
                </div>
            @endif
            @if($maintenance->notes_mouse_keyboard)
                <div class="p-3 bg-gray-50 rounded-lg">
                    <p class="text-xs font-medium text-gray-500 mb-1">Pemeriksaan Mouse dan Keyboard</p>
                    <p class="text-sm text-gray-800">{{ $maintenance->notes_mouse_keyboard }}</p>
                </div>
            @endif
            @if($maintenance->notes_ups)
                <div class="p-3 bg-gray-50 rounded-lg">
                    <p class="text-xs font-medium text-gray-500 mb-1">Pemeriksaan UPS</p>
                    <p class="text-sm text-gray-800">{{ $maintenance->notes_ups }}</p>
                </div>
            @endif
            @if($maintenance->notes_monitor)
                <div class="p-3 bg-gray-50 rounded-lg">
                    <p class="text-xs font-medium text-gray-500 mb-1">Pemeriksaan Monitor</p>
                    <p class="text-sm text-gray-800">{{ $maintenance->notes_monitor }}</p>
                </div>
            @endif
        </div>
    </div>
@endif
@endsection
