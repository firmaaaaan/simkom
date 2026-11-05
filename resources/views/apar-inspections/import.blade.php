@extends('layouts.app')

@section('title', 'Import Lembar Periksa APAR')
@section('header', 'Import Lembar Periksa APAR')

@section('content')
@include('partials.k3-import-page', [
    'title' => 'Lembar Periksa APAR',
    'subtitle' => 'Upload file Excel (.xlsx, .xls) untuk mengimport pemeriksaan APAR secara batch. Kolom Kode kosong akan membuat data baru; kode yang sudah ada akan diperbarui. Kolom item wajib diisi Baik atau Tidak.',
    'indexRoute' => 'apar-inspections.index',
    'templateRoute' => 'apar-inspections.template',
    'storeRoute' => 'apar-inspections.store-import',
    'accept' => '.xlsx,.xls',
    'formatLines' => [
        ['sheet' => 'Lembar Periksa', 'columns' => 'Kode, No. Registrasi, Tanggal Inspeksi, Lokasi, Diperiksa Oleh, lalu per item: Kawat Segel, Ket. Kawat Segel, Pin, Ket. Pin, Penunjuk Tekanan, Ket. Penunjuk Tekanan, Kondisi Catridge, Ket. Kondisi Catridge, Tepung Kimia, Ket. Tepung Kimia, Kondisi Selang, Ket. Kondisi Selang, Kondisi Botol, Ket. Kondisi Botol'],
    ],
])
@endsection
