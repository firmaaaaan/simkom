@extends('layouts.admin')

@section('title', 'Kartu Kendali - ' . $inventaris_iot_jaringan->nama_inventaris)

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="fw-bold mb-0">Kartu Kendali - {{ $inventaris_iot_jaringan->nama_inventaris }}</h4>
            <a href="{{ route('admin.inventaris-iot-jaringan.show', $inventaris_iot_jaringan) }}" class="btn btn-outline-secondary">
                <i class="bx bx-arrow-back"></i> Kembali
            </a>
        </div>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="row">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Form Pemeriksaan</h5>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('admin.inventaris-iot-jaringan.kartu-kendali.store', $inventaris_iot_jaringan) }}" method="POST">
                            @csrf
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="tanggal_pemeriksaan" class="form-label">Tanggal Pemeriksaan <span class="text-danger">*</span></label>
                                    <input type="date" name="tanggal_pemeriksaan" id="tanggal_pemeriksaan" value="{{ old('tanggal_pemeriksaan', date('Y-m-d')) }}" class="form-control @error('tanggal_pemeriksaan') is-invalid @enderror" required>
                                    @error('tanggal_pemeriksaan')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <label for="pemeriksa" class="form-label">Pemeriksa <span class="text-danger">*</span></label>
                                    <input type="text" name="pemeriksa" id="pemeriksa" value="{{ old('pemeriksa') }}" class="form-control @error('pemeriksa') is-invalid @enderror" placeholder="Nama pemeriksa" required>
                                    @error('pemeriksa')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <label for="tahun_ajaran_id" class="form-label">Tahun Ajaran <span class="text-danger">*</span></label>
                                    <select name="tahun_ajaran_id" id="tahun_ajaran_id" class="form-select @error('tahun_ajaran_id') is-invalid @enderror" required>
                                        <option value="">Pilih Tahun Ajaran</option>
                                        @foreach($tahunAjaranList as $ta)
                                            <option value="{{ $ta->id }}" {{ old('tahun_ajaran_id', $ta->status == 'aktif' ? $ta->id : '') == $ta->id ? 'selected' : '' }}>{{ $ta->nama }} {{ $ta->status == 'aktif' ? '(Aktif)' : '' }}</option>
                                        @endforeach
                                    </select>
                                    @error('tahun_ajaran_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-12">
                                    <label for="kondisi_keseluruhan" class="form-label">Kondisi Keseluruhan <span class="text-danger">*</span></label>
                                    <select name="kondisi_keseluruhan" id="kondisi_keseluruhan" class="form-select @error('kondisi_keseluruhan') is-invalid @enderror" required>
                                        <option value="baik" {{ old('kondisi_keseluruhan') == 'baik' ? 'selected' : '' }}>Baik</option>
                                        <option value="cukup" {{ old('kondisi_keseluruhan') == 'cukup' ? 'selected' : '' }}>Cukup</option>
                                        <option value="rusak" {{ old('kondisi_keseluruhan') == 'rusak' ? 'selected' : '' }}>Rusak</option>
                                    </select>
                                    @error('kondisi_keseluruhan')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Detail Pemeriksaan Komponen</label>
                                    <div class="card border">
                                        <div class="card-body p-0">
                                            <div class="table-responsive">
                                                <table class="table table-bordered mb-0">
                                                    <thead class="table-light">
                                                        <tr>
                                                            <th>Komponen</th>
                                                            <th style="width: 200px;">Kondisi</th>
                                                            <th>Catatan</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @forelse($inventaris_iot_jaringan->items as $item)
                                                            <tr>
                                                                <td>{{ $item->komponen->nama_komponen ?? '-' }} ({{ $item->komponen->kategori ?? '-' }})</td>
                                                                <td>
                                                                    <select name="items[{{ $loop->index }}][kondisi]" class="form-select form-select-sm">
                                                                        <option value="baik" {{ old("items.{$loop->index}.kondisi") == 'baik' ? 'selected' : '' }}>Baik</option>
                                                                        <option value="cukup" {{ old("items.{$loop->index}.kondisi") == 'cukup' ? 'selected' : '' }}>Cukup</option>
                                                                        <option value="rusak" {{ old("items.{$loop->index}.kondisi") == 'rusak' ? 'selected' : '' }}>Rusak</option>
                                                                    </select>
                                                                    <input type="hidden" name="items[{{ $loop->index }}][nama]" value="{{ $item->komponen->nama_komponen ?? 'Komponen' }}">
                                                                </td>
                                                                <td>
                                                                    <input type="text" name="items[{{ $loop->index }}][catatan]" class="form-control form-control-sm" value="{{ old("items.{$loop->index}.catatan") }}" placeholder="Catatan...">
                                                                </td>
                                                            </tr>
                                                        @empty
                                                            <tr>
                                                                <td colspan="3" class="text-center text-muted">Tidak ada komponen</td>
                                                            </tr>
                                                        @endforelse
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <label for="catatan" class="form-label">Catatan Tambahan</label>
                                    <textarea name="catatan" id="catatan" rows="3" class="form-control @error('catatan') is-invalid @enderror" placeholder="Catatan tambahan pemeriksaan">{{ old('catatan') }}</textarea>
                                    @error('catatan')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-12 pt-2">
                                    <div class="d-flex gap-2">
                                        <a href="{{ route('admin.inventaris-iot-jaringan.show', $inventaris_iot_jaringan) }}" class="btn btn-outline-secondary">Batal</a>
                                        <button type="submit" class="btn btn-primary">Simpan Kartu Kendali</button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Riwayat Pemeriksaan</h5>
                    </div>
                    <div class="card-body">
                        @if($riwayat->isNotEmpty())
                            @foreach($riwayat as $item)
                                <div class="border rounded p-3 mb-2">
                                    <div class="d-flex justify-content-between">
                                        <strong>{{ \Carbon\Carbon::parse($item->tanggal_pemeriksaan)->translatedFormat('d F Y') }}</strong>
                                        @php
                                            $badgeClass = match($item->kondisi_keseluruhan) {
                                                'baik' => 'bg-label-success',
                                                'cukup' => 'bg-label-warning',
                                                'rusak' => 'bg-label-danger',
                                            };
                                        @endphp
                                        <span class="badge {{ $badgeClass }} rounded-pill">{{ ucfirst($item->kondisi_keseluruhan) }}</span>
                                    </div>
                                    <p class="mb-0 mt-1 small text-muted">Pemeriksa: {{ $item->pemeriksa }}</p>
                                </div>
                            @endforeach
                        @else
                            <p class="text-muted mb-0">Belum ada riwayat pemeriksaan.</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection