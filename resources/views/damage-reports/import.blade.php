@extends('layouts.app')

@section('title', 'Import Laporan Kerusakan')
@section('header', 'Import Laporan Kerusakan/KEH/Pencurian')

@section('content')
@include('partials.k3-import-page', [
    'title' => 'Laporan Kerusakan',
    'subtitle' => 'Upload file Excel (.xlsx, .xls) dua sheet untuk mengimport laporan kerusakan/kehilangan/pencurian (Form 007) secara batch. Sheet "Peralatan" merujuk Kode Laporan pada sheet "Laporan" dan mengganti daftar peralatan laporan terkait.',
    'indexRoute' => 'damage-reports.index',
    'templateRoute' => 'damage-reports.template',
    'storeRoute' => 'damage-reports.store-import',
    'accept' => '.xlsx,.xls',
    'formatLines' => [
        ['sheet' => 'Laporan', 'columns' => 'Kode, Nama Pelapor, Ruangan, Telepon, Tanggal Insiden, Waktu Kejadian (HH:MM), Tanggal Dilaporkan, Waktu Dilaporkan, Lokasi Spesifik, Jenis Kejadian (Kerusakan/Kehilangan/Pencurian), Deskripsi Kejadian, Perkiraan Biaya, Penanggung Jawab, Dilaporkan ke Polisi (Ya/Tidak), Berkas Polisi, Petugas Polisi, Unit Kepolisian, Telepon Polisi'],
        ['sheet' => 'Peralatan', 'columns' => 'Kode Laporan, Nama Peralatan, Merk, No Inventaris, Jumlah, Keterangan'],
    ],
])
@endsection
