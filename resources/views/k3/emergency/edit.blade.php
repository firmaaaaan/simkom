@extends('layouts.app')
@section('title','Edit Tanggap Darurat')
@section('header','Edit Tanggap Darurat')
@section('content')
<form method="POST" action="{{ route('k3.emergency.update',$emergency) }}" class="bg-white rounded-xl border p-6 space-y-4">
@csrf @method('PUT')
<div><label class="block text-sm font-medium">Laboratorium</label><select name="laboratory_id" class="w-full border rounded-lg p-2"><option value="">-</option>@foreach($laboratories as $lab)<option value="{{ $lab->id }}" @selected($emergency->laboratory_id==$lab->id)>{{ $lab->name }}</option>@endforeach</select></div>
<div><label class="block text-sm font-medium">Skenario</label><input name="scenario" value="{{ $emergency->scenario }}" class="w-full border rounded-lg p-2" required></div>
<div><label class="block text-sm font-medium">Prosedur</label><textarea name="procedure" class="w-full border rounded-lg p-2">{{ $emergency->procedure }}</textarea></div>
<div><label class="block text-sm font-medium">Penanggung Jawab</label><input name="responsible_person" value="{{ $emergency->responsible_person }}" class="w-full border rounded-lg p-2"></div>
<div><label class="block text-sm font-medium">Kontak</label><input name="contact" value="{{ $emergency->contact }}" class="w-full border rounded-lg p-2"></div>
<div><label class="block text-sm font-medium">Peralatan</label><textarea name="equipment" class="w-full border rounded-lg p-2">{{ $emergency->equipment }}</textarea></div>
<div><label class="block text-sm font-medium">Tanggal Latihan</label><input type="date" name="drill_date" value="{{ $emergency->drill_date?->format('Y-m-d') }}" class="w-full border rounded-lg p-2"></div>
<div><label class="block text-sm font-medium">Catatan</label><textarea name="notes" class="w-full border rounded-lg p-2">{{ $emergency->notes }}</textarea></div>
<div class="flex gap-2"><button class="px-4 py-2 bg-green-600 text-white rounded-lg">Simpan</button><a href="{{ route('k3.emergency.index') }}" class="px-4 py-2 border rounded-lg">Batal</a></div>
</form>
@endsection