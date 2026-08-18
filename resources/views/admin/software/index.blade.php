@extends('layouts.admin')

@section('title', 'Manajemen Software')

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <h4 class="fw-bold py-3 mb-4">Manajemen Software</h4>

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
                <form method="GET" action="{{ route('admin.software.index') }}" class="d-flex flex-wrap align-items-center gap-2">
                    <input type="text" name="search" value="{{ request('search') }}" class="form-control form-control-sm" placeholder="Cari nama, kode, versi..." style="width: 220px;">
                    <select name="kategori" class="form-select form-select-sm" style="width: 170px;">
                        <option value="">Semua Kategori</option>
                        <option value="Operating System" {{ request('kategori') == 'Operating System' ? 'selected' : '' }}>Operating System</option>
                        <option value="Office" {{ request('kategori') == 'Office' ? 'selected' : '' }}>Office</option>
                        <option value="Programming" {{ request('kategori') == 'Programming' ? 'selected' : '' }}>Programming</option>
                        <option value="Design" {{ request('kategori') == 'Design' ? 'selected' : '' }}>Design</option>
                        <option value="Database" {{ request('kategori') == 'Database' ? 'selected' : '' }}>Database</option>
                        <option value="Antivirus" {{ request('kategori') == 'Antivirus' ? 'selected' : '' }}>Antivirus</option>
                        <option value="Lainnya" {{ request('kategori') == 'Lainnya' ? 'selected' : '' }}>Lainnya</option>
                    </select>
                    <button type="submit" class="btn btn-primary btn-sm" title="Filter">
                        <i class="bx bx-search"></i>
                    </button>
                    @if(request('search') || request('kategori'))
                        <a href="{{ route('admin.software.index') }}" class="btn btn-outline-secondary btn-sm" title="Reset">
                            <i class="bx bx-reset"></i>
                        </a>
                    @endif
                </form>
            </div>
        </div>

        <!-- Data Table -->
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">Daftar Software</h5>
                <div class="d-flex gap-2">
                    <div class="dropdown">
                        <button class="btn btn-success dropdown-toggle" type="button" data-bs-toggle="dropdown">
                            <i class="bx bx-import me-1"></i> Import
                        </button>
                        <ul class="dropdown-menu">
                            <li>
                                <a href="{{ route('admin.software.import.form') }}" class="dropdown-item">
                                    <i class="bx bx-upload me-2"></i> Import Data
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('admin.software.export.excel', request()->query()) }}" class="dropdown-item">
                                    <i class="bx bx-file me-2"></i> Download Template Excel
                                </a>
                            </li>
                        </ul>
                    </div>
                    <a href="{{ route('admin.software.export.excel', request()->query()) }}" class="btn btn-success">
                        <i class="bx bx-file me-1"></i> Export Excel
                    </a>
                    <a href="{{ route('admin.software.create') }}" class="btn btn-primary">
                        <i class="bx bx-plus me-1"></i> Tambah Software
                    </a>
                </div>
            </div>
            <div class="table-responsive text-nowrap">
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>Kode</th>
                            <th>Nama Software</th>
                            <th>Kategori</th>
                            <th>Versi</th>
                            <th>Lisensi</th>
                            <th>Status</th>
                            <th class="text-end">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($software as $item)
                            <tr>
                                <td><span class="fw-semibold">{{ $item->kode_software }}</span></td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar avatar-sm me-3">
                                            <span class="avatar-initial rounded bg-label-primary">{{ substr($item->nama_software, 0, 2) }}</span>
                                        </div>
                                        <div>
                                            <div class="fw-semibold">{{ $item->nama_software }}</div>
                                            <small class="text-muted">{{ $item->versi ?: '-' }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td>{{ $item->kategori }}</td>
                                <td>{{ $item->versi ?: '-' }}</td>
                                <td>
                                    @php
                                        $lisensiClass = [
                                            'gratis' => 'bg-label-success',
                                            'berbayar' => 'bg-label-danger',
                                            'edukasi' => 'bg-label-info',
                                            'trial' => 'bg-label-warning',
                                            'open_source' => 'bg-label-primary',
                                        ][$item->lisensi] ?? 'bg-label-secondary';
                                    @endphp
                                    <span class="badge {{ $lisensiClass }} rounded-pill">{{ ucfirst($item->lisensi) }}</span>
                                </td>
                                <td>
                                    @php
                                        $statusClass = [
                                            'aktif' => 'bg-label-success',
                                            'tidak_aktif' => 'bg-label-danger',
                                            'trial' => 'bg-label-warning',
                                        ][$item->status] ?? 'bg-label-secondary';
                                    @endphp
                                    <span class="badge {{ $statusClass }} rounded-pill">{{ ucfirst($item->status) }}</span>
                                </td>
                                <td class="text-center">
                                    <div class="d-flex gap-1 justify-content-center">
                                        <a href="{{ route('admin.software.edit', $item) }}" class="btn btn-sm btn-warning">
                                            <i class="bx bx-edit"></i>
                                        </a>
                                        <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#deleteModal" data-delete-action="{{ route('admin.software.destroy', $item) }}">
                                            <i class="bx bx-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-5">
                                    <div class="text-muted">
                                        <i class="bx bx-software bx-lg mb-2 d-block"></i>
                                        <p>Tidak ada data software.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
                <div class="my-4">
                    {{ $software->links() }}
                </div>
            </div>
        </div>
    </div>
@endsection
