@extends('layouts.admin')

@section('title', 'Laporan Kendala Komputer')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <h5 class="mb-0">Laporan Kendala Komputer</h5>
                    <div class="d-flex gap-2">
                        <a href="{{ route('admin.laporan-kendala-komputer.export', request()->query()) }}" class="btn btn-success">
                            <i class="bx bx-file me-1"></i> Export Excel
                        </a>
                        <a href="{{ route('laporan-kendala-komputer.create') }}" class="btn btn-primary" target="_blank">
                            <i class="bx bx-plus me-1"></i> Buat Laporan
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    <form method="GET" action="{{ route('admin.laporan-kendala-komputer.index') }}" class="d-flex flex-wrap align-items-center gap-2 mb-4">
                        <input type="text" name="search" value="{{ request('search') }}" class="form-control form-control-sm" placeholder="Cari nama, npm, atau deskripsi..." style="width: 220px;">
                        <button type="submit" class="btn btn-primary btn-sm" title="Filter">
                            <i class="bx bx-search"></i>
                        </button>
                        @if(request('search'))
                            <a href="{{ route('admin.laporan-kendala-komputer.index') }}" class="btn btn-outline-secondary btn-sm" title="Reset">
                                <i class="bx bx-reset"></i>
                            </a>
                        @endif
                    </form>

                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>No</th>
                                    <th>Komputer</th>
                                    <th>Pelapor</th>
                                    <th>NPM/NIM</th>
                                    <th>Prodi</th>
                                    <th>Kode Tracker</th>
                                    <th>Tanggal Lapor</th>
                                    <th>Status</th>
                                    <th class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($laporan as $item)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>
                                            <div class="fw-medium">{{ $item->komputer->nama_komputer ?? '-' }}</div>
                                            <small class="text-muted">{{ $item->komputer->kode_komputer ?? '' }}</small>
                                        </td>
                                        <td>{{ $item->nama_pelapor }}</td>
                                        <td>{{ $item->npm_nim }}</td>
                                        <td>{{ $item->nama_prodi ?? '-' }}</td>
                                        <td><code class="text-xs">{{ $item->kode_tracker }}</code></td>
                                        <td>{{ $item->tanggal_lapor->translatedFormat('d F Y') }}</td>
                                        <td>
                                            <span class="badge {{ $item->getStatusBadgeClass() }} rounded-pill">{{ ucfirst($item->status_kendala) }}</span>
                                        </td>
                                        <td class="text-center">
                                            <a href="{{ route('admin.laporan-kendala-komputer.show', $item) }}" class="btn btn-sm btn-info">
                                                <i class="bx bx-show me-1"></i> Detail
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="9" class="text-center text-muted py-4">Belum ada laporan kendala.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4">
                        {{ $laporan->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
