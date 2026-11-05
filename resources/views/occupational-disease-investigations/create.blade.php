@extends('layouts.app')

@section('title', 'Buat Penyelidikan PAK')
@section('header', 'Buat Penyelidikan Penyakit Akibat Kerja')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <a href="{{ route('occupational-disease-investigations.index') }}" class="inline-flex items-center gap-2 text-sm text-gray-600 hover:text-green-600">Kembali</a>

    <form action="{{ route('occupational-disease-investigations.store') }}" method="POST" class="space-y-6">
        @csrf
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 space-y-4">
            <h3 class="text-sm font-semibold text-gray-900">1. IDENTITAS</h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div><label class="block text-sm font-medium text-gray-700 mb-1">Nama <span class="text-red-500">*</span></label><input type="text" name="nama" required class="w-full px-4 py-2.5 text-sm border border-gray-200 rounded-lg"></div>
                <div><label class="block text-sm font-medium text-gray-700 mb-1">NIP</label><input type="text" name="nip" class="w-full px-4 py-2.5 text-sm border border-gray-200 rounded-lg"></div>
                <div><label class="block text-sm font-medium text-gray-700 mb-1">Bagian</label><input type="text" name="bagian" class="w-full px-4 py-2.5 text-sm border border-gray-200 rounded-lg"></div>
                <div><label class="block text-sm font-medium text-gray-700 mb-1">Jabatan</label><input type="text" name="jabatan" class="w-full px-4 py-2.5 text-sm border border-gray-200 rounded-lg"></div>
                <div><label class="block text-sm font-medium text-gray-700 mb-1">Jenis Kelamin</label><select name="jenis_kelamin" class="w-full px-4 py-2.5 text-sm border border-gray-200 rounded-lg"><option value="">Pilih</option><option value="Laki-laki">Laki-laki</option><option value="Perempuan">Perempuan</option></select></div>
                <div><label class="block text-sm font-medium text-gray-700 mb-1">Lama Bekerja</label><input type="text" name="lama_bekerja" class="w-full px-4 py-2.5 text-sm border border-gray-200 rounded-lg"></div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 space-y-4">
            <h3 class="text-sm font-semibold text-gray-900">2. ANAMNESIA</h3>
            <div><label class="block text-sm font-medium text-gray-700 mb-1">Keluhan</label><textarea name="keluhan" rows="3" class="w-full px-4 py-2.5 text-sm border border-gray-200 rounded-lg"></textarea></div>
            <div><label class="block text-sm font-medium text-gray-700 mb-1">Riwayat Penyakit</label><textarea name="riwayat_penyakit" rows="2" class="w-full px-4 py-2.5 text-sm border border-gray-200 rounded-lg"></textarea></div>
            <div><label class="block text-sm font-medium text-gray-700 mb-1">Riwayat Penyakit Keluarga</label><textarea name="riwayat_penyakit_keluarga" rows="2" class="w-full px-4 py-2.5 text-sm border border-gray-200 rounded-lg"></textarea></div>
            <div><label class="block text-sm font-medium text-gray-700 mb-1">Riwayat Pekerjaan</label><textarea name="riwayat_pekerjaan" rows="3" class="w-full px-4 py-2.5 text-sm border border-gray-200 rounded-lg"></textarea></div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 space-y-4">
            <h3 class="text-sm font-semibold text-gray-900">3-9. HASIL PEMERIKSAAN</h3>
            <div><label class="block text-sm font-medium text-gray-700 mb-1">3. Hasil Pemeriksaan Fisik</label><textarea name="pemeriksaan_fisik" rows="2" class="w-full px-4 py-2.5 text-sm border border-gray-200 rounded-lg"></textarea></div>
            <div><label class="block text-sm font-medium text-gray-700 mb-1">4. Hasil Pemeriksaan Radiologi</label><textarea name="pemeriksaan_radiologi" rows="2" class="w-full px-4 py-2.5 text-sm border border-gray-200 rounded-lg"></textarea></div>
            <div><label class="block text-sm font-medium text-gray-700 mb-1">5. Hasil Pemeriksaan Laboratorium</label><textarea name="pemeriksaan_laboratorium" rows="2" class="w-full px-4 py-2.5 text-sm border border-gray-200 rounded-lg"></textarea></div>
            <div><label class="block text-sm font-medium text-gray-700 mb-1">6. Pemeriksaan Penunjang Termasuk Biological Monitoring</label><textarea name="pemeriksaan_penunjang" rows="2" class="w-full px-4 py-2.5 text-sm border border-gray-200 rounded-lg"></textarea></div>
            <div><label class="block text-sm font-medium text-gray-700 mb-1">7. Hasil Pemeriksaan Patologi Anatomi</label><textarea name="pemeriksaan_patologi_anatomi" rows="2" class="w-full px-4 py-2.5 text-sm border border-gray-200 rounded-lg"></textarea></div>
            <div><label class="block text-sm font-medium text-gray-700 mb-1">8. Perbandingan dengan Hasil Pemeriksaan Awal</label><textarea name="perbandingan_pemeriksaan_awal" rows="2" class="w-full px-4 py-2.5 text-sm border border-gray-200 rounded-lg"></textarea></div>
            <div><label class="block text-sm font-medium text-gray-700 mb-1">9. Perbandingan dengan Hasil Pemeriksaan Berkala/Khusus</label><textarea name="perbandingan_pemeriksaan_berkala" rows="2" class="w-full px-4 py-2.5 text-sm border border-gray-200 rounded-lg"></textarea></div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div><label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Pemeriksaan</label><input type="date" name="tanggal_pemeriksaan" class="w-full px-4 py-2.5 text-sm border border-gray-200 rounded-lg"></div>
                <div><label class="block text-sm font-medium text-gray-700 mb-1">Diperiksa Oleh</label><input type="text" name="diperiksa_oleh" class="w-full px-4 py-2.5 text-sm border border-gray-200 rounded-lg"></div>
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-3">
            <button type="submit" class="px-6 py-2.5 bg-green-600 text-white text-sm font-medium rounded-lg">Simpan</button>
            <button type="submit" name="action" value="save_another" class="px-6 py-2.5 border border-green-600 text-green-700 bg-white text-sm font-medium rounded-lg">Simpan &amp; Buat Ulang</button>
            <a href="{{ route('occupational-disease-investigations.index') }}" class="px-6 py-2.5 bg-gray-100 text-gray-700 text-sm font-medium rounded-lg">Batal</a>
        </div>
    </form>
</div>
@endsection
