@extends('layouts.admin')

@section('title', 'Edit Software')

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <h4 class="fw-bold py-3 mb-4">Edit Software</h4>

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
                <h5 class="card-title mb-0">Form Edit Software</h5>
                <small class="text-muted">Perbarui data software</small>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.software.update', $software) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="kode_software" class="form-label">Kode Software</label>
                            <input type="text" name="kode_software" id="kode_software" value="{{ old('kode_software', $software->kode_software) }}" class="form-control bg-light" readonly>
                        </div>
                        <div class="col-md-6">
                            <label for="nama_software" class="form-label">Nama Software <span class="text-danger">*</span></label>
                            <input type="text" name="nama_software" id="nama_software" value="{{ old('nama_software', $software->nama_software) }}" class="form-control @error('nama_software') is-invalid @enderror" placeholder="Contoh: Microsoft Office 365">
                            @error('nama_software')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="kategori" class="form-label">Kategori <span class="text-danger">*</span></label>
                            <select name="kategori" id="kategori" class="form-select @error('kategori') is-invalid @enderror">
                                <option value="">Pilih Kategori</option>
                                <option value="Operating System" {{ old('kategori', $software->kategori) == 'Operating System' ? 'selected' : '' }}>Operating System</option>
                                <option value="Office" {{ old('kategori', $software->kategori) == 'Office' ? 'selected' : '' }}>Office</option>
                                <option value="Programming" {{ old('kategori', $software->kategori) == 'Programming' ? 'selected' : '' }}>Programming</option>
                                <option value="Design" {{ old('kategori', $software->kategori) == 'Design' ? 'selected' : '' }}>Design</option>
                                <option value="Database" {{ old('kategori', $software->kategori) == 'Database' ? 'selected' : '' }}>Database</option>
                                <option value="Antivirus" {{ old('kategori', $software->kategori) == 'Antivirus' ? 'selected' : '' }}>Antivirus</option>
                                <option value="Lainnya" {{ old('kategori', $software->kategori) == 'Lainnya' ? 'selected' : '' }}>Lainnya</option>
                            </select>
                            @error('kategori')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="versi" class="form-label">Versi</label>
                            <input type="text" name="versi" id="versi" value="{{ old('versi', $software->versi) }}" class="form-control @error('versi') is-invalid @enderror" placeholder="Contoh: 21H2, 2024">
                            @error('versi')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="lisensi" class="form-label">Lisensi <span class="text-danger">*</span></label>
                            <select name="lisensi" id="lisensi" class="form-select @error('lisensi') is-invalid @enderror">
                                <option value="gratis" {{ old('lisensi', $software->lisensi) == 'gratis' ? 'selected' : '' }}>Gratis</option>
                                <option value="berbayar" {{ old('lisensi', $software->lisensi) == 'berbayar' ? 'selected' : '' }}>Berbayar</option>
                                <option value="edukasi" {{ old('lisensi', $software->lisensi) == 'edukasi' ? 'selected' : '' }}>Edukasi</option>
                                <option value="trial" {{ old('lisensi', $software->lisensi) == 'trial' ? 'selected' : '' }}>Trial</option>
                                <option value="open_source" {{ old('lisensi', $software->lisensi) == 'open_source' ? 'selected' : '' }}>Open Source</option>
                            </select>
                            @error('lisensi')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="tanggal_instalasi" class="form-label">Tanggal Instalasi</label>
                            <input type="date" name="tanggal_instalasi" id="tanggal_instalasi" value="{{ old('tanggal_instalasi', $software->tanggal_instalasi) }}" class="form-control @error('tanggal_instalasi') is-invalid @enderror">
                            @error('tanggal_instalasi')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="tanggal_expired" class="form-label">Tanggal Expired</label>
                            <input type="date" name="tanggal_expired" id="tanggal_expired" value="{{ old('tanggal_expired', $software->tanggal_expired) }}" class="form-control @error('tanggal_expired') is-invalid @enderror">
                            @error('tanggal_expired')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                            <select name="status" id="status" class="form-select @error('status') is-invalid @enderror">
                                <option value="aktif" {{ old('status', $software->status) == 'aktif' ? 'selected' : '' }}>Aktif</option>
                                <option value="tidak_aktif" {{ old('status', $software->status) == 'tidak_aktif' ? 'selected' : '' }}>Tidak Aktif</option>
                                <option value="trial" {{ old('status', $software->status) == 'trial' ? 'selected' : '' }}>Trial</option>
                            </select>
                            @error('status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-12">
                            <label for="catatan" class="form-label">Catatan</label>
                            <textarea name="catatan" id="catatan" rows="3" class="form-control @error('catatan') is-invalid @enderror" placeholder="Catatan tambahan">{{ old('catatan', $software->catatan) }}</textarea>
                            @error('catatan')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-12 pt-2">
                            <div class="d-flex gap-2">
                                <a href="{{ route('admin.software.index') }}" class="btn btn-outline-secondary">Batal</a>
                                <button type="submit" class="btn btn-primary">Perbarui</button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
