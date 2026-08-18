@extends('layouts.admin')

@section('title', 'Tambah Hardware')

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <h4 class="fw-bold py-3 mb-4">Tambah Hardware</h4>

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
                <h5 class="card-title mb-0">Form Tambah Hardware</h5>
                <small class="text-muted">Isi data hardware dengan lengkap dan benar</small>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.hardware.store') }}" method="POST">
                    @csrf
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="kode_hardware" class="form-label">Kode Hardware</label>
                            <input type="text" name="kode_hardware" id="kode_hardware" value="{{ $kode_hardware ?? '' }}" class="form-control bg-light" readonly>
                        </div>
                        <div class="col-md-6">
                            <label for="nama_hardware" class="form-label">Nama Hardware <span class="text-danger">*</span></label>
                            <input type="text" name="nama_hardware" id="nama_hardware" value="{{ old('nama_hardware') }}" class="form-control @error('nama_hardware') is-invalid @enderror" placeholder="Contoh: RAM DDR4 8GB">
                            @error('nama_hardware')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="kategori" class="form-label">Kategori <span class="text-danger">*</span></label>
                            <select name="kategori" id="kategori" class="form-select @error('kategori') is-invalid @enderror">
                                <option value="">Pilih Kategori</option>
                                <option value="RAM" {{ old('kategori') == 'RAM' ? 'selected' : '' }}>RAM</option>
                                <option value="VGA" {{ old('kategori') == 'VGA' ? 'selected' : '' }}>VGA / GPU</option>
                                <option value="SSD" {{ old('kategori') == 'SSD' ? 'selected' : '' }}>SSD</option>
                                <option value="HDD" {{ old('kategori') == 'HDD' ? 'selected' : '' }}>HDD</option>
                                <option value="Motherboard" {{ old('kategori') == 'Motherboard' ? 'selected' : '' }}>Motherboard</option>
                                <option value="Processor" {{ old('kategori') == 'Processor' ? 'selected' : '' }}>Processor</option>
                                <option value="Monitor" {{ old('kategori') == 'Monitor' ? 'selected' : '' }}>Monitor</option>
                                <option value="Keyboard" {{ old('kategori') == 'Keyboard' ? 'selected' : '' }}>Keyboard</option>
                                <option value="Mouse" {{ old('kategori') == 'Mouse' ? 'selected' : '' }}>Mouse</option>
                                <option value="Lainnya" {{ old('kategori') == 'Lainnya' ? 'selected' : '' }}>Lainnya</option>
                            </select>
                            @error('kategori')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="merek" class="form-label">Merek</label>
                            <input type="text" name="merek" id="merek" value="{{ old('merek') }}" class="form-control @error('merek') is-invalid @enderror" placeholder="Contoh: Kingston, Samsung, ASUS">
                            @error('merek')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-12">
                            <label for="spesifikasi" class="form-label">Spesifikasi</label>
                            <textarea name="spesifikasi" id="spesifikasi" rows="2" class="form-control @error('spesifikasi') is-invalid @enderror" placeholder="Contoh: DDR4 8GB 3200MHz">{{ old('spesifikasi') }}</textarea>
                            @error('spesifikasi')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="jumlah" class="form-label">Jumlah <span class="text-danger">*</span></label>
                            <input type="number" name="jumlah" id="jumlah" value="{{ old('jumlah', 0) }}" min="0" class="form-control @error('jumlah') is-invalid @enderror">
                            @error('jumlah')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="lokasi" class="form-label">Lokasi</label>
                            <input type="text" name="lokasi" id="lokasi" value="{{ old('lokasi') }}" class="form-control @error('lokasi') is-invalid @enderror" placeholder="Contoh: Lab Komputer, Rak A">
                            @error('lokasi')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
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
                        <div class="col-12 pt-2">
                            <div class="d-flex gap-2">
                                <a href="{{ route('admin.hardware.index') }}" class="btn btn-outline-secondary">Batal</a>
                                <button type="submit" class="btn btn-primary">Simpan</button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
