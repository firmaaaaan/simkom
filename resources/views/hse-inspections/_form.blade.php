@php
    $isEdit = isset($hseInspection);
    $rawRows = old('findings', $isEdit ? ($hseInspection->findings ?? []) : []);
    $rows = is_array($rawRows) ? array_values($rawRows) : [];

    $rows = array_map(fn ($row) => [
        'lokasi' => $row['lokasi'] ?? '',
        'temuan' => $row['temuan'] ?? '',
        'unsafe_condition' => ! empty($row['unsafe_condition']),
        'unsafe_action' => ! empty($row['unsafe_action']),
        'tanggal_kejadian' => $row['tanggal_kejadian'] ?? '',
        'tindakan_perbaikan' => $row['tindakan_perbaikan'] ?? '',
        'tanggal_perbaikan' => $row['tanggal_perbaikan'] ?? '',
        'pic' => $row['pic'] ?? '',
    ], $rows);

    while (count($rows) < 3) {
        $rows[] = \App\Models\HseInspection::blankFinding();
    }
@endphp

@if($errors->any())
    <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg text-sm">
        <ul class="list-disc list-inside space-y-1">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 pb-4 border-b border-gray-100">
        <h3 class="text-sm font-semibold text-gray-900">Identitas Inspeksi</h3>
        <p class="text-xs text-gray-500">
            Form 04 &middot; No. Dokumen {{ \App\Models\HseInspection::DOC_NUMBER }} &middot; Tgl. Berlaku {{ \App\Models\HseInspection::DOC_EFFECTIVE_DATE }}
        </p>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Lokasi <span class="text-red-500">*</span></label>
            <input type="text" name="lokasi" value="{{ old('lokasi', $isEdit ? $hseInspection->lokasi : '') }}" required placeholder="Contoh: Gedung Lab Terpadu" class="w-full px-4 py-2.5 text-sm border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Inspeksi Oleh <span class="text-red-500">*</span></label>
            <input type="text" name="diperiksa_oleh" value="{{ old('diperiksa_oleh', $isEdit ? $hseInspection->diperiksa_oleh : ($defaultDiperiksaOleh ?? auth()->user()?->name)) }}" required class="w-full px-4 py-2.5 text-sm border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Inspeksi <span class="text-red-500">*</span></label>
            <input type="date" name="tanggal_inspeksi" value="{{ old('tanggal_inspeksi', $isEdit ? $hseInspection->tanggal_inspeksi?->format('Y-m-d') : date('Y-m-d')) }}" required class="w-full px-4 py-2.5 text-sm border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
        </div>
    </div>

    <div class="pt-4 border-t border-gray-100" x-data="hseFindingsForm" data-rows="{{ json_encode($rows) }}">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 mb-3">
            <div>
                <h3 class="text-sm font-semibold text-gray-900 mb-1">Temuan &amp; Dokumentasi</h3>
                <p class="text-xs text-gray-500">Catat temuan unsafe condition / unsafe action beserta tindakan perbaikannya. Baris kosong tidak akan disimpan.</p>
            </div>
            <button type="button" @click="addRow()" x-bind:disabled="rows.length >= 20" class="inline-flex items-center gap-2 px-3 py-2 bg-green-50 text-green-700 text-xs font-medium rounded-lg hover:bg-green-100 disabled:opacity-50 disabled:cursor-not-allowed transition-colors">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
                Tambah Temuan
            </button>
        </div>

        <div>
            <div class="overflow-x-auto border border-gray-200 rounded-lg">
                <table class="w-full text-sm" style="min-width: 1000px;">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th class="px-3 py-3 text-center font-medium text-gray-600 w-10">No</th>
                            <th class="px-3 py-3 text-left font-medium text-gray-600 w-40">Lokasi</th>
                            <th class="px-3 py-3 text-left font-medium text-gray-600">Temuan &amp; Dokumentasi</th>
                            <th class="px-3 py-3 text-center font-medium text-gray-600 w-24">Unsafe Condition</th>
                            <th class="px-3 py-3 text-center font-medium text-gray-600 w-24">Unsafe Action</th>
                            <th class="px-3 py-3 text-left font-medium text-gray-600 w-36">Tanggal Kejadian</th>
                            <th class="px-3 py-3 text-left font-medium text-gray-600">Tindakan Perbaikan</th>
                            <th class="px-3 py-3 text-left font-medium text-gray-600 w-36">Tanggal Perbaikan</th>
                            <th class="px-3 py-3 text-left font-medium text-gray-600 w-32">PIC</th>
                            <th class="px-2 py-3 w-10"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <template x-for="(row, index) in rows" :key="row.uid">
                            <tr>
                                <td class="px-3 py-2 text-center text-gray-500" x-text="index + 1"></td>
                                <td class="px-3 py-2">
                                    <input type="text" :name="'findings[' + index + '][lokasi]'" x-model="row.lokasi" placeholder="Lokasi temuan" class="w-full px-3 py-1.5 text-sm border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                                </td>
                                <td class="px-3 py-2">
                                    <textarea :name="'findings[' + index + '][temuan]'" x-model="row.temuan" rows="2" placeholder="Uraian temuan & dokumentasi" class="w-full px-3 py-1.5 text-sm border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"></textarea>
                                </td>
                                <td class="px-3 py-2 text-center">
                                    <input type="checkbox" value="1" :name="'findings[' + index + '][unsafe_condition]'" x-model="row.unsafe_condition" class="rounded border-gray-300 text-amber-600 focus:ring-amber-500">
                                </td>
                                <td class="px-3 py-2 text-center">
                                    <input type="checkbox" value="1" :name="'findings[' + index + '][unsafe_action]'" x-model="row.unsafe_action" class="rounded border-gray-300 text-amber-600 focus:ring-amber-500">
                                </td>
                                <td class="px-3 py-2">
                                    <input type="date" :name="'findings[' + index + '][tanggal_kejadian]'" x-model="row.tanggal_kejadian" class="w-full px-3 py-1.5 text-sm border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                                </td>
                                <td class="px-3 py-2">
                                    <textarea :name="'findings[' + index + '][tindakan_perbaikan]'" x-model="row.tindakan_perbaikan" rows="2" placeholder="Tindakan perbaikan" class="w-full px-3 py-1.5 text-sm border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"></textarea>
                                </td>
                                <td class="px-3 py-2">
                                    <input type="date" :name="'findings[' + index + '][tanggal_perbaikan]'" x-model="row.tanggal_perbaikan" class="w-full px-3 py-1.5 text-sm border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                                </td>
                                <td class="px-3 py-2">
                                    <input type="text" :name="'findings[' + index + '][pic]'" x-model="row.pic" placeholder="PIC" class="w-full px-3 py-1.5 text-sm border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                                </td>
                                <td class="px-2 py-2 text-center">
                                    <button type="button" @click="removeRow(index)" x-show="rows.length > 1" title="Hapus baris" class="p-1.5 text-red-600 hover:bg-red-50 rounded-lg transition-colors">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                    </button>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
            <p class="mt-2 text-xs text-gray-500" x-show="rows.length >= 20" x-cloak>Maksimal 20 temuan per inspeksi.</p>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('hseFindingsForm', () => ({
            rows: [],
            init() {
                const parsed = JSON.parse(this.$el.dataset.rows || '[]');
                this.rows = parsed.map((row) => ({ ...this.blankRow(), ...row, uid: this.uid() }));
            },
            uid() {
                return Math.random().toString(36).slice(2);
            },
            blankRow() {
                return {
                    lokasi: '',
                    temuan: '',
                    unsafe_condition: false,
                    unsafe_action: false,
                    tanggal_kejadian: '',
                    tindakan_perbaikan: '',
                    tanggal_perbaikan: '',
                    pic: '',
                };
            },
            addRow() {
                if (this.rows.length < 20) {
                    this.rows.push({ ...this.blankRow(), uid: this.uid() });
                }
            },
            removeRow(index) {
                if (this.rows.length > 1) {
                    this.rows.splice(index, 1);
                }
            },
        }));
    });
</script>
@endpush
