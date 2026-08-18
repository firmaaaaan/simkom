@extends('layouts.admin')

@section('title', 'Manajemen Inventaris IoT & Jaringan')

@php
    $statusLabels = ['tersedia' => 'Tersedia', 'dipinjam' => 'Dipinjam', 'perbaikan' => 'Perbaikan', 'tidak_aktif' => 'Tidak Aktif'];
    $statusColors = [
        'tersedia' => 'bg-label-success',
        'dipinjam' => 'bg-label-warning',
        'perbaikan' => 'bg-label-info',
        'tidak_aktif' => 'bg-label-danger',
    ];
@endphp

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <h4 class="fw-bold py-3 mb-4">Manajemen Inventaris IoT & Jaringan</h4>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible" role="alert">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <!-- Summary Cards -->
        <div class="row g-3 mb-4">
            <div class="col-6 col-md-4 col-xl-2">
                <a href="{{ route('admin.inventaris-iot-jaringan.index') }}" class="card h-100 text-decoration-none {{ !request('status') ? 'border-primary shadow-sm' : '' }}">
                    <div class="card-body d-flex flex-column align-items-center text-center py-3">
                        <div class="avatar avatar-sm mb-2">
                            <span class="avatar-initial rounded bg-label-primary"><i class="bx bx-chip"></i></span>
                        </div>
                        <h5 class="mb-0">{{ $summary['total'] }}</h5>
                        <small class="text-muted">Total Inventaris</small>
                    </div>
                </a>
            </div>
            <div class="col-6 col-md-4 col-xl-2">
                <a href="{{ route('admin.inventaris-iot-jaringan.index', ['status' => 'tersedia']) }}" class="card h-100 text-decoration-none {{ request('status') == 'tersedia' ? 'border-success shadow-sm' : '' }}">
                    <div class="card-body d-flex flex-column align-items-center text-center py-3">
                        <div class="avatar avatar-sm mb-2">
                            <span class="avatar-initial rounded bg-label-success"><i class="bx bx-check-circle"></i></span>
                        </div>
                        <h5 class="mb-0">{{ $summary['tersedia'] }}</h5>
                        <small class="text-muted">Tersedia</small>
                    </div>
                </a>
            </div>
            <div class="col-6 col-md-4 col-xl-2">
                <a href="{{ route('admin.inventaris-iot-jaringan.index', ['status' => 'dipinjam']) }}" class="card h-100 text-decoration-none {{ request('status') == 'dipinjam' ? 'border-warning shadow-sm' : '' }}">
                    <div class="card-body d-flex flex-column align-items-center text-center py-3">
                        <div class="avatar avatar-sm mb-2">
                            <span class="avatar-initial rounded bg-label-warning"><i class="bx bx-time-five"></i></span>
                        </div>
                        <h5 class="mb-0">{{ $summary['dipinjam'] }}</h5>
                        <small class="text-muted">Dipinjam</small>
                    </div>
                </a>
            </div>
            <div class="col-6 col-md-4 col-xl-2">
                <a href="{{ route('admin.inventaris-iot-jaringan.index', ['status' => 'perbaikan']) }}" class="card h-100 text-decoration-none {{ request('status') == 'perbaikan' ? 'border-info shadow-sm' : '' }}">
                    <div class="card-body d-flex flex-column align-items-center text-center py-3">
                        <div class="avatar avatar-sm mb-2">
                            <span class="avatar-initial rounded bg-label-info"><i class="bx bx-wrench"></i></span>
                        </div>
                        <h5 class="mb-0">{{ $summary['perbaikan'] }}</h5>
                        <small class="text-muted">Perbaikan</small>
                    </div>
                </a>
            </div>
            <div class="col-6 col-md-4 col-xl-2">
                <a href="{{ route('admin.inventaris-iot-jaringan.index', ['status' => 'tidak_aktif']) }}" class="card h-100 text-decoration-none {{ request('status') == 'tidak_aktif' ? 'border-danger shadow-sm' : '' }}">
                    <div class="card-body d-flex flex-column align-items-center text-center py-3">
                        <div class="avatar avatar-sm mb-2">
                            <span class="avatar-initial rounded bg-label-danger"><i class="bx bx-power-off"></i></span>
                        </div>
                        <h5 class="mb-0">{{ $summary['tidak_aktif'] }}</h5>
                        <small class="text-muted">Tidak Aktif</small>
                    </div>
                </a>
            </div>
            <div class="col-6 col-md-4 col-xl-2 d-flex align-items-stretch">
                <a href="{{ route('admin.inventaris-iot-jaringan.create') }}" class="card h-100 w-100 text-decoration-none border-dashed">
                    <div class="card-body d-flex flex-column align-items-center justify-content-center text-center py-3">
                        <div class="avatar avatar-sm mb-2">
                            <span class="avatar-initial rounded bg-label-primary"><i class="bx bx-plus"></i></span>
                        </div>
                        <small class="fw-semibold text-primary">Tambah Inventaris</small>
                    </div>
                </a>
            </div>
        </div>

        <!-- Filter Section -->
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                <h5 class="card-title mb-0">Filter Data</h5>
                <form method="GET" action="{{ route('admin.inventaris-iot-jaringan.index') }}" class="d-flex flex-wrap align-items-center gap-2">
                    <input type="text" name="search" value="{{ request('search') }}" class="form-control form-control-sm" placeholder="Cari nama inventaris..." style="width: 200px;">
                    <select name="kategori" class="form-select form-select-sm" style="width: 140px;">
                        <option value="">Semua Kategori</option>
                        <option value="IoT" {{ request('kategori') == 'IoT' ? 'selected' : '' }}>IoT</option>
                        <option value="Jaringan" {{ request('kategori') == 'Jaringan' ? 'selected' : '' }}>Jaringan</option>
                    </select>
                    <select name="jenis" class="form-select form-select-sm" style="width: 120px;">
                        <option value="">Semua Jenis</option>
                        <option value="Satuan" {{ request('jenis') == 'Satuan' ? 'selected' : '' }}>Satuan</option>
                        <option value="Paket" {{ request('jenis') == 'Paket' ? 'selected' : '' }}>Paket</option>
                        <option value="Sistem" {{ request('jenis') == 'Sistem' ? 'selected' : '' }}>Sistem</option>
                        <option value="Box" {{ request('jenis') == 'Box' ? 'selected' : '' }}>Box</option>
                    </select>
                    <select name="status" class="form-select form-select-sm" style="width: 130px;">
                        <option value="">Semua Status</option>
                        @foreach($statusLabels as $key => $label)
                            <option value="{{ $key }}" {{ request('status') == $key ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                    <button type="submit" class="btn btn-primary btn-sm" title="Filter">
                        <i class="bx bx-search"></i>
                    </button>
                    @if(request('search') || request('kategori') || request('jenis') || request('status'))
                        <a href="{{ route('admin.inventaris-iot-jaringan.index') }}" class="btn btn-outline-secondary btn-sm" title="Reset">
                            <i class="bx bx-reset"></i>
                        </a>
                    @endif
                </form>
            </div>
        </div>

        <!-- Action Bar -->
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <span class="text-muted">Menampilkan {{ $inventaris->firstItem() ?? 0 }}–{{ $inventaris->lastItem() ?? 0 }} dari {{ $inventaris->total() }} data</span>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('admin.inventaris-iot-jaringan.export.excel', request()->query()) }}" class="btn btn-success">
                    <i class="bx bx-file me-1"></i> Export Excel
                </a>
                <a href="{{ route('admin.inventaris-iot-jaringan.qr-stiker') }}" class="btn btn-dark">
                    <i class="bx bx-qr me-1"></i> Cetak QR Stiker
                </a>
                <a href="{{ route('admin.inventaris-iot-jaringan.create') }}" class="btn btn-primary">
                    <i class="bx bx-plus me-1"></i> Tambah Inventaris
                </a>
            </div>
        </div>

        <!-- Data Cards -->
        <div class="row g-4">
            @forelse ($inventaris as $item)
                <div class="col-md-6 col-xl-4">
                    <div class="card h-100 shadow-none border">
                        <div class="card-header d-flex justify-content-between align-items-start gap-2">
                            <div class="d-flex align-items-start gap-3">
                                <span class="avatar avatar-sm mt-1">
                                    <span class="avatar-initial rounded bg-label-primary">
                                        <i class="bx {{ $item->kategori == 'IoT' ? 'bx-chip' : 'bx-network-chart' }}"></i>
                                    </span>
                                </span>
                                <div>
                                    <h5 class="card-title mb-1">{{ $item->nama_inventaris }}</h5>
                                    @if($item->kode_perangkat)
                                        <small class="text-muted d-block mb-1">{{ $item->kode_perangkat }}</small>
                                    @endif
                                    <div class="d-flex flex-wrap gap-1 mt-1">
                                        <span class="badge bg-label-primary rounded-pill">{{ $item->kategori }}</span>
                                        <span class="badge bg-label-info rounded-pill">{{ $item->jenis }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card-body d-flex flex-column">
                            <div class="row g-3 mb-3">
                                <div class="col-6">
                                    <h6 class="text-muted small text-uppercase mb-1">
                                        <i class="bx bx-map-pin me-1"></i>Lokasi
                                    </h6>
                                    <p class="mb-0 fw-semibold small">{{ $item->lokasi ?: '-' }}</p>
                                </div>
                                <div class="col-6">
                                    <h6 class="text-muted small text-uppercase mb-1">
                                        <i class="bx bx-info-circle me-1"></i>Status
                                    </h6>
                                    <span class="badge {{ $statusColors[$item->status] ?? 'bg-label-secondary' }} rounded-pill">{{ $statusLabels[$item->status] ?? ucfirst(str_replace('_', ' ', $item->status)) }}</span>
                                </div>
                            </div>

                            <!-- Komponen -->
                            <h6 class="text-muted small text-uppercase mb-2">
                                <i class="bx bx-package me-1"></i>Komponen
                            </h6>
                            <div class="d-flex flex-wrap gap-2 mb-2">
                                @foreach($item->items as $sub)
                                    <span class="badge bg-label-secondary rounded-pill">{{ $sub->komponen->nama_komponen ?? '-' }} x{{ $sub->jumlah }}</span>
                                @endforeach
                                @if($item->items->isEmpty())
                                    <span class="text-muted small">Belum ada komponen</span>
                                @endif
                            </div>
                        </div>
                        <div class="card-footer bg-transparent border-top pt-3">
                            <div class="d-flex flex-wrap justify-content-end gap-2">
                                <a href="{{ route('admin.inventaris-iot-jaringan.show', $item) }}" class="btn btn-sm btn-outline-primary" title="Lihat Detail">
                                    <i class="bx bx-show me-1"></i> Detail
                                </a>
                                <a href="{{ route('admin.inventaris-iot-jaringan.kartu-kendali.create', $item) }}" class="btn btn-sm btn-outline-info" title="Kartu Kendali">
                                    <i class="bx bx-check me-1"></i> Kartu
                                </a>
                                <a href="{{ route('admin.inventaris-iot-jaringan.edit', $item) }}" class="btn btn-sm btn-outline-warning" title="Edit">
                                    <i class="bx bx-edit me-1"></i> Edit
                                </a>
                                <button type="button" class="btn btn-sm btn-outline-danger" title="Hapus" data-bs-toggle="modal" data-bs-target="#deleteModal" data-delete-action="{{ route('admin.inventaris-iot-jaringan.destroy', $item) }}">
                                    <i class="bx bx-trash me-1"></i> Hapus
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12">
                    <div class="card">
                        <div class="card-body text-center py-5">
                            <div class="text-muted">
                                <i class="bx bx-chip bx-lg mb-2 d-block"></i>
                                <p class="mb-0">Tidak ada data inventaris IoT & Jaringan.</p>
                                <a href="{{ route('admin.inventaris-iot-jaringan.create') }}" class="btn btn-primary btn-sm mt-3">
                                    <i class="bx bx-plus me-1"></i> Tambah Inventaris Pertama
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            @endforelse
        </div>
        <div class="d-flex justify-content-center my-4">
            {{ $inventaris->links() }}
        </div>
    </div>
@endsection
