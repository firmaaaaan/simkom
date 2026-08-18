@extends('layouts.admin')

@section('title', 'Edit Pemeliharaan Komputer')

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="fw-bold mb-0">Edit Pemeliharaan Komputer</h4>
            <a href="{{ route('admin.pemeliharaan-komputer.index') }}" class="btn btn-outline-secondary">
                <i class="bx bx-arrow-back"></i> Kembali
            </a>
        </div>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Form Edit Pemeliharaan</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.pemeliharaan-komputer.update', $pemeliharaan) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="laboratorium_id" class="form-label">Laboratorium <span class="text-danger">*</span></label>
                            <select name="laboratorium_id" id="laboratorium_id" class="form-select @error('laboratorium_id') is-invalid @enderror" required>
                                <option value="">Pilih Laboratorium</option>
                                @foreach($laboratoriums as $lab)
                                    <option value="{{ $lab->id }}" {{ (old('laboratorium_id', $pemeliharaan->komputer->laboratorium_id ?? '')) == $lab->id ? 'selected' : '' }}>{{ $lab->nama_laboratorium }}</option>
                                @endforeach
                            </select>
                            @error('laboratorium_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="komputer_id" class="form-label">Komputer <span class="text-danger">*</span></label>
                            <select name="komputer_id" id="komputer_id" class="form-select @error('komputer_id') is-invalid @enderror" required>
                                <option value="">Pilih Komputer</option>
                                @foreach($komputers as $komp)
                                    <option value="{{ $komp->id }}" data-lab="{{ $komp->laboratorium_id }}" {{ (old('komputer_id', $pemeliharaan->komputer_id ?? '')) == $komp->id ? 'selected' : '' }}>{{ $komp->nama_komputer }}</option>
                                @endforeach
                            </select>
                            @error('komputer_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="tahun_ajaran_id" class="form-label">Tahun Ajaran <span class="text-danger">*</span></label>
                            <select name="tahun_ajaran_id" id="tahun_ajaran_id" class="form-select @error('tahun_ajaran_id') is-invalid @enderror" required>
                                <option value="">Pilih Tahun Ajaran</option>
                                @foreach($tahunAjaranList as $ta)
                                    <option value="{{ $ta->id }}" {{ old('tahun_ajaran_id', $pemeliharaan->tahun_ajaran_id ?? '') == $ta->id ? 'selected' : '' }}>{{ $ta->nama }} {{ $ta->status == 'aktif' ? '(Aktif)' : '' }}</option>
                                @endforeach
                            </select>
                            @error('tahun_ajaran_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="tanggal_pemeliharaan" class="form-label">Tanggal Pemeliharaan <span class="text-danger">*</span></label>
                            <input type="date" name="tanggal_pemeliharaan" id="tanggal_pemeliharaan" value="{{ old('tanggal_pemeliharaan', $pemeliharaan->tanggal_pemeliharaan->format('Y-m-d')) }}" class="form-control @error('tanggal_pemeliharaan') is-invalid @enderror" required>
                            @error('tanggal_pemeliharaan')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="jenis_pemeliharaan" class="form-label">Jenis Pemeliharaan <span class="text-danger">*</span></label>
                            <select name="jenis_pemeliharaan" id="jenis_pemeliharaan" class="form-select @error('jenis_pemeliharaan') is-invalid @enderror" required>
                                <option value="preventif" {{ old('jenis_pemeliharaan', $pemeliharaan->jenis_pemeliharaan) == 'preventif' ? 'selected' : '' }}>Preventif</option>
                                <option value="korektif" {{ old('jenis_pemeliharaan', $pemeliharaan->jenis_pemeliharaan) == 'korektif' ? 'selected' : '' }}>Korektif</option>
                                <option value="upgrade" {{ old('jenis_pemeliharaan', $pemeliharaan->jenis_pemeliharaan) == 'upgrade' ? 'selected' : '' }}>Upgrade</option>
                                <option value="penggantian" {{ old('jenis_pemeliharaan', $pemeliharaan->jenis_pemeliharaan) == 'penggantian' ? 'selected' : '' }}>Penggantian</option>
                                <option value="lainnya" {{ old('jenis_pemeliharaan', $pemeliharaan->jenis_pemeliharaan) == 'lainnya' ? 'selected' : '' }}>Lainnya</option>
                            </select>
                            @error('jenis_pemeliharaan')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="biaya" class="form-label">Biaya (Rp)</label>
                            <input type="number" name="biaya" id="biaya" value="{{ old('biaya', $pemeliharaan->biaya) }}" class="form-control @error('biaya') is-invalid @enderror" placeholder="0" min="0" step="1000">
                            @error('biaya')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="pic" class="form-label">PIC (Penanggung Jawab)</label>
                            <input type="text" name="pic" id="pic" value="{{ old('pic', $pemeliharaan->pic) }}" class="form-control @error('pic') is-invalid @enderror" placeholder="Nama PIC">
                            @error('pic')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-12">
                            <label for="deskripsi" class="form-label">Deskripsi <span class="text-danger">*</span></label>
                            <textarea name="deskripsi" id="deskripsi" rows="4" class="form-control @error('deskripsi') is-invalid @enderror" placeholder="Jelaskan detail pemeliharaan yang dilakukan..." required>{{ old('deskripsi', $pemeliharaan->deskripsi) }}</textarea>
                            @error('deskripsi')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-12 pt-2">
                            <div class="d-flex gap-2">
                                <a href="{{ route('admin.pemeliharaan-komputer.index') }}" class="btn btn-outline-secondary">Batal</a>
                                <button type="submit" class="btn btn-primary">Update</button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var laboratoriumSelect = document.getElementById('laboratorium_id');
            var komputerSelect = document.getElementById('komputer_id');

            function updateKomputerOptions() {
                var selectedLab = laboratoriumSelect.value;
                var komputerOptions = komputerSelect.querySelectorAll('option');

                komputerOptions.forEach(function (option) {
                    if (!option.value) {
                        option.style.display = 'block';
                        return;
                    }
                    if (selectedLab && option.dataset.lab !== selectedLab) {
                        option.style.display = 'none';
                    } else {
                        option.style.display = 'block';
                    }
                });

                if (selectedLab && komputerSelect.value && komputerSelect.querySelector('option[value="' + komputerSelect.value + '"]').style.display === 'none') {
                    komputerSelect.value = '';
                }
            }

            if (laboratoriumSelect && komputerSelect) {
                laboratoriumSelect.addEventListener('change', updateKomputerOptions);
                updateKomputerOptions();
            }
        });
    </script>
@endsection
