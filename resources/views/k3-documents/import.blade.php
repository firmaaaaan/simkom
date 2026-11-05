@extends('layouts.app')

@section('title', 'Import Dokumen K3')
@section('header', 'Import Dokumen K3 Laboratorium')

@section('content')
@include('partials.k3-import-page', [
    'title' => 'Dokumen K3',
    'subtitle' => 'Upload file Excel (.xlsx, .xls) untuk mengimport metadata dokumen K3 secara batch. Berkas fisik tidak ikut diimport — unggah melalui halaman Ubah dokumen setelahnya. Kolom Kode kosong akan membuat data baru; kode yang sudah ada akan diperbarui.',
    'indexRoute' => 'k3-documents.index',
    'templateRoute' => 'k3-documents.template',
    'storeRoute' => 'k3-documents.store-import',
    'accept' => '.xlsx,.xls',
    'formatLines' => [
        ['sheet' => 'Dokumen', 'columns' => 'Kode, Judul, Kategori (form/sop/peraturan/pelatihan/laporan/lain-lain), Nomor Dokumen, Tanggal Berlaku, Tanggal Revisi, Keterangan'],
    ],
])
@endsection
