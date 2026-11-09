{{-- Form Jadwal Pemeliharaan Alat & Bahan (FM-02) — dipakai create & edit.
     Vars: $laboratories, $defaultPic, $jenisList, $frekuensiList, $statusList, $items, $jadwalPemeliharaan (nullable).
     Input items: items[index][field] — JS tambah/hapus/hitung ulang indeks. --}}

@php
    $jadwalPemeliharaan ??= null;
    $oldItems = old('items', $items ?? []);
    if (empty($oldItems)) $oldItems = array_fill(0, 10, []);
    $endRow = count($oldItems);
@endphp

<div class="bg-white rounded-xl border border-gray-200 p-6 space-y-5">
    <h2 class="text-base font-semibold text-gray-800">Identitas Dokumen</h2>
    <p class="text-xs text-gray-500">No. Dokumen: {{ \App\Models\JadwalPemeliharaan::DOC_NUMBER }} (FM-02). Isi laboratorium/unit, periode, PIC, dan tanggal penyusunan.</p>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Laboratorium / Unit (dari master)</label>
            <select name="laboratory_id" id="labSelect" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-green-500 focus:border-green-500">
                <option value="">— Manual / Lainnya —</option>
                @foreach($laboratories as $lab)
                    <option value="{{ $lab->id }}" {{ old('laboratory_id', $jadwalPemeliharaan?->laboratory_id) == $lab->id ? 'selected' : '' }}>{{ $lab->name }}</option>
                @endforeach
            </select>
            @error('laboratory_id')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Nama Laboratorium/Unit (manual)</label>
            <input type="text" name="laboratorium_name" value="{{ old('laboratorium_name', $jadwalPemeliharaan?->laboratorium_name) }}" placeholder="Isi bila tidak memilih dari master, mis. Lab. Komputer 2"
                class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-green-500 focus:border-green-500">
            @error('laboratorium_name')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Periode (Tahun Ajaran) <span class="text-red-500">*</span></label>
            <select name="academic_year_id" required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-green-500 focus:border-green-500">
                <option value="">— Pilih Tahun Ajaran —</option>
                @foreach($academicYears ?? [] as $year)
                    <option value="{{ $year->id }}" {{ old('academic_year_id', $jadwalPemeliharaan?->academic_year_id ?? $currentAcademicYearId ?? null) == $year->id ? 'selected' : '' }}>{{ $year->name }}{{ $year->status === 'Aktif' ? ' - Aktif' : '' }}</option>
                @endforeach
            </select>
            @error('academic_year_id')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">PIC/Petugas <span class="text-red-500">*</span></label>
            <input type="text" name="pic" required value="{{ old('pic', $jadwalPemeliharaan?->pic ?? $defaultPic) }}" placeholder="Nama PIC/petugas"
                class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-green-500 focus:border-green-500">
            @error('pic')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Penyusunan <span class="text-red-500">*</span></label>
            <input type="date" name="tanggal_penyusunan" required value="{{ old('tanggal_penyusunan', $jadwalPemeliharaan?->tanggal_penyusunan?->toDateString()) }}"
                class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-green-500 focus:border-green-500">
            @error('tanggal_penyusunan')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 border-t border-gray-100 pt-5">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Disusun oleh</label>
            <input type="text" name="disusun_oleh" value="{{ old('disusun_oleh', $jadwalPemeliharaan?->disusun_oleh ?? $jadwalPemeliharaan?->pic ?? $defaultPic) }}"
                class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-green-500 focus:border-green-500">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Diperiksa oleh (Kepala UPT/Lab)</label>
            <input type="text" name="diperiksa_oleh" value="{{ old('diperiksa_oleh', $jadwalPemeliharaan?->diperiksa_oleh) }}"
                class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-green-500 focus:border-green-500">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Mengetahui (Pejabat/Penanggung jawab)</label>
            <input type="text" name="mengetahui_oleh" value="{{ old('mengetahui_oleh', $jadwalPemeliharaan?->mengetahui_oleh) }}"
                class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-green-500 focus:border-green-500">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Disusun</label>
            <input type="date" name="disusun_tanggal" value="{{ old('disusun_tanggal', $jadwalPemeliharaan?->disusun_tanggal?->toDateString()) }}"
                class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-green-500 focus:border-green-500">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Diperiksa</label>
            <input type="date" name="diperiksa_tanggal" value="{{ old('diperiksa_tanggal', $jadwalPemeliharaan?->diperiksa_tanggal?->toDateString()) }}"
                class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-green-500 focus:border-green-500">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Mengetahui</label>
            <input type="date" name="mengetahui_tanggal" value="{{ old('mengetahui_tanggal', $jadwalPemeliharaan?->mengetahui_tanggal?->toDateString()) }}"
                class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-green-500 focus:border-green-500">
        </div>
    </div>
