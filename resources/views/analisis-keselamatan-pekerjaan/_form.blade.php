@php
    $isEdit = isset($jsa);
    $apdModel = \App\Models\JobSafetyAnalysis::class;

    $storedApd = old('apd', $isEdit ? ($jsa->apd ?? []) : []);
    if (! is_array($storedApd)) {
        $storedApd = [];
    }

    $stepRows = old('steps', $isEdit ? $jsa->step_rows : [array_fill_keys($apdModel::STEP_FIELDS, '')]);
    if (! is_array($stepRows) || $stepRows === []) {
        $stepRows = [array_fill_keys($apdModel::STEP_FIELDS, '')];
    }
    $stepRows = array_values($stepRows);

    $inputClass = 'w-full px-4 py-2.5 text-sm border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500';
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
        <h3 class="text-sm font-semibold text-gray-900">Identitas Pekerjaan</h3>
        <p class="text-xs text-gray-500">
            Form 06 &middot; No. Doc {{ $apdModel::DOC_NUMBER }} &middot; {{ $apdModel::DOC_DATE }}
        </p>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Nama Pekerja <span class="text-red-500">*</span></label>
            <input type="text" name="nama_pekerja" value="{{ old('nama_pekerja', $isEdit ? $jsa->nama_pekerja : '') }}" required placeholder="Nama yang melaksanakan pekerjaan" class="{{ $inputClass }}">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Nama Pekerjaan <span class="text-red-500">*</span></label>
            <input type="text" name="nama_pekerjaan" value="{{ old('nama_pekerjaan', $isEdit ? $jsa->nama_pekerjaan : '') }}" required placeholder="Contoh: Penggantian thermal paste PC" class="{{ $inputClass }}">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Pekerjaan <span class="text-red-500">*</span></label>
            <input type="date" name="tanggal_pekerjaan" value="{{ old('tanggal_pekerjaan', $isEdit ? $jsa->tanggal_pekerjaan?->format('Y-m-d') : date('Y-m-d')) }}" required class="{{ $inputClass }}">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Tempat <span class="text-red-500">*</span></label>
            <input type="text" name="tempat" value="{{ old('tempat', $isEdit ? $jsa->tempat : '') }}" required placeholder="Contoh: Lab Komputer 1" class="{{ $inputClass }}">
        </div>
    </div>

    <div class="pt-4 border-t border-gray-100">
        <h3 class="text-sm font-semibold text-gray-900 mb-1">Alat Pelindung Diri (APD)</h3>
        <p class="text-xs text-gray-500 mb-3">Centang APD yang diperlukan untuk melaksanakan pekerjaan.</p>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-2">
            @foreach($apdModel::APD_ITEMS as $key => $item)
                <label class="flex items-center gap-2 px-3 py-2 border border-gray-200 rounded-lg cursor-pointer hover:bg-green-50/50 transition-colors">
                    <input type="checkbox" name="apd[{{ $key }}]" value="1" @checked(! empty($storedApd[$key])) class="rounded border-gray-300 text-green-600 focus:ring-green-500">
                    <span class="text-sm text-gray-700">
                        {{ $item['label_id'] }}
                        <span class="block text-xs text-gray-400">{{ $item['label'] }}</span>
                    </span>
                </label>
            @endforeach
        </div>

        <div class="mt-3">
            <label class="block text-sm font-medium text-gray-700 mb-1">Lain-lain / Others</label>
            <input type="text" name="apd_lain" value="{{ old('apd_lain', $isEdit ? $jsa->apd_lain : '') }}" placeholder="APD lain jika ada" class="{{ $inputClass }}">
        </div>
    </div>

    <div class="pt-4 border-t border-gray-100">
        <h3 class="text-sm font-semibold text-gray-900 mb-1">Langkah Pekerjaan</h3>
        <p class="text-xs text-gray-500 mb-3">Uraikan langkah pekerjaan beserta identifikasi bahaya, pengendalian, dan penanggung jawabnya.</p>

        <div x-data="{ rows: @js($stepRows) }" class="space-y-3">
            <template x-for="(row, index) in rows" :key="index">
                <div class="border border-gray-200 rounded-lg p-4 bg-gray-50/60 space-y-3">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-semibold text-gray-500" x-text="'Langkah ' + (index + 1)"></span>
                        <button type="button" x-show="rows.length > 1" @click="rows.splice(index, 1)" class="p-1.5 text-red-600 hover:bg-red-50 rounded-lg transition-colors" title="Hapus baris">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Urutan Langkah Pekerjaan</label>
                        <textarea :name="'steps[' + index + '][langkah]'" x-model="rows[index].langkah" rows="2" maxlength="1000" placeholder="Langkah pekerjaan..." class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"></textarea>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Identifikasi Bahaya</label>
                        <textarea :name="'steps[' + index + '][bahaya]'" x-model="rows[index].bahaya" rows="2" maxlength="1000" placeholder="Potensi bahaya pada langkah ini..." class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"></textarea>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Pengendalian</label>
                        <textarea :name="'steps[' + index + '][pengendalian]'" x-model="rows[index].pengendalian" rows="2" maxlength="1000" placeholder="Pengendalian risiko (teknis, APD, prosedur)..." class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"></textarea>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Penanggung Jawab</label>
                        <input type="text" :name="'steps[' + index + '][penanggung_jawab]'" x-model="rows[index].penanggung_jawab" maxlength="255" placeholder="Nama penanggung jawab..." class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                    </div>
                </div>
            </template>
            <button type="button" @click="rows.push({ langkah: '', bahaya: '', pengendalian: '', penanggung_jawab: '' })" class="inline-flex items-center gap-2 px-3 py-1.5 border border-green-600 text-green-700 bg-white text-xs font-medium rounded-lg hover:bg-green-50 transition-colors">
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
                Tambah Langkah
            </button>
        </div>
    </div>

    <div class="pt-4 border-t border-gray-100">
        <h3 class="text-sm font-semibold text-gray-900 mb-2">Keterangan</h3>
        <ul class="list-disc list-inside text-xs text-gray-500 space-y-1">
            <li>Untuk pekerjaan yang memerlukan perpanjangan waktu dengan kasus yang sama dengan hasil identifikasi dan pengendalian yang sama, maka dapat diperpanjang satu kali perpanjangan.</li>
            <li>Ahli Teknik terkait merupakan Ahli Teknik sesuai bidangnya/Penanggungjawab Proses.</li>
            <li>Pengendalian bersifat teknis, perlengkapan APK, APD, harus berdasarkan standar dan/atau Peraturan perundangan sesuai dengan tingkat risiko hasil identifikasi bahaya.</li>
        </ul>
    </div>
</div>
