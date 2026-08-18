@extends('layouts.admin')

@section('title', 'Manajemen Komponen IoT')

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <h4 class="fw-bold py-3 mb-4">Manajemen Komponen IoT</h4>

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
                <form method="GET" action="{{ route('admin.komponen-iot.index') }}" class="d-flex flex-wrap align-items-center gap-2">
                    <input type="text" name="search" value="{{ request('search') }}" class="form-control form-control-sm" placeholder="Cari nama, kode, merek..." style="width: 220px;">
                    <button type="submit" class="btn btn-primary btn-sm" title="Filter">
                        <i class="bx bx-search"></i>
                    </button>
                    @if(request('search'))
                        <a href="{{ route('admin.komponen-iot.index') }}" class="btn btn-outline-secondary btn-sm" title="Reset">
                            <i class="bx bx-reset"></i>
                        </a>
                    @endif
                </form>
            </div>
        </div>

        <!-- Data Table -->
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">Daftar Komponen IoT</h5>
                <div class="d-flex gap-2">
                    <div class="dropdown">
                        <button class="btn btn-success dropdown-toggle" type="button" data-bs-toggle="dropdown">
                            <i class="bx bx-import me-1"></i> Import
                        </button>
                        <ul class="dropdown-menu">
                            <li>
                                <a href="{{ route('admin.komponen-iot.import.form') }}" class="dropdown-item">
                                    <i class="bx bx-upload me-2"></i> Import Data
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('admin.komponen-iot.export.excel', request()->query()) }}" class="dropdown-item">
                                    <i class="bx bx-file me-2"></i> Download Template Excel
                                </a>
                            </li>
                        </ul>
                    </div>
                    <a href="{{ route('admin.komponen-iot.export.excel', request()->query()) }}" class="btn btn-success">
                        <i class="bx bx-file me-1"></i> Export Excel
                    </a>
                    <a href="{{ route('admin.komponen-iot.create') }}" class="btn btn-primary">
                        <i class="bx bx-plus me-1"></i> Tambah Komponen
                    </a>
                </div>
            </div>
            <div class="table-responsive text-nowrap">
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>Kode</th>
                            <th>Foto</th>
                            <th>Nama Komponen</th>
                            <th>Merek</th>
                            <th>Jumlah</th>
                            <th>Lokasi</th>
                            <th class="text-end">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($komponen as $item)
                            <tr>
                                <td><span class="fw-semibold">{{ $item->kode_komponen }}</span></td>
                                <td>
                                    @if($item->foto)
                                        <img src="{{ asset('storage/' . $item->foto) }}" class="rounded preview-foto" alt="Foto" style="width: 40px; height: 40px; object-fit: cover; cursor: pointer;">
                                    @else
                                        <div class="avatar avatar-sm rounded bg-label-light d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                            <span class="text-muted" style="font-size: 10px;">-</span>
                                        </div>
                                    @endif
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="me-3">
                                            <span class="avatar-initial rounded bg-label-primary">{{ substr($item->nama_komponen, 0, 2) }}</span>
                                        </div>
                                        <div>
                                            <div class="fw-semibold">{{ $item->nama_komponen }}</div>
                                            <small class="text-muted">{{ $item->spesifikasi ? Str::limit($item->spesifikasi, 50) : '' }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td>{{ $item->merek ?: '-' }}</td>
                                <td>{{ $item->jumlah }}</td>
                                <td>{{ $item->lokasi ?: '-' }}</td>
                                <td class="text-center">
                                    <div class="d-flex gap-1 justify-content-center">
                                        <a href="{{ route('admin.komponen-iot.edit', ['komponen_iot' => $item->id]) }}" class="btn btn-sm btn-warning">
                                            <i class="bx bx-edit"></i>
                                        </a>
                                        <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#deleteModal" data-delete-action="{{ route('admin.komponen-iot.destroy', ['komponen_iot' => $item->id]) }}">
                                            <i class="bx bx-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-5">
                                    <div class="text-muted">
                                        <i class="bx bx-chip bx-lg mb-2 d-block"></i>
                                        <p>Tidak ada data komponen IoT.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
                <div class="mt-4">
                    {{ $komponen->links() }}
                </div>
            </div>
        </div>
    </div>

    <!-- Image Preview Modal -->
    <div class="modal fade" id="imagePreviewModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content bg-white">
                <div class="modal-body p-0 text-center">
                    <img id="previewImage" src="" alt="Preview" class="img-fluid rounded shadow-lg" style="max-height: 80vh; max-width: 100%;">
                </div>
                <div class="modal-footer justify-content-center border-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var previewButtons = document.querySelectorAll('.preview-foto');
            var modal = document.getElementById('imagePreviewModal');
            var previewImage = document.getElementById('previewImage');

            previewButtons.forEach(function(img) {
                img.addEventListener('click', function() {
                    var src = img.getAttribute('src');
                    previewImage.src = src;
                    if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                        var bsModal = new bootstrap.Modal(modal);
                        bsModal.show();
                    }
                });
            });
        });
    </script>
@endsection