</div>

<div class="bg-white rounded-xl border border-gray-200 p-6 space-y-4">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
        <div>
            <h2 class="text-base font-semibold text-gray-800">Rincian Alat/Bahan</h2>
            <p class="text-xs text-gray-500">Pilih Kode/No. Inventaris dari dropdown sesuai Lab, bisa banyak per baris untuk alat yang sama dalam 1 hari (mis. 5 unit AC). Klik chip × untuk menghapus, atau ketik kode manual lalu Enter. Jenis contoh: pembersihan, pengecekan fungsi, pelumasan, penggantian komponen, sterilisasi, pengondisian/penyimpanan.</p>
        </div>
        <button type="button" id="addRowBtn" class="inline-flex items-center gap-2 px-4 py-2 border border-green-600 text-green-700 text-sm font-medium rounded-lg hover:bg-green-50 transition-colors">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
            Tambah Baris
        </button>
    </div>

    @error('items')<p class="text-xs text-red-600">{{ $message }}</p>@enderror

    <div class="overflow-x-auto">
        <table class="w-full text-sm" id="itemsTable">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-2 py-2 text-center font-medium text-gray-500 w-10">No</th>
                    <th class="px-2 py-2 text-left font-medium text-gray-500 min-w-[190px]">Nama Alat/Bahan *</th>
                    <th class="px-2 py-2 text-left font-medium text-gray-500 min-w-[220px]">Kode/No. Inventaris</th>
                    <th class="px-2 py-2 text-left font-medium text-gray-500 min-w-[110px]">Lokasi</th>
                    <th class="px-2 py-2 text-left font-medium text-gray-500 min-w-[130px]">Jenis Pemeliharaan</th>
                    <th class="px-2 py-2 text-left font-medium text-gray-500 min-w-[100px]">Frekuensi</th>
                    <th class="px-2 py-2 text-left font-medium text-gray-500 min-w-[130px]">Jadwal</th>
                    <th class="px-2 py-2 text-left font-medium text-gray-500 min-w-[100px]">PIC</th>
                    <th class="px-2 py-2 text-left font-medium text-gray-500 min-w-[110px]">Status</th>
                    <th class="px-2 py-2 text-left font-medium text-gray-500 min-w-[140px]">Catatan/Hasil</th>
                    <th class="px-2 py-2 w-10"></th>
                </tr>
            </thead>
            <tbody id="itemsBody">
                @foreach($oldItems as $i => $row)
                    @php
                        $row = is_array($row) ? $row : [];
                        $selectedKodes = $row['kode_inventaris'] ?? [];
                        if (! is_array($selectedKodes)) {
                            $selectedKodes = array_values(array_filter(array_map('trim', preg_split('/[\r\n,]+/', (string) $selectedKodes))));
                        }
                    @endphp
                    <tr class="border-t border-gray-100 item-row">
                        <td class="px-2 py-2 text-center text-gray-500 row-no">{{ $i + 1 }}</td>
                        <td class="px-2 py-2"><input type="text" name="items[{{ $i }}][nama_alat_bahan]" value="{{ $row['nama_alat_bahan'] ?? '' }}" class="w-full rounded border border-gray-300 px-2 py-1.5 text-sm focus:ring-green-500 focus:border-green-500" placeholder="Nama alat/bahan"></td>
                        <td class="px-2 py-2 align-top">
                            <div class="kode-picker space-y-1.5">
                                <div class="flex items-center gap-1">
                                    <select class="kode-add flex-1 min-w-0 rounded border border-gray-300 px-2 py-1.5 text-sm focus:ring-green-500 focus:border-green-500" title="Pilih kode inventaris sesuai lab">
                                        <option value="">+ Pilih kode…</option>
                                    </select>
                                </div>
                                <input type="text" class="kode-manual w-full rounded border border-gray-200 px-2 py-1 text-xs text-gray-600 placeholder-gray-400 focus:ring-green-500 focus:border-green-500" placeholder="atau ketik kode manual, Enter">
                                <div class="kode-chips flex flex-wrap gap-1.5 min-h-[26px] rounded bg-gray-50 border border-dashed border-gray-200 px-1.5 py-1">
                                    @foreach($selectedKodes as $code)
                                        <input type="hidden" name="items[{{ $i }}][kode_inventaris][]" value="{{ $code }}" class="kode-hidden">
                                        <span class="kode-chip inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-green-100 text-green-700 text-[11px] font-medium">
                                            {{ $code }}
                                            <button type="button" class="kode-remove leading-none font-bold hover:text-red-600" data-code="{{ $code }}" title="Hapus">&times;</button>
                                        </span>
                                    @endforeach
                                </div>
                            </div>
                        </td>
                        <td class="px-2 py-2"><input type="text" name="items[{{ $i }}][lokasi]" value="{{ $row['lokasi'] ?? '' }}" class="w-full rounded border border-gray-300 px-2 py-1.5 text-sm" placeholder="Lokasi"></td>
                        <td class="px-2 py-2">
                            @php $jenisVal = $row['jenis_pemeliharaan'] ?? ''; @endphp
                            <select name="items[{{ $i }}][jenis_pemeliharaan]" class="w-full rounded border border-gray-300 px-2 py-1.5 text-sm focus:ring-green-500 focus:border-green-500">
                                <option value="">— Pilih —</option>
                                @if($jenisVal !== '' && ! in_array($jenisVal, $jenisList, true))
                                    <option value="{{ $jenisVal }}" selected>{{ $jenisVal }}</option>
                                @endif
                                @foreach($jenisList as $jenis)
                                    <option value="{{ $jenis }}" {{ $jenisVal === $jenis ? 'selected' : '' }}>{{ $jenis }}</option>
                                @endforeach
                            </select>
                        </td>
                        <td class="px-2 py-2">
                            @php $frekVal = $row['frekuensi'] ?? ''; @endphp
                            <select name="items[{{ $i }}][frekuensi]" class="w-full rounded border border-gray-300 px-2 py-1.5 text-sm focus:ring-green-500 focus:border-green-500">
                                <option value="">— Pilih —</option>
                                @if($frekVal !== '' && ! in_array($frekVal, $frekuensiList, true))
                                    <option value="{{ $frekVal }}" selected>{{ $frekVal }}</option>
                                @endif
                                @foreach($frekuensiList as $frek)
                                    <option value="{{ $frek }}" {{ $frekVal === $frek ? 'selected' : '' }}>{{ $frek }}</option>
                                @endforeach
                            </select>
                        </td>
                        <td class="px-2 py-2"><input type="date" name="items[{{ $i }}][jadwal]" value="{{ $row['jadwal'] ?? '' }}" class="w-full rounded border border-gray-300 px-2 py-1.5 text-sm"></td>
                        <td class="px-2 py-2"><input type="text" name="items[{{ $i }}][pic]" value="{{ $row['pic'] ?? '' }}" class="w-full rounded border border-gray-300 px-2 py-1.5 text-sm" placeholder="PIC"></td>
                        <td class="px-2 py-2">
                            <select name="items[{{ $i }}][status]" class="w-full rounded border border-gray-300 px-2 py-1.5 text-sm">
                                @foreach($statusList as $st)
                                    <option value="{{ $st }}" {{ ($row['status'] ?? 'Terjadwal') === $st ? 'selected' : '' }}>{{ $st }}</option>
                                @endforeach
                            </select>
                        </td>
                        <td class="px-2 py-2"><input type="text" name="items[{{ $i }}][catatan_hasil]" value="{{ $row['catatan_hasil'] ?? '' }}" class="w-full rounded border border-gray-300 px-2 py-1.5 text-sm" placeholder="Catatan/hasil"></td>
                        <td class="px-2 py-2 text-center">
                            <button type="button" class="remove-row p-1.5 rounded-lg hover:bg-red-50 text-red-500" title="Hapus baris">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" /></svg>
                            </button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <p class="text-xs text-gray-500">Baris kosong tidak akan disimpan. Minimal 1 baris dengan Nama Alat/Bahan wajib diisi.</p>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    var body = document.getElementById('itemsBody');
    var addBtn = document.getElementById('addRowBtn');
    function reindex() {
        var rows = body.querySelectorAll('.item-row');
        rows.forEach(function (row, idx) {
            row.querySelector('.row-no').textContent = idx + 1;
            row.querySelectorAll('input, select').forEach(function (el) {
                if (!el.name) return;
                el.name = el.name.replace(/items\[\d+\]/, 'items[' + idx + ']');
            });
        });
    }
    var labSelect = document.getElementById('labSelect');
    var inventarisCache = [];
    var jenisOptions = @json($jenisList ?? []);
    var frekuensiOptions = @json($frekuensiList ?? []);
    var hasJenis = new Set(jenisOptions);
    var hasFrekuensi = new Set(frekuensiOptions);
    function fillOptionSelect(sel, options, knownSet) {
        if (!sel || sel.options.length > 1) return;
        var current = sel.value;
        sel.innerHTML = '<option value="">— Pilih —</option>';
        if (current && !knownSet.has(current)) {
            var o = document.createElement('option');
            o.value = current; o.textContent = current; o.selected = true;
            sel.appendChild(o);
        }
        options.forEach(function (v) {
            var opt = document.createElement('option');
            opt.value = v; opt.textContent = v;
            if (v === current) opt.selected = true;
            sel.appendChild(opt);
        });
    }
    function renderJenisFrekuensi() {
        body.querySelectorAll('.item-row').forEach(function (row) {
            fillOptionSelect(row.querySelector('select[name$="[jenis_pemeliharaan]"]'), jenisOptions, hasJenis);
            fillOptionSelect(row.querySelector('select[name$="[frekuensi]"]'), frekuensiOptions, hasFrekuensi);
        });
    }
    function renderKodeOptions() {
        body.querySelectorAll('.kode-add').forEach(function (sel) {
            var current = sel.value;
            sel.innerHTML = '<option value="">+ Pilih kode…</option>';
            inventarisCache.forEach(function (d) {
                var opt = document.createElement('option');
                opt.value = d.code; opt.textContent = d.label;
                sel.appendChild(opt);
            });
            if (current) sel.value = current;
        });
    }
    function loadInventaris() {
        var labId = labSelect ? labSelect.value : '';
        if (!labId) { inventarisCache = []; renderKodeOptions(); return; }
        fetch('{{ route('jadwal-pemeliharaan.inventaris') }}?laboratory_id=' + encodeURIComponent(labId), {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
        }).then(function (r) { return r.json(); }).then(function (json) {
            inventarisCache = json.data || [];
            renderKodeOptions();
        }).catch(function () {});
    }
    function getKodes(rowEl) {
        return Array.from(rowEl.querySelectorAll('.kode-hidden')).map(function (h) { return h.value; });
    }
    function addKode(rowEl, code) {
        code = (code || '').trim();
        if (!code) return;
        code.split(/[\r\n,]+/).forEach(function (part) {
            part = part.trim();
            if (!part) return;
            if (getKodes(rowEl).indexOf(part) !== -1) return;
            var wrap = rowEl.querySelector('.kode-chips');
            var input = document.createElement('input');
            input.type = 'hidden';
            input.className = 'kode-hidden';
            input.name = 'items[0][kode_inventaris][]';
            input.value = part;
            wrap.appendChild(input);
            var chip = document.createElement('span');
            chip.className = 'kode-chip inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-green-100 text-green-700 text-[11px] font-medium';
            chip.textContent = part + ' ';
            var btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'kode-remove leading-none font-bold hover:text-red-600';
            btn.setAttribute('data-code', part);
            btn.title = 'Hapus';
            btn.innerHTML = '&times;';
            chip.appendChild(btn);
            wrap.appendChild(chip);
        });
        reindex();
    }
    function removeKode(rowEl, code) {
        rowEl.querySelectorAll('.kode-hidden').forEach(function (h) { if (h.value === code) h.remove(); });
        rowEl.querySelectorAll('.kode-chip').forEach(function (c) {
            var btn = c.querySelector('.kode-remove');
            if (btn && btn.getAttribute('data-code') === code) c.remove();
        });
    }
    body.addEventListener('change', function (e) {
        var sel = e.target.closest('.kode-add');
        if (!sel) return;
        addKode(sel.closest('.item-row'), sel.value);
        sel.value = '';
    });
    body.addEventListener('keydown', function (e) {
        var manual = e.target.closest('.kode-manual');
        if (!manual) return;
        if (e.key !== 'Enter') return;
        e.preventDefault();
        addKode(manual.closest('.item-row'), manual.value);
        manual.value = '';
    });
    body.addEventListener('click', function (e) {
        var btn = e.target.closest('.kode-remove');
        if (!btn) return;
        removeKode(btn.closest('.item-row'), btn.getAttribute('data-code'));
    });
    labSelect && labSelect.addEventListener('change', loadInventaris);
    addBtn && addBtn.addEventListener('click', function () {
        var tpl = document.createElement('template');
        tpl.innerHTML = '<tr class="border-t border-gray-100 item-row">' +
            '<td class="px-2 py-2 text-center text-gray-500 row-no"></td>' +
            '<td class="px-2 py-2"><input type="text" name="items[0][nama_alat_bahan]" class="w-full rounded border border-gray-300 px-2 py-1.5 text-sm focus:ring-green-500 focus:border-green-500" placeholder="Nama alat/bahan"></td>' +
            '<td class="px-2 py-2 align-top"><div class="kode-picker space-y-1.5"><div class="flex items-center gap-1"><select class="kode-add flex-1 min-w-0 rounded border border-gray-300 px-2 py-1.5 text-sm focus:ring-green-500 focus:border-green-500"><option value="">+ Pilih kode…</option></select></div><input type="text" class="kode-manual w-full rounded border border-gray-200 px-2 py-1 text-xs text-gray-600 placeholder-gray-400 focus:ring-green-500 focus:border-green-500" placeholder="atau ketik kode manual, Enter"><div class="kode-chips flex flex-wrap gap-1.5 min-h-[26px] rounded bg-gray-50 border border-dashed border-gray-200 px-1.5 py-1"></div></div></td>' +
            '<td class="px-2 py-2"><input type="text" name="items[0][lokasi]" class="w-full rounded border border-gray-300 px-2 py-1.5 text-sm" placeholder="Lokasi"></td>' +
            '<td class="px-2 py-2"><select name="items[0][jenis_pemeliharaan]" class="w-full rounded border border-gray-300 px-2 py-1.5 text-sm focus:ring-green-500 focus:border-green-500"><option value="">— Pilih —</option></select></td>' +
            '<td class="px-2 py-2"><select name="items[0][frekuensi]" class="w-full rounded border border-gray-300 px-2 py-1.5 text-sm focus:ring-green-500 focus:border-green-500"><option value="">— Pilih —</option></select></td>' +
            '<td class="px-2 py-2"><input type="date" name="items[0][jadwal]" class="w-full rounded border border-gray-300 px-2 py-1.5 text-sm"></td>' +
            '<td class="px-2 py-2"><input type="text" name="items[0][pic]" class="w-full rounded border border-gray-300 px-2 py-1.5 text-sm" placeholder="PIC"></td>' +
            '<td class="px-2 py-2"><select name="items[0][status]" class="w-full rounded border border-gray-300 px-2 py-1.5 text-sm"><option>Terjadwal</option><option>Selesai</option><option>Tunda</option></select></td>' +
            '<td class="px-2 py-2"><input type="text" name="items[0][catatan_hasil]" class="w-full rounded border border-gray-300 px-2 py-1.5 text-sm" placeholder="Catatan/hasil"></td>' +
            '<td class="px-2 py-2 text-center"><button type="button" class="remove-row p-1.5 rounded-lg hover:bg-red-50 text-red-500" title="Hapus baris"><svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" /></svg></button></td>' +
            '</tr>';
        body.appendChild(tpl.content.firstChild);
        reindex();
        renderKodeOptions();
        renderJenisFrekuensi();
    });
    body.addEventListener('click', function (e) {
        var btn = e.target.closest('.remove-row');
        if (!btn) return;
        if (body.querySelectorAll('.item-row').length <= 1) { alert('Minimal 1 baris harus ada.'); return; }
        btn.closest('.item-row').remove();
        reindex();
    });
    reindex();
    renderJenisFrekuensi();
    loadInventaris();
});
</script>
@endpush
