@extends('layouts.admin')

@section('title', 'Edit Komponen Jaringan')

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <h4 class="fw-bold py-3 mb-4">Edit Komponen Jaringan</h4>

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
                <h5 class="card-title mb-0">Form Edit Komponen Jaringan</h5>
                <small class="text-muted">Perbarui data komponen Jaringan</small>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.komponen-jaringan.update', ['komponen_jaringan' => $komponenJaringan->id]) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="kode_komponen" class="form-label">Kode Komponen</label>
                            <input type="text" name="kode_komponen" id="kode_komponen" value="{{ old('kode_komponen', $komponenJaringan->kode_komponen) }}" class="form-control bg-light" readonly>
                        </div>
                        <div class="col-md-6">
                            <label for="nama_komponen" class="form-label">Nama Komponen <span class="text-danger">*</span></label>
                            <input type="text" name="nama_komponen" id="nama_komponen" value="{{ old('nama_komponen', $komponenJaringan->nama_komponen) }}" class="form-control @error('nama_komponen') is-invalid @enderror" placeholder="Contoh: Switch, Router, Access Point">
                            @error('nama_komponen')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="merek" class="form-label">Merek</label>
                            <input type="text" name="merek" id="merek" value="{{ old('merek', $komponenJaringan->merek) }}" class="form-control @error('merek') is-invalid @enderror" placeholder="Contoh: Cisco, TP-Link, Mikrotik">
                            @error('merek')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-12">
                            <label for="spesifikasi" class="form-label">Spesifikasi</label>
                            <textarea name="spesifikasi" id="spesifikasi" rows="2" class="form-control @error('spesifikasi') is-invalid @enderror" placeholder="Contoh: 24-port Gigabit, PoE, managed">{{ old('spesifikasi', $komponenJaringan->spesifikasi) }}</textarea>
                            @error('spesifikasi')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="jumlah" class="form-label">Jumlah <span class="text-danger">*</span></label>
                            <input type="number" name="jumlah" id="jumlah" value="{{ old('jumlah', $komponenJaringan->jumlah) }}" min="0" class="form-control @error('jumlah') is-invalid @enderror">
                            @error('jumlah')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="lokasi" class="form-label">Lokasi</label>
                            <input type="text" name="lokasi" id="lokasi" value="{{ old('lokasi', $komponenJaringan->lokasi) }}" class="form-control @error('lokasi') is-invalid @enderror" placeholder="Contoh: Server Room, Rack 3">
                            @error('lokasi')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                            <select name="status" id="status" class="form-select @error('status') is-invalid @enderror">
                                <option value="tersedia" {{ old('status', $komponenJaringan->status) == 'tersedia' ? 'selected' : '' }}>Tersedia</option>
                                <option value="dipinjam" {{ old('status', $komponenJaringan->status) == 'dipinjam' ? 'selected' : '' }}>Dipinjam</option>
                                <option value="perbaikan" {{ old('status', $komponenJaringan->status) == 'perbaikan' ? 'selected' : '' }}>Perbaikan</option>
                                <option value="tidak_aktif" {{ old('status', $komponenJaringan->status) == 'tidak_aktif' ? 'selected' : '' }}>Tidak Aktif</option>
                            </select>
                            @error('status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-12">
                            <label for="catatan" class="form-label">Catatan</label>
                            <textarea name="catatan" id="catatan" rows="3" class="form-control @error('catatan') is-invalid @enderror" placeholder="Catatan tambahan">{{ old('catatan', $komponenJaringan->catatan) }}</textarea>
                            @error('catatan')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-12">
                            <label for="foto" class="form-label">Foto Komponen</label>
                            <input type="file" name="foto" id="foto" accept="image/*" class="form-control @error('foto') is-invalid @enderror">
                            <p class="text-xs text-muted mt-1">Format: JPG, JPEG, PNG, GIF, WEBP. Maksimal 2MB.</p>
                            @error('foto')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            @if($komponenJaringan->foto)
                                <img src="{{ asset('storage/' . $komponenJaringan->foto) }}" class="mt-2 max-h-48 rounded border border-slate-200" alt="Foto Komponen">
                            @endif
                            <img id="preview_foto" class="mt-2 hidden max-h-48 rounded border border-slate-200" alt="Preview">
                        </div>
                        <div class="col-12 pt-2">
                            <div class="d-flex gap-2">
                                <a href="{{ route('admin.komponen-jaringan.index') }}" class="btn btn-outline-secondary">Batal</a>
                                <button type="submit" class="btn btn-primary">Perbarui</button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var fotoInput = document.getElementById('foto');
            var preview = document.getElementById('preview_foto');

            if (fotoInput && preview) {
                fotoInput.addEventListener('change', function() {
                    var file = fotoInput.files[0];
                    if (file) {
                        var reader = new FileReader();
                        reader.onload = function(e) {
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
@endsection
