@php
    $isEdit = isset($k3InspectionReport);
    $checklist = old('checklist', $isEdit ? ($k3InspectionReport->checklist_items ?? []) : \App\Models\K3InspectionReport::defaultChecklist());
    $findingsRaw = old('findings', $isEdit ? ($k3InspectionReport->findings ?? []) : []);
    $findings = is_array($findingsRaw) ? array_values($findingsRaw) : [];
    while (count($findings) < 3) {
        $findings[] = \App\Models\K3InspectionReport::blankFinding();
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
        <h3 class="text-sm font-semibold text-gray-900">Berita Acara</h3>
        <p class="text-xs text-gray-500">Form 005 &middot; No. Doc {{ \App\Models\K3InspectionReport::DOC_NUMBER }} &middot; {{ \App\Models\K3InspectionReport::DOC_DATE }}</p>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Hari <span class="text-red-500">*</span></label>
            <input type="text" name="hari" value="{{ old('hari', $isEdit ? $k3InspectionReport->hari : '') }}" required placeholder="Senin" class="w-full px-4 py-2.5 text-sm border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal <span class="text-red-500">*</span></label>
            <input type="date" name="tanggal_ba" value="{{ old('tanggal_ba', $isEdit ? $k3InspectionReport->tanggal_ba?->format('Y-m-d') : date('Y-m-d')) }}" required class="w-full px-4 py-2.5 text-sm border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Bulan <span class="text-red-500">*</span></label>
            <input type="text" name="bulan_ba" value="{{ old('bulan_ba', $isEdit ? $k3InspectionReport->bulan_ba : \Carbon\Carbon::now()->translatedFormat('F')) }}" required class="w-full px-4 py-2.5 text-sm border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Tahun <span class="text-red-500">*</span></label>
            <input type="text" name="tahun_ba" value="{{ old('tahun_ba', $isEdit ? $k3InspectionReport->tahun_ba : date('Y')) }}" required class="w-full px-4 py-2.5 text-sm border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
        </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Bulan Inspeksi <span class="text-red-500">*</span></label>
            <input type="text" name="bulan_inspeksi" value="{{ old('bulan_inspeksi', $isEdit ? $k3InspectionReport->bulan_inspeksi : \Carbon\Carbon::now()->translatedFormat('F Y')) }}" required placeholder="November 2024" class="w-full px-4 py-2.5 text-sm border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
        </div>
        <div class="sm:col-span-2">
            <label class="block text-sm font-medium text-gray-700 mb-1">Lokasi <span class="text-red-500">*</span></label>
            <input type="text" name="lokasi" value="{{ old('lokasi', $isEdit ? $k3InspectionReport->lokasi : 'UPT Laboratorium Unisa Yogyakarta') }}" required class="w-full px-4 py-2.5 text-sm border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
        </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Nama Pemeriksa <span class="text-red-500">*</span></label>
            <input type="text" name="pemeriksa_nama" value="{{ old('pemeriksa_nama', $isEdit ? $k3InspectionReport->pemeriksa_nama : \App\Models\K3InspectionReport::PEMERIKSA_NAMA) }}" required class="w-full px-4 py-2.5 text-sm border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Jabatan Pemeriksa <span class="text-red-500">*</span></label>
            <input type="text" name="pemeriksa_jabatan" value="{{ old('pemeriksa_jabatan', $isEdit ? $k3InspectionReport->pemeriksa_jabatan : \App\Models\K3InspectionReport::PEMERIKSA_JABATAN) }}" required class="w-full px-4 py-2.5 text-sm border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
        </div>
    </div>
</div>

<div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
    <div class="px-6 py-4 border-b border-gray-200">
        <h3 class="text-sm font-semibold text-gray-900">Checklist Inspeksi K3</h3>
        <p class="text-xs text-gray-500 mt-0.5">Beri tanda pada Ya/Tidak/N/A untuk setiap item.</p>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                    <th class="px-3 py-3 text-center font-medium text-gray-600 w-10">No</th>
                    <th class="px-3 py-3 text-left font-medium text-gray-600">Item</th>
                    <th class="px-2 py-3 text-center font-medium text-gray-600 w-12">Ya</th>
                    <th class="px-2 py-3 text-center font-medium text-gray-600 w-12">Tidak</th>
                    <th class="px-2 py-3 text-center font-medium text-gray-600 w-12">N/A</th>
                    <th class="px-3 py-3 text-left font-medium text-gray-600 w-40">Catatan</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @foreach($checklist as $idx => $row)
                    <tr>
                        <td class="px-3 py-2 text-center text-gray-500">{{ $row['item_no'] ?? ($idx+1) }}</td>
                        <td class="px-3 py-2 text-gray-900">{{ $row['item_text'] ?? '' }}</td>
                        <td class="px-2 py-2 text-center">
                            <input type="checkbox" name="checklist[{{ $idx }}][ya]" value="1" @checked(!empty($row['ya'])) class="rounded border-gray-300 text-green-600 focus:ring-green-500">
                        </td>
                        <td class="px-2 py-2 text-center">
                            <input type="checkbox" name="checklist[{{ $idx }}][tidak]" value="1" @checked(!empty($row['tidak'])) class="rounded border-gray-300 text-red-600 focus:ring-red-500">
                        </td>
                        <td class="px-2 py-2 text-center">
                            <input type="checkbox" name="checklist[{{ $idx }}][na]" value="1" @checked(!empty($row['na'])) class="rounded border-gray-300 text-gray-600 focus:ring-gray-500">
                        </td>
                        <td class="px-3 py-2">
                            <input type="text" name="checklist[{{ $idx }}][catatan]" value="{{ $row['catatan'] ?? '' }}" class="w-full px-2 py-1 text-xs border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                        </td>
                    </tr>
                    @if(($row['item_no'] ?? ($idx+1)) == 22)
                        <tr class="bg-gray-50/50">
                            <td class="px-3 py-1.5"></td>
                            <td colspan="5" class="px-3 py-1.5 text-xs text-gray-600">
                                @foreach(\App\Models\K3InspectionReport::P3K_ITEMS as $p3k)
                                    <span class="inline-flex items-center px-1.5 py-0.5 mr-1 mb-0.5 rounded bg-white border border-gray-200">{{ $p3k }}</span>
                                @endforeach
                            </td>
                        </tr>
                    @endif
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
    <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
        <div>
            <h3 class="text-sm font-semibold text-gray-900">Form Laporan Inspeksi K3 (Temuan)</h3>
            <p class="text-xs text-gray-500 mt-0.5">Catatan temuan, saran perbaikan, pelaksana, target, dan catatan.</p>
        </div>
        <button type="button" @click="addFinding()" x-data x-bind:disabled="findingCount >= 30" class="inline-flex items-center gap-2 px-3 py-2 bg-green-50 text-green-700 text-xs font-medium rounded-lg hover:bg-green-100 disabled:opacity-50 disabled:cursor-not-allowed transition-colors">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
            </svg>
            Tambah Temuan
        </button>
    </div>
    <div class="overflow-x-auto" x-data="k3Findings" data-findings="{{ json_encode($findings) }}">
        <table class="w-full text-sm" style="min-width: 1100px;">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                    <th class="px-3 py-3 text-center font-medium text-gray-600 w-10">No</th>
                    <th class="px-3 py-3 text-left font-medium text-gray-600">Temuan Inspeksi</th>
                    <th class="px-3 py-3 text-left font-medium text-gray-600 w-32">Dokumentasi</th>
                    <th class="px-3 py-3 text-left font-medium text-gray-600">Saran Perbaikan</th>
                    <th class="px-3 py-3 text-left font-medium text-gray-600 w-32">Pelaksana</th>
                    <th class="px-3 py-3 text-left font-medium text-gray-600 w-32">Target</th>
                    <th class="px-3 py-3 text-left font-medium text-gray-600 w-36">Catatan</th>
                    <th class="px-2 py-3 w-10"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                <template x-for="(finding, index) in findings" :key="finding.uid">
                    <tr class="align-top">
                        <td class="px-3 py-2 text-center text-gray-500" x-text="index + 1"></td>
                        <td class="px-3 py-2">
                            <textarea :name="'findings[' + index + '][temuan]'" x-model="finding.temuan" rows="2" class="w-full px-3 py-1.5 text-sm border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"></textarea>
                        </td>
                        <td class="px-3 py-2">
                            <input type="text" :name="'findings[' + index + '][dokumentasi]'" x-model="finding.dokumentasi" class="w-full px-3 py-1.5 text-sm border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                        </td>
                        <td class="px-3 py-2">
                            <textarea :name="'findings[' + index + '][saran_perbaikan]'" x-model="finding.saran_perbaikan" rows="2" class="w-full px-3 py-1.5 text-sm border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"></textarea>
                        </td>
                        <td class="px-3 py-2">
                            <input type="text" :name="'findings[' + index + '][pelaksana]'" x-model="finding.pelaksana" class="w-full px-3 py-1.5 text-sm border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                        </td>
                        <td class="px-3 py-2">
                            <input type="text" :name="'findings[' + index + '][target]'" x-model="finding.target" class="w-full px-3 py-1.5 text-sm border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                        </td>
                        <td class="px-3 py-2">
                            <textarea :name="'findings[' + index + '][catatan]'" x-model="finding.catatan" rows="2" class="w-full px-3 py-1.5 text-sm border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"></textarea>
                        </td>
                        <td class="px-2 py-2 text-center">
                            <button type="button" @click="removeFinding(index)" x-show="findings.length > 1" title="Hapus baris" class="p-1.5 text-red-600 hover:bg-red-50 rounded-lg transition-colors">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </td>
                    </tr>
                </template>
            </tbody>
        </table>
        <p class="m-3 text-xs text-gray-500" x-show="findings.length >= 30" x-cloak>Maksimal 30 temuan per laporan.</p>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('k3Findings', () => ({
            findings: [],
            get findingCount() { return this.findings.length; },
            init() {
                const parsed = JSON.parse(this.$el.dataset.findings || '[]');
                this.findings = parsed.map((row) => ({ ...this.blank(), ...row, uid: this.uid() }));
            },
            uid() { return Math.random().toString(36).slice(2); },
            blank() { return { temuan: '', dokumentasi: '', saran_perbaikan: '', pelaksana: '', target: '', catatan: '' }; },
            addFinding() { if (this.findings.length < 30) { this.findings.push({ ...this.blank(), uid: this.uid() }); } },
            removeFinding(index) { if (this.findings.length > 1) { this.findings.splice(index, 1); } },
        }));
    });
</script>
@endpush
