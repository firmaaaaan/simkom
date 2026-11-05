@extends('layouts.app')

@section('title', 'Import Laporan Insiden K3')
@section('header', 'Import Laporan Insiden K3')

@section('content')
@include('partials.k3-import-page', [
    'title' => 'Laporan Insiden K3',
    'subtitle' => 'Upload file Excel (.xlsx, .xls) untuk mengimport laporan insiden K3 secara batch. Kolom Kode kosong akan membuat data baru; kode yang sudah ada akan diperbarui. Kolom gambar diabaikan (unggah manual).',
    'indexRoute' => 'incident-reports.index',
    'templateRoute' => 'incident-reports.template',
    'storeRoute' => 'incident-reports.store-import',
    'accept' => '.xlsx,.xls',
    'formatLines' => [
        ['sheet' => 'Laporan Insiden', 'columns' => 'Kode, Tanggal Kejadian, Waktu Kejadian (HH:MM), Jenis Kejadian (Kecelakaan/Insiden/Nearmiss/Duga Bahaya), Lokasi, Nama Korban, Jabatan, Asal Institusi, Sebab, Riwayat, Saksi, Akibat, Likelihood (1-5), Impact (1-5), Pelapor'],
    ],
])
@endsection
