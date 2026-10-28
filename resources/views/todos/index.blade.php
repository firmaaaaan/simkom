@extends('layouts.app')

@section('title', 'To-Do List')
@section('header', 'To-Do List')

@section('content')
<div x-data="kanban(@js($board), @js($labels), @js(url('/todos')))" x-cloak class="space-y-6">

    {{-- Toast --}}
    <template x-if="toast">
        <div class="fixed top-4 right-4 z-50 border px-4 py-3 rounded-lg text-sm shadow-lg max-w-sm"
             :class="toast.type === 'error' ? 'bg-red-50 border-red-200 text-red-700' : 'bg-green-50 border-green-200 text-green-700'"
             x-text="toast.message"></div>
    </template>

    {{-- Action Bar --}}
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <p class="text-sm text-gray-600">Kelola tugas pribadi Anda dengan papan Kanban. Seret kartu untuk memindahkannya antar kolom.</p>
        <div class="flex items-center gap-2 w-full sm:w-auto">
            <div class="relative flex-1 sm:flex-none">
                <svg class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                </svg>
                <input type="search" x-model="search" placeholder="Cari kartu..."
                       class="w-full sm:w-56 pl-9 pr-3 py-2 text-sm border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
            </div>
            <button type="button" @click="showColumnManager = true"
                    class="inline-flex items-center gap-1.5 px-3 py-2 bg-white border border-gray-200 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-50 transition-colors whitespace-nowrap">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z" />
                </svg>
                Kelola Kolom
            </button>
            <button type="button" @click="showLabelManager = true"
                    class="inline-flex items-center gap-1.5 px-3 py-2 bg-white border border-gray-200 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-50 transition-colors whitespace-nowrap">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9.568 3H5.25A2.25 2.25 0 003 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.33a18.095 18.095 0 005.223-5.223c.542-.827.369-1.908-.33-2.607L11.16 3.66A2.25 2.25 0 009.568 3z" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 6h.008v.008H6V6z" />
                </svg>
                Kelola Label
            </button>
        </div>
    </div>

    {{-- Board --}}
    <div class="flex gap-4 overflow-x-auto pb-4 items-start">
        <template x-for="(column, columnIndex) in columns" :key="column.id">
            <div class="w-72 shrink-0 bg-gray-100 rounded-xl p-3 flex flex-col max-h-[calc(100vh-14rem)]">
                {{-- Column Header --}}
                <div class="flex items-center justify-between mb-3 gap-2">
                    <div class="flex items-center gap-2 min-w-0">
                        <h3 class="font-semibold text-gray-800 text-sm truncate" x-text="column.name"></h3>
                        <span class="text-xs text-gray-500 bg-gray-200 rounded-full px-2 py-0.5 shrink-0"
                              x-text="column.tasks.length"></span>
                    </div>
                    <button type="button" @click.stop="deleteColumn(column)" title="Hapus kolom"
                            class="p-1 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded transition-colors shrink-0">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.165m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                        </svg>
                    </button>
                </div>

                {{-- Cards --}}
                <div class="kanban-sortable space-y-2 min-h-[80px] flex-1 overflow-y-auto pr-0.5"
                     :data-column-id="column.id">
                    <template x-for="task in columnTasks(column)" :key="task.id">
                        <div class="kanban-card bg-white rounded-lg shadow-sm border border-gray-200 p-3 cursor-pointer hover:shadow-md transition-shadow"
                             :data-task-id="task.id" @click="openTask(task.id)">
                            {{-- Label dots --}}
                            <div class="flex flex-wrap gap-1 mb-1.5" x-show="(task.labels || []).length > 0">
                                <template x-for="label in (task.labels || [])" :key="label.id">
                                    <span class="h-2 w-8 rounded-full" :style="`background:${label.color}`" :title="label.name"></span>
                                </template>
                            </div>

                            <p class="text-sm font-medium text-gray-900 leading-snug" x-text="task.title"></p>

                            {{-- Badges --}}
                            <div class="mt-2 flex flex-wrap items-center gap-x-2.5 gap-y-1 text-xs text-gray-500"
                                 x-show="hasBadges(task)">
                                <span x-show="task.due_date" class="inline-flex items-center gap-1"
                                      :class="isOverdue(task) ? 'text-red-600 font-medium' : ''">
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" />
                                    </svg>
                                    <span x-text="formatDate(task.due_date)"></span>
                                </span>
                                <span x-show="task.priority" class="inline-flex items-center gap-1">
                                    <span class="h-2 w-2 rounded-full" :class="priorityColor(task.priority)"></span>
                                    <span x-text="priorityLabel(task.priority)"></span>
                                </span>
                                <span x-show="task.checklist_total > 0" class="inline-flex items-center gap-1"
                                      :class="task.checklist_done === task.checklist_total ? 'text-green-600' : ''">
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    <span x-text="task.checklist_done + '/' + task.checklist_total"></span>
                                </span>
                                <span x-show="task.attachment_count > 0" class="inline-flex items-center gap-1">
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M18.375 12.739l-7.693 7.693a4.5 4.5 0 01-6.364-6.364l10.94-10.94A3 3 0 1119.5 7.372L8.552 18.32m.009-.01l-.01.01m5.699-9.941l-7.81 7.81a1.5 1.5 0 002.112 2.13" />
                                    </svg>
                                    <span x-text="task.attachment_count"></span>
                                </span>
                                <span x-show="task.comment_count > 0" class="inline-flex items-center gap-1">
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M8.625 12a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H8.25m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H12m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0h-.375M21 12c0 4.556-4.03 8.25-9 8.25a9.764 9.764 0 01-2.555-.337A5.972 5.972 0 015.41 20.97a5.969 5.969 0 01-.474-.065 4.48 4.48 0 00.978-2.025c.09-.457-.133-.901-.467-1.226C3.93 16.178 3 14.189 3 12c0-4.556 4.03-8.25 9-8.25s9 3.694 9 8.25z" />
                                    </svg>
                                    <span x-text="task.comment_count"></span>
                                </span>
                            </div>
                        </div>
                    </template>

                    {{-- Empty column --}}
                    <div x-show="column.tasks.length === 0"
                         class="text-center text-xs text-gray-400 py-4 border border-dashed border-gray-300 rounded-lg">
                        Tidak ada kartu
                    </div>
                </div>

                {{-- Quick Add --}}
                <form @submit.prevent="quickAddTask(column)" class="mt-2 pt-1">
                    <input type="text" x-model="quickAddTitles[column.id]" placeholder="+ Tambah kartu"
                           class="w-full px-3 py-2 text-sm bg-transparent border border-transparent hover:border-gray-300 focus:bg-white focus:border-green-500 focus:ring-2 focus:ring-green-500 rounded-lg placeholder-gray-400 transition-colors">
                </form>
            </div>
        </template>

        {{-- Add Column --}}
        <button type="button" @click="addColumn"
                class="w-72 shrink-0 py-3 rounded-xl border-2 border-dashed border-gray-300 text-sm font-medium text-gray-500 hover:border-green-500 hover:text-green-600 hover:bg-green-50 transition-colors">
            + Tambah Kolom
        </button>
    </div>

    @include('todos.partials.task-detail-modal')
    @include('todos.partials.column-manager-modal')
    @include('todos.partials.label-manager-modal')
</div>
@endsection

@push('styles')
<style>
    .kanban-sortable::-webkit-scrollbar { width: 6px; }
    .kanban-sortable::-webkit-scrollbar-thumb { background: #d1d5db; border-radius: 3px; }
    .sortable-ghost { opacity: 0.4; }
    .sortable-chosen { box-shadow: 0 8px 16px rgba(0,0,0,0.12); }
</style>
@endpush
