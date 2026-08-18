@extends('layouts.admin')

@section('title', 'Log Aktivitas - ' . $user->name)

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="fw-bold mb-0">Log Aktivitas - {{ $user->name }}</h4>
            <a href="{{ route('admin.activity-log.index') }}" class="btn btn-outline-secondary">
                <i class="bx bx-arrow-back me-1"></i> Kembali
            </a>
        </div>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="card mb-4">
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-4">
                        <p class="text-muted mb-1">Nama</p>
                        <p class="fw-semibold mb-0">{{ $user->name }}</p>
                    </div>
                    <div class="col-md-4">
                        <p class="text-muted mb-1">Email</p>
                        <p class="fw-semibold mb-0">{{ $user->email }}</p>
                    </div>
                    <div class="col-md-4">
                        <p class="text-muted mb-1">Total Aktivitas</p>
                        <p class="fw-semibold mb-0">{{ $logs->total() }} aksi</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Riwayat Aktivitas</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Waktu</th>
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
                                    <td colspan="5" class="text-center text-muted py-4">Belum ada aktivitas untuk akun ini.</td>
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
