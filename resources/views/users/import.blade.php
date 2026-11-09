@extends('layouts.app')

@section('title', 'Import User')
@section('header', 'Import User')

@section('content')
@include('partials.k3-import-page', [
    'title' => 'Pengguna',
    'subtitle' => 'Upload file Excel (.xlsx, .xls, .csv) untuk mengimpor pengguna secara batch. Pengguna dicocokkan berdasarkan Email: jika email sudah ada, data Nama/Role diperbarui (Password hanya diubah jika kolom Password diisi); jika email belum ada, pengguna baru dibuat.',
    'indexRoute' => 'users.index',
    'templateRoute' => 'users.template',
    'storeRoute' => 'users.store-import',
    'accept' => '.xlsx,.xls,.csv',
    'formatLines' => [
        ['sheet' => 'Pengguna', 'columns' => 'Nama, Email, Password, Role (nama/label role, wajib; Password wajib untuk pengguna baru, minimal 8 karakter)'],
    ],
])
@endsection
