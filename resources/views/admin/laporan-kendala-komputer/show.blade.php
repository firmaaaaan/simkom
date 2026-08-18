@extends('layouts.admin')

@section('title', 'Detail Laporan Kendala Komputer')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <h5 class="mb-0">Detail Laporan Kendala Komputer</h5>
                    <a href="{{ route('admin.laporan-kendala-komputer.index') }}" class="btn btn-outline-secondary">Kembali</a>
                </div>
                <div class="card-body">
                    <div class="row g-4">
                        <div class="col-md-6">
                            <h6 class="text-muted small text-uppercase">Komputer</h6>
                            <p class="mb-0 fw-semibold">{{ $laporanKendalaKomputer->komputer->nama_komputer ?? '-' }}</p>
                            <small class="text-muted">{{ $laporanKendalaKomputer->komputer->kode_komputer ?? '' }}</small>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-muted small text-uppercase">Laboratorium</h6>
                            <p class="mb-0">{{ $laporanKendalaKomputer->komputer->laboratorium->nama_laboratorium ?? '-' }}</p>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-muted small text-uppercase">Nama Pelapor</h6>
                            <p class="mb-0">{{ $laporanKendalaKomputer->nama_pelapor }}</p>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-muted small text-uppercase">NPM/NIM</h6>
                            <p class="mb-0">{{ $laporanKendalaKomputer->npm_nim }}</p>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-muted small text-uppercase">Nama Prodi</h6>
                            <p class="mb-0">{{ $laporanKendalaKomputer->nama_prodi ?? '-' }}</p>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-muted small text-uppercase">Kategori Kerusakan</h6>
                            <p class="mb-0 fw-semibold">{{ ucfirst($laporanKendalaKomputer->kategori_kerusakan ?? '-') }}</p>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-muted small text-uppercase">Kode Tracker</h6>
                            <p class="mb-0"><code>{{ $laporanKendalaKomputer->kode_tracker }}</code></p>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-muted small text-uppercase">Tanggal Lapor</h6>
                            <p class="mb-0">{{ $laporanKendalaKomputer->tanggal_lapor->translatedFormat('d F Y') }}</p>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-muted small text-uppercase">Status</h6>
                            <span class="badge {{ $laporanKendalaKomputer->getStatusBadgeClass() }} rounded-pill">{{ ucfirst($laporanKendalaKomputer->status_kendala) }}</span>
                        </div>
                        <div class="col-12">
                            <h6 class="text-muted small text-uppercase">Deskripsi Kendala</h6>
                            <p class="mb-0">{{ $laporanKendalaKomputer->deskripsi_kendala }}</p>
                        </div>

                        @if($laporanKendalaKomputer->gambar)
                            <div class="col-12">
                                <h6 class="text-muted small text-uppercase">Gambar Kendala</h6>
                                <img src="{{ asset('storage/' . $laporanKendalaKomputer->gambar) }}" class="img-fluid rounded border border-slate-200" style="max-height: 300px;" alt="Gambar Kendala">
                            </div>
                        @endif

                        <div class="col-12 mt-4">
                            <div class="card bg-label-primary border-0">
                                <div class="card-body">
                                    <h6 class="card-title mb-3">Update Status</h6>
                                    <form method="POST" action="{{ route('admin.laporan-kendala-komputer.update-status', $laporanKendalaKomputer) }}">
                                        @csrf
                                        <div class="row g-3">
                                            <div class="col-md-4">
                                                <label class="form-label">Status <span class="text-danger">*</span></label>
                                                <select name="status_kendala" class="form-select" required>
                                                    <option value="menunggu" {{ $laporanKendalaKomputer->status_kendala == 'menunggu' ? 'selected' : '' }}>Menunggu</option>
                                                    <option value="diperbaiki" {{ $laporanKendalaKomputer->status_kendala == 'diperbaiki' ? 'selected' : '' }}>Diperbaiki</option>
                                                    <option value="selesai" {{ $laporanKendalaKomputer->status_kendala == 'selesai' ? 'selected' : '' }}>Selesai</option>
                                                </select>
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label">Tanggal Perbaikan</label>
                                                <input type="date" name="tanggal_perbaikan" class="form-control" value="{{ old('tanggal_perbaikan', $laporanKendalaKomputer->tanggal_perbaikan?->format('Y-m-d')) }}">
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label">Catatan Admin</label>
                                                <input type="text" name="catatan_admin" class="form-control" value="{{ old('catatan_admin', $laporanKendalaKomputer->catatan_admin) }}" placeholder="Catatan opsional">
                                            </div>
                                            <div class="col-12">
                                                <button type="submit" class="btn btn-primary">Update Status</button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
