@extends('layouts.app')

@section('title', 'Buat Laporan Kerusakan/Kehilangan/Pencurian')
@section('header', 'Buat Laporan Kerusakan/Kehilangan/Pencurian')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <a href="{{ route('damage-reports.index') }}" class="inline-flex items-center gap-2 text-sm text-gray-600 hover:text-green-600 transition-colors">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"/></svg>
        Kembali
    </a>

    @if($errors->any())
        <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg text-sm">
            <ul class="list-disc list-inside space-y-1">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
    @endif

    <form action="{{ route('damage-reports.store') }}" method="POST" class="space-y-6">
        @csrf
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 space-y-5">
            <h3 class="text-sm font-semibold text-gray-900">Dilaporkan Oleh</h3>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div><label class="block text-sm font-medium text-gray-700 mb-1">Nama <span class="text-red-500">*</span></label><input type="text" name="pelapor_nama" value="{{ old('pelapor_nama') }}" required class="w-full px-4 py-2.5 text-sm border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500"></div>
                <div><label class="block text-sm font-medium text-gray-700 mb-1">Ruangan Lab <span class="text-red-500">*</span></label><input type="text" name="pelapor_ruangan" value="{{ old('pelapor_ruangan') }}" required class="w-full px-4 py-2.5 text-sm border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500"></div>
                <div><label class="block text-sm font-medium text-gray-700 mb-1">Telepon</label><input type="text" name="pelapor_telepon" value="{{ old('pelapor_telepon') }}" class="w-full px-4 py-2.5 text-sm border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500"></div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 space-y-5">
            <h3 class="text-sm font-semibold text-gray-900">Informasi Insiden</h3>
            <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
                <div><label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Insiden <span class="text-red-500">*</span></label><input type="date" name="tanggal_insiden" value="{{ old('tanggal_insiden') }}" required class="w-full px-4 py-2.5 text-sm border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500"></div>
                <div><label class="block text-sm font-medium text-gray-700 mb-1">Waktu Kejadian</label><input type="time" name="waktu_kejadiaan" value="{{ old('waktu_kejadiaan') }}" class="w-full px-4 py-2.5 text-sm border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500"></div>
                <div><label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Dilaporkan</label><input type="date" name="tanggal_dilaporkan" value="{{ old('tanggal_dilaporkan') }}" class="w-full px-4 py-2.5 text-sm border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500"></div>
                <div><label class="block text-sm font-medium text-gray-700 mb-1">Waktu Dilaporkan</label><input type="time" name="waktu_dilaporkan" value="{{ old('waktu_dilaporkan') }}" class="w-full px-4 py-2.5 text-sm border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500"></div>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div><label class="block text-sm font-medium text-gray-700 mb-1">Jenis Kejadian <span class="text-red-500">*</span></label><select name="jenis_kejadian" required class="w-full px-4 py-2.5 text-sm border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500"><option value="">Pilih</option><option value="kerusakan" {{ old('jenis_kejadian')=='kerusakan'?'selected':'' }}>Kerusakan</option><option value="kehilangan" {{ old('jenis_kejadian')=='kehilangan'?'selected':'' }}>Kehilangan</option><option value="pencurian" {{ old('jenis_kejadian')=='pencurian'?'selected':'' }}>Pencurian</option></select></div>
                <div><label class="block text-sm font-medium text-gray-700 mb-1">Lokasi Spesifik</label><input type="text" name="lokasi_spesifik" value="{{ old('lokasi_spesifik') }}" class="w-full px-4 py-2.5 text-sm border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500"></div>
            </div>
            <div><label class="block text-sm font-medium text-gray-700 mb-1">Bagaimana Peralatan Rusak/Hilang/Dicuri? <span class="text-red-500">*</span></label><textarea name="deskripsi_kejadian" rows="4" required class="w-full px-4 py-2.5 text-sm border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500">{{ old('deskripsi_kejadian') }}</textarea></div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div><label class="block text-sm font-medium text-gray-700 mb-1">Perkiraan Biaya Perbaikan/Penggantian</label><input type="text" name="perkiraan_biaya" value="{{ old('perkiraan_biaya') }}" class="w-full px-4 py-2.5 text-sm border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500"></div>
                <div><label class="block text-sm font-medium text-gray-700 mb-1">Penanggung Jawab Peralatan</label><input type="text" name="penanggung_jawab" value="{{ old('penanggung_jawab') }}" class="w-full px-4 py-2.5 text-sm border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500"></div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 space-y-4">
            <div class="flex items-center gap-2">
                <input type="checkbox" name="dilaporkan_ke_polisi" value="1" {{ old('dilaporkan_ke_polisi') ? 'checked' : '' }} class="rounded border-gray-300 text-green-600 focus:ring-green-500">
                <label class="text-sm font-medium text-gray-700">Apakah dilaporkan ke polisi?</label>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div><label class="block text-sm font-medium text-gray-700 mb-1">Berkas Polisi</label><input type="text" name="berkas_polisi" value="{{ old('berkas_polisi') }}" class="w-full px-4 py-2.5 text-sm border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500"></div>
                <div><label class="block text-sm font-medium text-gray-700 mb-1">Petugas yang Bertanggung Jawab</label><input type="text" name="petugas_polisi" value="{{ old('petugas_polisi') }}" class="w-full px-4 py-2.5 text-sm border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500"></div>
                <div><label class="block text-sm font-medium text-gray-700 mb-1">Unit Kepolisian</label><input type="text" name="unit_kepolisian" value="{{ old('unit_kepolisian') }}" class="w-full px-4 py-2.5 text-sm border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500"></div>
                <div><label class="block text-sm font-medium text-gray-700 mb-1">Telepon</label><input type="text" name="telepon_polisi" value="{{ old('telepon_polisi') }}" class="w-full px-4 py-2.5 text-sm border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500"></div>
            </div>
            <p class="text-xs text-gray-500">Laporan Polisi (jika Pencurian) dan faktur biaya penggantian harus dilampirkan.</p>
        </div>

        <div class="flex flex-wrap items-center gap-3">
            <button type="submit" class="px-6 py-2.5 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700">Simpan Laporan</button>
            <button type="submit" name="action" value="save_another" class="px-6 py-2.5 border border-green-600 text-green-700 bg-white text-sm font-medium rounded-lg hover:bg-green-50">Simpan &amp; Buat Ulang</button>
            <a href="{{ route('damage-reports.index') }}" class="px-6 py-2.5 bg-gray-100 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-200">Batal</a>
        </div>
    </form>
</div>
@endsection
