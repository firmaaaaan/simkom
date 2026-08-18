@extends('layouts.admin')

@section('title', 'Detail Laboratorium')

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="fw-bold mb-0">Detail Laboratorium</h4>
            <div class="d-flex gap-2">
                <a href="{{ route('admin.laboratorium.edit', $laboratorium) }}" class="btn btn-warning">
                    <i class="bx bx-edit me-1"></i> Edit
                </a>
                <a href="{{ route('admin.laboratorium.index') }}" class="btn btn-outline-secondary">
                    <i class="bx bx-arrow-back"></i> Kembali
                </a>
            </div>
        </div>

        <div class="row">
            <div class="col-md-4 mb-4">
                <div class="card shadow-sm sticky-top" style="top: 78px; z-index: 1020;">
                    <div class="card-header bg-transparent border-0">
                        <h5 class="card-title mb-0">{{ $laboratorium->nama_laboratorium }}</h5>
                        <small class="text-muted">{{ $laboratorium->kode_laboratorium }}</small>
                    </div>
                    <div class="card-body">
                        <div class="text-center">
                            <div class="avatar avatar-xl mx-auto mb-3">
                                <span class="avatar-initial rounded bg-label-primary" style="font-size: 2rem;">{{ substr($laboratorium->nama_laboratorium, 0, 2) }}</span>
                            </div>
                            @php
                                $statusClass = $laboratorium->status == 'aktif' ? 'bg-label-success' : 'bg-label-danger';
                            @endphp
                            <span class="badge {{ $statusClass }} rounded-pill">Status: {{ ucfirst($laboratorium->status) }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-8">
                <div class="card mb-4 shadow-sm">
                    <div class="card-header d-flex align-items-center">
                        <i class="bx bx-building-house bx-sm text-primary me-2"></i>
                        <h5 class="card-title mb-0">Informasi Laboratorium</h5>
                    </div>
                    <div class="card-body">
                        <table class="table table-borderless mb-0">
                            <tr>
                                <th class="text-muted" style="width: 40%;">Kode Laboratorium</th>
                                <td>{{ $laboratorium->kode_laboratorium }}</td>
                            </tr>
                            <tr>
                                <th class="text-muted">Nama Laboratorium</th>
                                <td>{{ $laboratorium->nama_laboratorium }}</td>
                            </tr>
                            <tr>
                                <th class="text-muted">Gedung / Ruangan</th>
                                <td>{{ $laboratorium->gedung }} / {{ $laboratorium->ruangan }}</td>
                            </tr>
                            <tr>
                                <th class="text-muted">Kapasitas</th>
                                <td>{{ $laboratorium->kapasitas ? $laboratorium->kapasitas . ' orang' : '-' }}</td>
                            </tr>
                            <tr>
                                <th class="text-muted">Fasilitas</th>
                                <td>{{ $laboratorium->fasilitas ?: '-' }}</td>
                            </tr>
                            <tr>
                                <th class="text-muted">Status Lab</th>
                                <td>
                                    @if($laboratorium->status == 'aktif')
                                        <span class="badge bg-label-success rounded-pill">{{ ucfirst($laboratorium->status) }}</span>
                                    @else
                                        <span class="badge bg-label-danger rounded-pill">{{ ucfirst($laboratorium->status) }}</span>
                                    @endif
                                </td>
                            </tr>
                            @if($laboratorium->catatan)
                                <tr>
                                    <th class="text-muted">Catatan</th>
                                    <td>{{ $laboratorium->catatan }}</td>
                                </tr>
                            @endif
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
