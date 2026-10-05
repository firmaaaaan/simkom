@extends('layouts.app')

@section('title', 'Notifikasi')
@section('header', 'Semua Notifikasi')

@section('content')
<div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
    <p class="text-sm text-gray-500">Semua notifikasi sistem — tiket, penggunaan lab, dan peminjaman</p>
    <div class="flex flex-wrap items-center gap-2">
        <form action="{{ route('notifications.read-all') }}" method="POST" class="inline">
            @csrf
            <button type="submit" class="inline-flex items-center gap-2 px-4 py-2 border border-green-600 text-green-600 text-sm font-medium rounded-lg hover:bg-green-50 transition-colors">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
                Tandai semua dibaca
            </button>
        </form>
        <form action="{{ route('notifications.destroy-all') }}" method="POST" class="inline" onsubmit="return confirm('Yakin ingin menghapus semua notifikasi?')">
            @csrf
            @method('DELETE')
            <button type="submit" class="inline-flex items-center gap-2 px-4 py-2 border border-red-600 text-red-600 text-sm font-medium rounded-lg hover:bg-red-50 transition-colors">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                </svg>
                Hapus semua
            </button>
        </form>
    </div>
</div>

@if(session('success'))
    <div class="mb-4 px-4 py-3 bg-green-50 border border-green-200 text-green-700 rounded-lg text-sm flex items-center gap-2">
        <svg class="w-5 h-5 text-green-600 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        {{ session('success') }}
    </div>
@endif

<div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
    <div class="px-6 py-4 border-b border-gray-100">
        <h2 class="text-base font-semibold text-gray-800">Daftar Notifikasi</h2>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm table-responsive-cards">
            <thead>
                <tr class="border-b border-gray-100">
                    <th class="text-left px-6 py-3 font-medium text-gray-500">No</th>
                    <th class="text-left px-6 py-3 font-medium text-gray-500">Status</th>
                    <th class="text-left px-6 py-3 font-medium text-gray-500">Judul</th>
                    <th class="text-left px-6 py-3 font-medium text-gray-500">Pesan</th>
                    <th class="text-left px-6 py-3 font-medium text-gray-500">Tipe</th>
                    <th class="text-left px-6 py-3 font-medium text-gray-500">Waktu</th>
                    <th class="text-left px-6 py-3 font-medium text-gray-500">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $typeLabels = ['ticket' => 'Tiket', 'lab_usage' => 'Penggunaan Lab', 'borrowing' => 'Peminjaman', 'box_usage' => 'Peminjaman Box'];
                @endphp
                @forelse($notifications as $index => $notification)
                    <tr class="border-b border-gray-50 hover:bg-gray-50 transition-colors {{ $notification->is_unread ? 'bg-green-50/40' : '' }}">
                        <td data-label="No" class="px-6 py-4 text-gray-500">{{ $notifications->firstItem() + $index }}</td>
                        <td data-label="Status" class="px-6 py-4">
                            @if($notification->is_unread)
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    <span class="w-1.5 h-1.5 bg-green-500 rounded-full"></span> Baru
                                </span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-600">Dibaca</span>
                            @endif
                        </td>
                        <td data-label="Judul" class="px-6 py-4 font-medium text-gray-800">
                            @if($notification->url)
                                <a href="{{ $notification->url }}" class="hover:text-green-600 transition-colors {{ $notification->is_unread ? 'font-semibold' : '' }}">{{ $notification->title }}</a>
                            @else
                                {{ $notification->title }}
                            @endif
                        </td>
                        <td data-label="Pesan" class="px-6 py-4 text-gray-600">{{ $notification->message }}</td>
                        <td data-label="Tipe" class="px-6 py-4 text-gray-600">{{ $typeLabels[$notification->type] ?? ucfirst($notification->type) }}</td>
                        <td data-label="Waktu" class="px-6 py-4 text-gray-500">{{ $notification->created_at->format('d M Y H:i') }}</td>
                        <td data-label="Aksi" class="px-6 py-4">
                            <form action="{{ route('notifications.destroy', $notification) }}" method="POST" class="inline" onsubmit="return confirm('Yakin ingin menghapus notifikasi ini?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="p-1.5 rounded-lg hover:bg-red-50 transition-colors" title="Hapus">
                                    <svg class="w-4 h-4 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                                    </svg>
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-6 py-12 text-center">
                            <svg class="w-12 h-12 text-gray-300 mx-auto mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0" />
                            </svg>
                            <p class="text-gray-500 text-sm">Tidak ada notifikasi</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($notifications->hasPages())
        <div class="px-6 py-3 border-t border-gray-100">
            {{ $notifications->links() }}
        </div>
    @endif
</div>
@endsection
