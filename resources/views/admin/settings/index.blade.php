@extends('layouts.admin')

@section('title', 'Pengaturan')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="row">
        <div class="col-md-12">
            <div class="card mb-4">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <h5 class="mb-0">Pengaturan Akun</h5>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    <div class="row">
                        <div class="col-md-4">
                            <div class="card h-100">
                                <div class="card-body text-center">
                                    <div class="avatar avatar-xl mx-auto mb-3">
                                        <div class="avatar-initial rounded bg-label-primary" style="font-size: 2rem;">
                                            {{ substr(Auth::user()->name, 0, 1) }}
                                        </div>
                                    </div>
                                    <h5 class="card-title mb-1">{{ Auth::user()->name }}</h5>
                                    <p class="text-muted mb-0">{{ Auth::user()->email }}</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-8">
                            <div class="card h-100">
                                <div class="card-header">
                                    <h5 class="mb-0">Ubah Kata Sandi</h5>
                                </div>
                                <div class="card-body">
                    <form method="POST" action="{{ route('admin.settings.password.update') }}">
                        @csrf
                        <div class="mb-3">
                            <label for="current_password" class="form-label">Kata Sandi Saat Ini <span class="text-danger">*</span></label>
                            <input type="password" name="current_password" id="current_password" class="form-control @error('current_password') is-invalid @enderror" required>
                            @error('current_password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">Kata Sandi Baru <span class="text-danger">*</span></label>
                            <input type="password" name="password" id="password" class="form-control @error('password') is-invalid @enderror" required>
                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label for="password_confirmation" class="form-label">Konfirmasi Kata Sandi Baru <span class="text-danger">*</span></label>
                            <input type="password" name="password_confirmation" id="password_confirmation" class="form-control" required>
                        </div>
                        <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Reset to Default -->
            <div class="row mt-3">
                <div class="col-md-12">
                    <div class="card mb-4">
                        <div class="card-header d-flex align-items-center justify-content-between">
                            <h5 class="mb-0 text-danger">Reset Kata Sandi ke Default</h5>
                        </div>
                        <div class="card-body">
                            <div class="alert alert-warning" role="alert">
                                <i class="bx bx-error-circle me-1"></i>
                                <strong>Perhatian:</strong> Tindakan ini akan mengembalikan kata sandi Anda ke nilai default secara langsung.
                                <br><small>Default password: <code>{{ env('DEFAULT_ADMIN_PASSWORD', 'password') }}</code></small>
                            </div>
                            <form method="POST" action="{{ route('admin.settings.password.reset') }}" id="resetForm">
                                @csrf
                                <div class="form-check mb-3">
                                    <input class="form-check-input" type="checkbox" name="reset_confirmation" id="reset_confirmation" value="1" required>
                                    <label class="form-check-label" for="reset_confirmation">
                                        Saya yakin ingin mereset kata sandi ke default
                                    </label>
                                </div>
                                <button type="submit" class="btn btn-danger" id="resetBtn">Reset ke Default</button>
                            </form>

                            <script>
                                document.getElementById('resetForm').addEventListener('submit', function(e) {
                                    var checkbox = document.getElementById('reset_confirmation');
                                    var btn = document.getElementById('resetBtn');
                                    
                                    if (!checkbox.checked) {
                                        e.preventDefault();
                                        alert('Silakan centang konfirmasi terlebih dahulu.');
                                        return;
                                    }
                                    
                                    btn.disabled = true;
                                    btn.innerHTML = 'Mereset...';
                                });
                            </script>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection