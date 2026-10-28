{{-- Column Manager Modal --}}
<div x-show="showColumnManager" class="fixed inset-0 z-40 flex items-center justify-center p-4" style="display:none">
    <div class="absolute inset-0 bg-black/50" @click="showColumnManager = false"></div>
    <div class="relative bg-white rounded-xl shadow-xl w-full max-w-md p-6" @click.stop x-transition>
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg font-semibold text-gray-900">Kelola Kolom</h2>
            <button type="button" @click="showColumnManager = false" title="Tutup"
                    class="p-1.5 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-lg transition-colors">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        {{-- Column List --}}
        <div class="space-y-2 mb-4">
            <template x-for="(column, index) in columns" :key="column.id">
                <div class="flex items-center gap-2 p-2.5 bg-gray-50 rounded-lg border border-gray-100">
                    <div class="flex flex-col gap-0.5">
                        <button type="button" @click="moveColumn(index, -1)" :disabled="index === 0" title="Naik"
                                class="p-0.5 text-gray-400 hover:text-gray-600 disabled:opacity-30 disabled:cursor-not-allowed">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 15.75l7.5-7.5 7.5 7.5" />
                            </svg>
                        </button>
                        <button type="button" @click="moveColumn(index, 1)" :disabled="index === columns.length - 1" title="Turun"
                                class="p-0.5 text-gray-400 hover:text-gray-600 disabled:opacity-30 disabled:cursor-not-allowed">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                            </svg>
                        </button>
                    </div>

                    <template x-if="editingColumnId === column.id">
                        <div class="flex-1 flex items-center gap-2">
                            <input type="text" x-model="editingColumnName" @keyup.enter="saveRenameColumn()" @keyup.escape="editingColumnId = null"
                                   class="flex-1 px-2.5 py-1.5 text-sm border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                            <button type="button" @click="saveRenameColumn()"
                                    class="p-1.5 text-green-600 hover:bg-green-50 rounded-lg transition-colors" title="Simpan">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                                </svg>
                            </button>
                        </div>
                    </template>
                    <template x-if="editingColumnId !== column.id">
                        <div class="flex-1 flex items-center justify-between gap-2 min-w-0">
                            <span class="text-sm font-medium text-gray-800 truncate" x-text="column.name"></span>
                            <div class="flex items-center gap-1 shrink-0">
                                <button type="button" @click="startRenameColumn(column)" title="Ubah nama"
                                        class="p-1 text-gray-400 hover:text-amber-600 hover:bg-amber-50 rounded transition-colors">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125" />
                                    </svg>
                                </button>
                                <button type="button" @click="deleteColumn(column)" title="Hapus"
                                        class="p-1 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded transition-colors">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.165m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </template>
                </div>
            </template>
            <p x-show="columns.length === 0" class="text-sm text-gray-400 text-center py-4">Belum ada kolom.</p>
        </div>

        {{-- Add Column --}}
        <form @submit.prevent="storeColumn()" class="flex gap-2">
            <input type="text" x-model="newColumnName" placeholder="Nama kolom baru..."
                   class="flex-1 px-3 py-2 text-sm border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
            <button type="submit" class="px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700 transition-colors">
                Tambah
            </button>
        </form>
    </div>
</div>
