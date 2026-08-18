@extends('layouts.admin')

@section('title', 'Dashboard')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="row">
        <div class="col-12 mb-4">
            <h4 class="fw-bold mb-0">Dashboard</h4>
            <p class="text-muted">Ringkasan sistem inventaris laboratorium komputer</p>
        </div>

        <!-- Statistik Utama -->
        <div class="col-md-6 col-lg-3 mb-4">
            <a href="{{ route('admin.komputer.index') }}" class="card text-decoration-none h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-1">Total Komputer</p>
                            <h4 class="fw-bold mb-0">{{ \App\Models\Komputer::count() }}</h4>
                        </div>
                        <div class="avatar avatar-icon">
                            <div class="avatar-initial rounded bg-label-primary">
                                <i class="bx bx-desktop text-primary"></i>
                            </div>
                        </div>
                    </div>
                    <div class="d-flex align-items-center mt-3 gap-3">
                        <span class="badge bg-label-success rounded-pill">Aktif: {{ \App\Models\Komputer::where('status', 'aktif')->count() }}</span>
                        <span class="badge bg-label-danger rounded-pill">Rusak: {{ \App\Models\Komputer::where('status', 'rusak')->count() }}</span>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-md-6 col-lg-3 mb-4">
            <a href="{{ route('admin.laboratorium.index') }}" class="card text-decoration-none h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-1">Laboratorium</p>
                            <h4 class="fw-bold mb-0">{{ \App\Models\Laboratorium::count() }}</h4>
                        </div>
                        <div class="avatar avatar-icon">
                            <div class="avatar-initial rounded bg-label-info">
                                <i class="bx bx-building-house text-info"></i>
                            </div>
                        </div>
                    </div>
                    <div class="d-flex align-items-center mt-3 gap-3">
                        <span class="badge bg-label-success rounded-pill">Aktif: {{ \App\Models\Laboratorium::where('status', 'aktif')->count() }}</span>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-md-6 col-lg-3 mb-4">
            <a href="{{ route('admin.laporan-kendala-komputer.index') }}" class="card text-decoration-none h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-1">Laporan Kendala</p>
                            <h4 class="fw-bold mb-0">{{ \App\Models\LaporanKendalaKomputer::count() }}</h4>
                        </div>
                        <div class="avatar avatar-icon">
                            <div class="avatar-initial rounded bg-label-warning">
                                <i class="bx bx-error-circle text-warning"></i>
                            </div>
                        </div>
                    </div>
                    <div class="d-flex align-items-center mt-3 gap-3">
                        <span class="badge bg-label-warning rounded-pill">Menunggu: {{ \App\Models\LaporanKendalaKomputer::where('status_kendala', 'menunggu')->count() }}</span>
                        <span class="badge bg-label-success rounded-pill">Selesai: {{ \App\Models\LaporanKendalaKomputer::where('status_kendala', 'selesai')->count() }}</span>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-md-6 col-lg-3 mb-4">
            <a href="{{ route('admin.peminjaman-komputer.index') }}" class="card text-decoration-none h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-1">Peminjaman Komputer</p>
                            <h4 class="fw-bold mb-0">{{ \App\Models\PeminjamanKomputer::count() }}</h4>
                        </div>
                        <div class="avatar avatar-icon">
                            <div class="avatar-initial rounded bg-label-info">
                                <i class="bx bx-desktop text-info"></i>
                            </div>
                        </div>
                    </div>
                    <div class="d-flex align-items-center mt-3 gap-3">
                        <span class="badge bg-label-warning rounded-pill">Menunggu: {{ \App\Models\PeminjamanKomputer::where('status_peminjaman', 'menunggu')->count() }}</span>
                        <span class="badge bg-label-success rounded-pill">Dikembalikan: {{ \App\Models\PeminjamanKomputer::where('status_peminjaman', 'dikembalikan')->count() }}</span>
                    </div>
                </div>
            </a>
        </div>

        <!-- Jumlah Komputer per Laboratorium -->
        <div class="col-12 mb-4">
            <div class="card">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <h5 class="mb-0">Jumlah Komputer per Laboratorium</h5>
                    <a href="{{ route('admin.laboratorium.index') }}" class="btn btn-sm btn-outline-primary">Lihat Semua</a>
                </div>
                <div class="card-body">
                    @php
                        $laboratoriums = \App\Models\Laboratorium::withCount('komputers')->orderBy('nama_laboratorium')->get();
                    @endphp
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Kode Lab</th>
                                    <th>Nama Laboratorium</th>
                                    <th>Gedung / Ruangan</th>
                                    <th class="text-center">Total Komputer</th>
                                    <th class="text-center">Aktif</th>
                                    <th class="text-center">Perbaikan</th>
                                    <th class="text-center">Rusak</th>
                                    <th>Status Lab</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($laboratoriums as $lab)
                                    @php
                                        $komputers = $lab->komputer ?? \App\Models\Komputer::where('laboratorium_id', $lab->id)->get();
                                        $total = $komputers->count();
                                        $aktif = $komputers->where('status', 'aktif')->count();
                                        $perbaikan = $komputers->where('status', 'perbaikan')->count();
                                        $rusak = $komputers->where('status', 'rusak')->count();
                                    @endphp
                                    <tr>
                                        <td><span class="fw-semibold">{{ $lab->kode_laboratorium }}</span></td>
                                        <td>{{ $lab->nama_laboratorium }}</td>
                                        <td>{{ $lab->gedung }} / {{ $lab->ruangan }}</td>
                                        <td class="text-center"><strong>{{ $total }}</strong></td>
                                        <td class="text-center"><span class="badge bg-label-success rounded-pill">{{ $aktif }}</span></td>
                                        <td class="text-center"><span class="badge bg-label-warning rounded-pill">{{ $perbaikan }}</span></td>
                                        <td class="text-center"><span class="badge bg-label-danger rounded-pill">{{ $rusak }}</span></td>
                                        <td>
                                            @if($lab->status == 'aktif')
                                                <span class="badge bg-label-success rounded-pill">Aktif</span>
                                            @else
                                                <span class="badge bg-label-danger rounded-pill">Nonaktif</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center text-muted py-3">Belum ada data laboratorium.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Peminjaman & Laporan Terbaru -->
        <div class="col-lg-6 mb-4">
            <div class="card h-100">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <h5 class="mb-0">Peminjaman Komputer Terbaru</h5>
                    <a href="{{ route('admin.peminjaman-komputer.index') }}" class="btn btn-sm btn-outline-primary">Lihat Semua</a>
                </div>
                <div class="card-body">
                    @php
                        $peminjamanTerbaru = \App\Models\PeminjamanKomputer::with('komputer')->latest()->take(5)->get();
                    @endphp
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Komputer</th>
                                    <th>Peminjam</th>
                                    <th>Tanggal</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($peminjamanTerbaru as $item)
                                    <tr>
                                        <td>{{ $item->komputer->nama_komputer ?? '-' }}</td>
                                        <td>{{ $item->nama_peminjam }}</td>
                                        <td>{{ $item->tanggal_pinjam->translatedFormat('d F Y') }}</td>
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
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center text-muted py-3">Belum ada data peminjaman.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-6 mb-4">
            <div class="card h-100">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <h5 class="mb-0">Laporan Kendala Terbaru</h5>
                    <a href="{{ route('admin.laporan-kendala-komputer.index') }}" class="btn btn-sm btn-outline-primary">Lihat Semua</a>
                </div>
                <div class="card-body">
                    @php
                        $laporanTerbaru = \App\Models\LaporanKendalaKomputer::with('komputer')->latest()->take(5)->get();
                    @endphp
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Komputer</th>
                                    <th>Pelapor</th>
                                    <th>Tanggal</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($laporanTerbaru as $item)
                                    <tr>
                                        <td>{{ $item->komputer->nama_komputer ?? '-' }}</td>
                                        <td>{{ $item->nama_pelapor }}</td>
                                        <td>{{ $item->tanggal_lapor->translatedFormat('d F Y') }}</td>
                                        <td>
                                            @php
                                                $badgeClass = match($item->status_kendala) {
                                                    'menunggu' => 'bg-label-warning',
                                                    'diperbaiki' => 'bg-label-info',
                                                    'selesai' => 'bg-label-success',
                                                    default => 'bg-label-secondary',
                                                };
                                            @endphp
                                            <span class="badge {{ $badgeClass }} rounded-pill">{{ ucfirst($item->status_kendala) }}</span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center text-muted py-3">Belum ada laporan kendala.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
