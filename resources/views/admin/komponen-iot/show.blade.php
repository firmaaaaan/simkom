@extends('layouts.admin')

@section('title', 'Detail Komponen IoT')

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <h4 class="fw-bold py-3 mb-4">Detail Komponen IoT</h4>

        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">{{ $komponenIot->nama_komponen }}</h5>
                <div class="d-flex gap-2">
                    <a href="{{ route('admin.komponen-iot.edit', ['komponen_iot' => $komponenIot->id]) }}" class="btn btn-primary">
                        <i class="bx bx-edit me-1"></i> Edit
                    </a>
                    <a href="{{ route('admin.komponen-iot.index') }}" class="btn btn-outline-secondary">Kembali</a>
                </div>
            </div>
            <div class="card-body">
                <div class="row g-4">
                    <div class="col-md-6">
                        <h6 class="text-muted small text-uppercase">Kode Komponen</h6>
                        <p class="mb-0 fw-semibold">{{ $komponenIot->kode_komponen }}</p>
                    </div>
                    <div class="col-md-6">
                        <h6 class="text-muted small text-uppercase">Nama Komponen</h6>
                        <p class="mb-0 fw-semibold">{{ $komponenIot->nama_komponen }}</p>
                    </div>
                    <div class="col-md-6">
                        <h6 class="text-muted small text-uppercase">Kategori</h6>
                        <p class="mb-0">{{ $komponenIot->kategori }}</p>
                    </div>
                    <div class="col-md-6">
                        <h6 class="text-muted small text-uppercase">Merek</h6>
                        <p class="mb-0">{{ $komponenIot->merek ?: '-' }}</p>
                    </div>
                    <div class="col-md-6">
                        <h6 class="text-muted small text-uppercase">Jumlah</h6>
                        <p class="mb-0">{{ $komponenIot->jumlah }}</p>
                    </div>
                    <div class="col-md-6">
                        <h6 class="text-muted small text-uppercase">Lokasi</h6>
                        <p class="mb-0">{{ $komponenIot->lokasi ?: '-' }}</p>
                    </div>
                    <div class="col-md-6">
                        <h6 class="text-muted small text-uppercase">Status</h6>
                        @php
                            $statusClass = [
                                'tersedia' => 'bg-label-success',
                                'dipinjam' => 'bg-label-warning',
                                'perbaikan' => 'bg-label-info',
                                'tidak_aktif' => 'bg-label-danger',
                            ][$komponenIot->status] ?? 'bg-label-secondary';
                        @endphp
                        <span class="badge {{ $statusClass }} rounded-pill">{{ ucfirst($komponenIot->status) }}</span>
                    </div>
                    <div class="col-12">
                        <h6 class="text-muted small text-uppercase">Spesifikasi</h6>
                        <p class="mb-0">{{ $komponenIot->spesifikasi ?: '-' }}</p>
                    </div>
                    <div class="col-12">
                        <h6 class="text-muted small text-uppercase">Catatan</h6>
                        <p class="mb-0">{{ $komponenIot->catatan ?: '-' }}</p>
                    </div>
                    @if($komponenIot->foto)
                        <div class="col-12 mt-4">
                            <h6 class="text-muted small text-uppercase">Foto Komponen</h6>
                            <img src="{{ asset('storage/' . $komponenIot->foto) }}" class="img-fluid rounded border border-slate-200" alt="Foto Komponen" style="max-height: 400px;">
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
