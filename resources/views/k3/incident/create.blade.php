@extends('layouts.app')
@section('title','Tambah Laporan Insiden')
@section('header','Tambah Laporan Insiden')
@section('content')
<form method="POST" action="{{ route('k3.incident.store') }}" class="bg-white rounded-xl border p-6 space-y-4">
@csrf
<div><label class="block text-sm font-medium">Laboratorium</label><select name="laboratory_id" class="w-full border rounded-lg p-2"><option value="">-</option>@foreach($laboratories as $lab)<option value="{{ $lab->id }}">{{ $lab->name }}</option>@endforeach</select></div>
<div><label class="block text-sm font-medium">Tanggal</label><input type="date" name="incident_date" class="w-full border rounded-lg p-2" required></div>
<div><label class="block text-sm font-medium">Pelapor</label><input name="reporter_name" class="w-full border rounded-lg p-2"></div>
<div><label class="block text-sm font-medium">Jenis Insiden</label><input name="incident_type" class="w-full border rounded-lg p-2"></div>
<div><label class="block text-sm font-medium">Deskripsi</label><textarea name="description" class="w-full border rounded-lg p-2"></textarea></div>
<div><label class="block text-sm font-medium">Dampak</label><textarea name="impact" class="w-full border rounded-lg p-2"></textarea></div>
<div><label class="block text-sm font-medium">Tindakan Segera</label><textarea name="immediate_action" class="w-full border rounded-lg p-2"></textarea></div>
<div><label class="block text-sm font-medium">Tindak Lanjut</label><textarea name="follow_up" class="w-full border rounded-lg p-2"></textarea></div>
<div><label class="block text-sm font-medium">Status</label><input name="status" value="Open" class="w-full border rounded-lg p-2"></div>
<div class="flex gap-2"><button class="px-4 py-2 bg-green-600 text-white rounded-lg">Simpan</button><a href="{{ route('k3.incident.index') }}" class="px-4 py-2 border rounded-lg">Batal</a></div>
</form>
@endsection