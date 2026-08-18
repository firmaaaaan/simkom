@extends('layouts.admin')

@section('title', 'Pengaturan Jadwal Kuliah')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <h5 class="mb-0">Pengaturan API Jadwal Kuliah</h5>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    <div class="alert alert-info" role="alert">
                        <h6 class="alert-heading">Cara Menggunakan:</h6>
                        <ol class="mb-0 mt-2">
                            <li>Masukkan URL API jadwal dari SIMPTT atau sistem lain</li>
                            <li>Masukkan token API jika diperlukan</li>
                            <li>Pilih tipe jadwal: Kuliah atau Non-Kuliah</li>
                            <li>Klik Simpan</li>
                            <li>Buka halaman publik <a href="{{ route('jadwal-kuliah.index') }}" target="_blank">Jadwal Kuliah</a> untuk melihat kalender</li>
                        </ol>
                    </div>

                    <form method="POST" action="{{ route('admin.jadwal-kuliah.store') }}" class="row g-3 mt-4">
                        @csrf
                        <div class="col-md-6">
                            <label for="api_url" class="form-label">URL API Jadwal <span class="text-danger">*</span></label>
                            <input type="url" name="api_url" id="api_url" class="form-control" placeholder="https://simptt.example.com/api/jadwal" required>
                            @error('api_url') <div class="text-danger mt-1">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="api_token" class="form-label">Token API (Opsional)</label>
                            <input type="text" name="api_token" id="api_token" class="form-control" placeholder="Masukkan token jika diperlukan">
                            @error('api_token') <div class="text-danger mt-1">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="tipe" class="form-label">Tipe Jadwal <span class="text-danger">*</span></label>
                            <select name="tipe" id="tipe" class="form-select" required>
                                <option value="kuliah">Jadwal Kuliah</option>
                                <option value="non_kuliah">Jadwal Non-Kuliah</option>
                            </select>
                            @error('tipe') <div class="text-danger mt-1">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="refresh_interval" class="form-label">Interval Refresh (menit) <span class="text-danger">*</span></label>
                            <input type="number" name="refresh_interval" id="refresh_interval" class="form-control" value="60" min="5" required>
                            @error('refresh_interval') <div class="text-danger mt-1">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary">Simpan Pengaturan</button>
                            <a href="{{ route('jadwal-kuliah.index') }}" target="_blank" class="btn btn-outline-secondary ms-2">Lihat Jadwal</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Daftar Konfigurasi API</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>No</th>
                                    <th>Tipe</th>
                                    <th>URL API</th>
                                    <th>Token</th>
                                    <th>Status</th>
                                    <th>Refresh</th>
                                    <th>Terakhir Sync</th>
                                    <th class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($settings as $item)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ ucfirst($item->tipe) }}</td>
                                        <td><code>{{ $item->api_url }}</code></td>
                                        <td>{{ $item->api_token ? '***' . substr($item->api_token, -4) : '-' }}</td>
                                        <td>
                                            <span class="badge {{ $item->is_active ? 'bg-label-success' : 'bg-label-secondary' }} rounded-pill">
                                                {{ $item->is_active ? 'Aktif' : 'Nonaktif' }}
                                            </span>
                                        </td>
                                        <td>{{ $item->refresh_interval }} menit</td>
                                        <td>{{ $item->last_sync ? $item->last_sync->translatedFormat('d F Y H:i') : '-' }}</td>
                                        <td class="text-center">
                                            <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#deleteModal{{ $item->id }}">
                                                <i class="bx bx-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center text-muted py-4">Belum ada pengaturan API jadwal.</td>
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

@foreach($settings as $item)
    <div class="modal fade" id="deleteModal{{ $item->id }}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <form method="POST" action="{{ route('admin.jadwal-kuliah.destroy', $item->id) }}" class="modal-content">
                @csrf
                @method('DELETE')
                <div class="modal-header">
                    <h5 class="modal-title">Hapus Pengaturan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Apakah Anda yakin ingin menghapus pengaturan <strong>{{ $item->nama_settings }}</strong>?</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger">Hapus</button>
                </div>
            </form>
        </div>
    </div>
@endforeach
@endsection
