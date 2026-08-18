@extends('layouts.admin')

@section('title', 'Peminjaman Inventaris IoT & Jaringan')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <h5 class="mb-0">Peminjaman Inventaris IoT & Jaringan</h5>
                    <a href="{{ route('admin.peminjaman-iot-jaringan.export', request()->query()) }}" class="btn btn-success">
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

                    <form method="GET" action="{{ route('admin.peminjaman-iot-jaringan.index') }}" class="d-flex flex-wrap align-items-center gap-2 mb-4">
                        <input type="text" name="search" value="{{ request('search') }}" class="form-control form-control-sm" placeholder="Cari nama atau npm..." style="width: 220px;">
                        <button type="submit" class="btn btn-primary btn-sm" title="Filter">
                            <i class="bx bx-search"></i>
                        </button>
                        @if(request('search'))
                            <a href="{{ route('admin.peminjaman-iot-jaringan.index') }}" class="btn btn-outline-secondary btn-sm" title="Reset">
                                <i class="bx bx-reset"></i>
                            </a>
                        @endif
                    </form>

                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>No</th>
                                    <th>Inventaris</th>
                                    <th>Peminjam</th>
                                    <th>NPM/NIM</th>
                                    <th>Tanggal Pinjam</th>
                                    <th>Estimasi Kembali</th>
                                    <th>Tanggal Kembali</th>
                                    <th>Status</th>
                                    <th class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($peminjaman as $item)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>
                                            <div class="fw-medium">{{ $item->inventaris->nama_inventaris ?? '-' }}</div>
                                            <small class="text-muted">{{ $item->inventaris->kode_perangkat ?? '' }}</small>
                                        </td>
                                        <td>{{ $item->nama_peminjam }}</td>
                                        <td>{{ $item->npm_nim }}</td>
                                        <td>{{ $item->tanggal_pinjam->translatedFormat('d F Y') }}</td>
                                        <td>{{ $item->tanggal_kembali_direncanakan->translatedFormat('d F Y') }}</td>
                                        <td>{{ $item->tanggal_kembali_aktual ? $item->tanggal_kembali_aktual->translatedFormat('d F Y') : '-' }}</td>
                                        <td>
                                            @php
                                                $badgeClass = $item->status == 'dipinjam' ? 'bg-label-warning' : 'bg-label-success';
                                            @endphp
                                            <span class="badge {{ $badgeClass }} rounded-pill">{{ ucfirst($item->status) }}</span>
                                        </td>
                                        <td class="text-center">
                                            @if($item->status == 'dipinjam')
                                                <button type="button" class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#returnModal{{ $item->id }}">
                                                    <i class="bx bx-check me-1"></i> Kembalikan
                                                </button>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="9" class="text-center text-muted py-4">Belum ada data peminjaman.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4">
                        {{ $peminjaman->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@foreach($peminjaman as $item)
    @if($item->status == 'dipinjam')
        <div class="modal fade" id="returnModal{{ $item->id }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <form method="POST" action="{{ route('admin.peminjaman-iot-jaringan.return', $item) }}" class="modal-content">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">Konfirmasi Pengembalian</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p>Apakah Anda yakin ingin mengembalikan <strong>{{ $item->inventaris->nama_inventaris ?? '-' }}</strong>?</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-success">Kembalikan</button>
                    </div>
                </form>
            </div>
        </div>
    @endif
@endforeach
@endsection
