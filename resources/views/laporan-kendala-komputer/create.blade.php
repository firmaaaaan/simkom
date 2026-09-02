@extends('layouts.public')

@section('title', 'Lapor Kendala Komputer')

@section('content')
<div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden mb-6">
        <div class="bg-primary-600 px-6 py-5">
            <h1 class="text-xl font-bold text-white">Lapor Kendala Komputer</h1>
            <p class="text-primary-100 text-sm mt-1">Isi form berikut untuk melaporkan kendala komputer di laboratorium</p>
        </div>
        <div class="p-6">
            @if(session('error'))
                <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg mb-4">
                    {{ session('error') }}
                </div>
            @endif

            @if($errors->any())
                <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg mb-4">
                    <strong>Terjadi kesalahan!</strong> Periksa kembali form Anda.
                </div>
            @endif

            <div class="space-y-4">
                <div>
                    <label for="laboratorium_id" class="block text-sm font-medium text-slate-700 mb-1">Pilih Laboratorium</label>
                    <select name="laboratorium_id" id="laboratorium_id" class="w-full rounded-lg border-slate-300 focus:border-primary-500 focus:ring-primary-500 px-3 py-2 border @error('laboratorium_id') border-red-500 @enderror">
                        <option value="">-- Pilih Laboratorium --</option>
                        @foreach($laboratoriums as $lab)
                            <option value="{{ $lab->id }}" {{ old('laboratorium_id') == $lab->id ? 'selected' : '' }}>
                                {{ $lab->nama_laboratorium }} ({{ $lab->kode_laboratorium }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-3">Pilih Komputer <span class="text-red-500">*</span></label>

                    <p id="komputer-hint" class="text-sm text-slate-400 bg-slate-50 border border-dashed border-slate-200 rounded-xl px-4 py-6 text-center mb-3">
                        Silakan pilih laboratorium terlebih dahulu untuk menampilkan daftar komputer.
                    </p>

                    @php
                        $statusLabels = ['aktif' => 'Normal', 'tidak_aktif' => 'Tidak Aktif', 'perbaikan' => 'Perbaikan', 'rusak' => 'Rusak'];
                        $statusColors = [
                            'aktif' => 'bg-green-50 text-green-700',
                            'tidak_aktif' => 'bg-slate-100 text-slate-600',
                            'perbaikan' => 'bg-yellow-50 text-yellow-700',
                            'rusak' => 'bg-red-50 text-red-700',
                        ];
                    @endphp
                    <div id="komputer-cards" class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-3">
                        @foreach($komputers as $komputer)
                            @php $selected = old('komputer_id') == $komputer->id; @endphp
                            <label data-lab="{{ $komputer->laboratorium->id ?? '' }}" class="komputer-card block cursor-pointer {{ $selected ? '' : 'hidden' }}">
                                <input type="radio" name="komputer_id" value="{{ $komputer->id }}" class="sr-only" {{ $selected ? 'checked' : '' }} required>
                                <span class="card-body relative flex flex-col items-center gap-1 p-3 pt-3.5 rounded-xl border-2 bg-white transition-all hover:border-primary-300 hover:bg-primary-50/40 {{ $selected ? 'border-primary-500 bg-primary-50 shadow-md' : 'border-slate-200' }}">
                                    <span class="card-check absolute -top-2 -right-2 w-5 h-5 rounded-full border-2 flex items-center justify-center {{ $selected ? 'border-primary-600 bg-primary-600' : 'border-slate-300 bg-white' }}">
                                        <svg class="card-check-icon w-3 h-3 text-white {{ $selected ? '' : 'hidden' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                                        </svg>
                                    </span>
                                    <span class="w-9 h-9 rounded-lg bg-slate-100 flex items-center justify-center mb-1">
                                        <svg class="w-5 h-5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z"/>
                                        </svg>
                                    </span>
                                    <span class="block w-full text-xs font-semibold text-slate-800 truncate text-center" title="{{ $komputer->nama_komputer }}">{{ $komputer->nama_komputer }}</span>
                                    <span class="block text-[10px] text-slate-500">{{ $komputer->kode_komputer }}</span>
                                    <span class="text-[9px] font-semibold px-1.5 py-0.5 rounded-full mt-0.5 {{ $statusColors[$komputer->status] ?? 'bg-slate-100 text-slate-600' }}">{{ $statusLabels[$komputer->status] ?? ucfirst($komputer->status) }}</span>
                                </span>
                            </label>
                        @endforeach
                    </div>

                    <p id="komputer-empty" class="hidden text-sm text-slate-500 bg-slate-50 border border-dashed border-slate-200 rounded-xl px-4 py-6 text-center">
                        Tidak ada komputer di laboratorium ini.
                    </p>
                    @error('komputer_id') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
                </div>

            </div>
        </div>
    </div>
</div>

<div id="kendala-modal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4">
    <div id="kendala-modal-backdrop" class="absolute inset-0 bg-black/50 backdrop-blur-sm"></div>
    <div class="relative bg-white rounded-2xl shadow-xl w-full max-w-2xl max-h-[90vh] overflow-y-auto">
        <div class="sticky top-0 bg-white border-b border-slate-200 px-6 py-4 flex items-center justify-between rounded-t-2xl z-10">
            <div>
                <h2 class="text-lg font-bold text-slate-900">Lapor Kendala Komputer</h2>
                <div id="modal-komputer-info" class="text-sm text-slate-500 mt-0.5"></div>
            </div>
            <button type="button" id="kendala-modal-close" class="text-slate-400 hover:text-slate-600 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <form method="POST" action="{{ route('laporan-kendala-komputer.store') }}" class="p-6 space-y-4" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="komputer_id" id="modal-komputer-id" required>

            <div id="modal-komputer-detail" class="bg-slate-50 rounded-xl p-4 border border-slate-100">
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 text-sm">
                    <div>
                        <span class="text-slate-500">Nama Komputer</span>
                        <div id="info-nama-komputer" class="font-medium text-slate-900">-</div>
                    </div>
                    <div>
                        <span class="text-slate-500">Kode Komputer</span>
                        <div id="info-kode-komputer" class="font-medium text-slate-900">-</div>
                    </div>
                    <div>
                        <span class="text-slate-500">Laboratorium</span>
                        <div id="info-laboratorium" class="font-medium text-slate-900">-</div>
                    </div>
                    <div>
                        <span class="text-slate-500">Status</span>
                        <div id="info-status" class="font-medium text-slate-900">-</div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="nama_pelapor" class="block text-sm font-medium text-slate-700 mb-1">Nama Lengkap <span class="text-red-500">*</span></label>
                    <input type="text" name="nama_pelapor" id="nama_pelapor" value="{{ old('nama_pelapor') }}" class="w-full rounded-lg border-slate-300 focus:border-primary-500 focus:ring-primary-500 px-3 py-2 border @error('nama_pelapor') border-red-500 @enderror" placeholder="Masukkan nama lengkap" required>
                    @error('nama_pelapor') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label for="npm_nim" class="block text-sm font-medium text-slate-700 mb-1">NPM/NIM <span class="text-red-500">*</span></label>
                    <input type="text" name="npm_nim" id="npm_nim" value="{{ old('npm_nim') }}" class="w-full rounded-lg border-slate-300 focus:border-primary-500 focus:ring-primary-500 px-3 py-2 border @error('npm_nim') border-red-500 @enderror" placeholder="Masukkan NPM atau NIM" required>
                    @error('npm_nim') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
                </div>
            </div>

            <div>
                <label for="nama_prodi" class="block text-sm font-medium text-slate-700 mb-1">Nama Prodi (Opsional)</label>
                <input type="text" name="nama_prodi" id="nama_prodi" value="{{ old('nama_prodi') }}" class="w-full rounded-lg border-slate-300 focus:border-primary-500 focus:ring-primary-500 px-3 py-2 border @error('nama_prodi') border-red-500 @enderror" placeholder="Contoh: Teknik Informatika">
                @error('nama_prodi') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
            </div>

            <div>
                <label for="kategori_kerusakan" class="block text-sm font-medium text-slate-700 mb-1">Kategori Kerusakan</label>
                <select name="kategori_kerusakan" id="kategori_kerusakan" class="w-full rounded-lg border-slate-300 focus:border-primary-500 focus:ring-primary-500 px-3 py-2 border @error('kategori_kerusakan') border-red-500 @enderror">
                    <option value="">-- Pilih Kategori Kerusakan --</option>
                    <option value="hardware" {{ old('kategori_kerusakan') == 'hardware' ? 'selected' : '' }}>Hardware</option>
                    <option value="software" {{ old('kategori_kerusakan') == 'software' ? 'selected' : '' }}>Software</option>
                    <option value="jaringan" {{ old('kategori_kerusakan') == 'jaringan' ? 'selected' : '' }}>Jaringan</option>
                    <option value="lainnya" {{ old('kategori_kerusakan') == 'lainnya' ? 'selected' : '' }}>Lainnya</option>
                </select>
                @error('kategori_kerusakan') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
            </div>

            <div>
                <label for="kondisi" class="block text-sm font-medium text-slate-700 mb-1">Kondisi Kendala <span class="text-red-500">*</span></label>
                <select name="kondisi" id="kondisi" class="w-full rounded-lg border-slate-300 focus:border-primary-500 focus:ring-primary-500 px-3 py-2 border @error('kondisi') border-red-500 @enderror" required>
                    <option value="">-- Pilih Kondisi --</option>
                    <option value="ringan" {{ old('kondisi') == 'ringan' ? 'selected' : '' }}>Ringan (tidak mengganggu praktikum)</option>
                    <option value="berat" {{ old('kondisi') == 'berat' ? 'selected' : '' }}>Berat (tidak bisa digunakan)</option>
                </select>
                @error('kondisi') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
            </div>

            <div>
                <label for="deskripsi_kendala" class="block text-sm font-medium text-slate-700 mb-1">Deskripsi Kendala <span class="text-red-500">*</span></label>
                <textarea name="deskripsi_kendala" id="deskripsi_kendala" rows="4" class="w-full rounded-lg border-slate-300 focus:border-primary-500 focus:ring-primary-500 px-3 py-2 border @error('deskripsi_kendala') border-red-500 @enderror" placeholder="Jelaskan kendala yang terjadi..." required>{{ old('deskripsi_kendala') }}</textarea>
                @error('deskripsi_kendala') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
            </div>

            <div>
                <label for="gambar" class="block text-sm font-medium text-slate-700 mb-1">Upload Gambar (Opsional)</label>
                <input type="file" name="gambar" id="gambar" accept="image/*" class="w-full rounded-lg border-slate-300 focus:border-primary-500 focus:ring-primary-500 px-3 py-2 border @error('gambar') border-red-500 @enderror">
                <p class="text-xs text-slate-500 mt-1">Format: JPG, JPEG, PNG. Maksimal 2MB.</p>
                @error('gambar') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
                <img id="preview_gambar" class="mt-2 hidden max-h-48 rounded-lg border border-slate-200" alt="Preview">
            </div>

            <div class="flex items-center gap-3 pt-2 border-t border-slate-200">
                <button type="submit" class="bg-primary-600 text-white px-6 py-2.5 rounded-lg hover:bg-primary-700 transition-colors font-medium">
                    Kirim Laporan
                </button>
                <button type="button" id="kendala-modal-cancel" class="text-slate-600 hover:text-slate-900 transition-colors">Batal</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        var laboratoriumSelect = document.getElementById('laboratorium_id');
        var cards = Array.prototype.slice.call(document.querySelectorAll('.komputer-card'));
        var hint = document.getElementById('komputer-hint');
        var emptyState = document.getElementById('komputer-empty');
        var modal = document.getElementById('kendala-modal');
        var modalBackdrop = document.getElementById('kendala-modal-backdrop');
        var modalClose = document.getElementById('kendala-modal-close');
        var modalCancel = document.getElementById('kendala-modal-cancel');
        var modalKomputerId = document.getElementById('modal-komputer-id');
        var modalKomputerInfo = document.getElementById('modal-komputer-info');

        @php
            $komputerJson = $komputers->map(fn($k) => [
                'id' => $k->id,
                'nama' => $k->nama_komputer,
                'kode' => $k->kode_komputer,
                'lab' => $k->laboratorium->nama_laboratorium ?? '-',
                'status' => $k->status,
            ]);
        @endphp
        var komputerData = @json($komputerJson);

        function openModal() {
            modal.classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }

        function closeModal() {
            modal.classList.add('hidden');
            document.body.style.overflow = '';
        }

        function syncCardStates() {
            cards.forEach(function (card) {
                var radio = card.querySelector('input[name="komputer_id"]');
                var isChecked = !!(radio && radio.checked);
                var body = card.querySelector('.card-body');
                if (body) {
                    body.classList.toggle('border-slate-200', !isChecked);
                    body.classList.toggle('border-primary-500', isChecked);
                    body.classList.toggle('bg-white', !isChecked);
                    body.classList.toggle('bg-primary-50', isChecked);
                    body.classList.toggle('shadow-md', isChecked);
                }
                var check = card.querySelector('.card-check');
                if (check) {
                    check.classList.toggle('border-slate-300', !isChecked);
                    check.classList.toggle('border-primary-600', isChecked);
                    check.classList.toggle('bg-white', !isChecked);
                    check.classList.toggle('bg-primary-600', isChecked);
                }
                var icon = card.querySelector('.card-check-icon');
                if (icon) icon.classList.toggle('hidden', !isChecked);
            });
        }

        function showKendalaModal() {
            var selectedRadio = document.querySelector('input[name="komputer_id"]:checked');
            if (!selectedRadio) return;

            var komputerId = parseInt(selectedRadio.value);
            var data = komputerData.find(function (k) { return k.id === komputerId; });
            if (!data) return;

            var statusLabels = { 'aktif': 'Normal', 'tidak_aktif': 'Tidak Aktif', 'perbaikan': 'Perbaikan', 'rusak': 'Rusak' };

            modalKomputerId.value = data.id;
            modalKomputerInfo.textContent = data.nama + ' — ' + data.kode;
            document.getElementById('info-nama-komputer').textContent = data.nama;
            document.getElementById('info-kode-komputer').textContent = data.kode;
            document.getElementById('info-laboratorium').textContent = data.lab;
            document.getElementById('info-status').textContent = statusLabels[data.status] || data.status;

            openModal();
        }

        function filterCards() {
            var labId = laboratoriumSelect ? laboratoriumSelect.value : '';
            var visible = 0;

            cards.forEach(function (card) {
                var show = !labId || card.getAttribute('data-lab') === labId;
                card.classList.toggle('hidden', !show);
                if (show) visible++;
            });

            if (hint) hint.classList.toggle('hidden', !!labId);
            if (emptyState) emptyState.classList.toggle('hidden', visible > 0 || !labId);

            cards.forEach(function (card) {
                if (card.classList.contains('hidden')) {
                    var radio = card.querySelector('input[name="komputer_id"]');
                    if (radio && radio.checked) radio.checked = false;
                }
            });

            syncCardStates();
        }

        if (laboratoriumSelect) {
            laboratoriumSelect.addEventListener('change', filterCards);
        }

        cards.forEach(function (card) {
            var radio = card.querySelector('input[name="komputer_id"]');
            if (radio) {
                radio.addEventListener('change', function () {
                    syncCardStates();
                    showKendalaModal();
                });
            }
        });

        if (modalClose) modalClose.addEventListener('click', closeModal);
        if (modalCancel) modalCancel.addEventListener('click', closeModal);
        if (modalBackdrop) modalBackdrop.addEventListener('click', closeModal);
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && !modal.classList.contains('hidden')) closeModal();
        });

        filterCards();
        syncCardStates();

        var gambarInput = document.getElementById('gambar');
        var preview = document.getElementById('preview_gambar');

        if (gambarInput && preview) {
            gambarInput.addEventListener('change', function () {
                var file = gambarInput.files[0];
                if (file) {
                    var reader = new FileReader();
                    reader.onload = function (e) {
                        preview.src = e.target.result;
                        preview.classList.remove('hidden');
                    };
                    reader.readAsDataURL(file);
                } else {
                    preview.classList.add('hidden');
                }
            });
        }
    });
</script>
@endpush