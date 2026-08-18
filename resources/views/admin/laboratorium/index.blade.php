@extends('layouts.admin')

@section('title', 'Manajemen Laboratorium')

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <h4 class="fw-bold py-3 mb-4">Manajemen Laboratorium</h4>

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

        <!-- Filter Section -->
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                <h5 class="card-title mb-0">Filter Data</h5>
                <form method="GET" action="{{ route('admin.laboratorium.index') }}" class="d-flex flex-wrap align-items-center gap-2">
                    <input type="text" name="search" value="{{ request('search') }}" class="form-control form-control-sm" placeholder="Cari nama, kode, gedung, ruangan..." style="width: 220px;">
                    <button type="submit" class="btn btn-primary btn-sm" title="Filter">
                        <i class="bx bx-search"></i>
                    </button>
                    @if(request('search'))
                        <a href="{{ route('admin.laboratorium.index') }}" class="btn btn-outline-secondary btn-sm" title="Reset">
                            <i class="bx bx-reset"></i>
                        </a>
                    @endif
                </form>
            </div>
        </div>

        <!-- Data Table -->
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">Daftar Laboratorium</h5>
                <a href="{{ route('admin.laboratorium.create') }}" class="btn btn-primary">
                    <i class="bx bx-plus me-1"></i> Tambah Laboratorium
                </a>
            </div>
            <div class="table-responsive text-nowrap">
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>Kode</th>
                            <th>Nama Laboratorium</th>
                            <th>Gedung / Ruangan</th>
                            <th>Kapasitas</th>
                            <th>Status</th>
                            <th class="text-end">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($laboratorium as $item)
                            <tr>
                                <td><span class="fw-semibold">{{ $item->kode_laboratorium }}</span></td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar avatar-sm me-3">
                                            <span class="avatar-initial rounded bg-label-primary">{{ substr($item->nama_laboratorium, 0, 2) }}</span>
                                        </div>
                                        <div>
                                            <div class="fw-semibold">{{ $item->nama_laboratorium }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td>{{ $item->gedung }} / {{ $item->ruangan }}</td>
                                <td>{{ $item->kapasitas ? $item->kapasitas . ' orang' : '-' }}</td>
                                <td>
                                    @if($item->status == 'aktif')
                                        <span class="badge bg-label-success rounded-pill">{{ ucfirst($item->status) }}</span>
                                    @else
                                        <span class="badge bg-label-danger rounded-pill">{{ ucfirst($item->status) }}</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <div class="d-flex gap-1 justify-content-center">
                                        <a href="{{ route('admin.laboratorium.edit', $item) }}" class="btn btn-sm btn-warning">
                                            <i class="bx bx-edit"></i>
                                        </a>
                                        <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#deleteModal" data-delete-action="{{ route('admin.laboratorium.destroy', $item) }}">
                                            <i class="bx bx-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-5">
                                    <div class="text-muted">
                                        <i class="bx bx-building-house bx-lg mb-2 d-block"></i>
                                        <p>Tidak ada data laboratorium.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
                <div class="mt-4">
                    {{ $laboratorium->links() }}
                </div>
            </div>
        </div>
    </div>
@endsection
