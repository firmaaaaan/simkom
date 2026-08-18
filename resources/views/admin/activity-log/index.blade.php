@extends('layouts.admin')

@section('title', 'Log Aktivitas')

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="fw-bold mb-0">Log Aktivitas</h4>
            <div class="d-flex gap-2">
                <a href="{{ route('admin.activity-log.index', ['export' => 'excel']) }}" class="btn btn-success">
                    <i class="bx bx-file me-1"></i> Export Excel
                </a>
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                <h5 class="card-title mb-0">Filter Log</h5>
                <form method="GET" action="{{ route('admin.activity-log.index') }}" class="d-flex flex-wrap align-items-center gap-2">
                    <select name="user_id" class="form-select form-select-sm" style="width: 200px;">
                        <option value="">Semua Akun</option>
                        @foreach($users as $u)
                            <option value="{{ $u->id }}" {{ request('user_id') == $u->id ? 'selected' : '' }}>{{ $u->name }} ({{ $u->email }})</option>
                        @endforeach
                    </select>
                    <input type="text" name="search" value="{{ request('search') }}" class="form-control form-control-sm" placeholder="Cari deskripsi, URL, method..." style="width: 220px;">
                    <button type="submit" class="btn btn-primary btn-sm" title="Filter">
                        <i class="bx bx-search"></i>
                    </button>
                    @if(request('user_id') || request('search'))
                        <a href="{{ route('admin.activity-log.index') }}" class="btn btn-outline-secondary btn-sm" title="Reset">
                            <i class="bx bx-reset"></i>
                        </a>
                    @endif
                </form>
            </div>
        </div>

        <div class="card mt-4">
            <div class="card-header">
                <h5 class="card-title mb-0">Daftar Aktivitas</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Waktu</th>
                                <th>Akun</th>
                                <th>Method</th>
                                <th>URL</th>
                                <th>Deskripsi</th>
                                <th>IP Address</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($logs as $log)
                                <tr>
                                    <td>{{ $log->created_at->translatedFormat('d F Y H:i:s') }}</td>
                                    <td>{{ $log->user->name ?? 'Guest' }}</td>
                                    <td>
                                        @php
                                            $methodClass = match($log->method) {
                                                'GET' => 'bg-label-info',
                                                'POST' => 'bg-label-success',
                                                'PUT' => 'bg-label-warning',
                                                'PATCH' => 'bg-label-warning',
                                                'DELETE' => 'bg-label-danger',
                                                default => 'bg-label-secondary',
                                            };
                                        @endphp
                                        <span class="badge {{ $methodClass }} rounded-pill">{{ $log->method ?? 'N/A' }}</span>
                                    </td>
                                    <td>{{ Str::limit($log->url, 60) }}</td>
                                    <td>{{ $log->description }}</td>
                                    <td>{{ $log->ip_address ?? '-' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-4">Belum ada log aktivitas.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="mt-4">
            {{ $logs->links() }}
        </div>
    </div>
@endsection
