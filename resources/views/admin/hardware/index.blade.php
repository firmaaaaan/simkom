@extends('layouts.admin')

@section('title', 'Manajemen Hardware')

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <h4 class="fw-bold py-3 mb-4">Manajemen Hardware</h4>

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
                <form method="GET" action="{{ route('admin.hardware.index') }}" class="d-flex flex-wrap align-items-center gap-2">
                    <input type="text" name="search" value="{{ request('search') }}" class="form-control form-control-sm" placeholder="Cari nama, kode, merek..." style="width: 220px;">
                    <select name="kategori" class="form-select form-select-sm" style="width: 170px;">
                        <option value="">Semua Kategori</option>
                        <option value="RAM" {{ request('kategori') == 'RAM' ? 'selected' : '' }}>RAM</option>
                        <option value="VGA" {{ request('kategori') == 'VGA' ? 'selected' : '' }}>VGA / GPU</option>
                        <option value="SSD" {{ request('kategori') == 'SSD' ? 'selected' : '' }}>SSD</option>
                        <option value="HDD" {{ request('kategori') == 'HDD' ? 'selected' : '' }}>HDD</option>
                        <option value="Motherboard" {{ request('kategori') == 'Motherboard' ? 'selected' : '' }}>Motherboard</option>
                        <option value="Processor" {{ request('kategori') == 'Processor' ? 'selected' : '' }}>Processor</option>
                        <option value="Monitor" {{ request('kategori') == 'Monitor' ? 'selected' : '' }}>Monitor</option>
                        <option value="Keyboard" {{ request('kategori') == 'Keyboard' ? 'selected' : '' }}>Keyboard</option>
                        <option value="Mouse" {{ request('kategori') == 'Mouse' ? 'selected' : '' }}>Mouse</option>
                        <option value="Lainnya" {{ request('kategori') == 'Lainnya' ? 'selected' : '' }}>Lainnya</option>
                    </select>
                    <button type="submit" class="btn btn-primary btn-sm" title="Filter">
                        <i class="bx bx-search"></i>
                    </button>
                    @if(request('search') || request('kategori'))
                        <a href="{{ route('admin.hardware.index') }}" class="btn btn-outline-secondary btn-sm" title="Reset">
                            <i class="bx bx-reset"></i>
                        </a>
                    @endif
                </form>
            </div>
        </div>

        <!-- Data Table -->
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">Daftar Hardware</h5>
                <div class="d-flex gap-2">
                    <div class="dropdown">
                        <button class="btn btn-success dropdown-toggle" type="button" data-bs-toggle="dropdown">
                            <i class="bx bx-import me-1"></i> Import
                        </button>
                        <ul class="dropdown-menu">
                            <li>
                                <a href="{{ route('admin.hardware.import.form') }}" class="dropdown-item">
                                    <i class="bx bx-upload me-2"></i> Import Data
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('admin.hardware.export.excel', request()->query()) }}" class="dropdown-item">
                                    <i class="bx bx-file me-2"></i> Download Template Excel
                                </a>
                            </li>
                        </ul>
                    </div>
                    <a href="{{ route('admin.hardware.export.excel', request()->query()) }}" class="btn btn-success">
                        <i class="bx bx-file me-1"></i> Export Excel
                    </a>
                    <a href="{{ route('admin.hardware.create') }}" class="btn btn-primary">
                        <i class="bx bx-plus me-1"></i> Tambah Hardware
                    </a>
                </div>
            </div>
            <div class="table-responsive text-nowrap">
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>Kode</th>
                            <th>Nama Hardware</th>
                            <th>Kategori</th>
                            <th>Merek</th>
                            <th>Jumlah</th>
                            <th>Lokasi</th>
                            <th>Status</th>
                            <th class="text-end">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($hardware as $item)
                            <tr>
                                <td><span class="fw-semibold">{{ $item->kode_hardware }}</span></td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar avatar-sm me-3">
                                            <span class="avatar-initial rounded bg-label-primary">{{ substr($item->nama_hardware, 0, 2) }}</span>
                                        </div>
                                        <div>
                                            <div class="fw-semibold">{{ $item->nama_hardware }}</div>
                                            <small class="text-muted">{{ $item->spesifikasi ? Str::limit($item->spesifikasi, 50) : '' }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td>{{ $item->kategori }}</td>
                                <td>{{ $item->merek ?: '-' }}</td>
                                <td>{{ $item->jumlah }}</td>
                                <td>{{ $item->lokasi ?: '-' }}</td>
                                <td>
                                    @php
                                        $statusClass = [
                                            'tersedia' => 'bg-label-success',
                                            'dipinjam' => 'bg-label-warning',
                                            'perbaikan' => 'bg-label-info',
                                            'tidak_aktif' => 'bg-label-danger',
                                        ][$item->status] ?? 'bg-label-secondary';
                                    @endphp
                                    <span class="badge {{ $statusClass }} rounded-pill">{{ ucfirst($item->status) }}</span>
                                </td>
                                <td class="text-center">
                                    <div class="d-flex gap-1 justify-content-center">
                                        <a href="{{ route('admin.hardware.edit', $item) }}" class="btn btn-sm btn-warning">
                                            <i class="bx bx-edit"></i>
                                        </a>
                                        <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#deleteModal" data-delete-action="{{ route('admin.hardware.destroy', $item) }}">
                                            <i class="bx bx-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-5">
                                    <div class="text-muted">
                                        <i class="bx bx-chip bx-lg mb-2 d-block"></i>
                                        <p>Tidak ada data hardware.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
                <div class="my-4">
                    {{ $hardware->links() }}
                </div>
            </div>
        </div>
    </div>
@endsection
