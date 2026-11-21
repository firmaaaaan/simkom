@extends('layouts.app')

@section('title', 'Detail Alat')
@section('header', 'Detail Alat Laboratorium')

@section('content')
<div class="max-w-2xl space-y-4">
    <div class="bg-white rounded-xl border border-gray-200 p-6 space-y-3">
        <div><span class="font-semibold">Nama Alat:</span> {{ $alat->name }}</div>
        <div><span class="font-semibold">No Inventaris:</span> {{ $alat->code ?? '-' }}</div>
        <div><span class="font-semibold">Merk/Type:</span> {{ $alat->merk_type ?? '-' }}</div>
        <div><span class="font-semibold">Laboratorium:</span> {{ $alat->laboratory->name ?? '-' }}</div>
        <div><span class="font-semibold">Lokasi:</span> {{ $alat->location ?? '-' }}</div>
        <div><span class="font-semibold">Status:</span> {{ $alat->status ?? '-' }}</div>
        <div><span class="font-semibold">Keterangan:</span> {{ $alat->description ?? '-' }}</div>
        <div class="pt-2 flex flex-wrap gap-3">
            <a href="{{ route('alat.checks', $alat) }}" class="text-green-600 hover:underline">Kartu Kendali</a>
            <a href="{{ route('alat.edit', $alat) }}" class="text-blue-600 hover:underline">Ubah</a>
            <a href="{{ route('alat.labels', ['laboratory_id' => $alat->laboratory_id]) }}" class="text-emerald-600 hover:underline">Cetak Label</a>
            <a href="{{ route('alat.index') }}" class="text-gray-600 hover:underline">Kembali</a>
        </div>
    </div>

    @canany(['edit-alat','edit-kartu-alat','edit-alat-laboratorium','edit-equipment','edit-kartu-kendali-alat'])
    <div class="bg-white rounded-xl border border-amber-200 p-6 space-y-3">
        <h2 class="text-base font-semibold text-gray-800">Pindah Laboratorium</h2>
        <p class="text-sm text-gray-500">
            Pindahkan alat ke laboratorium lain. Lokasi tetap menggunakan default dari SIMPTT Inventaris. No Inventaris dan QR code yang sudah tercetak <span class="font-semibold">tetap sama</span>.
        </p>
        <form action="{{ route('alat.pindah', $alat) }}" method="POST" class="space-y-3">
            @csrf
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Laboratorium Baru/Tujuan <span class="text-red-500">*</span></label>
                <select name="laboratory_id" class="w-full rounded-lg border-gray-300 border px-3 py-2 text-sm">
                    @foreach(\App\Models\Laboratory::orderBy('name')->get() as $lab)
                        <option value="{{ $lab->id }}" {{ $alat->laboratory_id == $lab->id ? 'selected' : '' }}>{{ $lab->name }}</option>
                    @endforeach
                </select>
                @error('laboratory_id') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
            <button type="submit" class="px-4 py-2 bg-amber-600 text-white text-sm font-medium rounded-lg hover:bg-amber-700">Pindahkan</button>
        </form>
    </div>
    @endcanany
</div>
@endsection