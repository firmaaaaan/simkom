@extends('layouts.admin')

@section('title', 'Manajemen Akun')

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="fw-bold mb-0">Manajemen Akun</h4>
            <a href="{{ route('admin.user.create') }}" class="btn btn-primary">
                <i class="bx bx-plus me-1"></i> Tambah Akun
            </a>
        </div>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <!-- Summary Cards -->
        <div class="row g-3 mb-4">
            <div class="col-6 col-md-4 col-xl-3">
                <a href="{{ route('admin.user.index') }}" class="card h-100 text-decoration-none">
                    <div class="card-body d-flex flex-column align-items-center text-center py-3">
                        <div class="avatar avatar-sm mb-2">
                            <span class="avatar-initial rounded bg-label-primary"><i class="bx bx-user"></i></span>
                        </div>
                        <h5 class="mb-0">{{ $summary['total'] }}</h5>
                        <small class="text-muted">Total Akun</small>
                    </div>
                </a>
            </div>
            <div class="col-6 col-md-4 col-xl-3">
                <a href="{{ route('admin.activity-log.index') }}" class="card h-100 text-decoration-none">
                    <div class="card-body d-flex flex-column align-items-center text-center py-3">
                        <div class="avatar avatar-sm mb-2">
                            <span class="avatar-initial rounded bg-label-info"><i class="bx bx-history"></i></span>
                        </div>
                        <h5 class="mb-0">{{ $summary['logs'] }}</h5>
                        <small class="text-muted">Total Log Aktivitas</small>
                    </div>
                </a>
            </div>
            <div class="col-6 col-md-4 col-xl-3 d-flex align-items-stretch">
                <a href="{{ route('admin.user.create') }}" class="card h-100 w-100 text-decoration-none border-dashed">
                    <div class="card-body d-flex flex-column align-items-center justify-content-center text-center py-3">
                        <div class="avatar avatar-sm mb-2">
                            <span class="avatar-initial rounded bg-label-primary"><i class="bx bx-plus"></i></span>
                        </div>
                        <small class="fw-semibold text-primary">Tambah Akun</small>
                    </div>
                </a>
            </div>
        </div>

        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                <h5 class="card-title mb-0">Daftar Akun</h5>
                <form method="GET" action="{{ route('admin.user.index') }}" class="d-flex gap-2">
                    <input type="text" name="search" value="{{ request('search') }}" class="form-control form-control-sm" placeholder="Cari nama atau email..." style="width: 220px;">
                    <button type="submit" class="btn btn-primary btn-sm">
                        <i class="bx bx-search"></i>
                    </button>
                    @if(request('search'))
                        <a href="{{ route('admin.user.index') }}" class="btn btn-outline-secondary btn-sm" title="Reset">
                            <i class="bx bx-reset"></i>
                        </a>
                    @endif
                </form>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>No</th>
                                <th>Nama</th>
                                <th>Email</th>
                                <th>Dibuat</th>
                                <th class="text-center">Status Akun</th>
                                <th class="text-center">Log Aktivitas</th>
                                <th class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($users as $user)
                                <tr>
                                    <td>{{ $users->firstItem() + $loop->index }}</td>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="avatar avatar-xs">
                                                <span class="avatar-initial rounded bg-label-primary">{{ strtoupper(substr($user->name, 0, 1)) }}</span>
                                            </span>
                                            <span class="fw-semibold">{{ $user->name }}</span>
                                            @if($user->id === auth()->id())
                                                <span class="badge bg-label-primary rounded-pill">Anda</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td>{{ $user->email }}</td>
                                    <td>{{ $user->created_at->translatedFormat('d F Y H:i') }}</td>
                                    <td class="text-center">
                                        <form method="POST" action="{{ route('admin.user.toggle-active', $user) }}" class="d-inline">
                                            @csrf
                                            <div class="form-check form-switch d-inline-flex align-items-center gap-2 mb-0">
                                                <input type="checkbox" class="form-check-input" role="switch" id="status-{{ $user->id }}" {{ $user->is_active ? 'checked' : '' }} {{ $user->id === auth()->id() ? 'disabled' : '' }} onchange="this.form.submit()">
                                                <label class="form-check-label small mb-0 {{ $user->is_active ? 'text-success fw-semibold' : 'text-danger fw-semibold' }}" for="status-{{ $user->id }}">
                                                    {{ $user->is_active ? 'Aktif' : 'Nonaktif' }}
                                                </label>
                                            </div>
                                        </form>
                                    </td>
                                    <td class="text-center">
                                        <a href="{{ route('admin.activity-log.show', $user) }}" class="badge bg-label-info rounded-pill text-decoration-none" title="Lihat log aktivitas">
                                            <i class="bx bx-history me-1"></i>{{ $user->activity_logs_count }} aksi
                                        </a>
                                    </td>
                                    <td class="text-center">
                                        <div class="d-flex gap-1 justify-content-center">
                                            <a href="{{ route('admin.activity-log.show', $user) }}" class="btn btn-sm btn-outline-info" title="Log Aktivitas">
                                                <i class="bx bx-history"></i>
                                            </a>
                                            <a href="{{ route('admin.user.edit', $user) }}" class="btn btn-sm btn-outline-warning" title="Edit">
                                                <i class="bx bx-edit"></i>
                                            </a>
                                            @if($user->id !== auth()->id())
                                                <button type="button" class="btn btn-sm btn-outline-danger" title="Hapus" data-bs-toggle="modal" data-bs-target="#deleteModal" data-delete-action="{{ route('admin.user.destroy', $user) }}">
                                                    <i class="bx bx-trash"></i>
                                                </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center text-muted py-4">
                                        {{ request('search') ? 'Tidak ada akun yang cocok dengan pencarian.' : 'Belum ada akun.' }}
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="mt-4">
            {{ $users->links() }}
        </div>
    </div>
@endsection
