@extends('layouts.admin')

@section('title', 'Data Komputer')

@php
    $statusLabels = ['aktif' => 'Normal', 'tidak_aktif' => 'Tidak Aktif', 'perbaikan' => 'Perbaikan', 'rusak' => 'Rusak'];
@endphp

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <h4 class="fw-bold py-3 mb-4">Data Komputer</h4>

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
                <a href="{{ route('admin.komputer.index') }}" class="card h-100 text-decoration-none {{ !request('status') ? 'border-primary shadow-sm' : '' }}">
                    <div class="card-body d-flex flex-column align-items-center text-center py-3">
                        <div class="avatar avatar-sm mb-2">
                            <span class="avatar-initial rounded bg-label-primary"><i class="bx bx-desktop"></i></span>
                        </div>
                        <h5 class="mb-0">{{ $summary['total'] }}</h5>
                        <small class="text-muted">Total Komputer</small>
                    </div>
                </a>
            </div>
            <div class="col-6 col-md-4 col-xl-2">
                <a href="{{ route('admin.komputer.index', ['status' => 'aktif']) }}" class="card h-100 text-decoration-none {{ request('status') == 'aktif' ? 'border-success shadow-sm' : '' }}">
                    <div class="card-body d-flex flex-column align-items-center text-center py-3">
                        <div class="avatar avatar-sm mb-2">
                            <span class="avatar-initial rounded bg-label-success"><i class="bx bx-check-circle"></i></span>
                        </div>
                        <h5 class="mb-0">{{ $summary['aktif'] }}</h5>
                        <small class="text-muted">Normal</small>
                    </div>
                </a>
            </div>
            <div class="col-6 col-md-4 col-xl-2">
                <a href="{{ route('admin.komputer.index', ['status' => 'perbaikan']) }}" class="card h-100 text-decoration-none {{ request('status') == 'perbaikan' ? 'border-warning shadow-sm' : '' }}">
                    <div class="card-body d-flex flex-column align-items-center text-center py-3">
                        <div class="avatar avatar-sm mb-2">
                            <span class="avatar-initial rounded bg-label-warning"><i class="bx bx-wrench"></i></span>
                        </div>
                        <h5 class="mb-0">{{ $summary['perbaikan'] }}</h5>
                        <small class="text-muted">Perbaikan</small>
                    </div>
                </a>
            </div>
            <div class="col-6 col-md-4 col-xl-2">
                <a href="{{ route('admin.komputer.index', ['status' => 'rusak']) }}" class="card h-100 text-decoration-none {{ request('status') == 'rusak' ? 'border-danger shadow-sm' : '' }}">
                    <div class="card-body d-flex flex-column align-items-center text-center py-3">
                        <div class="avatar avatar-sm mb-2">
                            <span class="avatar-initial rounded bg-label-danger"><i class="bx bx-error-circle"></i></span>
                        </div>
                        <h5 class="mb-0">{{ $summary['rusak'] }}</h5>
                        <small class="text-muted">Rusak</small>
                    </div>
                </a>
            </div>
            <div class="col-6 col-md-4 col-xl-2">
                <a href="{{ route('admin.komputer.index', ['status' => 'tidak_aktif']) }}" class="card h-100 text-decoration-none {{ request('status') == 'tidak_aktif' ? 'border-secondary shadow-sm' : '' }}">
                    <div class="card-body d-flex flex-column align-items-center text-center py-3">
                        <div class="avatar avatar-sm mb-2">
                            <span class="avatar-initial rounded bg-label-secondary"><i class="bx bx-power-off"></i></span>
                        </div>
                        <h5 class="mb-0">{{ $summary['tidak_aktif'] }}</h5>
                        <small class="text-muted">Tidak Aktif</small>
                    </div>
                </a>
            </div>
            <div class="col-6 col-md-4 col-xl-2 d-flex align-items-stretch">
                <a href="{{ route('admin.komputer.create') }}" class="card h-100 w-100 text-decoration-none border-dashed">
                    <div class="card-body d-flex flex-column align-items-center justify-content-center text-center py-3">
                        <div class="avatar avatar-sm mb-2">
                            <span class="avatar-initial rounded bg-label-primary"><i class="bx bx-plus"></i></span>
                        </div>
                        <small class="fw-semibold text-primary">Tambah Komputer</small>
                    </div>
                </a>
            </div>
        </div>

        <!-- Filter Section -->
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                <h5 class="card-title mb-0">Filter Data</h5>
                <form method="GET" action="{{ route('admin.komputer.index') }}" class="d-flex flex-wrap align-items-center gap-2">
                    <input type="text" name="search" value="{{ request('search') }}" class="form-control form-control-sm" placeholder="Cari nama, kode komputer..." style="width: 200px;">
                    <select name="laboratorium_id" class="form-select form-select-sm" style="width: 200px;">
                        <option value="">Semua Laboratorium</option>
                        @foreach($laboratoriums as $lab)
                            <option value="{{ $lab->id }}" {{ request('laboratorium_id') == $lab->id ? 'selected' : '' }}>{{ $lab->nama_laboratorium }} ({{ $lab->kode_laboratorium }})</option>
                        @endforeach
                    </select>
                    <select name="status" class="form-select form-select-sm" style="width: 150px;">
                        <option value="">Semua Status</option>
                        @foreach($statusLabels as $key => $label)
                            <option value="{{ $key }}" {{ request('status') == $key ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                    <button type="submit" class="btn btn-primary btn-sm" title="Filter">
                        <i class="bx bx-search"></i>
                    </button>
                    @if(request('search') || request('laboratorium_id') || request('status'))
                        <a href="{{ route('admin.komputer.index') }}" class="btn btn-outline-secondary btn-sm" title="Reset">
                            <i class="bx bx-reset"></i>
                        </a>
                    @endif
                </form>
            </div>
        </div>

        <!-- Action Bar -->
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <span class="text-muted">Menampilkan {{ $komputers->firstItem() ?? 0 }}–{{ $komputers->lastItem() ?? 0 }} dari {{ $komputers->total() }} data</span>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('admin.komputer.export.excel', request()->query()) }}" class="btn btn-success">
                    <i class="bx bx-file me-1"></i> Export Excel
                </a>
                <a href="{{ route('admin.komputer.qr-stiker') }}" class="btn btn-dark">
                    <i class="bx bx-qr me-1"></i> Cetak QR Stiker
                </a>
                <a href="{{ route('admin.komputer.create') }}" class="btn btn-primary">
                    <i class="bx bx-plus me-1"></i> Tambah Komputer
                </a>
            </div>
        </div>

        <!-- Cards -->
        <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 g-4">
            @forelse ($komputers as $komputer)
                <div class="col">
                    <div class="card h-100 shadow-none border">
                        <div class="position-relative">
                            @if($komputer->foto_url)
                                <img src="{{ $komputer->foto_url }}" class="card-img-top object-fit-cover" alt="{{ $komputer->nama_komputer }}" style="height: 160px;" />
                            @else
                                <div class="d-flex align-items-center justify-content-center bg-label-light" style="height: 160px;">
                                    <img src="{{ asset('assets') }}/img/illustrations/man-with-laptop-light.png" alt="Tanpa Gambar" class="img-fluid" style="max-height: 120px;" />
                                </div>
                            @endif
                            <span class="position-absolute top-0 end-0 m-2 badge {{ $komputer->getStatusBadgeClass() }} rounded-pill">{{ $statusLabels[$komputer->status] ?? ucfirst(str_replace('_', ' ', $komputer->status)) }}</span>
                        </div>
                        <div class="card-body d-flex flex-column">
                            <h5 class="card-title mb-1">{{ $komputer->nama_komputer }}</h5>
                            <small class="text-muted mb-3">{{ $komputer->kode_komputer }}</small>

                            <!-- Data Lab -->
                            <div class="mb-2">
                                @if($komputer->laboratorium)
                                    <span class="badge bg-label-primary rounded-pill">
                                        <i class="bx bx-building-house me-1"></i> {{ $komputer->laboratorium->nama_laboratorium }}
                                    </span>
                                    <small class="text-muted d-block mt-1">
                                        <i class="bx bx-map-pin me-1"></i>{{ $komputer->laboratorium->gedung }} / {{ $komputer->laboratorium->ruangan }}
                                    </small>
                                @else
                                    <span class="text-muted fst-italic">Tidak terhubung ke lab</span>
                                @endif
                            </div>

                            <!-- Ringkasan Hardware & Software -->
                            <div class="d-flex align-items-center gap-3 mt-auto pt-2">
                                <span class="d-inline-flex align-items-center gap-1 text-muted small">
                                    <i class="bx bx-chip text-info"></i>
                                    <span>{{ $komputer->hardware->count() }} hardware</span>
                                </span>
                                <span class="d-inline-flex align-items-center gap-1 text-muted small">
                                    <i class="bx bx-software text-success"></i>
                                    <span>{{ $komputer->software->count() }} software</span>
                                </span>
                            </div>
                        </div>
                        <div class="card-footer bg-transparent border-top pt-3">
                            <div class="d-flex flex-wrap justify-content-end gap-2">
                                <a href="{{ route('admin.komputer.show', $komputer) }}" class="btn btn-sm btn-outline-primary" title="Lihat Detail">
                                    <i class="bx bx-show me-1"></i> Detail
                                </a>
                                <a href="{{ route('admin.komputer.kartu-kendali.create', $komputer) }}" class="btn btn-sm btn-outline-info" title="Kartu Kendali">
                                    <i class="bx bx-check me-1"></i> Kartu
                                </a>
                                <a href="{{ route('admin.komputer.edit', $komputer) }}" class="btn btn-sm btn-outline-warning" title="Edit">
                                    <i class="bx bx-edit me-1"></i> Edit
                                </a>
                                <form action="{{ route('admin.komputer.destroy', $komputer) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="button" class="btn btn-sm btn-outline-danger" title="Hapus" data-bs-toggle="modal" data-bs-target="#deleteModal" data-delete-action="{{ route('admin.komputer.destroy', $komputer) }}">
                                        <i class="bx bx-trash me-1"></i> Hapus
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12">
                    <div class="card">
                        <div class="card-body text-center py-5">
                            <div class="text-muted">
                                <i class="bx bx-desktop bx-lg mb-2 d-block"></i>
                                <p class="mb-0">Tidak ada data komputer.</p>
                                <a href="{{ route('admin.komputer.create') }}" class="btn btn-primary btn-sm mt-3">
                                    <i class="bx bx-plus me-1"></i> Tambah Komputer Pertama
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            @endforelse
        </div>

        <div class="d-flex justify-content-center my-4">
            {{ $komputers->links() }}
        </div>

    </div>
@endsection
