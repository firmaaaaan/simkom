@extends('layouts.app')

@section('title', 'Import Analisis Keselamatan Pekerjaan')
@section('header', 'Import Analisis Keselamatan Pekerjaan (JSA)')

@section('content')
@include('partials.k3-import-page', [
    'title' => 'Formulir JSA',
    'subtitle' => 'Upload file Excel (.xlsx, .xls) dua sheet untuk mengimport formulir JSA secara batch. Sheet "Langkah Pekerjaan" merujuk Kode JSA pada sheet "JSA" dan mengganti seluruh langkah JSA terkait. Isi kolom Kode agar langkah dapat tertaut.',
    'indexRoute' => 'analisis-keselamatan-pekerjaan.index',
    'templateRoute' => 'analisis-keselamatan-pekerjaan.template',
    'storeRoute' => 'analisis-keselamatan-pekerjaan.store-import',
    'accept' => '.xlsx,.xls',
    'formatLines' => [
        ['sheet' => 'JSA', 'columns' => 'Kode, Nama Pekerja, Nama Pekerjaan, Tanggal Pekerjaan, Tempat, APD (label dipisah koma, mis. "Helm, Sarung Tangan"), APD Lain'],
        ['sheet' => 'Langkah Pekerjaan', 'columns' => 'Kode JSA, Urutan Langkah Pekerjaan, Identifikasi Bahaya, Pengendalian, Penanggung Jawab'],
    ],
])
@endsection
