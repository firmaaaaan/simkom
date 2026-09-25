@extends('layouts.app')

@section('title', 'Kelola Denah Lab')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Kelola Denah Lab</h1>
            <p class="text-sm text-gray-500 mt-1">Kelola layout denah laboratorium (drag & drop, rotasi komputer)</p>
        </div>
        <a href="{{ route('lab-layouts.create') }}"
           class="inline-flex items-center gap-2 px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700 transition-colors">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
            </svg>
            Buat Layout Baru
        </a>
    </div>

    {{-- Flash messages --}}
    @if(session('success'))
        <div class="mb-4 p-4 bg-green-50 border border-green-200 rounded-lg text-green-800">
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-lg text-red-800">
            {{ session('error') }}
        </div>
    @endif

    {{-- Tabs Draft / Published --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="border-b border-gray-200">
            <nav class="flex -mb-px" aria-label="Tabs">
                <button type="button"
                        class="tab-btn px-6 py-3 text-sm font-medium border-b-2 transition-colors
                               {{ request()->get('tab', 'drafts') === 'drafts' ? 'border-green-600 text-green-600' : 'border-transparent text-gray-500 hover:text-gray-700' }}"
                        onclick="window.location='{{ route('lab-layouts.index', ['tab' => 'drafts']) }}'">
                    Draft
                    <span class="ml-2 inline-flex items-center justify-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                        {{ $layouts->filter(fn($l) => $l->is_draft && !$l->is_published)->count() }}
                    </span>
                </button>
                <button type="button"
                        class="tab-btn px-6 py-3 text-sm font-medium border-b-2 transition-colors
                               {{ request()->get('tab', 'drafts') === 'published' ? 'border-green-600 text-green-600' : 'border-transparent text-gray-500 hover:text-gray-700' }}"
                        onclick="window.location='{{ route('lab-layouts.index', ['tab' => 'published']) }}'">
                    Published
                    <span class="ml-2 inline-flex items-center justify-center px-2 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                        {{ $layouts->filter(fn($l) => $l->is_published)->count() }}
                    </span>
                </button>
            </nav>
        </div>

        <div class="p-4">
            @if($layouts->isEmpty())
                <div class="text-center py-12">
                    <svg class="w-12 h-12 text-gray-300 mx-auto mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9.75 3.104v5.714a2.25 2.25 0 01-.659 1.591L5 14.5M9.75 3.104c-.251.023-.501.05-.75.082m.75-.082a24.301 24.301 0 014.5 0m0 0v5.714a2.25 2.25 0 00.659 1.591L19 14.5m-4.25-11.396c.251.023.501.05.75.082M12 21a8.966 8.966 0 005.982-2.275M12 21a8.966 8.966 0 01-5.982-2.275M15.75 3.186a24.284 24.284 0 012.038.443M8.25 3.186a24.284 24.284 0 00-2.038.443M18 14.5l-6 6-6-6" />
                    </svg>
                    <p class="text-gray-500">Belum ada layout denah.</p>
                    <a href="{{ route('lab-layouts.create') }}" class="mt-4 inline-flex items-center gap-2 px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700 transition-colors">
                        Buat Layout Pertama
                    </a>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="text-left text-gray-500 border-b border-gray-200">
                                <th class="pb-3 font-medium">Nama Layout</th>
                                <th class="pb-3 font-medium">Laboratorium</th>
                                <th class="pb-3 font-medium">Grid</th>
                                <th class="pb-3 font-medium">Objek</th>
                                <th class="pb-3 font-medium">Status</th>
                                <th class="pb-3 font-medium">Diperbarui</th>
                                <th class="pb-3 font-medium text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($layouts as $layout)
                                @php
                                    $isCurrentTab = request()->get('tab', 'drafts');
                                    $isDraft = $layout->is_draft && !$layout->is_published;
                                    $isPublished = $layout->is_published;
                                    $showInTab = ($isCurrentTab === 'drafts' && $isDraft) || ($isCurrentTab === 'published' && $isPublished);
                                @endphp
                                @if($showInTab)
                                <tr class="hover:bg-gray-50">
                                    <td class="py-4 font-medium text-gray-900">{{ $layout->name }}</td>
                                    <td class="py-4 text-gray-600">{{ $layout->laboratory->name ?? '-' }} <span class="text-xs text-gray-400">({{ $layout->laboratory->code ?? '-' }})</span></td>
                                    <td class="py-4 text-gray-600">{{ $layout->grid_cols }} x {{ $layout->grid_rows }} ({{ $layout->cell_size }}px)</td>
                                    <td class="py-4 text-gray-600">{{ $layout->items->count() }}</td>
                                    <td class="py-4">
                                        @if($layout->is_published)
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">Published</span>
                                        @else
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">Draft</span>
                                        @endif
                                    </td>
                                    <td class="py-4 text-gray-500">{{ $layout->updated_at->format('d M Y H:i') }}</td>
                                    <td class="py-4 text-right">
                                        <div class="flex items-center justify-end gap-2">
                                            <a href="{{ route('lab-layouts.show', $layout) }}"
                                               class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium text-blue-600 hover:bg-blue-50 rounded-lg transition-colors"
                                               title="Preview">
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                                </svg>
                                                Lihat
                                            </a>
                                            <a href="{{ route('lab-layouts.edit', $layout) }}"
                                               class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium text-green-600 hover:bg-green-50 rounded-lg transition-colors"
                                               title="Edit">
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125" />
                                                </svg>
                                                Edit
                                            </a>
                                            @if(!$layout->is_published)
                                                <form action="{{ route('lab-layouts.publish', $layout) }}" method="POST" class="inline">
                                                    @csrf
                                                    <button type="submit"
                                                            class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium text-purple-600 hover:bg-purple-50 rounded-lg transition-colors"
                                                            title="Publikasikan"
                                                            onclick="return confirm('Publikasikan layout ini? Layout lain di laboratorium yang sama akan dinonaktifkan.')">
                                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z" />
                                                        </svg>
                                                        Publish
                                                    </button>
                                                </form>
                                            @endif
                                            <form action="{{ route('lab-layouts.duplicate', $layout) }}" method="POST" class="inline">
                                                @csrf
                                                <button type="submit"
                                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium text-gray-600 hover:bg-gray-50 rounded-lg transition-colors"
                                                        title="Duplikat">
                                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                                    </svg>
                                                    Copy
                                                </button>
                                            </form>
                                            @if(!$layout->is_published)
                                                <form action="{{ route('lab-layouts.destroy', $layout) }}" method="POST" class="inline" onsubmit="return confirm('Yakin hapus layout ini?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit"
                                                            class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium text-red-600 hover:bg-red-50 rounded-lg transition-colors"
                                                            title="Hapus">
                                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                                                        </svg>
                                                        Hapus
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                                @endif
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Pagination --}}
                @if($layouts->hasPages())
                    <div class="mt-4">
                        {{ $layouts->appends(['tab' => request()->get('tab', 'drafts')])->links() }}
                    </div>
                @endif
            @endif
        </div>
    </div>
</div>
@endsection