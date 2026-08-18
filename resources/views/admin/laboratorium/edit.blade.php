@extends('layouts.admin')

@section('title', 'Edit Laboratorium')

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <h4 class="fw-bold py-3 mb-4">Edit Laboratorium</h4>

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
                <h5 class="card-title mb-0">Form Edit Laboratorium</h5>
                <small class="text-muted">Perbarui data laboratorium</small>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.laboratorium.update', $laboratorium) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="kode_laboratorium" class="form-label">Kode Laboratorium <span class="text-danger">*</span></label>
                            <input type="text" name="kode_laboratorium" id="kode_laboratorium" value="{{ old('kode_laboratorium', $laboratorium->kode_laboratorium) }}" class="form-control @error('kode_laboratorium') is-invalid @enderror" placeholder="Contoh: LAB-001">
                            @error('kode_laboratorium')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="nama_laboratorium" class="form-label">Nama Laboratorium <span class="text-danger">*</span></label>
                            <input type="text" name="nama_laboratorium" id="nama_laboratorium" value="{{ old('nama_laboratorium', $laboratorium->nama_laboratorium) }}" class="form-control @error('nama_laboratorium') is-invalid @enderror" placeholder="Contoh: Laboratorium Komputer">
                            @error('nama_laboratorium')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="gedung" class="form-label">Gedung <span class="text-danger">*</span></label>
                            <input type="text" name="gedung" id="gedung" value="{{ old('gedung', $laboratorium->gedung) }}" class="form-control @error('gedung') is-invalid @enderror" placeholder="Contoh: Gedung A">
                            @error('gedung')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="ruangan" class="form-label">Ruangan <span class="text-danger">*</span></label>
                            <input type="text" name="ruangan" id="ruangan" value="{{ old('ruangan', $laboratorium->ruangan) }}" class="form-control @error('ruangan') is-invalid @enderror" placeholder="Contoh: Lantai 2">
                            @error('ruangan')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="kapasitas" class="form-label">Kapasitas (Orang)</label>
                            <input type="number" name="kapasitas" id="kapasitas" value="{{ old('kapasitas', $laboratorium->kapasitas) }}" min="0" class="form-control @error('kapasitas') is-invalid @enderror" placeholder="Contoh: 40">
                            @error('kapasitas')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                            <select name="status" id="status" class="form-select @error('status') is-invalid @enderror">
                                <option value="aktif" {{ old('status', $laboratorium->status) == 'aktif' ? 'selected' : '' }}>Aktif</option>
                                <option value="non-aktif" {{ old('status', $laboratorium->status) == 'non-aktif' ? 'selected' : '' }}>Non-Aktif</option>
                            </select>
                            @error('status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-12">
                            <label for="fasilitas" class="form-label">Fasilitas</label>
                            <textarea name="fasilitas" id="fasilitas" rows="3" class="form-control @error('fasilitas') is-invalid @enderror" placeholder="Contoh: Proyektor, AC, Whiteboard">{{ old('fasilitas', $laboratorium->fasilitas) }}</textarea>
                            @error('fasilitas')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-12">
                            <label for="catatan" class="form-label">Catatan</label>
                            <textarea name="catatan" id="catatan" rows="3" class="form-control @error('catatan') is-invalid @enderror" placeholder="Catatan tambahan">{{ old('catatan', $laboratorium->catatan) }}</textarea>
                            @error('catatan')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-12 pt-2">
                            <div class="d-flex gap-2">
                                <a href="{{ route('admin.laboratorium.index') }}" class="btn btn-outline-secondary">Batal</a>
                                <button type="submit" class="btn btn-primary">Perbarui</button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
