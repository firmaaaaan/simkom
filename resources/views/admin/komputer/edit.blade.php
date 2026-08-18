@extends('layouts.admin')

@section('title', 'Edit Komputer')

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <h4 class="fw-bold py-3 mb-4">Edit Komputer</h4>

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
                <h5 class="card-title mb-0">Form Edit Komputer</h5>
                <small class="text-muted">Perbarui data komputer</small>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.komputer.update', $komputer) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="kode_komputer" class="form-label">Kode Komputer</label>
                            <input type="text" name="kode_komputer" id="kode_komputer" value="{{ $komputer->kode_komputer }}" class="form-control bg-light" readonly>
                        </div>
                        <div class="col-md-6">
                            <label for="nama_komputer" class="form-label">Nama Komputer <span class="text-danger">*</span></label>
                            <input type="text" name="nama_komputer" id="nama_komputer" value="{{ old('nama_komputer', $komputer->nama_komputer) }}" class="form-control @error('nama_komputer') is-invalid @enderror" placeholder="Contoh: PC Komputer 01">
                            @error('nama_komputer')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="laboratorium_id" class="form-label">Laboratorium</label>
                            <select name="laboratorium_id" id="laboratorium_id" class="form-select @error('laboratorium_id') is-invalid @enderror">
                                <option value="">Pilih Laboratorium</option>
                                @foreach($laboratoriums as $lab)
                                    <option value="{{ $lab->id }}" {{ old('laboratorium_id', $komputer->laboratorium_id) == $lab->id ? 'selected' : '' }}>{{ $lab->nama_laboratorium }} ({{ $lab->kode_laboratorium }})</option>
                                @endforeach
                            </select>
                            @error('laboratorium_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                            <select name="status" id="status" class="form-select @error('status') is-invalid @enderror">
                                <option value="aktif" {{ old('status', $komputer->status) == 'aktif' ? 'selected' : '' }}>Aktif</option>
                                <option value="tidak_aktif" {{ old('status', $komputer->status) == 'tidak_aktif' ? 'selected' : '' }}>Tidak Aktif</option>
                                <option value="perbaikan" {{ old('status', $komputer->status) == 'perbaikan' ? 'selected' : '' }}>Perbaikan</option>
                                <option value="rusak" {{ old('status', $komputer->status) == 'rusak' ? 'selected' : '' }}>Rusak</option>
                            </select>
                            @error('status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-12">
                            <label for="foto" class="form-label">Foto / Gambar</label>
                            <input type="file" name="foto" id="foto" class="form-control @error('foto') is-invalid @enderror" accept="image/*">
                            <small class="text-muted">Format: jpeg, png, jpg, gif, webp. Maksimal 2MB. Kosongkan jika tidak ingin mengganti.</small>
                            @error('foto')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            @if($komputer->foto_url)
                                <div class="mt-2">
                                    <img src="{{ $komputer->foto_url }}" alt="Foto Komputer" class="img-thumbnail" style="max-height: 120px;">
                                </div>
                            @endif
                        </div>
                        @include('partials.select-searchable', [
                            'name' => 'hardware_ids',
                            'label' => 'Hardware (dari data yang ada)',
                            'group' => 'Hardware',
                            'items' => $hardwareOptions ?? [],
                            'errorKey' => 'hardware_ids',
                        ])
                        @include('partials.select-searchable', [
                            'name' => 'software_ids',
                            'label' => 'Software (dari data yang ada)',
                            'group' => 'Software',
                            'items' => $softwareOptions ?? [],
                            'errorKey' => 'software_ids',
                        ])
                        <div class="col-12">
                            <label for="spesifikasi" class="form-label">Spesifikasi (otomatis)</label>
                            <input type="text" name="spesifikasi" id="spesifikasi" value="{{ old('spesifikasi', $komputer->spesifikasi) }}" class="form-control bg-light" readonly placeholder="Akan diisi otomatis berdasarkan hardware dan software yang dipilih">
                            <small class="text-muted">Nilai ini dibuat otomatis dari hardware dan software yang terpilih — tidak perlu diisi manual.</small>
                        </div>
                        <div class="col-12">
                            <label for="catatan" class="form-label">Catatan</label>
                            <textarea name="catatan" id="catatan" rows="3" class="form-control @error('catatan') is-invalid @enderror" placeholder="Catatan tambahan">{{ old('catatan', $komputer->catatan) }}</textarea>
                            @error('catatan')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-12 pt-2">
                            <div class="d-flex gap-2">
                                <a href="{{ route('admin.komputer.index') }}" class="btn btn-outline-secondary">Batal</a>
                                <button type="submit" class="btn btn-primary">Perbarui</button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @include('partials.select-searchable-js')
@endsection
