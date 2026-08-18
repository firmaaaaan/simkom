@extends('layouts.admin')

@section('title', 'Peminjaman Komputer')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <h5 class="mb-0">Peminjaman Komputer</h5>
                    <a href="{{ route('admin.peminjaman-komputer.export.excel', request()->query()) }}" class="btn btn-success">
                        <i class="bx bx-file me-1"></i> Export Excel
                    </a>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    <form method="GET" action="{{ route('admin.peminjaman-komputer.index') }}" class="d-flex flex-wrap align-items-center gap-2 mb-4">
                        <input type="text" name="search" value="{{ request('search') }}" class="form-control form-control-sm" placeholder="Cari nama atau npm..." style="width: 220px;">
                        <button type="submit" class="btn btn-primary btn-sm" title="Filter">
                            <i class="bx bx-search"></i>
                        </button>
                        @if(request('search'))
                            <a href="{{ route('admin.peminjaman-komputer.index') }}" class="btn btn-outline-secondary btn-sm" title="Reset">
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
                                    <th>Peminjam</th>
                                    <th>NPM/NIM</th>
                                    <th>Kode Tracker</th>
                                    <th>Tanggal</th>
                                    <th>Jam</th>
                                    <th>Status Peminjaman</th>
                                    <th>Status Komputer</th>
                                    <th class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($peminjaman as $item)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>
                                            <div class="fw-medium">{{ $item->komputer->nama_komputer ?? '-' }}</div>
                                            <small class="text-muted">{{ $item->komputer->kode_komputer ?? '' }}</small>
                                        </td>
                                        <td>{{ $item->nama_peminjam }}</td>
                                        <td>{{ $item->npm_nim }}</td>
                                        <td><code class="text-xs">{{ $item->kode_tracker }}</code></td>
                                        <td>{{ $item->tanggal_pinjam->translatedFormat('d F Y') }}</td>
                                        <td>{{ $item->jam_mulai }} - {{ $item->jam_selesai }}</td>
                                        <td>
                                            @php
                                                $badgeClass = match($item->status_peminjaman) {
                                                    'menunggu' => 'bg-label-warning',
                                                    'disetujui' => 'bg-label-info',
                                                    'ditolak' => 'bg-label-danger',
                                                    'dipinjam' => 'bg-label-primary',
                                                    'dikembalikan' => 'bg-label-success',
                                                    default => 'bg-label-secondary',
                                                };
                                            @endphp
                                            <span class="badge {{ $badgeClass }} rounded-pill">{{ ucfirst($item->status_peminjaman) }}</span>
                                        </td>
                                        <td>
                                            @php
                                                $statusBadge = match($item->komputer->status) {
                                                    'aktif' => 'bg-label-success',
                                                    'dipinjam' => 'bg-label-warning',
                                                    'perbaikan' => 'bg-label-warning',
                                                    'rusak' => 'bg-label-danger',
                                                    default => 'bg-label-secondary',
                                                };
                                            @endphp
                                            <span class="badge {{ $statusBadge }} rounded-pill">{{ ucfirst($item->komputer->status ?? '-') }}</span>
                                        </td>
                                        <td class="text-center">
                                            @if($item->status_peminjaman == 'menunggu')
                                                <button type="button" class="btn btn-sm btn-success me-1" data-bs-toggle="modal" data-bs-target="#approveModal{{ $item->id }}">
                                                    <i class="bx bx-check me-1"></i> Setujui
                                                </button>
                                                <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#rejectModal{{ $item->id }}">
                                                    <i class="bx bx-x me-1"></i> Tolak
                                                </button>
                                            @elseif($item->status_peminjaman == 'disetujui' || $item->status_peminjaman == 'dipinjam')
                                                <button type="button" class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#returnModal{{ $item->id }}">
                                                    <i class="bx bx-check me-1"></i> Selesaikan
                                                </button>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="10" class="text-center text-muted py-4">Belum ada data peminjaman komputer.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if($peminjaman->count() > 0)
                        @foreach($peminjaman as $item)
                            @if($item->status_peminjaman == 'menunggu')
                                <div class="modal fade" id="rejectModal{{ $item->id }}" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <form method="POST" action="{{ route('admin.peminjaman-komputer.reject', $item) }}" class="modal-content">
                                            @csrf
                                            <div class="modal-header">
                                                <h5 class="modal-title">Tolak Peminjaman</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="mb-3">
                                                    <label for="alasan_penolakan" class="form-label">Alasan Penolakan</label>
                                                    <textarea name="alasan_penolakan" id="alasan_penolakan" rows="3" class="form-control @error('alasan_penolakan') is-invalid @enderror" placeholder="Masukkan alasan penolakan..."></textarea>
                                                    @error('alasan_penolakan')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                                <button type="submit" class="btn btn-danger">Tolak Peminjaman</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>

                                <div class="modal fade" id="approveModal{{ $item->id }}" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <form method="POST" action="{{ route('admin.peminjaman-komputer.approve', $item) }}" class="modal-content">
                                            @csrf
                                            <div class="modal-header">
                                                <h5 class="modal-title">Setujui Peminjaman</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                <p>Apakah Anda yakin ingin menyetujui peminjaman komputer <strong>{{ $item->komputer->nama_komputer ?? '-' }}</strong> oleh <strong>{{ $item->nama_peminjam }}</strong>?</p>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                                <button type="submit" class="btn btn-success">Setujui</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            @endif

                            @if($item->status_peminjaman == 'disetujui' || $item->status_peminjaman == 'dipinjam')
                                <div class="modal fade" id="returnModal{{ $item->id }}" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <form method="POST" action="{{ route('admin.peminjaman-komputer.return', $item) }}" class="modal-content">
                                            @csrf
                                            <div class="modal-header">
                                                <h5 class="modal-title">Konfirmasi Penyelesaian</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                <p>Apakah Anda yakin ingin menyelesaikan peminjaman komputer <strong>{{ $item->komputer->nama_komputer ?? '-' }}</strong>?</p>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                                <button type="submit" class="btn btn-success">Selesaikan</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            @endif
                        @endforeach
                    @endif

                    <div class="mt-4">
                        {{ $peminjaman->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection