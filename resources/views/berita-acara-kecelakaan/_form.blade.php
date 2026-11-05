@php
    $isEdit = isset($report);

    $uraianRows = old('uraian_kejadian', $isEdit ? $report->uraian_rows : ['']);
    if (! is_array($uraianRows) || $uraianRows === []) {
        $uraianRows = [''];
    }
    $uraianRows = array_values($uraianRows);
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
        <h3 class="text-sm font-semibold text-gray-900">Yang Bertanda Tangan</h3>
        <p class="text-xs text-gray-500">
            Form 03 &middot; No. Doc {{ \App\Models\AccidentReport::DOC_NUMBER }} &middot; {{ \App\Models\AccidentReport::DOC_DATE }}
        </p>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Nama <span class="text-red-500">*</span></label>
            <input type="text" name="nama_pelapor" value="{{ old('nama_pelapor', $isEdit ? $report->nama_pelapor : ($defaultNamaPelapor ?? auth()->user()?->name)) }}" required placeholder="Nama yang menerangkan" class="w-full px-4 py-2.5 text-sm border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Jabatan <span class="text-red-500">*</span></label>
            <input type="text" name="jabatan_pelapor" value="{{ old('jabatan_pelapor', $isEdit ? $report->jabatan_pelapor : '') }}" required placeholder="Contoh: Koordinator K3 Laboratorium" class="w-full px-4 py-2.5 text-sm border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Berita Acara <span class="text-red-500">*</span></label>
            <input type="date" name="tanggal" value="{{ old('tanggal', $isEdit ? $report->tanggal?->format('Y-m-d') : date('Y-m-d')) }}" required class="w-full px-4 py-2.5 text-sm border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
            <p class="text-xs text-gray-500 mt-1">Digunakan pada tempat &amp; tanggal penandatanganan.</p>
        </div>
    </div>

    <div class="pt-4 border-t border-gray-100">
        <h3 class="text-sm font-semibold text-gray-900 mb-1">Korban Kecelakaan</h3>
        <p class="text-xs text-gray-500 mb-3">Peneliti/Praktikan/PLP/Pengguna Laboratorium Lain yang mengalami kecelakaan.</p>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Nama <span class="text-red-500">*</span></label>
                <input type="text" name="nama_korban" value="{{ old('nama_korban', $isEdit ? $report->nama_korban : '') }}" required class="w-full px-4 py-2.5 text-sm border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Alamat <span class="text-red-500">*</span></label>
                <input type="text" name="alamat_korban" value="{{ old('alamat_korban', $isEdit ? $report->alamat_korban : '') }}" required class="w-full px-4 py-2.5 text-sm border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Tempat Tanggal Lahir <span class="text-red-500">*</span></label>
                <input type="text" name="ttl_korban" value="{{ old('ttl_korban', $isEdit ? $report->ttl_korban : '') }}" required placeholder="Contoh: Yogyakarta, 12 Mei 2005" class="w-full px-4 py-2.5 text-sm border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Unit/Status <span class="text-red-500">*</span></label>
                <input type="text" name="unit_status_korban" value="{{ old('unit_status_korban', $isEdit ? $report->unit_status_korban : '') }}" required placeholder="Contoh: Praktikan / PLP / Peneliti" class="w-full px-4 py-2.5 text-sm border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
            </div>
        </div>
    </div>

    <div class="pt-4 border-t border-gray-100">
        <h3 class="text-sm font-semibold text-gray-900 mb-1">Uraian Kejadian</h3>
        <p class="text-xs text-gray-500 mb-3">Tuliskan uraian kejadian poin demi poin (akan diberi nomor otomatis pada dokumen).</p>

        <div x-data="{ rows: @js($uraianRows) }" class="space-y-3">
            <template x-for="(row, index) in rows" :key="index">
                <div class="flex gap-3 items-start">
                    <span class="w-6 shrink-0 pt-2.5 text-sm text-gray-500 text-right" x-text="(index + 1) + '.'"></span>
                    <textarea name="uraian_kejadian[]" x-model="rows[index]" rows="2" required maxlength="1000" placeholder="Uraian kejadian..." class="flex-1 px-4 py-2.5 text-sm border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"></textarea>
                    <button type="button" x-show="rows.length > 1" @click="rows.splice(index, 1)" class="p-2 text-red-600 hover:bg-red-50 rounded-lg transition-colors shrink-0" title="Hapus poin">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </template>
            <button type="button" @click="rows.push('')" class="inline-flex items-center gap-2 px-3 py-1.5 border border-green-600 text-green-700 bg-white text-xs font-medium rounded-lg hover:bg-green-50 transition-colors">
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
                Tambah Uraian
            </button>
        </div>
    </div>

    <div class="pt-4 border-t border-gray-100">
        <h3 class="text-sm font-semibold text-gray-900 mb-3">Dampak Kecelakaan</h3>
        <textarea name="dampak" rows="3" required maxlength="2000" placeholder="Dampak dari kecelakaan kerja tersebut..." class="w-full px-4 py-2.5 text-sm border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">{{ old('dampak', $isEdit ? $report->dampak : '') }}</textarea>
    </div>

    <div class="pt-4 border-t border-gray-100">
        <h3 class="text-sm font-semibold text-gray-900 mb-3">Saksi</h3>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Saksi I <span class="text-red-500">*</span></label>
                <input type="text" name="saksi_satu" value="{{ old('saksi_satu', $isEdit ? $report->saksi_satu : '') }}" required class="w-full px-4 py-2.5 text-sm border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Saksi II <span class="text-red-500">*</span></label>
                <input type="text" name="saksi_dua" value="{{ old('saksi_dua', $isEdit ? $report->saksi_dua : '') }}" required class="w-full px-4 py-2.5 text-sm border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
            </div>
        </div>
    </div>
</div>
