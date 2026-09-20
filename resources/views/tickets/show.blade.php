@extends('layouts.app')

@section('title', 'Detail Tiket')
@section('header', 'Detail Tiket Kendala')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    {{-- Back Link --}}
    <a href="{{ route('tickets.index') }}" class="inline-flex items-center gap-2 text-sm text-gray-600 hover:text-green-600 transition-colors">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"/>
        </svg>
        Kembali
    </a>

    {{-- Success Message --}}
    @if(session('success'))
        <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg text-sm">
            {{ session('success') }}
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Main Content --}}
        <div class="lg:col-span-2 space-y-6">
            {{-- Ticket Info --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <div class="flex items-start justify-between mb-4">
                    <div>
                        <h3 class="text-xl font-bold text-gray-900">{{ $ticket->title }}</h3>
                        <p class="text-sm text-gray-500 mt-1">Dibuat {{ $ticket->created_at->format('d M Y H:i') }}</p>
                    </div>
                    <div class="flex items-center gap-2">
                        @php
                            $statusColors = [
                                'Open' => 'bg-blue-100 text-blue-700 border-blue-200',
                                'In Progress' => 'bg-yellow-100 text-yellow-700 border-yellow-200',
                                'Resolved' => 'bg-green-100 text-green-700 border-green-200',
                                'Closed' => 'bg-gray-100 text-gray-500 border-gray-200',
                            ];
                        @endphp
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium border {{ $statusColors[$ticket->status] ?? '' }}">
                            {{ $ticket->status }}
                        </span>
                    </div>
                </div>

                <div class="prose prose-sm max-w-none text-gray-700 mb-6">
                    {!! nl2br(e($ticket->description)) !!}
                </div>

                @if($ticket->images && count($ticket->images) > 0)
                    <div class="mb-6">
                        <p class="text-sm font-medium text-gray-700 mb-2">Foto Kendala</p>
                        <div class="flex flex-wrap gap-3">
                            @foreach($ticket->images as $image)
                                <a href="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($image) }}" target="_blank" class="block w-24 h-24 rounded-lg overflow-hidden border border-gray-200 hover:ring-2 hover:ring-green-500 transition-all">
                                    <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($image) }}" alt="Foto kendala" class="w-full h-full object-cover">
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif

                <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 pt-4 border-t border-gray-100">
                    <div>
                        <p class="text-xs text-gray-500 mb-1">Kategori</p>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                            {{ $ticket->category }}
                        </span>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 mb-1">Prioritas</p>
                        @php
                            $priorityColors = [
                                'Rendah' => 'bg-gray-100 text-gray-700',
                                'Sedang' => 'bg-blue-100 text-blue-700',
                                'Tinggi' => 'bg-orange-100 text-orange-700',
                                'Darurat' => 'bg-red-100 text-red-700',
                            ];
                        @endphp
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $priorityColors[$ticket->priority] ?? '' }}">
                            {{ $ticket->priority }}
                        </span>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 mb-1">Laboratorium</p>
                        <p class="text-sm font-medium text-gray-900">{{ $ticket->laboratory->name ?? '-' }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 mb-1">Komputer</p>
                        <p class="text-sm font-medium text-gray-900">{{ $ticket->computer->code ?? '-' }}</p>
                    </div>
                </div>
            </div>

            {{-- Comments --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <h4 class="text-lg font-semibold text-gray-900 mb-4">Komentar ({{ $ticket->comments->count() }})</h4>

                @if($ticket->comments->count() > 0)
                    <div class="space-y-4 mb-6">
                        @foreach($ticket->comments as $comment)
                            <div class="flex gap-3">
                                <div class="w-8 h-8 bg-green-100 text-green-700 rounded-full flex items-center justify-center text-sm font-semibold flex-shrink-0">
                                    {{ strtoupper(substr($comment->user->name ?? 'U', 0, 1)) }}
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center gap-2 mb-1">
                                        <span class="text-sm font-medium text-gray-900">{{ $comment->user->name ?? 'Unknown' }}</span>
                                        <span class="text-xs text-gray-400">{{ $comment->created_at->format('d M Y H:i') }}</span>
                                        @if($comment->user_id === auth()->id())
                                            <form onsubmit="return confirm('Hapus komentar ini?')" method="POST" class="inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" formaction="{{ route('tickets.delete-comment', [$ticket, $comment]) }}" class="text-xs text-red-400 hover:text-red-600 transition-colors">
                                                    Hapus
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                    <p class="text-sm text-gray-700">{{ $comment->message }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-sm text-gray-500 mb-6">Belum ada komentar.</p>
                @endif

                {{-- Add Comment --}}
                <form action="{{ route('tickets.add-comment', $ticket) }}" method="POST" class="border-t border-gray-100 pt-4">
                    @csrf
                    <div class="flex gap-3">
                        <div class="w-8 h-8 bg-green-600 text-white rounded-full flex items-center justify-center text-sm font-semibold flex-shrink-0">
                            {{ strtoupper(substr(auth()->user()->name ?? 'A', 0, 1)) }}
                        </div>
                        <div class="flex-1">
                            <textarea name="message" rows="2" required placeholder="Tulis komentar..." class="w-full px-4 py-2.5 text-sm border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"></textarea>
                            <div class="flex justify-end mt-2">
                                <button type="submit" class="px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700 transition-colors">
                                    Kirim
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        {{-- Sidebar --}}
        <div class="space-y-6">
            {{-- Status Update --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <h4 class="text-sm font-semibold text-gray-900 mb-4">Ubah Status</h4>
                <form action="{{ route('tickets.update-status', $ticket) }}" method="POST">
                    @csrf
                    @method('PATCH')
                    <div class="space-y-3">
                        @php
                            $statuses = ['Open', 'In Progress', 'Resolved', 'Closed'];
                        @endphp
                        @foreach($statuses as $status)
                            <label class="flex items-center gap-3 p-3 rounded-lg border cursor-pointer transition-colors {{ $ticket->status === $status ? 'border-green-500 bg-green-50' : 'border-gray-200 hover:border-gray-300' }}">
                                <input type="radio" name="status" value="{{ $status }}" {{ $ticket->status === $status ? 'checked' : '' }} class="text-green-600 focus:ring-green-500">
                                <span class="text-sm font-medium text-gray-700">{{ $status }}</span>
                            </label>
                        @endforeach
                    </div>
                    <button type="submit" class="w-full mt-4 px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700 transition-colors">
                        Perbarui Status
                    </button>
                </form>
            </div>

            {{-- Info --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <h4 class="text-sm font-semibold text-gray-900 mb-4">Informasi</h4>
                <div class="space-y-3 text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-500">Pelapor</span>
                        <span class="font-medium text-gray-900">{{ $ticket->reporter_name ?? $ticket->reporter->name ?? '-' }}</span>
                    </div>
                    @if($ticket->reporter_nim)
                        <div class="flex justify-between">
                            <span class="text-gray-500">NIM/NIDN</span>
                            <span class="font-medium text-gray-900">{{ $ticket->reporter_nim }}</span>
                        </div>
                    @endif
                    @if($ticket->reporter_prodi)
                        <div class="flex justify-between">
                            <span class="text-gray-500">Program Studi</span>
                            <span class="font-medium text-gray-900">{{ $ticket->reporter_prodi }}</span>
                        </div>
                    @endif
                    @if($ticket->tracking_code)
                        <div class="flex justify-between">
                            <span class="text-gray-500">Kode Tracking</span>
                            <span class="font-mono font-bold text-blue-600">{{ $ticket->tracking_code }}</span>
                        </div>
                    @endif
                    <div class="flex justify-between">
                        <span class="text-gray-500">Ditugaskan</span>
                        <span class="font-medium text-gray-900">{{ $ticket->assignee->name ?? '-' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">Tahun Ajaran</span>
                        <span class="font-medium text-gray-900">{{ $ticket->academicYear->name ?? '-' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">Dibuat</span>
                        <span class="font-medium text-gray-900">{{ $ticket->created_at->format('d M Y') }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">Diperbarui</span>
                        <span class="font-medium text-gray-900">{{ $ticket->updated_at->format('d M Y') }}</span>
                    </div>
                </div>
            </div>

            {{-- Actions --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <h4 class="text-sm font-semibold text-gray-900 mb-4">Aksi</h4>
                <div class="space-y-2">
                    <a href="{{ route('tickets.edit', $ticket) }}" class="flex items-center gap-2 w-full px-4 py-2.5 bg-blue-50 text-blue-700 text-sm font-medium rounded-lg hover:bg-blue-100 transition-colors">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125"/>
                        </svg>
                        Edit Tiket
                    </a>
                    <form onsubmit="return confirm('Hapus tiket ini secara permanen?')" method="POST">
                        @csrf
                        @method('DELETE')
                        <button type="submit" formaction="{{ route('tickets.destroy', $ticket) }}" class="flex items-center gap-2 w-full px-4 py-2.5 bg-red-50 text-red-700 text-sm font-medium rounded-lg hover:bg-red-100 transition-colors">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0"/>
                            </svg>
                            Hapus Tiket
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
