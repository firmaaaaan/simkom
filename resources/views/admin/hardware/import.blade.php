@extends('layouts.admin')

@section('title', 'Import Hardware')

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <h4 class="fw-bold py-3 mb-4">Import Hardware</h4>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @isset($errors)
            @if($errors->any())
                <div class="alert alert-danger alert-dismissible" role="alert">
                    <strong>Terjadi kesalahan!</strong> Periksa kembali file Anda.
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif
        @endisset

        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Import Data Hardware</h5>
                <small class="text-muted">Format yang didukung: Excel (.xlsx)</small>
            </div>
            <div class="card-body">
                <div class="alert alert-info mb-3">
                    <strong>Petunjuk:</strong> Download template Excel terlebih dahulu, isi data sesuai format, lalu upload file untuk import.
                </div>
                <div class="mb-3">
                    <a href="{{ route('admin.hardware.export.excel') }}" class="btn btn-warning">
                        <i class="bx bx-download me-1"></i> Download Template Excel
                    </a>
                </div>
                <form method="POST" action="{{ route('admin.hardware.import') }}" enctype="multipart/form-data" class="row g-3">
                    @csrf
                    <div class="col-md-6">
                        <label for="file" class="form-label">Pilih File <span class="text-danger">*</span></label>
                        <input type="file" name="file" id="file" class="form-control @error('file') is-invalid @enderror" accept=".xlsx,.xls" required>
                        @isset($errors)
                            @error('file')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        @endisset
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Format Import</label>
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-success">
                                <i class="bx bx-file me-1"></i> Excel
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
