{{-- Task Detail Modal --}}
<div x-show="showDetail" class="fixed inset-0 z-40 flex items-center justify-center p-4" style="display:none">
    <div class="absolute inset-0 bg-black/50" @click="closeDetail"></div>
    <div class="relative bg-white rounded-xl shadow-xl w-full max-w-2xl max-h-[90vh] overflow-y-auto"
         @click.stop x-transition>
        <template x-if="currentTask">
            <div class="p-6 space-y-6">
                {{-- Header --}}
                <div class="flex items-start justify-between gap-3">
                    <input type="text" x-model="currentTask.title" @change="saveTask()" required
                           class="flex-1 text-lg font-semibold text-gray-900 border border-transparent hover:border-gray-200 focus:border-green-500 focus:ring-2 focus:ring-green-500 rounded-lg px-2 py-1 -ml-2">
                    <button type="button" @click="closeDetail" title="Tutup"
                            class="p-1.5 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-lg transition-colors">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                {{-- Properties --}}
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">Kolom</label>
                        <select x-model="currentTask.column_id" @change="saveTask()"
                                class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                            <template x-for="column in columns" :key="column.id">
                                <option :value="column.id" x-text="column.name"></option>
                            </template>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">Tenggat</label>
                        <input type="date" x-model="currentTask.due_date" @change="saveTask()"
                               class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
                               :class="isOverdue(currentTask) ? 'border-red-300 text-red-600' : ''">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">Prioritas</label>
                        <select x-model="currentTask.priority" @change="saveTask()"
                                class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                            <option value="">Tanpa prioritas</option>
                            <option value="low">Rendah</option>
                            <option value="medium">Sedang</option>
                            <option value="high">Tinggi</option>
                        </select>
                    </div>
                </div>

                {{-- Labels --}}
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-2">Label</label>
                    <div class="flex flex-wrap gap-2">
                        <template x-for="label in labels" :key="label.id">
                            <button type="button" @click="toggleLabel(label)"
                                    class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium border transition-colors"
                                    :class="taskHasLabel(label.id)
                                        ? 'border-transparent text-white shadow-sm'
                                        : 'border-gray-200 text-gray-600 hover:bg-gray-50'"
                                    :style="taskHasLabel(label.id) ? `background:${label.color}` : ''">
                                <span x-show="!taskHasLabel(label.id)" class="h-2 w-2 rounded-full" :style="`background:${label.color}`"></span>
                                <span x-text="label.name"></span>
                            </button>
                        </template>
                        <span x-show="labels.length === 0" class="text-xs text-gray-400">Belum ada label. Buat lewat menu "Kelola Label".</span>
                    </div>
                </div>

                {{-- Description --}}
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">Deskripsi</label>
                    <textarea x-model="currentTask.description" @change="saveTask()" rows="3"
                              placeholder="Tambahkan deskripsi..."
                              class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 resize-y"></textarea>
                </div>

                {{-- Checklist --}}
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <label class="text-xs font-medium text-gray-500">Checklist</label>
                        <span x-show="currentTask.checklist_total > 0" class="text-xs text-gray-500"
                              x-text="currentTask.checklist_done + '/' + currentTask.checklist_total + ' selesai'"></span>
                    </div>
                    <div x-show="currentTask.checklist_total > 0" class="h-1.5 bg-gray-100 rounded-full mb-3 overflow-hidden">
                        <div class="h-full bg-green-500 rounded-full transition-all"
                             :style="`width:${currentTask.checklist_total ? (currentTask.checklist_done / currentTask.checklist_total) * 100 : 0}%`"></div>
                    </div>
                    <div class="space-y-2 mb-3">
                        <template x-for="item in currentTask.checklist" :key="item.id">
                            <div class="flex items-center gap-2 group">
                                <input type="checkbox" :checked="item.is_done"
                                       @change="toggleChecklistItem(item, $event.target.checked)"
                                       class="rounded border-gray-300 text-green-600 focus:ring-green-500 shrink-0">
                                <span class="flex-1 text-sm" :class="item.is_done ? 'line-through text-gray-400' : 'text-gray-700'"
                                      x-text="item.text"></span>
                                <button type="button" @click="deleteChecklistItem(item)" title="Hapus"
                                        class="p-1 text-gray-300 hover:text-red-600 rounded opacity-0 group-hover:opacity-100 transition-opacity">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>
                        </template>
                    </div>
                    <form @submit.prevent="addChecklistItem()" class="flex gap-2">
                        <input type="text" x-model="newChecklistText" placeholder="Tambah item checklist..."
                               class="flex-1 px-3 py-2 text-sm border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                        <button type="submit" class="px-3 py-2 bg-gray-100 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-200 transition-colors">
                            Tambah
                        </button>
                    </form>
                </div>

                {{-- Attachments --}}
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-2">Lampiran <span class="text-gray-400 font-normal">(maks. 5MB)</span></label>
                    <div class="space-y-2 mb-3">
                        <template x-for="file in currentTask.attachments" :key="file.id">
                            <div class="flex items-center gap-3 p-2.5 bg-gray-50 rounded-lg border border-gray-100 group">
                                <svg class="w-5 h-5 text-gray-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                                </svg>
                                <div class="flex-1 min-w-0">
                                    <a :href="attachmentUrl(file.id)" class="text-sm font-medium text-gray-900 hover:text-green-600 truncate block" x-text="file.original_name"></a>
                                    <span class="text-xs text-gray-400" x-text="file.human_size"></span>
                                </div>
                                <button type="button" @click="deleteAttachment(file)" title="Hapus"
                                        class="p-1 text-gray-300 hover:text-red-600 rounded transition-colors">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.165m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                                    </svg>
                                </button>
                            </div>
                        </template>
                    </div>
                    <div class="flex items-center gap-2">
                        <input type="file" x-ref="fileInput" @change="onFileSelected($event)"
                               accept=".jpg,.jpeg,.png,.gif,.webp,.pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.zip,.txt,.csv"
                               class="block w-full text-sm text-gray-600 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-green-50 file:text-green-700 hover:file:bg-green-100">
                        <button type="button" @click="uploadFile()" :disabled="!selectedFile || uploading"
                                class="px-3 py-2 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed shrink-0">
                            <span x-show="!uploading">Unggah</span>
                            <span x-show="uploading">Mengunggah...</span>
                        </button>
                    </div>
                </div>

                {{-- Comments --}}
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-2">Komentar</label>
                    <div class="space-y-3 mb-3">
                        <template x-for="comment in currentTask.comments" :key="comment.id">
                            <div class="flex gap-3">
                                <div class="h-8 w-8 rounded-full bg-green-100 text-green-700 flex items-center justify-center text-xs font-bold shrink-0"
                                     x-text="(comment.user || '?').charAt(0).toUpperCase()"></div>
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center gap-2">
                                        <span class="text-sm font-medium text-gray-900" x-text="comment.user"></span>
                                        <span class="text-xs text-gray-400" x-text="comment.created_at"></span>
                                        <button x-show="comment.can_delete" type="button" @click="deleteComment(comment)"
                                                class="text-xs text-gray-300 hover:text-red-600 transition-colors ml-auto">Hapus</button>
                                    </div>
                                    <p class="text-sm text-gray-700 mt-0.5 whitespace-pre-wrap" x-text="comment.body"></p>
                                </div>
                            </div>
                        </template>
                        <p x-show="currentTask.comments.length === 0" class="text-xs text-gray-400">Belum ada komentar.</p>
                    </div>
                    <form @submit.prevent="addComment()" class="flex gap-2">
                        <input type="text" x-model="commentText" placeholder="Tulis komentar..."
                               class="flex-1 px-3 py-2 text-sm border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                        <button type="submit" class="px-3 py-2 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700 transition-colors">
                            Kirim
                        </button>
                    </form>
                </div>

                {{-- Footer --}}
                <div class="flex justify-end pt-4 border-t border-gray-100">
                    <button type="button" @click="deleteTask()"
                            class="inline-flex items-center gap-1.5 px-3 py-2 text-red-600 text-sm font-medium rounded-lg hover:bg-red-50 transition-colors">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.165m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                        </svg>
                        Hapus Kartu
                    </button>
                </div>
            </div>
        </template>
        <template x-if="!currentTask">
            <div class="p-10 text-center text-sm text-gray-500">Memuat detail kartu...</div>
        </template>
    </div>
</div>
