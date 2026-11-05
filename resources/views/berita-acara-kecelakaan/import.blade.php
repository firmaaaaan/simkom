@extends('layouts.app')

@section('title', 'Import Berita Acara Kecelakaan')
@section('header', 'Import Berita Acara Kecelakaan')

@section('content')
@include('partials.k3-import-page', [
    'title' => 'Berita Acara Kecelakaan',
    'subtitle' => 'Upload file Excel (.xlsx, .xls) untuk mengimport berita acara kecelakaan secara batch. Kolom Kode kosong akan membuat data baru; kode yang sudah ada akan diperbarui. Minimal satu kolom Uraian Kejadian wajib diisi.',
    'indexRoute' => 'berita-acara-kecelakaan.index',
    'templateRoute' => 'berita-acara-kecelakaan.template',
    'storeRoute' => 'berita-acara-kecelakaan.store-import',
    'accept' => '.xlsx,.xls',
    'formatLines' => [
        ['sheet' => 'Berita Acara', 'columns' => 'Kode, Tanggal, Nama Pelapor, Jabatan Pelapor, Nama Korban, Alamat Korban, Tempat Tanggal Lahir, Unit/Status, Uraian Kejadian 1-3, Dampak, Saksi I, Saksi II'],
    ],
])
@endsection
