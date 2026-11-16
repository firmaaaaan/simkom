@extends('layouts.app')

@section('title', 'Detail Alat')
@section('header', 'Detail Alat Laboratorium')

@section('content')
<div class="max-w-2xl bg-white rounded-xl border border-gray-200 p-6 space-y-3">
    <div><span class="font-semibold">Nama Alat:</span> {{ $alat->name }}</div>
    <div><span class="font-semibold">No Inventaris:</span> {{ $alat->code ?? '-' }}</div>
    <div><span class="font-semibold">Merk/Type:</span> {{ $alat->merk_type ?? '-' }}</div>
    <div><span class="font-semibold">Laboratorium:</span> {{ $alat->laboratory->name ?? '-' }}</div>
    <div><span class="font-semibold">Lokasi:</span> {{ $alat->location ?? '-' }}</div>
    <div><span class="font-semibold">Status:</span> {{ $alat->status ?? '-' }}</div>
    <div><span class="font-semibold">Keterangan:</span> {{ $alat->description ?? '-' }}</div>
    <div class="pt-2">
        <a href="{{ route('alat.checks', $alat) }}" class="text-green-600 hover:underline">Kartu Kendali</a>
        <a href="{{ route('alat.edit', $alat) }}" class="ml-3 text-blue-600 hover:underline">Ubah</a>
        <a href="{{ route('alat.index') }}" class="ml-3 text-gray-600 hover:underline">Kembali</a>
    </div>
</div>
@endsection
