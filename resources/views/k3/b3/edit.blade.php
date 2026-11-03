@extends('layouts.app')
@section('title','Edit Limbah B3')
@section('header','Edit Limbah B3')
@section('content')
<form method="POST" action="{{ route('k3.b3.update',$b3Waste) }}" class="bg-white rounded-xl border p-6 space-y-4">
@csrf @method('PUT')
<div><label class="block text-sm font-medium">Laboratorium</label><select name="laboratory_id" class="w-full border rounded-lg p-2"><option value="">-</option>@foreach($laboratories as $lab)<option value="{{ $lab->id }}" @selected($b3Waste->laboratory_id==$lab->id)>{{ $lab->name }}</option>@endforeach</select></div>
<div><label class="block text-sm font-medium">Nama Limbah</label><input name="waste_name" value="{{ $b3Waste->waste_name }}" class="w-full border rounded-lg p-2" required></div>
<div><label class="block text-sm font-medium">Jenis</label><input name="waste_type" value="{{ $b3Waste->waste_type }}" class="w-full border rounded-lg p-2"></div>
<div><label class="block text-sm font-medium">Sumber</label><input name="source" value="{{ $b3Waste->source }}" class="w-full border rounded-lg p-2"></div>
<div class="grid grid-cols-2 gap-3"><div><label class="block text-sm font-medium">Jumlah</label><input type="number" step="0.01" name="quantity" value="{{ $b3Waste->quantity }}" class="w-full border rounded-lg p-2"></div><div><label class="block text-sm font-medium">Satuan</label><input name="unit" value="{{ $b3Waste->unit }}" class="w-full border rounded-lg p-2"></div></div>
<div><label class="block text-sm font-medium">Penyimpanan</label><textarea name="storage_method" class="w-full border rounded-lg p-2">{{ $b3Waste->storage_method }}</textarea></div>
<div><label class="block text-sm font-medium">Pembuangan</label><textarea name="disposal_method" class="w-full border rounded-lg p-2">{{ $b3Waste->disposal_method }}</textarea></div>
<div><label class="block text-sm font-medium">Tanggal Pembuangan</label><input type="date" name="disposal_date" value="{{ optional($b3Waste->disposal_date)->format('Y-m-d') }}" class="w-full border rounded-lg p-2"></div>
<div><label class="block text-sm font-medium">PIC</label><input name="pic" value="{{ $b3Waste->pic }}" class="w-full border rounded-lg p-2"></div>
<div><label class="block text-sm font-medium">Catatan</label><textarea name="notes" class="w-full border rounded-lg p-2">{{ $b3Waste->notes }}</textarea></div>
<div class="flex gap-2"><button class="px-4 py-2 bg-green-600 text-white rounded-lg">Simpan</button><a href="{{ route('k3.b3.index') }}" class="px-4 py-2 border rounded-lg">Batal</a></div>
</form>
@endsection