@php
    $initialLicenses = array_values($initialLicenses ?? []);
    $computerOptions = $computerOptions ?? [];
@endphp

<div class="pt-4 border-t border-gray-100"
    x-data="softwareLicenseForm({{ Js::from($initialLicenses) }}, {{ Js::from($computerOptions) }})">
    <input type="hidden" name="licenses_submitted" value="1">
    <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-3 mb-4">
        <div>
            <p class="text-sm font-semibold text-gray-800">
                Lisensi <span class="text-xs font-normal text-gray-400">(opsional)</span>
            </p>
            <p class="text-xs text-gray-400 mt-0.5">Isi jumlah lisensi, baris product key dibuat otomatis. Boleh dikosongkan.</p>
        </div>
        <div class="flex items-center gap-2">
            <label for="license_count" class="text-xs font-medium text-gray-500">Jumlah</label>
            <input type="number" id="license_count" min="0" x-model.number="count"
                @change="setCount($event.target.value)"
                class="w-20 px-3 py-1.5 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent">
            <button type="button" @click="addRow()"
                class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-medium text-green-700 bg-green-50 rounded-lg hover:bg-green-100 transition-colors">
                + Tambah
            </button>
        </div>
    </div>

    <div x-show="licenses.length === 0" x-cloak
        class="px-4 py-6 text-center text-sm text-gray-400 border border-dashed border-gray-200 rounded-lg">
        Belum ada lisensi. Isi jumlah di atas atau klik "+ Tambah".
    </div>

    <div class="space-y-3">
        <template x-for="(license, index) in licenses" :key="license._key">
            <div class="border border-gray-200 rounded-xl p-4 bg-gray-50/40">
                <input type="hidden" :name="'licenses[' + index + '][id]'" :value="license.id || ''">

                <div class="flex items-center justify-between mb-3">
                    <p class="text-xs font-semibold text-gray-600">
                        Lisensi <span x-text="index + 1"></span>
                    </p>
                    <button type="button" @click="removeRow(index)"
                        class="text-xs font-medium text-red-600 hover:text-red-700">
                        Hapus
                    </button>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Label</label>
                        <input type="text" x-model="license.label" :name="'licenses[' + index + '][label]'"
                            class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent placeholder-gray-400"
                            placeholder="Contoh: Lisensi Lab 1">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Product Key</label>
                        <input type="text" x-model="license.license_key" :name="'licenses[' + index + '][license_key]'"
                            class="w-full px-3 py-2 text-sm font-mono border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent placeholder-gray-400"
                            placeholder="XXXXX-XXXXX-XXXXX">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">PC Terpasang</label>
                        <select x-model="license.assigned_computer_id" :name="'licenses[' + index + '][assigned_computer_id]'"
                            class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent">
                            <option value="">-- Belum dipasang --</option>
                            <template x-for="pc in computers" :key="pc.id">
                                <option :value="pc.id" x-text="pc.label"></option>
                            </template>
                        </select>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Tanggal Beli</label>
                            <input type="date" x-model="license.purchase_date" :name="'licenses[' + index + '][purchase_date]'"
                                class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Expired</label>
                            <input type="date" x-model="license.expiry_date" :name="'licenses[' + index + '][expiry_date]'"
                                class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent">
                        </div>
                    </div>
                    <div class="sm:col-span-2">
                        <label class="block text-xs font-medium text-gray-600 mb-1">Catatan</label>
                        <input type="text" x-model="license.notes" :name="'licenses[' + index + '][notes]'"
                            class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent placeholder-gray-400"
                            placeholder="Catatan lisensi (opsional)">
                    </div>
                </div>
            </div>
        </template>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('softwareLicenseForm', (initial = [], computers = []) => ({
            licenses: [],
            computers: computers,
            counter: 0,
            count: 0,
            init() {
                this.licenses = (initial || []).map((license) => ({
                    id: license.id || null,
                    label: license.label || '',
                    license_key: license.license_key || '',
                    assigned_computer_id: license.assigned_computer_id || '',
                    purchase_date: license.purchase_date || '',
                    expiry_date: license.expiry_date || '',
                    notes: license.notes || '',
                    _key: this.counter++,
                }));
                this.count = this.licenses.length;
            },
            blank() {
                return {
                    id: null,
                    label: '',
                    license_key: '',
                    assigned_computer_id: '',
                    purchase_date: '',
                    expiry_date: '',
                    notes: '',
                    _key: this.counter++,
                };
            },
            setCount(value) {
                const target = Math.max(0, parseInt(value || 0, 10) || 0);

                if (target < this.licenses.length) {
                    const remove = this.licenses.length - target;
                    if (!confirm('Kurangi ' + remove + ' baris lisensi? Data pada baris yang dihapus akan hilang.')) {
                        this.count = this.licenses.length;
                        return;
                    }
                    this.licenses.splice(target);
                }

                while (this.licenses.length < target) {
                    this.licenses.push(this.blank());
                }

                this.count = this.licenses.length;
            },
            addRow() {
                this.licenses.push(this.blank());
                this.count = this.licenses.length;
            },
            removeRow(index) {
                this.licenses.splice(index, 1);
                this.count = this.licenses.length;
            },
        }));
    });
</script>
@endpush
