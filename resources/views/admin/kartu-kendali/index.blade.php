@extends('layouts.admin')

@section('title', 'Manajemen Kartu Kendali')

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="fw-bold mb-0">Manajemen Kartu Kendali</h4>
            <div class="d-flex gap-2">
                <a href="{{ route('admin.kartu-kendali.export.komputer', request()->query()) }}" class="btn btn-dark">
                    <i class="bx bx-export me-1"></i> Export Komputer
                </a>
                <a href="{{ route('admin.kartu-kendali.export.iot', request()->query()) }}" class="btn btn-info">
                    <i class="bx bx-export me-1"></i> Export IoT & Jaringan
                </a>
            </div>
        </div>

        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                <h5 class="card-title mb-0">Filter Data</h5>
                <form method="GET" action="{{ route('admin.kartu-kendali.index') }}" class="d-flex flex-wrap align-items-center gap-2">
                    <select name="tipe" class="form-select form-select-sm" style="width: 150px;">
                        <option value="">Semua Tipe</option>
                        <option value="komputer" {{ request('tipe') == 'komputer' ? 'selected' : '' }}>Komputer</option>
                        <option value="iot" {{ request('tipe') == 'iot' ? 'selected' : '' }}>IoT & Jaringan</option>
                    </select>
                    <select name="tahun_ajaran_id" class="form-select form-select-sm" style="width: 170px;">
                        <option value="">Semua Tahun Ajaran</option>
                        @foreach($tahunList as $tahun)
                            <option value="{{ $tahun->id }}" {{ request('tahun_ajaran_id') == $tahun->id ? 'selected' : '' }}>{{ $tahun->nama }}</option>
                        @endforeach
                    </select>
                    <select name="laboratorium_id" class="form-select form-select-sm" style="width: 200px;">
                        <option value="">Semua Laboratorium</option>
                        @foreach($laboratoriums as $lab)
                            <option value="{{ $lab->id }}" {{ request('laboratorium_id') == $lab->id ? 'selected' : '' }}>{{ $lab->nama_laboratorium }} ({{ $lab->kode_laboratorium }})</option>
                        @endforeach
                    </select>
                    <button type="submit" class="btn btn-primary btn-sm" title="Filter">
                        <i class="bx bx-search"></i>
                    </button>
                    @if(request('tipe') || request('tahun_ajaran_id') || request('laboratorium_id'))
                        <a href="{{ route('admin.kartu-kendali.index') }}" class="btn btn-outline-secondary btn-sm" title="Reset">
                            <i class="bx bx-reset"></i>
                        </a>
                    @endif
                </form>
            </div>
        </div>

        <div class="card mt-4">
            <div class="card-header">
                <h5 class="card-title mb-0">Daftar Kartu Kendali</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>No</th>
                                <th>Tipe</th>
                                <th>Nama</th>
                                <th>Laboratorium</th>
                                <th>Tanggal Pemeriksaan</th>
                                <th>Tahun Ajaran</th>
                                <th>Kondisi</th>
                                <th>Pemeriksa</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($kartuKendali as $item)
                                @php
                                    $inspectable = $item->inspectable;
                                    $tanggal = \Carbon\Carbon::parse($item->tanggal_pemeriksaan);
                                    $tahunAjaran = $item->tahunAjaran->nama ?? '-';
                                @endphp
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>
                                        @if($item->inspectable_type == \App\Models\Komputer::class)
                                            <span class="badge bg-label-primary rounded-pill">Komputer</span>
                                        @else
                                            <span class="badge bg-label-info rounded-pill">IoT & Jaringan</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($item->inspectable_type == \App\Models\Komputer::class)
                                            {{ $inspectable->nama_komputer ?? '-' }}
                                        @else
                                            {{ $inspectable->nama_inventaris ?? '-' }}
                                        @endif
                                    </td>
                                    <td>
                                        @if($item->inspectable_type == \App\Models\Komputer::class)
                                            {{ $inspectable->laboratorium->nama_laboratorium ?? '-' }}
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td>{{ $tanggal->translatedFormat('d F Y') }}</td>
                                    <td>{{ $tahunAjaran }}</td>
                                    <td>
                                        @php
                                            $badgeClass = match($item->kondisi_keseluruhan) {
                                                'baik' => 'bg-label-success',
                                                'cukup' => 'bg-label-warning',
                                                'rusak' => 'bg-label-danger',
                                                default => 'bg-label-secondary',
                                            };
                                        @endphp
                                        <span class="badge {{ $badgeClass }} rounded-pill">{{ ucfirst($item->kondisi_keseluruhan) }}</span>
                                    </td>
                                    <td>{{ $item->pemeriksa }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center text-muted py-4">Tidak ada data kartu kendali.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="mt-4">
            {{ $kartuKendali->links() }}
        </div>
    </div>
@endsection

