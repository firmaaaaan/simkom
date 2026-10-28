{{-- Label Manager Modal --}}
<div x-show="showLabelManager" class="fixed inset-0 z-40 flex items-center justify-center p-4" style="display:none">
    <div class="absolute inset-0 bg-black/50" @click="showLabelManager = false"></div>
    <div class="relative bg-white rounded-xl shadow-xl w-full max-w-md p-6" @click.stop x-transition>
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg font-semibold text-gray-900">Kelola Label</h2>
            <button type="button" @click="showLabelManager = false" title="Tutup"
                    class="p-1.5 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-lg transition-colors">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        {{-- Label List --}}
        <div class="space-y-2 mb-4">
            <template x-for="label in labels" :key="label.id">
                <div class="flex items-center gap-3 p-2.5 bg-gray-50 rounded-lg border border-gray-100">
                    <span class="h-4 w-10 rounded-full shrink-0" :style="`background:${label.color}`"></span>
                    <span class="flex-1 text-sm font-medium text-gray-800 truncate" x-text="label.name"></span>
                    <button type="button" @click="editLabel(label)" title="Ubah"
                            class="p-1 text-gray-400 hover:text-amber-600 hover:bg-amber-50 rounded transition-colors">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125" />
                        </svg>
                    </button>
                    <button type="button" @click="deleteLabel(label)" title="Hapus"
                            class="p-1 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded transition-colors">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.165m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                        </svg>
                    </button>
                </div>
            </template>
            <p x-show="labels.length === 0" class="text-sm text-gray-400 text-center py-4">Belum ada label.</p>
        </div>

        {{-- Label Form --}}
        <form @submit.prevent="saveLabel()" class="space-y-3">
            <p class="text-xs font-medium text-gray-500" x-text="labelForm.id ? 'Ubah Label' : 'Label Baru'"></p>
            <div class="flex items-center gap-3">
                <input type="color" x-model="labelForm.color"
                       class="h-10 w-14 rounded-lg border border-gray-200 cursor-pointer p-1">
                <input type="text" x-model="labelForm.name" placeholder="Nama label..." maxlength="50" required
                       class="flex-1 px-3 py-2 text-sm border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
            </div>
            <div class="flex items-center gap-2">
                <button type="submit" class="px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700 transition-colors"
                        x-text="labelForm.id ? 'Simpan Perubahan' : 'Tambah Label'"></button>
                <button x-show="labelForm.id" type="button" @click="resetLabelForm()"
                        class="px-4 py-2 bg-gray-100 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-200 transition-colors">
                    Batal
                </button>
            </div>
        </form>
    </div>
</div>
