@extends('layouts.admin')

@section('title', 'Tambah Inventaris IoT & Jaringan')

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <h4 class="fw-bold py-3 mb-4">Tambah Inventaris IoT & Jaringan</h4>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if($errors->any())
            <div class="alert alert-danger alert-dismissible" role="alert">
                <strong>Terjadi kesalahan!</strong> Periksa kembali form Anda.
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Form Tambah Inventaris IoT & Jaringan</h5>
                <small class="text-muted">Pilih komponen dari data yang sudah ada</small>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.inventaris-iot-jaringan.store') }}" method="POST">
                    @csrf
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="kode_perangkat" class="form-label">Kode Perangkat <span class="text-danger">*</span></label>
                            <input type="text" name="kode_perangkat" id="kode_perangkat" value="{{ $kode_perangkat ?? old('kode_perangkat') }}" class="form-control" readonly>
                        </div>
                        <div class="col-md-6">
                            <label for="nama_inventaris" class="form-label">Nama Inventaris <span class="text-danger">*</span></label>
                            <input type="text" name="nama_inventaris" id="nama_inventaris" value="{{ old('nama_inventaris') }}" class="form-control @error('nama_inventaris') is-invalid @enderror" placeholder="Contoh: Paket Lab IoT A">
                            @error('nama_inventaris')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="kategori" class="form-label">Kategori <span class="text-danger">*</span></label>
                            <select name="kategori" id="kategori" class="form-select @error('kategori') is-invalid @enderror">
                                <option value="">Pilih Kategori</option>
                                <option value="IoT" {{ old('kategori') == 'IoT' ? 'selected' : '' }}>IoT</option>
                                <option value="Jaringan" {{ old('kategori') == 'Jaringan' ? 'selected' : '' }}>Jaringan</option>
                            </select>
                            @error('kategori')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="jenis" class="form-label">Jenis <span class="text-danger">*</span></label>
                            <select name="jenis" id="jenis" class="form-select @error('jenis') is-invalid @enderror">
                                <option value="">Pilih Jenis</option>
                                <option value="Satuan" {{ old('jenis') == 'Satuan' ? 'selected' : '' }}>Satuan</option>
                                <option value="Paket" {{ old('jenis') == 'Paket' ? 'selected' : '' }}>Paket</option>
                                <option value="Sistem" {{ old('jenis') == 'Sistem' ? 'selected' : '' }}>Sistem</option>
                                <option value="Box" {{ old('jenis') == 'Box' ? 'selected' : '' }}>Box</option>
                            </select>
                            @error('jenis')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="lokasi" class="form-label">Lokasi</label>
                            <select name="lokasi" id="lokasi" class="form-select @error('lokasi') is-invalid @enderror">
                                <option value="">Pilih Lokasi</option>
                                @foreach($laboratoriums as $lab)
                                    <option value="{{ $lab->nama_laboratorium }}" {{ old('lokasi') == $lab->nama_laboratorium ? 'selected' : '' }}>{{ $lab->nama_laboratorium }}</option>
                                @endforeach
                            </select>
                            @error('lokasi')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-3">
                            <label for="jumlah_inventaris" class="form-label">Jumlah <span class="text-danger">*</span></label>
                            <input type="number" name="jumlah_inventaris" id="jumlah_inventaris" value="{{ old('jumlah_inventaris', 1) }}" min="1" class="form-control @error('jumlah_inventaris') is-invalid @enderror">
                            @error('jumlah_inventaris')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-3">
                            <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                            <select name="status" id="status" class="form-select @error('status') is-invalid @enderror">
                                <option value="tersedia" {{ old('status') == 'tersedia' ? 'selected' : '' }}>Tersedia</option>
                                <option value="dipinjam" {{ old('status') == 'dipinjam' ? 'selected' : '' }}>Dipinjam</option>
                                <option value="perbaikan" {{ old('status') == 'perbaikan' ? 'selected' : '' }}>Perbaikan</option>
                                <option value="tidak_aktif" {{ old('status') == 'tidak_aktif' ? 'selected' : '' }}>Tidak Aktif</option>
                            </select>
                            @error('status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-12">
                            <label for="catatan" class="form-label">Catatan</label>
                            <textarea name="catatan" id="catatan" rows="3" class="form-control @error('catatan') is-invalid @enderror" placeholder="Catatan tambahan">{{ old('catatan') }}</textarea>
                            @error('catatan')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12">
                            <div class="card border">
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <h6 class="mb-0">Daftar Komponen dalam Inventaris</h6>
                                    <button type="button" class="btn btn-sm btn-primary" id="btn-add-item">
                                        <i class="bx bx-plus me-1"></i> Tambah Komponen
                                    </button>
                                </div>
                                <div class="card-body">
                                    <div id="items-container">
                                        <p class="text-muted small mb-0">Pilih jenis Satuan, Paket, Sistem, atau Box untuk menambahkan komponen.</p>
                                    </div>
                                    @error('items')
                                        <div class="text-danger small mt-2">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="col-12 pt-2">
                            <div class="d-flex gap-2">
                                <a href="{{ route('admin.inventaris-iot-jaringan.index') }}" class="btn btn-outline-secondary">Batal</a>
                                <button type="submit" class="btn btn-primary">Simpan</button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<style>
.komponen-search { position: relative; }
.komponen-dropdown { top: 100%; left: 0; background: #fff; border: 1px solid #e5e5e5; border-radius: .375rem; box-shadow: 0 .5rem 1rem rgba(0,0,0,.12); }
</style>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        var jenisSelect = document.getElementById('jenis');
        var kategoriSelect = document.getElementById('kategori');
        var itemsContainer = document.getElementById('items-container');
        var btnAddItem = document.getElementById('btn-add-item');
        var itemIndex = 0;

        var komponenData = @json($komponen);
        var selectedKategori = '{{ old('kategori') }}';

        function getFilteredOptions(kategori) {
            var options = [{ id: '', label: 'Pilih Komponen' }];
            komponenData.forEach(function (k) {
                if (!kategori || k.kategori === kategori) {
                    options.push({ id: k.id, label: k.nama_komponen + ' (' + k.kategori + ')' });
                }
            });
            return options;
        }

        function buildOptionsHtml(kategori, selectedId) {
            var options = getFilteredOptions(kategori);
            var html = '<option value="">Pilih Komponen</option>';
            options.forEach(function (opt) {
                var selected = (opt.id && selectedId && opt.id == selectedId) ? ' selected' : '';
                html += '<option value="' + opt.id + '"' + selected + '>' + opt.label + '</option>';
            });
            return html;
        }

        function updateJumlahBehavior() {
            if (!jenisSelect || !itemsContainer) return;
            itemsContainer.style.display = 'block';
        }

        function addItemRow(data) {
            var row = document.createElement('div');
            row.className = 'item-row border rounded p-3 mb-2';
            var options = getFilteredOptions(selectedKategori);
            var selectedId = data.komponen_iot_jaringan_id || '';
            var selectedLabel = '';
            options.forEach(function (opt) {
                if (opt.id && opt.id == selectedId) selectedLabel = opt.label;
            });

            row.innerHTML = `
                <div class="row g-2">
                    <div class="col-md-6">
                        <label class="form-label small">Komponen</label>
                        <input type="hidden" name="items[${itemIndex}][komponen_iot_jaringan_id]" class="form-select komponen-select" value="${selectedId}" required>
                        <input type="text" class="form-control komponen-search" placeholder="Cari komponen..." value="${selectedLabel}" autocomplete="off">
                        <div class="komponen-dropdown list-group position-absolute w-100" style="max-height: 200px; overflow-y: auto; z-index: 1050; display: none;"></div>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small">Jumlah</label>
                        <input type="number" name="items[${itemIndex}][jumlah]" class="form-control" value="${data.jumlah || 1}" min="1" required>
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="button" class="btn btn-sm btn-outline-danger btn-remove-item">
                            <i class="bx bx-trash"></i>
                        </button>
                    </div>
                </div>
            `;
            itemsContainer.appendChild(row);
            itemIndex++;

            var searchInput = row.querySelector('.komponen-search');
            var dropdown = row.querySelector('.komponen-dropdown');
            var hiddenInput = row.querySelector('input[type="hidden"]');

            function renderDropdown(filterText) {
                var q = (filterText || '').toLowerCase();
                dropdown.innerHTML = '';
                options.forEach(function (opt) {
                    if (!opt.id) return;
                    if (q && opt.label.toLowerCase().indexOf(q) === -1) return;
                    var item = document.createElement('a');
                    item.href = '#';
                    item.className = 'list-group-item list-group-item-action';
                    item.textContent = opt.label;
                    item.addEventListener('mousedown', function (e) {
                        e.preventDefault();
                        hiddenInput.value = opt.id;
                        searchInput.value = opt.label;
                        dropdown.style.display = 'none';
                    });
                    dropdown.appendChild(item);
                });
                if (dropdown.children.length) dropdown.style.display = 'block';
                else dropdown.style.display = 'none';
            }

            searchInput.addEventListener('focus', function () {
                renderDropdown(searchInput.value);
            });

            searchInput.addEventListener('input', function () {
                renderDropdown(searchInput.value);
            });

            document.addEventListener('mousedown', function (e) {
                if (!row.contains(e.target)) dropdown.style.display = 'none';
            });

            row.querySelector('.btn-remove-item').addEventListener('click', function () {
                row.remove();
            });
        }

        function refreshAllKomponenSelects() {
            var rows = itemsContainer.querySelectorAll('.item-row');
            rows.forEach(function (row) {
                var searchInput = row.querySelector('.komponen-search');
                var hiddenInput = row.querySelector('input[type="hidden"]');
                if (!searchInput || !hiddenInput) return;
                var options = getFilteredOptions(selectedKategori);
                var currentVal = hiddenInput.value;
                var currentLabel = '';
                options.forEach(function (opt) {
                    if (opt.id && opt.id == currentVal) currentLabel = opt.label;
                });
                if (!currentLabel && currentVal) {
                    var found = options.find(function (o) { return o.id == currentVal; });
                    if (found) currentLabel = found.label;
                }
                searchInput.value = currentLabel;
                hiddenInput.value = currentVal;
            });
        }

        if (kategoriSelect) {
            kategoriSelect.addEventListener('change', function () {
                selectedKategori = this.value;
                refreshAllKomponenSelects();
            });
        }

        if (btnAddItem) {
            btnAddItem.addEventListener('click', function () {
                addItemRow({});
            });
        }

        jenisSelect.addEventListener('change', function () {
            updateJumlahBehavior();
            if (itemsContainer.children.length === 0) {
                addItemRow({});
            }
        });

        function initExistingItemRows() {
            var rows = itemsContainer.querySelectorAll('.item-row');
            rows.forEach(function (row) {
                var searchInput = row.querySelector('.komponen-search');
                var dropdown = row.querySelector('.komponen-dropdown');
                var hiddenInput = row.querySelector('input[type="hidden"]');
                if (!searchInput || !dropdown || !hiddenInput) return;

                function renderDropdown(filterText) {
                    var q = (filterText || '').toLowerCase();
                    dropdown.innerHTML = '';
                    var options = getFilteredOptions(selectedKategori);
                    options.forEach(function (opt) {
                        if (!opt.id) return;
                        if (q && opt.label.toLowerCase().indexOf(q) === -1) return;
                        var item = document.createElement('a');
                        item.href = '#';
                        item.className = 'list-group-item list-group-item-action';
                        item.textContent = opt.label;
                        item.addEventListener('mousedown', function (e) {
                            e.preventDefault();
                            hiddenInput.value = opt.id;
                            searchInput.value = opt.label;
                            dropdown.style.display = 'none';
                        });
                        dropdown.appendChild(item);
                    });
                    if (dropdown.children.length) dropdown.style.display = 'block';
                    else dropdown.style.display = 'none';
                }

                searchInput.addEventListener('focus', function () {
                    renderDropdown(searchInput.value);
                });

                searchInput.addEventListener('input', function () {
                    renderDropdown(searchInput.value);
                });

                document.addEventListener('mousedown', function (e) {
                    if (!row.contains(e.target)) dropdown.style.display = 'none';
                });
            });
        }

        initExistingItemRows();

        updateJumlahBehavior();
    });
</script>
@endpush
