@php
    $isEdit = isset($aparInspection);
    $storedItems = old('items', $isEdit ? ($aparInspection->items ?? []) : []);
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
        <h3 class="text-sm font-semibold text-gray-900">Identitas Pemeriksaan</h3>
        <p class="text-xs text-gray-500">
            Form 01 &middot; No. Doc {{ \App\Models\AparInspection::DOC_NUMBER }} &middot; {{ \App\Models\AparInspection::DOC_DATE }}
        </p>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">No. Reg. APAR <span class="text-red-500">*</span></label>
            <input type="text" name="nomor_registrasi" value="{{ old('nomor_registrasi', $isEdit ? $aparInspection->nomor_registrasi : '') }}" required placeholder="Contoh: APAR-LAB1-01" class="w-full px-4 py-2.5 text-sm border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Hari/Tanggal Pemeriksaan <span class="text-red-500">*</span></label>
            <input type="date" name="tanggal_inspeksi" value="{{ old('tanggal_inspeksi', $isEdit ? $aparInspection->tanggal_inspeksi?->format('Y-m-d') : date('Y-m-d')) }}" required class="w-full px-4 py-2.5 text-sm border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
            <p class="text-xs text-gray-500 mt-1">Nama hari ditampilkan otomatis mengikuti tanggal.</p>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Lokasi <span class="text-red-500">*</span></label>
            <input type="text" name="lokasi" value="{{ old('lokasi', $isEdit ? $aparInspection->lokasi : '') }}" required placeholder="Contoh: Lab Komputer 1" class="w-full px-4 py-2.5 text-sm border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Diperiksa Oleh <span class="text-red-500">*</span></label>
            <input type="text" name="diperiksa_oleh" value="{{ old('diperiksa_oleh', $isEdit ? $aparInspection->diperiksa_oleh : ($defaultDiperiksaOleh ?? auth()->user()?->name)) }}" required class="w-full px-4 py-2.5 text-sm border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
        </div>
    </div>

    <div class="pt-4 border-t border-gray-100">
        <h3 class="text-sm font-semibold text-gray-900 mb-1">Item yang akan Diinspeksi</h3>
        <p class="text-xs text-gray-500 mb-3">Pilih <span class="font-medium">Baik</span> atau <span class="font-medium">Tidak</span> untuk setiap item, lalu isi keterangan bila perlu.</p>

        <div class="overflow-x-auto border border-gray-200 rounded-lg">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-4 py-3 text-left font-medium text-gray-600 w-8">No</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-600">Item</th>
                        <th class="px-3 py-3 text-center font-medium text-gray-600 w-20">Baik</th>
                        <th class="px-3 py-3 text-center font-medium text-gray-600 w-20">Tidak</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-600">Keterangan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach(\App\Models\AparInspection::ITEMS as $key => $item)
                        @php
                            $status = $storedItems[$key]['status'] ?? 'baik';
                            $keterangan = $storedItems[$key]['keterangan'] ?? '';
                        @endphp
                        <tr>
                            <td class="px-4 py-3 text-gray-500">{{ $loop->iteration }}</td>
                            <td class="px-4 py-3">
                                <p class="font-medium text-gray-900">{{ $item['label'] }}</p>
                                <p class="text-xs text-gray-500">{{ $item['label_id'] }}</p>
                            </td>
                            <td class="px-3 py-3 text-center">
                                <input type="radio" name="items[{{ $key }}][status]" value="baik" @checked($status === 'baik') required class="text-green-600 focus:ring-green-500">
                            </td>
                            <td class="px-3 py-3 text-center">
                                <input type="radio" name="items[{{ $key }}][status]" value="tidak" @checked($status === 'tidak') required class="text-red-600 focus:ring-red-500">
                            </td>
                            <td class="px-4 py-3">
                                <input type="text" name="items[{{ $key }}][keterangan]" value="{{ $keterangan }}" placeholder="Opsional" class="w-full px-3 py-1.5 text-sm border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <p class="mt-3 text-xs text-gray-500">
            Sebelum dipasang pada tempatnya, Box Fire Extinguisher harus diberi "Tag" terlebih dahulu.
        </p>
    </div>
</div>
