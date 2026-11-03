@extends('layouts.app')
@section('title','Tambah Limbah B3')
@section('header','Tambah Limbah B3')
@section('content')
<form method="POST" action="{{ route('k3.b3.store') }}" class="bg-white rounded-xl border p-6 space-y-4">
@csrf
<div><label class="block text-sm font-medium">Laboratorium</label><select name="laboratory_id" class="w-full border rounded-lg p-2"><option value="">-</option>@foreach($laboratories as $lab)<option value="{{ $lab->id }}">{{ $lab->name }}</option>@endforeach</select></div>
<div><label class="block text-sm font-medium">Nama Limbah</label><input name="waste_name" class="w-full border rounded-lg p-2" required></div>
<div><label class="block text-sm font-medium">Jenis</label><input name="waste_type" class="w-full border rounded-lg p-2"></div>
<div><label class="block text-sm font-medium">Sumber</label><input name="source" class="w-full border rounded-lg p-2"></div>
<div class="grid grid-cols-2 gap-3"><div><label class="block text-sm font-medium">Jumlah</label><input type="number" step="0.01" name="quantity" class="w-full border rounded-lg p-2"></div><div><label class="block text-sm font-medium">Satuan</label><input name="unit" class="w-full border rounded-lg p-2"></div></div>
<div><label class="block text-sm font-medium">Penyimpanan</label><textarea name="storage_method" class="w-full border rounded-lg p-2"></textarea></div>
<div><label class="block text-sm font-medium">Pembuangan</label><textarea name="disposal_method" class="w-full border rounded-lg p-2"></textarea></div>
<div><label class="block text-sm font-medium">Tanggal Pembuangan</label><input type="date" name="disposal_date" class="w-full border rounded-lg p-2"></div>
<div><label class="block text-sm font-medium">PIC</label><input name="pic" class="w-full border rounded-lg p-2"></div>
<div><label class="block text-sm font-medium">Catatan</label><textarea name="notes" class="w-full border rounded-lg p-2"></textarea></div>
<div class="flex gap-2"><button class="px-4 py-2 bg-green-600 text-white rounded-lg">Simpan</button><a href="{{ route('k3.b3.index') }}" class="px-4 py-2 border rounded-lg">Batal</a></div>
</form>
@endsection