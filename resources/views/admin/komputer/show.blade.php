@extends('layouts.admin')

@section('title', 'Detail Komputer')

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="fw-bold mb-0">Detail Komputer</h4>
            <div class="d-flex gap-2">
                <a href="{{ route('admin.komputer.qr', $komputer) }}" class="btn btn-dark" target="_blank">
                    <i class="bx bx-qr me-1"></i> QR Stiker
                </a>
                <a href="{{ route('admin.komputer.pemeliharaan', $komputer) }}" class="btn btn-info">
                    <i class="bx bx-history me-1"></i> Riwayat Pemeliharaan
                </a>
                <a href="{{ route('admin.komputer.edit', $komputer) }}" class="btn btn-warning">
                    <i class="bx bx-edit me-1"></i> Edit
                </a>
                <a href="{{ route('admin.komputer.index') }}" class="btn btn-outline-secondary">
                    <i class="bx bx-arrow-back"></i> Kembali
                </a>
            </div>
        </div>

        <div class="row align-items-start">
            <div class="col-lg-4 col-md-5 mb-4">
                <div class="card shadow-sm sticky-top" style="top: 78px; z-index: 1020;">
                    <div class="card-header bg-transparent border-0">
                        <h5 class="card-title mb-0">{{ $komputer->nama_komputer }}</h5>
                        <small class="text-muted">{{ $komputer->kode_komputer }}</small>
                    </div>
                    <div class="card-body p-0">
                        @if($komputer->foto_url)
                            <img src="{{ $komputer->foto_url }}" class="img-fluid w-100 rounded-bottom object-fit-cover" alt="{{ $komputer->nama_komputer }}" style="height: 280px;" />
                        @else
                            <div class="d-flex align-items-center justify-content-center bg-label-light" style="height: 280px;">
                                <img src="{{ asset('assets') }}/img/illustrations/man-with-laptop-light.png" alt="Tanpa Gambar" class="img-fluid" style="max-height: 220px;" />
                            </div>
                        @endif
                    </div>
                    <div class="card-footer bg-transparent border-0">
                        @php
                            $statusClass = $komputer->getStatusBadgeClass();
                        @endphp
                        <span class="badge {{ $statusClass }} rounded-pill">Status: {{ ucfirst($komputer->status) }}</span>
                    </div>
                </div>
            </div>

            <div class="col-lg-8 col-md-7">

                @if($komputer->spesifikasi)
                    <div class="card mt-4 shadow-sm">
                        <div class="card-header d-flex align-items-center">
                            <i class="bx bx-info-circle bx-sm text-primary me-2"></i>
                            <h5 class="card-title mb-0">Spesifikasi</h5>
                        </div>
                        <div class="card-body">
                            <p class="mb-0">{{ $komputer->spesifikasi }}</p>
                        </div>
                    </div>
                @endif

                @if($komputer->catatan)
                    <div class="card mt-4 shadow-sm">
                        <div class="card-header d-flex align-items-center">
                            <i class="bx bx-note bx-sm text-secondary me-2"></i>
                            <h5 class="card-title mb-0">Catatan</h5>
                        </div>
                        <div class="card-body">
                            <p class="mb-0">{{ $komputer->catatan }}</p>
                        </div>
                    </div>
                @endif

                @php
                    $kartuKendaliHistory = $komputer->kartuKendali ?? \App\Models\KartuKendali::where('inspectable_type', \App\Models\Komputer::class)->where('inspectable_id', $komputer->id)->latest('tanggal_pemeriksaan')->get();
                @endphp
                @if($kartuKendaliHistory->isNotEmpty())
                    <div class="card mt-4 shadow-sm">
                        <div class="card-header d-flex align-items-center">
                            <i class="bx bx-check-square bx-sm text-success me-2"></i>
                            <h5 class="card-title mb-0">Riwayat Kartu Kendali</h5>
                            <a href="{{ route('admin.komputer.kartu-kendali.print', $komputer) }}" class="btn btn-sm btn-outline-primary ms-auto" target="_blank">
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
        </div>
        @php
        $pemeliharaanHistory = \App\Models\PemeliharaanKomputer::where('komputer_id', $komputer->id)->latest('tanggal_pemeliharaan')->get();
    @endphp
    @if($pemeliharaanHistory->isNotEmpty())
        <div class="card mt-4 shadow-sm">
            <div class="card-header d-flex align-items-center px-3">
                <i class="bx bx-wrench bx-sm text-warning me-2"></i>
                <h5 class="card-title mb-0">Riwayat Pemeliharaan</h5>
                <div class="ms-auto d-flex gap-2">
                    <a href="{{ route('admin.komputer.pemeliharaan.export.excel', $komputer) }}" class="btn btn-sm btn-success">
                        <i class="bx bx-file me-1"></i> Export Excel
                    </a>
                    <a href="{{ route('admin.pemeliharaan-komputer.index') }}" class="btn btn-sm btn-outline-primary">
                        <i class="bx bx-list-ul me-1"></i> Lihat Semua
                    </a>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3">Tanggal</th>
                            <th>Tahun Ajaran</th>
                            <th>Jenis</th>
                            <th>Deskripsi</th>
                            <th>Biaya</th>
                            <th>PIC</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($pemeliharaanHistory->take(5) as $pelih)
                            <tr>
                                <td class="ps-3">{{ \Carbon\Carbon::parse($pelih->tanggal_pemeliharaan)->translatedFormat('d F Y') }}</td>
                                <td>{{ $pelih->tahunAjaran->nama ?? "-" }}</td>
                                <td>
                                    @php
                                        $badgeClass = match($pelih->jenis_pemeliharaan) {
                                            'preventif' => 'bg-label-info',
                                            'korektif' => 'bg-label-warning',
                                            'upgrade' => 'bg-label-primary',
                                            'penggantian' => 'bg-label-danger',
                                            default => 'bg-label-secondary',
                                        };
                                    @endphp
                                    <span class="badge {{ $badgeClass }} rounded-pill">{{ ucfirst($pelih->jenis_pemeliharaan) }}</span>
                                </td>
                                <td>{{ Str::limit($pelih->deskripsi, 40) }}</td>
                                <td>{{ $pelih->biaya ? 'Rp ' . number_format($pelih->biaya, 0, ',', '.') : '-' }}</td>
                                <td>{{ $pelih->pic ?: '-' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
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
@endsection

