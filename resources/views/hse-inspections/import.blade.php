@extends('layouts.app')

@section('title', 'Import Inspeksi HSE')
@section('header', 'Import Inspeksi HSE')

@section('content')
@include('partials.k3-import-page', [
    'title' => 'Inspeksi HSE',
    'subtitle' => 'Upload file Excel (.xlsx, .xls) dua sheet untuk mengimport inspeksi HSE secara batch. Sheet "Temuan" merujuk Kode HSE pada sheet "HSE" dan mengganti seluruh temuan inspeksi terkait.',
    'indexRoute' => 'hse-inspections.index',
    'templateRoute' => 'hse-inspections.template',
    'storeRoute' => 'hse-inspections.store-import',
    'accept' => '.xlsx,.xls',
    'formatLines' => [
        ['sheet' => 'HSE', 'columns' => 'Kode, Lokasi, Diperiksa Oleh, Tanggal Inspeksi'],
        ['sheet' => 'Temuan', 'columns' => 'Kode HSE, Lokasi, Temuan, Unsafe Condition (Ya/Tidak), Unsafe Action (Ya/Tidak), Tanggal Kejadian, Tindakan Perbaikan, Tanggal Perbaikan, PIC'],
    ],
])
@endsection
