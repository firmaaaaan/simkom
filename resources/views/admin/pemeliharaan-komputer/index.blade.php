@extends('layouts.admin')

@section('title', 'Pemeliharaan Komputer')

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="fw-bold mb-0">Pemeliharaan Komputer</h4>
            <div class="d-flex gap-2">
                <a href="{{ route('admin.pemeliharaan-komputer.export.excel', request()->query()) }}" class="btn btn-success">
                    <i class="bx bx-file me-1"></i> Export Excel
                </a>
                <a href="{{ route('admin.pemeliharaan-komputer.create') }}" class="btn btn-primary">
                    <i class="bx bx-plus me-1"></i> Tambah Pemeliharaan
                </a>
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Daftar Pemeliharaan</h5>
            </div>
            <div class="card-body">
                 <form method="GET" action="{{ route('admin.pemeliharaan-komputer.index') }}" class="row g-3 mb-4">
                     @if(request('komputer_id'))
                         <input type="hidden" name="komputer_id" value="{{ request('komputer_id') }}">
                     @endif
                     <div class="col-md-3">
                         <label for="tahun_ajaran_id" class="form-label small">Tahun Ajaran</label>
                         <select name="tahun_ajaran_id" class="form-select">
                             <option value="">Semua Tahun Ajaran</option>
                             @foreach($tahunList as $tahun)
                                 <option value="{{ $tahun->id }}" {{ request('tahun_ajaran_id') == $tahun->id ? 'selected' : '' }}>{{ $tahun->nama }}</option>
                             @endforeach
                         </select>
                     </div>
                     <div class="col-md-3">
                         <label for="laboratorium_id" class="form-label small">Laboratorium</label>
                         <select name="laboratorium_id" class="form-select">
                             <option value="">Semua Laboratorium</option>
                             @foreach($laboratoriums as $lab)
                                 <option value="{{ $lab->id }}" {{ request('laboratorium_id') == $lab->id ? 'selected' : '' }}>{{ $lab->nama_laboratorium }} ({{ $lab->kode_laboratorium }})</option>
                             @endforeach
                         </select>
                     </div>
                     <div class="col-md-3">
                         <label for="jenis" class="form-label small">Jenis Pemeliharaan</label>
                         <select name="jenis" class="form-select">
                             <option value="">Semua Jenis</option>
                             <option value="preventif" {{ request('jenis') == 'preventif' ? 'selected' : '' }}>Preventif</option>
                             <option value="korektif" {{ request('jenis') == 'korektif' ? 'selected' : '' }}>Korektif</option>
                             <option value="upgrade" {{ request('jenis') == 'upgrade' ? 'selected' : '' }}>Upgrade</option>
                             <option value="penggantian" {{ request('jenis') == 'penggantian' ? 'selected' : '' }}>Penggantian</option>
                             <option value="lainnya" {{ request('jenis') == 'lainnya' ? 'selected' : '' }}>Lainnya</option>
                         </select>
                     </div>
                     <div class="col-md-3 d-flex align-items-end">
                         <button type="submit" class="btn btn-primary me-2">
                             <i class="bx bx-filter me-1"></i> Filter
                         </button>
                         <a href="{{ route('admin.pemeliharaan-komputer.index') }}" class="btn btn-outline-secondary">
                             <i class="bx bx-refresh me-1"></i> Reset
                         </a>
                     </div>
                 </form>

                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>No</th>
                                <th>Komputer</th>
                                <th>Laboratorium</th>
                                <th>Tahun Ajaran</th>
                                <th>Tanggal</th>
                                <th>Jenis</th>
                                <th>Deskripsi</th>
                                <th>Biaya</th>
                                <th>PIC</th>
                                <th class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($pemeliharaan as $item)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $item->komputer->nama_komputer ?? '-' }}</td>
                                    <td>{{ $item->komputer->laboratorium->nama_laboratorium ?? '-' }}</td>
                                    <td>{{ $item->tahunAjaran->nama ?? '-' }}</td>
                                    <td>{{ $item->tanggal_pemeliharaan->translatedFormat('d F Y') }}</td>
                                    <td>
                                        @php
                                            $badgeClass = match($item->jenis_pemeliharaan) {
                                                'preventif' => 'bg-label-info',
                                                'korektif' => 'bg-label-warning',
                                                'upgrade' => 'bg-label-primary',
                                                'penggantian' => 'bg-label-danger',
                                                default => 'bg-label-secondary',
                                            };
                                        @endphp
                                        <span class="badge {{ $badgeClass }} rounded-pill">{{ ucfirst($item->jenis_pemeliharaan) }}</span>
                                    </td>
                                    <td>{{ Str::limit($item->deskripsi, 40) }}</td>
                                    <td>{{ $item->biaya ? 'Rp ' . number_format($item->biaya, 0, ',', '.') : '-' }}</td>
                                    <td>{{ $item->pic ?: '-' }}</td>
                                    <td class="text-center">
                                        <div class="d-flex gap-1 justify-content-center">
                                            <a href="{{ route('admin.pemeliharaan-komputer.edit', $item) }}" class="btn btn-sm btn-warning">
                                                <i class="bx bx-edit"></i>
                                            </a>
                                            <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#deleteModal" data-delete-action="{{ route('admin.pemeliharaan-komputer.destroy', $item) }}">
                                                <i class="bx bx-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="10" class="text-center text-muted py-4">Belum ada data pemeliharaan.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-4">
                    {{ $pemeliharaan->links() }}
                </div>
            </div>
        </div>
    </div>
@endsection
