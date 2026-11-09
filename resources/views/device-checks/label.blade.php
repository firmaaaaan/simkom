@extends('layouts.app')

@section('title', 'Label Pengecekan Perangkat')
@section('header', 'Label Pengecekan Perangkat')

@section('content')
<div class="max-w-3xl mx-auto">
    <div class="flex flex-wrap items-center gap-3 mb-6">
        <a href="{{ route('device-checks.show', $deviceCheck) }}" class="text-sm text-gray-600 hover:text-green-600 transition-colors">
            &larr; Kembali
        </a>
        <button type="button" onclick="window.print()"
            class="no-print inline-flex items-center gap-2 px-4 py-2 border border-green-600 text-green-600 text-sm font-medium rounded-lg hover:bg-green-50 transition-colors">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0110.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0l.229 2.523a1.125 1.125 0 01-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0021 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 00-1.913-.247M6.34 18H5.25A2.25 2.25 0 013 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 011.913-.247m10.5 0a48.536 48.536 0 00-10.5 0m10.5 0V3.375c0-.621-.504-1.125-1.125-1.125h-8.25c-.621 0-1.125.504-1.125 1.125v3.659" />
            </svg>
            Cetak / PDF
        </button>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 p-6 mb-6">
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
            <div>
                <p class="text-xs text-gray-400 mb-1">Laboratorium</p>
                <p class="text-sm font-semibold text-gray-800">{{ $deviceCheck->laboratory->name ?? '-' }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-400 mb-1">Tahun Ajaran</p>
                <p class="text-sm font-semibold text-gray-800">{{ $deviceCheck->academicYear->name ?? '-' }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-400 mb-1">Tanggal</p>
                <p class="text-sm font-semibold text-gray-800">{{ \Carbon\Carbon::parse($deviceCheck->check_date)->translatedFormat('d M Y') }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-400 mb-1">Petugas</p>
                <p class="text-sm font-semibold text-gray-800">{{ $deviceCheck->officer_name ?? '-' }}</p>
            </div>
        </div>
    </div>

    @php
        $items = $deviceCheck->items()->with('computer')->get();
        $groups = $items->groupBy(fn ($item) => $item->computer_id);
    @endphp

    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden mb-6">
        <div class="overflow-x-auto">
            <table class="w-full text-sm border-collapse">
                <thead>
                    <tr class="bg-gray-50">
                        <th class="text-left px-4 py-2 font-medium text-gray-600 border border-gray-200">No</th>
                        <th class="text-left px-4 py-2 font-medium text-gray-600 border border-gray-200">Komputer</th>
                        <th class="text-left px-4 py-2 font-medium text-gray-600 border border-gray-200">Item</th>
                        <th class="text-center px-4 py-2 font-medium text-gray-600 border border-gray-200">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($groups as $computerId => $computerItems)
                        @php $computer = $computerItems->first()?->computer; @endphp
                        @foreach($computerItems as $idx => $item)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-2 border border-gray-200 text-center text-gray-500 {{ $idx === 0 ? 'font-medium text-gray-800' : '' }}">
                                    {{ $idx + 1 }}
                                </td>
                                <td class="px-4 py-2 border border-gray-200 text-gray-800 {{ $idx === 0 ? 'font-semibold' : '' }}">
                                    {{ $computer?->code ?? '-' }}
                                    @if($idx === 0 && $computer?->name)
                                        <br>
                                        <span class="text-xs text-gray-500">{{ $computer->name }}</span>
                                    @endif
                                </td>
                                <td class="px-4 py-2 border border-gray-200 text-gray-800">{{ $item->label ?? $item->item_key }}</td>
                                <td class="px-4 py-2 border border-gray-200 text-center">
                                    @php
                                        $statusLabel = match ($item->status) {
                                            \App\Models\DeviceCheckItem::STATUS_BERFUNGSI => 'Berfungsi',
                                            \App\Models\DeviceCheckItem::STATUS_RUSAK => 'Rusak',
                                            \App\Models\DeviceCheckItem::STATUS_TIDAK_ADA => 'Tidak Ada',
                                            default => '-',
                                        };
                                    @endphp
                                    <span class="inline-flex items-center justify-center w-6 h-6 rounded text-xs font-bold
                                        {{ $item->status === \App\Models\DeviceCheckItem::STATUS_BERFUNGSI ? 'bg-green-100 text-green-700' : ($item->status === \App\Models\DeviceCheckItem::STATUS_RUSAK ? 'bg-red-100 text-red-700' : 'bg-gray-100 text-gray-600') }}">
                                        {{ $statusLabel }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 p-6">
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-8 mt-2">
            <div>
                <p class="text-sm text-gray-700 mb-4">Petugas Pengecekan,</p>
                <div class="border-b-2 border-gray-300 pb-2"></div>
                <p class="text-xs text-gray-500 mt-1">{{ $deviceCheck->officer_name ?? '-' }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-700 mb-4">Laboratorium,</p>
                <div class="border-b-2 border-gray-300 pb-2"></div>
                <p class="text-xs text-gray-500 mt-1">{{ $deviceCheck->laboratory->name ?? '-' }}</p>
            </div>
        </div>
    </div>

    <div class="mt-6 text-xs text-gray-500 text-center">
        Dicetak: {{ now()->translatedFormat('d M Y H:i') }}
    </div>
</div>

<style>
    @media print {
        .no-print { display: none !important; }
    }
</style>
@endsection
