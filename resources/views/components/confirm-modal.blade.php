<div x-data="confirmModal" x-cloak>
    <div x-show="open" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 z-[60] flex items-center justify-center p-4 bg-black/60">
        <div x-show="open" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95" @click.away="cancel()" class="bg-white rounded-xl shadow-xl max-w-md w-full overflow-hidden">
            <div class="px-6 py-5">
                <div class="flex items-start gap-4">
                    <div class="w-10 h-10 rounded-full flex items-center justify-center flex-shrink-0" :class="type === 'danger' ? 'bg-red-100' : 'bg-amber-100'">
                        <svg x-show="type === 'danger'" class="w-6 h-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                        </svg>
                        <svg x-show="type !== 'danger'" class="w-6 h-6 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                        </svg>
                    </div>
                    <div class="flex-1 min-w-0">
                        <h3 class="text-base font-semibold text-gray-900 mb-1" x-text="title"></h3>
                        <p class="text-sm text-gray-600 mb-2" x-text="message"></p>
                        <input id="confirm-modal-input" type="text" class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500" style="display:none">
                    </div>
                </div>
            </div>
            <div class="px-6 py-4 bg-gray-50 flex items-center justify-end gap-3">
                <button type="button" x-show="cancelText !== '' && cancelText !== null" @click="cancel()" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors" x-text="cancelText"></button>
                <button type="button" @click="confirm()" class="px-4 py-2 text-sm font-medium text-white rounded-lg transition-colors" :class="type === 'danger' ? 'bg-red-600 hover:bg-red-700' : 'bg-green-600 hover:bg-green-700'" x-text="confirmText"></button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('confirmModal', () => ({
        open: false,
        title: 'Konfirmasi',
        message: 'Apakah Anda yakin?',
        confirmText: 'Ya',
        cancelText: 'Batal',
        type: 'warning',
        resolve: null,
        reject: null,

        init() {
            window.showConfirm = (options = {}) => {
                return new Promise((resolve, reject) => {
                    this.title = options.title || 'Konfirmasi';
                    this.message = options.message || 'Apakah Anda yakin?';
                    this.confirmText = options.confirmText || 'Ya';
                    this.cancelText = options.cancelText || 'Batal';
                    this.type = options.type || 'warning';
                    this.resolve = resolve;
                    this.reject = reject;
                    this.open = true;
                });
            };

            window.showAlert = (options = {}) => {
                return new Promise((resolve) => {
                    this.title = options.title || 'Informasi';
                    this.message = options.message || '';
                    this.confirmText = options.confirmText || 'OK';
                    this.cancelText = '';
                    this.type = options.type || 'warning';
                    this.resolve = () => { this.open = false; resolve(); };
                    this.reject = null;
                    this.open = true;
                });
            };

            window.showPrompt = (options = {}) => {
                return new Promise((resolve) => {
                    this.title = options.title || 'Masukkan';
                    this.message = options.message || '';
                    this.confirmText = options.confirmText || 'OK';
                    this.cancelText = options.cancelText || 'Batal';
                    this.type = options.type || 'warning';
                    this.resolve = () => {
                        const input = document.getElementById('confirm-modal-input');
                        const value = input ? input.value : '';
                        if (input) input.style.display = 'none';
                        this.open = false;
                        resolve(value);
                    };
                    this.reject = null;
                    this.open = true;
                    this.$nextTick(() => {
                        const input = document.getElementById('confirm-modal-input');
                        if (input) {
                            input.style.display = 'block';
                            input.value = options.defaultValue || '';
                            input.focus();
                        }
                    });
                });
            };
        },

        confirm() {
            if (document.getElementById('confirm-modal-input') && document.getElementById('confirm-modal-input').style.display !== 'none') {
                const input = document.getElementById('confirm-modal-input');
                if (this.resolve) { const v = input.value; input.style.display = 'none'; this.open = false; this.resolve(v); return; }
            }
            this.open = false;
            if (this.resolve) this.resolve(true);
        },

        cancel() {
            const input = document.getElementById('confirm-modal-input');
            if (input) input.style.display = 'none';
            this.open = false;
            if (this.resolve) this.resolve(false);
        }
    }));
});
</script>
