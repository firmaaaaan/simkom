@extends('layouts.admin')

@section('title', 'Detail Inventaris IoT & Jaringan')

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <h4 class="fw-bold py-3 mb-4">Detail Inventaris IoT & Jaringan</h4>

        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">{{ $inventaris_iot_jaringan->nama_inventaris }}</h5>
                <div class="d-flex gap-2">
                    <a href="{{ route('admin.inventaris-iot-jaringan.qr', $inventaris_iot_jaringan) }}" class="btn btn-dark" target="_blank">
                        <i class="bx bx-qr me-1"></i> QR Stiker
                    </a>
                    <a href="{{ route('admin.inventaris-iot-jaringan.edit', $inventaris_iot_jaringan) }}" class="btn btn-primary">
                        <i class="bx bx-edit me-1"></i> Edit
                    </a>
                    <a href="{{ route('admin.inventaris-iot-jaringan.index') }}" class="btn btn-outline-secondary">Kembali</a>
                </div>
            </div>
            <div class="card-body">
                <div class="row g-4">
                    <div class="col-md-6">
                        <h6 class="text-muted small text-uppercase">Kode Perangkat</h6>
                        <p class="mb-0 fw-semibold">{{ $inventaris_iot_jaringan->kode_perangkat ?: '-' }}</p>
                    </div>
                    <div class="col-md-6">
                        <h6 class="text-muted small text-uppercase">Nama Inventaris</h6>
                        <p class="mb-0 fw-semibold">{{ $inventaris_iot_jaringan->nama_inventaris }}</p>
                    </div>
                    <div class="col-md-6">
                        <h6 class="text-muted small text-uppercase">Kategori</h6>
                        <p class="mb-0">{{ $inventaris_iot_jaringan->kategori }}</p>
                    </div>
                    <div class="col-md-6">
                        <h6 class="text-muted small text-uppercase">Jenis</h6>
                        <p class="mb-0">{{ $inventaris_iot_jaringan->jenis }}</p>
                    </div>
                    <div class="col-md-6">
                        <h6 class="text-muted small text-uppercase">Lokasi</h6>
                        <p class="mb-0">{{ $inventaris_iot_jaringan->lokasi ?: '-' }}</p>
                    </div>
                    <div class="col-md-6">
                        <h6 class="text-muted small text-uppercase">Status</h6>
                        @php
                            $statusClass = [
                                'tersedia' => 'bg-label-success',
                                'dipinjam' => 'bg-label-warning',
                                'perbaikan' => 'bg-label-info',
                                'tidak_aktif' => 'bg-label-danger',
                            ][$inventaris_iot_jaringan->status] ?? 'bg-label-secondary';
                        @endphp
                        <span class="badge {{ $statusClass }} rounded-pill">{{ ucfirst($inventaris_iot_jaringan->status) }}</span>
                    </div>
                    <div class="col-12">
                        <h6 class="text-muted small text-uppercase">Catatan</h6>
                        <p class="mb-0">{{ $inventaris_iot_jaringan->catatan ?: '-' }}</p>
                    </div>
                </div>
            </div>
        </div>

        @php
            $riwayatPeminjaman = $inventaris_iot_jaringan->peminjaman()->latest()->get();
            $peminjamanAktif = $riwayatPeminjaman->firstWhere('status', 'dipinjam');
        @endphp

        <div class="card mt-4">
            <div class="card-header d-flex align-items-center">
                <i class="bx bx-transfer-alt bx-sm text-primary me-2"></i>
                <h5 class="card-title mb-0">Peminjaman</h5>
                @if(!$peminjamanAktif)
                    <a href="{{ route('inventaris-iot-jaringan.peminjaman.create', $inventaris_iot_jaringan) }}" class="btn btn-sm btn-primary ms-auto">
                        <i class="bx bx-plus me-1"></i> Buat Peminjaman
                    </a>
                @endif
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
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
                            @forelse($riwayatPeminjaman as $pinjam)
                                <tr>
                                    <td>{{ $pinjam->nama_peminjam }}</td>
                                    <td>{{ $pinjam->npm_nim }}</td>
                                    <td>{{ $pinjam->tanggal_pinjam->translatedFormat('d F Y') }}</td>
                                    <td>{{ $pinjam->tanggal_kembali_direncanakan->translatedFormat('d F Y') }}</td>
                                    <td>{{ $pinjam->tanggal_kembali_aktual ? $pinjam->tanggal_kembali_aktual->translatedFormat('d F Y') : '-' }}</td>
                                    <td>
                                        @php
                                            $badgeClass = $pinjam->status == 'dipinjam' ? 'bg-label-warning' : 'bg-label-success';
                                        @endphp
                                        <span class="badge {{ $badgeClass }} rounded-pill">{{ ucfirst($pinjam->status) }}</span>
                                    </td>
                                    <td class="text-center">
                                        @if($pinjam->status == 'dipinjam')
                                            <button type="button" class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#returnModal{{ $pinjam->id }}">
                                                <i class="bx bx-check me-1"></i> Kembalikan
                                            </button>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center text-muted py-4">Belum ada data peminjaman.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        @php
            $kartuKendaliHistory = $inventaris_iot_jaringan->kartuKendali ?? \App\Models\KartuKendali::where('inspectable_type', \App\Models\InventarisIoTJaringan::class)->where('inspectable_id', $inventaris_iot_jaringan->id)->latest('tanggal_pemeriksaan')->get();
        @endphp
        @if($kartuKendaliHistory->isNotEmpty())
            <div class="card mt-4">
                <div class="card-header d-flex align-items-center">
                    <i class="bx bx-check-square bx-sm text-success me-2"></i>
                    <h5 class="card-title mb-0">Riwayat Kartu Kendali</h5>
                    <a href="{{ route('admin.inventaris-iot-jaringan.kartu-kendali.print', $inventaris_iot_jaringan) }}" class="btn btn-sm btn-outline-primary ms-auto" target="_blank">
                        <i class="bx bx-printer me-1"></i> Print
                    </a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Tanggal</th>
                                    <th>Pemeriksa</th>
                                    <th>Kondisi</th>
                                    <th>Catatan</th>
                                    <th class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($kartuKendaliHistory as $kartu)
                                    <tr>
                                        <td>{{ \Carbon\Carbon::parse($kartu->tanggal_pemeriksaan)->translatedFormat('d F Y') }}</td>
                                        <td>{{ $kartu->pemeriksa }}</td>
                                        <td>
                                            @php
                                                $badgeClass = match($kartu->kondisi_keseluruhan) {
                                                    'baik' => 'bg-label-success',
                                                    'cukup' => 'bg-label-warning',
                                                    'rusak' => 'bg-label-danger',
                                                };
                                            @endphp
                                            <span class="badge {{ $badgeClass }} rounded-pill">{{ ucfirst($kartu->kondisi_keseluruhan) }}</span>
                                        </td>
                                        <td>{{ $kartu->catatan ?: '-' }}</td>
                                        <td class="text-center">
                                            <button type="button" class="btn btn-sm btn-info btn-detail-kartu" data-items='@json($kartu->items)'>
                                                <i class="bx bx-show me-1"></i> Detail
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endif
    </div>

    <div class="modal fade" id="detailKartuKendaliModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Detail Pemeriksaan Komponen</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <table class="table table-bordered mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Komponen</th>
                                <th>Kondisi</th>
                                <th>Catatan</th>
                            </tr>
                        </thead>
                        <tbody id="detailKartuKendaliBody">
                        </tbody>
                    </table>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var buttons = document.querySelectorAll('.btn-detail-kartu');
            var modalBody = document.getElementById('detailKartuKendaliBody');
            var modal = document.getElementById('detailKartuKendaliModal');

            function ucfirst(str) {
                if (!str) return '-';
                return str.charAt(0).toUpperCase() + str.slice(1);
            }

            buttons.forEach(function (btn) {
                btn.addEventListener('click', function () {
                    var items = [];
                    try {
                        items = JSON.parse(btn.getAttribute('data-items') || '[]');
                    } catch (e) {
                        items = [];
                    }

                    modalBody.innerHTML = '';

                    if (!items.length) {
                        modalBody.innerHTML = '<tr><td colspan="3" class="text-center text-muted">Tidak ada detail komponen</td></tr>';
                    } else {
                        items.forEach(function (item) {
                            var row = document.createElement('tr');
                            var badgeClass = 'bg-label-secondary';
                            if (item.kondisi === 'baik') badgeClass = 'bg-label-success';
                            else if (item.kondisi === 'cukup') badgeClass = 'bg-label-warning';
                            else if (item.kondisi === 'rusak') badgeClass = 'bg-label-danger';

                            row.innerHTML = '<td>' + (item.nama || '-') + '</td>' +
                                '<td><span class="badge ' + badgeClass + ' rounded-pill">' + ucfirst(item.kondisi) + '</span></td>' +
                                '<td>' + (item.catatan || '-') + '</td>';
                            modalBody.appendChild(row);
                        });
                    }

                    if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                        var bsModal = new bootstrap.Modal(modal);
                        bsModal.show();
                    }
                });
            });
        });
    </script>

    @foreach($inventaris_iot_jaringan->peminjaman as $pinjam)
        @if($pinjam->status == 'dipinjam')
            <div class="modal fade" id="returnModal{{ $pinjam->id }}" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog">
                    <form method="POST" action="{{ route('admin.peminjaman-iot-jaringan.return', $pinjam) }}" class="modal-content">
                        @csrf
                        <div class="modal-header">
                            <h5 class="modal-title">Konfirmasi Pengembalian</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <p>Apakah Anda yakin ingin mengembalikan <strong>{{ $inventaris_iot_jaringan->nama_inventaris }}</strong>?</p>
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
