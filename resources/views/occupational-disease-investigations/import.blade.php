@extends('layouts.app')

@section('title', 'Import Penyelidikan PAK')
@section('header', 'Import Penyelidikan Penyakit Akibat Kerja')

@section('content')
@include('partials.k3-import-page', [
    'title' => 'Penyelidikan PAK',
    'subtitle' => 'Upload file Excel (.xlsx, .xls) untuk mengimport penyelidikan penyakit akibat kerja (Form 008) secara batch. Kolom Kode kosong akan membuat data baru; kode yang sudah ada akan diperbarui.',
    'indexRoute' => 'occupational-disease-investigations.index',
    'templateRoute' => 'occupational-disease-investigations.template',
    'storeRoute' => 'occupational-disease-investigations.store-import',
    'accept' => '.xlsx,.xls',
    'formatLines' => [
        ['sheet' => 'Penyelidikan', 'columns' => 'Kode, Nama, NIP, Bagian, Jabatan, Jenis Kelamin (Laki-laki/Perempuan), Lama Bekerja, Keluhan, Riwayat Penyakit, Riwayat Penyakit Keluarga, Riwayat Pekerjaan, Pemeriksaan Fisik, Pemeriksaan Radiologi, Pemeriksaan Laboratorium, Pemeriksaan Penunjang, Pemeriksaan Patologi Anatomi, Perbandingan Pemeriksaan Awal, Perbandingan Pemeriksaan Berkala, Tanggal Pemeriksaan, Diperiksa Oleh'],
    ],
])
@endsection
