@extends('layouts.app')

@section('title', 'Import Laporan Inspeksi K3')
@section('header', 'Import Laporan Inspeksi K3')

@section('content')
@include('partials.k3-import-page', [
    'title' => 'Laporan Inspeksi K3',
    'subtitle' => 'Upload file Excel (.xlsx, .xls) tiga sheet untuk mengimport laporan inspeksi K3 (Form 05) secara batch. Sheet "Checklist" dan "Temuan" merujuk Kode Laporan pada sheet "Laporan". Item checklist yang tidak diisi memakai nilai default.',
    'indexRoute' => 'k3-inspection-reports.index',
    'templateRoute' => 'k3-inspection-reports.template',
    'storeRoute' => 'k3-inspection-reports.store-import',
    'accept' => '.xlsx,.xls',
    'formatLines' => [
        ['sheet' => 'Laporan', 'columns' => 'Kode, Hari, Tanggal BA, Bulan BA, Tahun BA, Bulan Inspeksi, Lokasi, Pemeriksa Nama, Pemeriksa Jabatan'],
        ['sheet' => 'Checklist', 'columns' => 'Kode Laporan, No Item (1-33), Hasil (Ya/Tidak/NA), Catatan'],
        ['sheet' => 'Temuan', 'columns' => 'Kode Laporan, Temuan, Dokumentasi, Saran Perbaikan, Pelaksana, Target, Catatan'],
    ],
])
@endsection
